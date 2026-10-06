<?php
class ActivityLogger extends Model
{
    protected string $table = 'activity_logs';

    public static function log(string $action, string $description = ''): void
    {
        $model = new self();
        $model->create([
            'user_id'     => Auth::id(),
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public function recent(int $limit = 15): array
    {
        $stmt = $this->db->prepare(
            "SELECT l.*, u.full_name FROM activity_logs l
             LEFT JOIN users u ON u.id = l.user_id
             ORDER BY l.created_at DESC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
