<?php

namespace App\Service;


class DatabaseService
{
    /**
     * @var \PDO
     */
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return \PDOStatement
     */
    private function prepare(string $sql = '', array $params = []): \PDOStatement
    {
        $prepare = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            if (isset($value['value'])) {
                $prepare->bindValue($key, $value['value'], $value['type'] ?? \PDO::PARAM_STR);
            }
        }
        $prepare->execute();

        return $prepare;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param string $class
     * @return array
     */
    public function fetchAll(string $sql = '', array $params = [], string $class = ''): array
    {
        $prepare = $this->prepare($sql, $params);

        if (!empty($class)) {
            $prepare->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $class);
        } else {
            $prepare->setFetchMode(\PDO::FETCH_ASSOC | \PDO::FETCH_PROPS_LATE);
        }

        return $prepare->fetchAll();
    }

    /**
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function execute(string $sql = '', array $params = []): int
    {
        $prepare = $this->prepare($sql, $params);

        return $prepare->rowCount();
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function rollback(): bool
    {
        return $this->db->rollBack();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function lastInsertId(string $name = ''): string
    {
        return $this->db->lastInsertId($name);
    }
}