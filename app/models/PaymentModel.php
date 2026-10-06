<?php
class PaymentModel extends Model
{
    protected string $table = 'payments';

    public function listWithDetails(array $filters = []): array
    {
        $sql = "SELECT p.*, i.invoice_number, u.full_name AS customer_name
                FROM payments p
                JOIN invoices i ON i.id = p.invoice_id
                JOIN customers c ON c.id = p.customer_id
                JOIN users u ON u.id = c.user_id
                WHERE 1=1";
        $params = [];
        if (!empty($filters['customer_id'])) {
            $sql .= " AND p.customer_id = :cid";
            $params['cid'] = $filters['customer_id'];
        }
        $sql .= " ORDER BY p.paid_at DESC";
        return $this->query($sql, $params);
    }
}
