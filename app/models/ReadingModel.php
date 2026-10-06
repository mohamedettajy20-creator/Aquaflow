<?php
class ReadingModel extends Model
{
    protected string $table = 'readings';

    public function listWithDetails(array $filters = []): array
    {
        $sql = "SELECT r.*, m.meter_number, u.full_name AS customer_name, au.full_name AS agent_name
                FROM readings r
                JOIN meters m ON m.id = r.meter_id
                JOIN customers c ON c.id = m.customer_id
                JOIN users u ON u.id = c.user_id
                LEFT JOIN agents ag ON ag.id = r.agent_id
                LEFT JOIN users au ON au.id = ag.user_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['agent_id'])) {
            $sql .= " AND r.agent_id = :agent_id";
            $params['agent_id'] = $filters['agent_id'];
        }
        $sql .= " ORDER BY r.created_at DESC";

        return $this->query($sql, $params);
    }

    public function byMeter(int $meterId): array
    {
        return $this->query(
            "SELECT * FROM readings WHERE meter_id = :m ORDER BY period_year DESC, period_month DESC",
            ['m' => $meterId]
        );
    }
}
