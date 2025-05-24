<?php
/**
 * Класс для работы с базой данных
 */
class Database
{
    private $connection;
    private static $instance = null;

    /**
     * Конструктор класса
     */
    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG) {
                die("Ошибка подключения к базе данных: " . $e->getMessage());
            } else {
                die("Ошибка подключения к базе данных.");
            }
        }
    }

    /**
     * Получение экземпляра класса (паттерн Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Получение объекта соединения
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Выполнение SQL запроса с параметрами
     * 
     * @param string $query SQL запрос
     * @param array $params Параметры запроса
     * @return PDOStatement|false
     */
    public function query($query, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (DEBUG) {
                die("Ошибка запроса: " . $e->getMessage());
            } else {
                die("Произошла ошибка при выполнении запроса.");
            }
        }
    }

    /**
     * Получение одной записи из БД
     * 
     * @param string $query SQL запрос
     * @param array $params Параметры запроса
     * @return array|false
     */
    public function fetch($query, $params = [])
    {
        $stmt = $this->query($query, $params);
        return $stmt->fetch();
    }

    /**
     * Получение всех записей из БД
     * 
     * @param string $query SQL запрос
     * @param array $params Параметры запроса
     * @return array
     */
    public function fetchAll($query, $params = [])
    {
        $stmt = $this->query($query, $params);
        return $stmt->fetchAll();
    }

    /**
     * Вставка данных в БД
     * 
     * @param string $table Название таблицы
     * @param array $data Данные для вставки
     * @return int|false ID вставленной записи или false
     */
    public function insert($table, $data)
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');
        
        $query = "INSERT INTO $table (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $placeholders) . ")";
        
        if ($this->query($query, array_values($data))) {
            return $this->connection->lastInsertId();
        }
        return false;
    }

    /**
     * Обновление данных в БД
     * 
     * @param string $table Название таблицы
     * @param array $data Данные для обновления
     * @param string $where Условие WHERE
     * @param array $whereParams Параметры условия
     * @return bool
     */
    public function update($table, $data, $where, $whereParams = [])
    {
        $fields = array_keys($data);
        $set = array_map(function($field) {
            return "$field = ?";
        }, $fields);
        
        $query = "UPDATE $table SET " . implode(", ", $set) . " WHERE $where";
        
        $params = array_merge(array_values($data), $whereParams);
        return $this->query($query, $params) ? true : false;
    }

    /**
     * Удаление данных из БД
     * 
     * @param string $table Название таблицы
     * @param string $where Условие WHERE
     * @param array $params Параметры условия
     * @return bool
     */
    public function delete($table, $where, $params = [])
    {
        $query = "DELETE FROM $table WHERE $where";
        return $this->query($query, $params) ? true : false;
    }
} 