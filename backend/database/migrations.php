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
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                is_admin TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица категорий
        $this->tables['category'] = "
            CREATE TABLE IF NOT EXISTS category (
                id_category INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                capacity INT NOT NULL,
                description TEXT NOT NULL,
                prev_text TEXT NOT NULL,
                image_link VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица домов
        $this->tables['home'] = "
            CREATE TABLE IF NOT EXISTS home (
                id_home INT AUTO_INCREMENT PRIMARY KEY,
                id_category INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                capacity INT NOT NULL,
                description TEXT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_category) REFERENCES category(id_category) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица изображений домов
        $this->tables['home_images'] = "
            CREATE TABLE IF NOT EXISTS home_images (
                id_home_images INT AUTO_INCREMENT PRIMARY KEY,
                id_home INT NOT NULL,
                image_link VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (id_home) REFERENCES home(id_home) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица услуг
        $this->tables['services'] = "
            CREATE TABLE IF NOT EXISTS services (
                id_services INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица развлечений
        $this->tables['gaiety'] = "
            CREATE TABLE IF NOT EXISTS gaiety (
                id_gaiety INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                image_link VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица акций
        $this->tables['promotion'] = "
            CREATE TABLE IF NOT EXISTS promotion (
                id_promotion INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                image_link VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";

        // Таблица бронирований
        $this->tables['reservation'] = "
            CREATE TABLE IF NOT EXISTS reservation (
                id_reservation INT AUTO_INCREMENT PRIMARY KEY,
                id_home INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                date_enter DATE NOT NULL,
                date_back DATE NOT NULL,
                count_old INT NOT NULL,
                count_child INT NOT NULL,
                status TINYINT(1) DEFAULT 0 COMMENT '0 - новая, 1 - подтверждена, 2 - отменена',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_home) REFERENCES home(id_home) ON DELETE CASCADE
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
    }

    /**
     * Выполнение миграций
     */
    public function migrate()
    {
        // Создание таблиц в правильном порядке
        $tableOrder = [
            'users',
            'category',
            'home',
            'home_images',
            'services',
            'gaiety',
            'promotion',
            'reservation',
            'reviews'
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
                    'email' => 'admin@example.com',
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
                echo "Доступные действия: migrate, reset";
                break;
        }
    } else {
        echo "Укажите действие в параметре 'action': migrate или reset";
    }
} 