<?php
/**
 * Система миграций для базы данных
 */
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/database/Database.php';

class Migrations
{
    private $db;
    private $tables = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Определение структуры таблиц
        $this->defineTables();
    }

    /**
     * Определение структуры таблиц
     */
    private function defineTables()
    {
        // Таблица пользователей
        $this->tables['users'] = "
            CREATE TABLE IF NOT EXISTS users (
                id_users INT AUTO_INCREMENT PRIMARY KEY,
                FIO VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                phone VARCHAR(50) NOT NULL,
                password VARCHAR(255) NOT NULL,
                room_area FLOAT NULL,
                is_admin TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица заявок
        $this->tables['application'] = "
            CREATE TABLE IF NOT EXISTS application (
                id_application INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                room_area FLOAT NOT NULL,
                phone VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица типов уборки
        $this->tables['type_cleaning'] = "
            CREATE TABLE IF NOT EXISTS type_cleaning (
                id_type_cleaning INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица услуг
        $this->tables['services'] = "
            CREATE TABLE IF NOT EXISTS services (
                id_services INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                comment TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица заказов
        $this->tables['orders'] = "
            CREATE TABLE IF NOT EXISTS orders (
                id_orders INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                email VARCHAR(255) NOT NULL,
                id_type_cleaning INT NOT NULL,
                room_area FLOAT NOT NULL,
                address TEXT NOT NULL,
                date DATE NOT NULL,
                time TIME NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                status TINYINT(1) DEFAULT 1 COMMENT '0 - Отменен, 1 - Создан, 2 - Уборка, 3 - Успешно',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_type_cleaning) REFERENCES type_cleaning(id_type_cleaning) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица связи заказов и доп. услуг
        $this->tables['orders_services'] = "
            CREATE TABLE IF NOT EXISTS orders_services (
                id_orders_services INT AUTO_INCREMENT PRIMARY KEY,
                id_orders INT NOT NULL,
                id_services INT NOT NULL,
                FOREIGN KEY (id_orders) REFERENCES orders(id_orders) ON DELETE CASCADE,
                FOREIGN KEY (id_services) REFERENCES services(id_services) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица cleaning
        $this->tables['cleaning'] = "
            CREATE TABLE IF NOT EXISTS cleaning (
                id_cleaning INT AUTO_INCREMENT PRIMARY KEY,
                id_type_cleaning INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                image_link VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_type_cleaning) REFERENCES type_cleaning(id_type_cleaning) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица статей
        $this->tables['article'] = "
            CREATE TABLE IF NOT EXISTS article (
                id_article INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                text TEXT NOT NULL,
                image_link VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица отзывов
        $this->tables['reviews'] = "
            CREATE TABLE IF NOT EXISTS reviews (
                id_reviews INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                text TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица для отслеживания миграций
        $this->tables['migrations'] = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration_name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
    }

    /**
     * Выполнение миграций
     */
    public function migrate()
    {
        // Создание таблиц в правильном порядке
        $tableOrder = [
            'migrations', 'users', 'type_cleaning', 'services', 
            'cleaning', 'orders', 'orders_services', 'application',
            'article', 'reviews'
        ];

        $conn = $this->db->getConnection();
        
        // Начало транзакции
        $conn->beginTransaction();

        try {
            // Создание таблиц в правильном порядке
            foreach ($tableOrder as $tableName) {
                if (isset($this->tables[$tableName])) {
                    $conn->exec($this->tables[$tableName]);
                    echo "Таблица '$tableName' создана или уже существует.<br>";
                }
            }
            
            // Создание администратора по умолчанию
            $adminExists = $this->db->fetch("SELECT COUNT(*) as count FROM users WHERE is_admin = 1", []);
            if (!$adminExists || $adminExists['count'] == 0) {
                $password = password_hash('admin123', PASSWORD_DEFAULT);
                $this->db->insert('users', [
                    'FIO' => 'Администратор',
                    'email' => 'admin@example.com',
                    'phone' => '+7 (999) 999-99-99',
                    'password' => $password,
                    'is_admin' => 1
                ]);
                echo "Администратор по умолчанию создан.<br>";
            }
            
            // Фиксация транзакции
            $conn->commit();
            echo "Миграции успешно применены.<br>";
        } catch (PDOException $e) {
            // Откат транзакции в случае ошибки
            $conn->rollBack();
            echo "Ошибка при выполнении миграций: " . $e->getMessage() . "<br>";
        }
    }

    /**
     * Сброс (удаление) всех таблиц
     */
    public function reset()
    {
        $conn = $this->db->getConnection();
        
        // Отключение внешних ключей
        $conn->exec('SET FOREIGN_KEY_CHECKS = 0');
        
        // Получение списка всех таблиц
        $tables = $this->db->fetchAll("SHOW TABLES", []);
        
        // Удаление таблиц
        foreach ($tables as $table) {
            $tableName = reset($table);
            $conn->exec("DROP TABLE IF EXISTS `$tableName`");
            echo "Таблица '$tableName' удалена.<br>";
        }
        
        // Включение внешних ключей
        $conn->exec('SET FOREIGN_KEY_CHECKS = 1');
        
        echo "Все таблицы были удалены.<br>";
    }
}

// Проверка, запущен ли скрипт из консоли или через браузер
if (php_sapi_name() === 'cli') {
    // Запуск из консоли
    $migrations = new Migrations();
    
    if (isset($argv[1])) {
        switch ($argv[1]) {
            case 'migrate':
                $migrations->migrate();
                break;
            case 'reset':
                $migrations->reset();
                break;
            default:
                echo "Доступные команды: migrate, reset\n";
                break;
        }
    } else {
        echo "Доступные команды: migrate, reset\n";
    }
} else {
    // Запуск через браузер
    if (isset($_GET['action'])) {
        $migrations = new Migrations();
        
        switch ($_GET['action']) {
            case 'migrate':
                $migrations->migrate();
                break;
            case 'reset':
                $migrations->reset();
                break;
            default:
                echo "Доступные действия: migrate, reset<br>";
                break;
        }
    } else {
        echo "<h1>Система миграций</h1>";
        echo "<p>Выберите действие:</p>";
        echo "<ul>";
        echo "<li><a href='?action=migrate'>Применить миграции</a></li>";
        echo "<li><a href='?action=reset'>Сбросить базу данных</a></li>";
        echo "</ul>";
    }
} 