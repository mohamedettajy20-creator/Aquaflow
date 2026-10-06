<?php
class NotificationModel extends Model
{
    protected string $table = 'notifications';

    public function forUser(int $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT :lim");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function unreadCount(int $userId): int
    {
        return $this->count(['user_id' => $userId, 'is_read' => 0]);
    }

    public function markAllRead(int $userId): void
    {
        $this->execute("UPDATE notifications SET is_read = 1 WHERE user_id = :uid", ['uid' => $userId]);
    }

    public function notify(int $userId, string $type, string $title, string $message, ?string $link = null): void
    {
        $this->create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'link'    => $link,
        ]);
    }
}
