<?php

namespace App\Domain;


class DatabaseService
{
    private $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    private function prepare($sql = '', $params = []): \PDOStatement
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

    public function fetchAll($sql = '', $params = [], $class = ''): array
    {
        $prepare = $this->prepare($sql, $params);

        if (!empty($class)) {
            $prepare->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $class);
        } else {
            $prepare->setFetchMode(\PDO::FETCH_ASSOC | \PDO::FETCH_PROPS_LATE);
        }
        $comments = $prepare->fetchAll();

        return $comments;
    }

    public function execute($sql = '', $params = []): int
    {
        $prepare = $this->prepare($sql, $params);

        return $prepare->rowCount();
    }

    public function exists($sql = '', $params = []): bool
    {
        $prepare = $this->prepare($sql, $params);
        $count = $prepare->fetchColumn(0);

        return $count > 0;
    }

}