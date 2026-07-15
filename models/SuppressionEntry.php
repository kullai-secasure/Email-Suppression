<?php
require_once 'config/database.php';

class SuppressionEntry
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function addEntry($email, $reason = 'manual')
    {
        $stmt = $this->db->prepare(
            'INSERT INTO suppression_list (email, reason, user_id, created_at) 
             VALUES (?, ?, ?, NOW()) ON DUPLICATE KEY UPDATE reason = ?'
        );
        return $stmt->execute([$email, $reason, $_SESSION['user_id'], $reason]);
    }

    public function getAll($userId)
    {
        $stmt = $this->db->prepare(
            'SELECT id, email, reason, user_id, created_at FROM suppression_list WHERE user_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isEmailSuppressed($email, $userId)
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM suppression_list WHERE email = ? AND user_id = ?'
        );
        $stmt->execute([$email, $userId]);
        return $stmt->fetchColumn() > 0;
    }
}
?>
