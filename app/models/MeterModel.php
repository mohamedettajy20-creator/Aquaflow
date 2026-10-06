<?php
class MeterModel extends Model
{
    protected string $table = 'meters';

    public function listWithDetails(string $search = ''): array
    {
        return $this->query(
            "SELECT m.*, u.full_name AS customer_name, c.customer_code
             FROM meters m
             JOIN customers c ON c.id = m.customer_id
             JOIN users u ON u.id = c.user_id
             WHERE (m.meter_number LIKE :s OR u.full_name LIKE :s OR c.customer_code LIKE :s)
             ORDER BY m.id DESC",
            ['s' => "%{$search}%"]
        );
    }

    public function findWithDetails(int $id): ?array
    {
        return $this->queryOne(
            "SELECT m.*, u.full_name AS customer_name, c.customer_code, c.address
             FROM meters m
             JOIN customers c ON c.id = m.customer_id
             JOIN users u ON u.id = c.user_id
             WHERE m.id = :id",
            ['id' => $id]
        );
    }

    public function byCustomer(int $customerId): array
    {
        return $this->where(['customer_id' => $customerId]);
    }

    public function lastReadingValue(int $meterId): float
    {
        $row = $this->queryOne(
            "SELECT current_reading FROM readings WHERE meter_id = :id ORDER BY period_year DESC, period_month DESC LIMIT 1",
            ['id' => $meterId]
        );
        if ($row) {
            return (float) $row['current_reading'];
        }
        $meter = $this->find($meterId);
        return $meter ? (float) $meter['initial_reading'] : 0.0;
    }

    public function nextMeterNumber(): string
    {
        $last = $this->queryOne("SELECT meter_number FROM meters ORDER BY id DESC LIMIT 1");
        $next = $last ? ((int) substr($last['meter_number'], 4)) + 1 : 1;
        return 'MTR-' . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }
}
