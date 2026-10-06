<?php
class InvoiceModel extends Model
{
    protected string $table = 'invoices';

    public function listWithDetails(array $filters = []): array
    {
        $sql = "SELECT i.*, u.full_name AS customer_name, c.customer_code, m.meter_number
                FROM invoices i
                JOIN customers c ON c.id = i.customer_id
                JOIN users u ON u.id = c.user_id
                JOIN meters m ON m.id = i.meter_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND i.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['customer_id'])) {
            $sql .= " AND i.customer_id = :customer_id";
            $params['customer_id'] = $filters['customer_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (i.invoice_number LIKE :s OR u.full_name LIKE :s)";
            $params['s'] = '%' . $filters['search'] . '%';
        }
        $sql .= " ORDER BY i.issue_date DESC";

        return $this->query($sql, $params);
    }

    public function findWithDetails(int $id): ?array
    {
        return $this->queryOne(
            "SELECT i.*, u.full_name AS customer_name, u.email AS customer_email, c.customer_code, c.address,
                    m.meter_number
             FROM invoices i
             JOIN customers c ON c.id = i.customer_id
             JOIN users u ON u.id = c.user_id
             JOIN meters m ON m.id = i.meter_id
             WHERE i.id = :id",
            ['id' => $id]
        );
    }

    public function details(int $invoiceId): array
    {
        return $this->query("SELECT * FROM invoice_details WHERE invoice_id = :id", ['id' => $invoiceId]);
    }

    public function nextInvoiceNumber(int $year, int $month): string
    {
        $last = $this->queryOne(
            "SELECT invoice_number FROM invoices WHERE period_year = :y AND period_month = :m ORDER BY id DESC LIMIT 1",
            ['y' => $year, 'm' => $month]
        );
        $seq = $last ? ((int) substr($last['invoice_number'], -4)) + 1 : 1001;
        return sprintf('INV-%04d-%02d-%d', $year, $month, $seq);
    }

    public function monthlyRevenue(int $months = 6): array
    {
        return $this->query(
            "SELECT period_year, period_month, SUM(total_amount) AS revenue,
                    SUM(CASE WHEN status='paid' THEN total_amount ELSE 0 END) AS collected
             FROM invoices
             GROUP BY period_year, period_month
             ORDER BY period_year DESC, period_month DESC
             LIMIT :lim",
            []
        ) ?: $this->monthlyRevenueFallback($months);
    }

    private function monthlyRevenueFallback(int $months): array
    {
        $stmt = $this->db->prepare(
            "SELECT period_year, period_month, SUM(total_amount) AS revenue,
                    SUM(CASE WHEN status='paid' THEN total_amount ELSE 0 END) AS collected
             FROM invoices
             GROUP BY period_year, period_month
             ORDER BY period_year DESC, period_month DESC
             LIMIT :lim"
        );
        $stmt->bindValue(':lim', $months, PDO::PARAM_INT);
        $stmt->execute();
        return array_reverse($stmt->fetchAll());
    }

    public function topConsumers(int $limit = 5): array
    {
        return $this->query(
            "SELECT u.full_name, c.customer_code, SUM(i.consumption) AS total_consumption
             FROM invoices i
             JOIN customers c ON c.id = i.customer_id
             JOIN users u ON u.id = c.user_id
             GROUP BY i.customer_id
             ORDER BY total_consumption DESC
             LIMIT " . (int)$limit
        );
    }
}
