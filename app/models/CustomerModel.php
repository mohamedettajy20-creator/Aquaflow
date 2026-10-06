<?php
class CustomerModel extends Model
{
    protected string $table = 'customers';

    /** All customers with joined user + agent info, optional search. */
    public function listWithDetails(string $search = '', int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT c.*, u.full_name, u.email, u.phone, u.status, u.avatar,
                       a.employee_code AS agent_code, au.full_name AS agent_name
                FROM customers c
                JOIN users u ON u.id = c.user_id
                LEFT JOIN agents a ON a.id = c.agent_id
                LEFT JOIN users au ON au.id = a.user_id
                WHERE (u.full_name LIKE :s OR u.email LIKE :s OR c.customer_code LIKE :s OR c.address LIKE :s)
                ORDER BY c.id DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':s', "%{$search}%");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countSearch(string $search = ''): int
    {
        $sql = "SELECT COUNT(*) c FROM customers c
                JOIN users u ON u.id = c.user_id
                WHERE (u.full_name LIKE :s OR u.email LIKE :s OR c.customer_code LIKE :s OR c.address LIKE :s)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['s' => "%{$search}%"]);
        return (int) $stmt->fetch()['c'];
    }

    public function findWithDetails(int $id): ?array
    {
        return $this->queryOne(
            "SELECT c.*, u.full_name, u.email, u.phone, u.status, u.avatar
             FROM customers c JOIN users u ON u.id = c.user_id
             WHERE c.id = :id",
            ['id' => $id]
        );
    }

    public function findByUserId(int $userId): ?array
    {
        return $this->first(['user_id' => $userId]);
    }

    public function nextCustomerCode(): string
    {
        $last = $this->queryOne("SELECT customer_code FROM customers ORDER BY id DESC LIMIT 1");
        $next = $last ? ((int) substr($last['customer_code'], 4)) + 1 : 1001;
        return 'CUS-' . $next;
    }
}
