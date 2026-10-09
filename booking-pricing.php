<?php
require_once __DIR__ . '/security.php';

function booking_age_category(?string $dob): string
{
    if (!$dob) return 'unknown';
    try {
        $birth = new DateTimeImmutable($dob);
        $today = new DateTimeImmutable('today');
        if ($birth > $today) return 'invalid';
        $age = $birth->diff($today)->y;
        return $age < 18 ? 'child' : ($age >= 60 ? 'senior' : 'adult');
    } catch (Throwable $e) {
        return 'invalid';
    }
}

function booking_category_rate(string $category): float
{
    return $category === 'child' ? 0.50 : ($category === 'senior' ? 0.90 : 1.00);
}

function booking_category_label(string $category): string
{
    return ['adult'=>'Adults','child'=>'Children','senior'=>'Seniors','unknown'=>'Travellers'][$category] ?? 'Travellers';
}

function booking_price_rows_from_travellers(array $travellers, float $unitPrice, float $fallbackPackageSubtotal = 0.0, int $fallbackPeople = 0): array
{
    $counts = ['adult'=>0,'child'=>0,'senior'=>0,'unknown'=>0];
    foreach ($travellers as $traveller) {
        $category = $traveller['age_category'] ?? '';
        if (!isset($counts[$category])) $category = 'unknown';
        $counts[$category]++;
    }
    if (!array_sum($counts) && $fallbackPeople > 0) $counts['unknown'] = $fallbackPeople;

    $rows = [];
    $calculated = 0.0;
    foreach ($counts as $category => $count) {
        if ($count <= 0) continue;
        $rate = booking_category_rate($category);
        $lineUnit = round($unitPrice * $rate, 2);
        $lineTotal = round($lineUnit * $count, 2);
        $calculated += $lineTotal;
        $rows[] = ['category'=>$category,'label'=>booking_category_label($category),'count'=>$count,'rate'=>$rate,'unit'=>$lineUnit,'total'=>$lineTotal];
    }
    if ($calculated <= 0 && $fallbackPackageSubtotal > 0) $rows[] = ['category'=>'unknown','label'=>'Package tour','count'=>max(1,$fallbackPeople),'rate'=>1.0,'unit'=>$fallbackPackageSubtotal / max(1,$fallbackPeople),'total'=>$fallbackPackageSubtotal];
    return $rows;
}

function render_booking_price_rows(array $rows, string $rowClass, string $amountTag = 'strong'): string
{
    $html = '';
    foreach ($rows as $row) {
        $note = $row['category'] === 'child' ? ' · 50% child rate' : ($row['category'] === 'senior' ? ' · 90% senior rate' : '');
        $html .= '<div class="'.$rowClass.'"><span><strong>'.e($row['label']).'</strong><small>'.inr($row['unit']).' × '.(int)$row['count'].' traveller'.((int)$row['count']===1?'':'s').$note.'</small></span><'.$amountTag.'>'.inr($row['total']).'</'.$amountTag.'></div>';
    }
    return $html;
}
