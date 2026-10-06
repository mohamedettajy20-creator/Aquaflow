<?php
/**
 * Global helper functions available in all views/controllers.
 */

/** XSS-safe output helper — use in every view when echoing user data. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function old(string $key, $default = '')
{
    $val = $_SESSION['old'][$key] ?? $default;
    return $val;
}

function formatMoney(float $amount, string $currency = 'MAD'): string
{
    return number_format($amount, 2) . ' ' . $currency;
}

function formatDate(?string $date, string $format = 'd M Y'): string
{
    if (!$date) return '—';
    return date($format, strtotime($date));
}

function statusBadge(string $status): string
{
    $map = [
        'paid'       => 'success',
        'unpaid'     => 'warning',
        'late'       => 'danger',
        'cancelled'  => 'secondary',
        'active'     => 'success',
        'inactive'   => 'secondary',
        'suspended'  => 'danger',
        'pending'    => 'warning',
        'validated'  => 'success',
        'rejected'   => 'danger',
        'completed'  => 'success',
        'failed'     => 'danger',
        'refunded'   => 'info',
        'maintenance'=> 'warning',
        'replaced'   => 'secondary',
    ];
    $class = $map[$status] ?? 'secondary';
    return '<span class="badge badge-soft-' . $class . '">' . ucfirst($status) . '</span>';
}

function currentUser(): ?array
{
    return Auth::user();
}

function monthName(int $month): string
{
    $names = [1=>'January','February','March','April','May','June','July','August','September','October','November','December'];
    return $names[$month] ?? (string)$month;
}
