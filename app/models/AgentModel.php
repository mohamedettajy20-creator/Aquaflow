<?php
class AgentModel extends Model
{
    protected string $table = 'agents';

    public function listWithDetails(): array
    {
        return $this->query(
            "SELECT a.*, u.full_name, u.email, u.phone, u.status,
                    (SELECT COUNT(*) FROM customers c WHERE c.agent_id = a.id) AS customer_count
             FROM agents a JOIN users u ON u.id = a.user_id
             ORDER BY a.id DESC"
        );
    }

    public function findByUserId(int $userId): ?array
    {
        return $this->first(['user_id' => $userId]);
    }

    public function nextEmployeeCode(): string
    {
        $last = $this->queryOne("SELECT employee_code FROM agents ORDER BY id DESC LIMIT 1");
        $next = $last ? ((int) substr($last['employee_code'], 3)) + 1 : 1;
        return 'AG-' . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
    }

    public function assignedCustomers(int $agentId): array
    {
        return $this->query(
            "SELECT c.*, u.full_name, u.phone, m.meter_number, m.id AS meter_id
             FROM customers c
             JOIN users u ON u.id = c.user_id
             LEFT JOIN meters m ON m.customer_id = c.id
             WHERE c.agent_id = :aid
             ORDER BY u.full_name",
            ['aid' => $agentId]
        );
    }
}
