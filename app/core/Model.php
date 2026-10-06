<?php
/**
 * Model — base class for all models. Provides generic, safe CRUD helpers
 * built entirely on PDO prepared statements.
 */
abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(string $orderBy = null): array
    {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        return $this->db->query($sql)->fetchAll();
    }

    public function where(array $conditions, string $orderBy = null): array
    {
        $clauses = [];
        foreach (array_keys($conditions) as $col) {
            $clauses[] = "{$col} = :{$col}";
        }
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $clauses);
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        return $stmt->fetchAll();
    }

    public function first(array $conditions): ?array
    {
        $rows = $this->where($conditions);
        return $rows[0] ?? null;
    }

    public function create(array $data): int
    {
        $columns      = array_keys($data);
        $placeholders = array_map(fn($c) => ":{$c}", $columns);

        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $assignments = array_map(fn($c) => "{$c} = :{$c}", array_keys($data));
        $sql = "UPDATE {$this->table} SET " . implode(',', $assignments) . " WHERE {$this->primaryKey} = :__id";
        $data['__id'] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) AS c FROM {$this->table}";
        if ($conditions) {
            $clauses = array_map(fn($c) => "{$c} = :{$c}", array_keys($conditions));
            $sql .= " WHERE " . implode(' AND ', $clauses);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($conditions);
        return (int) $stmt->fetch()['c'];
    }

    /** Escape hatch for complex queries specific to a model. */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }
}
