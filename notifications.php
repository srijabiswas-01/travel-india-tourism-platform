<?php
require_once __DIR__ . '/db.php';

function notification_link(?string $link, string $fallback = 'main.php'): string
{
    $link = trim((string)$link);
    return $link !== '' && preg_match('/^[A-Za-z0-9][A-Za-z0-9._\/?=&%#-]*$/', $link) ? $link : $fallback;
}

function create_notification(mysqli $db, string $title, string $message, string $audience = 'public', ?string $userEmail = null, ?string $link = null, string $type = 'info', ?int $adminId = null, ?string $startsAt = null, ?string $expiresAt = null): int
{
    $audience = in_array($audience, ['user', 'admin'], true) ? $audience : 'public';
    $type = in_array($type, ['info', 'success', 'warning'], true) ? $type : 'info';
    $userEmail = in_array($audience, ['user', 'admin'], true) && $userEmail !== null ? trim($userEmail) : null;
    $link = $link !== null && $link !== '' ? notification_link($link, '') : null;
    $statement = $db->prepare('INSERT INTO notifications(audience,user_email,title,message,link,type,created_by,starts_at,expires_at) VALUES(?,?,?,?,?,?,?,?,?)');
    $statement->bind_param('ssssssiss', $audience, $userEmail, $title, $message, $link, $type, $adminId, $startsAt, $expiresAt);
    $statement->execute();
    return (int)$db->insert_id;
}

function visible_notifications(mysqli $db, ?string $userEmail, int $limit = 8): array
{
    $limit = max(1, min(20, $limit));
    if ($userEmail) {
        $statement = $db->prepare("SELECT n.*,r.read_at FROM notifications n LEFT JOIN notification_reads r ON r.notification_id=n.id AND r.user_email=? WHERE n.is_active=1 AND (n.audience='public' OR (n.audience='user' AND n.user_email=?)) AND (n.starts_at IS NULL OR n.starts_at<=NOW()) AND (n.expires_at IS NULL OR n.expires_at>=NOW()) AND (r.read_at IS NULL OR r.read_at>=DATE_SUB(NOW(),INTERVAL 24 HOUR)) ORDER BY n.created_at DESC LIMIT ?");
        $statement->bind_param('ssi', $userEmail, $userEmail, $limit);
    } else {
        $statement = $db->prepare("SELECT n.*,NULL read_at FROM notifications n WHERE n.is_active=1 AND n.audience='public' AND (n.starts_at IS NULL OR n.starts_at<=NOW()) AND (n.expires_at IS NULL OR n.expires_at>=NOW()) ORDER BY n.created_at DESC LIMIT ?");
        $statement->bind_param('i', $limit);
    }
    $statement->execute();
    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}

function unread_notification_count(mysqli $db, string $userEmail): int
{
    $statement = $db->prepare("SELECT COUNT(*) total FROM notifications n LEFT JOIN notification_reads r ON r.notification_id=n.id AND r.user_email=? WHERE n.is_active=1 AND (n.audience='public' OR (n.audience='user' AND n.user_email=?)) AND (n.starts_at IS NULL OR n.starts_at<=NOW()) AND (n.expires_at IS NULL OR n.expires_at>=NOW()) AND r.notification_id IS NULL");
    $statement->bind_param('ss', $userEmail, $userEmail);
    $statement->execute();
    return (int)$statement->get_result()->fetch_assoc()['total'];
}

function visible_admin_notifications(mysqli $db, string $adminEmail, int $limit = 8): array
{
    $limit=max(1,min(20,$limit));
    $readerKey='admin:'.strtolower($adminEmail);
    $statement=$db->prepare("SELECT n.*,r.read_at FROM notifications n LEFT JOIN notification_reads r ON r.notification_id=n.id AND r.user_email=? WHERE n.is_active=1 AND n.audience='admin' AND (n.user_email IS NULL OR n.user_email=?) AND (n.starts_at IS NULL OR n.starts_at<=NOW()) AND (n.expires_at IS NULL OR n.expires_at>=NOW()) AND (r.read_at IS NULL OR r.read_at>=DATE_SUB(NOW(),INTERVAL 24 HOUR)) ORDER BY n.created_at DESC LIMIT ?");
    $statement->bind_param('ssi',$readerKey,$adminEmail,$limit);
    $statement->execute();
    return $statement->get_result()->fetch_all(MYSQLI_ASSOC);
}

function unread_admin_notification_count(mysqli $db, string $adminEmail): int
{
    $readerKey='admin:'.strtolower($adminEmail);
    $statement=$db->prepare("SELECT COUNT(*) total FROM notifications n LEFT JOIN notification_reads r ON r.notification_id=n.id AND r.user_email=? WHERE n.is_active=1 AND n.audience='admin' AND (n.user_email IS NULL OR n.user_email=?) AND (n.starts_at IS NULL OR n.starts_at<=NOW()) AND (n.expires_at IS NULL OR n.expires_at>=NOW()) AND r.notification_id IS NULL");
    $statement->bind_param('ss',$readerKey,$adminEmail);
    $statement->execute();
    return (int)$statement->get_result()->fetch_assoc()['total'];
}

function mark_admin_notification_read(mysqli $db, int $notificationId, string $adminEmail): void
{
    $readerKey='admin:'.strtolower($adminEmail);
    if($notificationId===0){
        $statement=$db->prepare("INSERT IGNORE INTO notification_reads(notification_id,user_email) SELECT n.id,? FROM notifications n WHERE n.is_active=1 AND n.audience='admin' AND (n.user_email IS NULL OR n.user_email=?) AND (n.starts_at IS NULL OR n.starts_at<=NOW()) AND (n.expires_at IS NULL OR n.expires_at>=NOW())");
        $statement->bind_param('ss',$readerKey,$adminEmail);
    }else{
        $statement=$db->prepare("INSERT IGNORE INTO notification_reads(notification_id,user_email) SELECT id,? FROM notifications WHERE id=? AND is_active=1 AND audience='admin' AND (user_email IS NULL OR user_email=?)");
        $statement->bind_param('sis',$readerKey,$notificationId,$adminEmail);
    }
    $statement->execute();
}

function mark_notification_read(mysqli $db, int $notificationId, string $userEmail): void
{
    if ($notificationId === 0) {
        $statement = $db->prepare("INSERT IGNORE INTO notification_reads(notification_id,user_email) SELECT id,? FROM notifications WHERE is_active=1 AND (audience='public' OR (audience='user' AND user_email=?)) AND (starts_at IS NULL OR starts_at<=NOW()) AND (expires_at IS NULL OR expires_at>=NOW())");
        $statement->bind_param('ss', $userEmail, $userEmail);
        $statement->execute();
        return;
    }
    $statement = $db->prepare("INSERT IGNORE INTO notification_reads(notification_id,user_email) SELECT id,? FROM notifications WHERE id=? AND is_active=1 AND (audience='public' OR (audience='user' AND user_email=?))");
    $statement->bind_param('sis', $userEmail, $notificationId, $userEmail);
    $statement->execute();
}
