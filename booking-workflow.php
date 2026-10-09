<?php
require_once __DIR__ . '/db.php';

function booking_overlap_conflicts(mysqli $db, string $email, string $from, string $to, bool $includeSelf, array $familyIds, array $otherTravellers, int $excludeBookingId = 0): array
{
    $rows = $db->execute_query(
        "SELECT DISTINCT b.id,b.booking_reference,b.destination,b.from_date,b.to_date,b.status
         FROM bookings b
         LEFT JOIN booking_travellers bt ON bt.booking_id=b.id
         WHERE b.user_email=? AND b.id<>? AND b.status IN ('pending','needs_information','confirmed','in_progress')
           AND ?<=b.to_date AND ?>=b.from_date
         ORDER BY b.from_date",
        [$email,$excludeBookingId,$from,$to]
    )->fetch_all(MYSQLI_ASSOC);
    if (!$rows) return [];

    $familyIds = array_values(array_unique(array_map('intval', $familyIds)));
    $otherKeys = [];
    foreach ($otherTravellers as $traveller) {
        $name = strtolower(trim((string)($traveller['name'] ?? '')));
        $dob = trim((string)($traveller['date_of_birth'] ?? ''));
        if ($name !== '' && $dob !== '') $otherKeys[$name . '|' . $dob] = true;
    }

    $conflicts = [];
    foreach ($rows as $booking) {
        $travellers = $db->execute_query('SELECT family_member_id,name,date_of_birth,traveller_type FROM booking_travellers WHERE booking_id=?',[(int)$booking['id']])->fetch_all(MYSQLI_ASSOC);
        $matched = [];
        foreach ($travellers as $traveller) {
            if ($includeSelf && $traveller['traveller_type'] === 'primary') $matched[] = $traveller['name'];
            elseif ($traveller['traveller_type'] === 'family' && in_array((int)$traveller['family_member_id'], $familyIds, true)) $matched[] = $traveller['name'];
            elseif ($traveller['traveller_type'] === 'other' && isset($otherKeys[strtolower(trim($traveller['name'])) . '|' . $traveller['date_of_birth']])) $matched[] = $traveller['name'];
        }
        if (!$matched) continue;
        $booking['severity'] = in_array($booking['status'], ['confirmed','in_progress'], true) ? 'hard' : 'warning';
        $booking['travellers'] = array_values(array_unique($matched));
        $conflicts[] = $booking;
    }
    return $conflicts;
}

function active_booking_request(mysqli $db, string $table, int $bookingId): ?array
{
    if (!in_array($table, ['booking_amendments','cancellation_requests'], true)) throw new InvalidArgumentException('Invalid request table');
    return $db->execute_query("SELECT * FROM `$table` WHERE booking_id=? AND status IN ('requested','under_review','customer_approval_required','refund_pending') ORDER BY id DESC LIMIT 1",[$bookingId])->fetch_assoc() ?: null;
}

function cancellation_quote(mysqli $db, array $booking): array
{
    $today = new DateTimeImmutable('today');
    $departure = new DateTimeImmutable($booking['from_date']);
    $days = (int)$today->diff($departure)->format('%r%a');
    $paid = $booking['payment_status'] === 'paid' ? max(0, (float)$booking['total_amount']) : 0.0;
    if ($booking['status'] === 'pending' || $booking['status'] === 'needs_information') {
        return ['id'=>null,'name'=>'Unconfirmed booking request','percentage'=>100.0,'fee'=>0.0,'paid'=>$paid,'refund'=>$paid,'days'=>$days];
    }
    $policy = $db->execute_query("SELECT * FROM cancellation_policies WHERE status='active' AND effective_from<=CURDATE() AND (effective_to IS NULL OR effective_to>=CURDATE()) AND minimum_days_before<=? ORDER BY minimum_days_before DESC LIMIT 1",[$days])->fetch_assoc();
    if (!$policy) $policy = ['id'=>null,'name'=>'No automatic refund','refund_percentage'=>0,'administrative_fee'=>0];
    $percentage = max(0, min(100, (float)$policy['refund_percentage']));
    $fee = max(0, (float)$policy['administrative_fee']);
    $refund = max(0, min($paid, ($paid * $percentage / 100) - $fee));
    return ['id'=>$policy['id']??null,'name'=>$policy['name'],'percentage'=>$percentage,'fee'=>$fee,'paid'=>$paid,'refund'=>$refund,'days'=>$days];
}
