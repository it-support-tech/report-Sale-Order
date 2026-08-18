<?php

function redirect(string $path): never
{
    header("Location: $path");
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_messages(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function money(float|string|null $value, int $decimals = 2): string
{
    return number_format((float) ($value ?? 0), $decimals);
}

function old(array $data, string $key, string $default = ''): string
{
    return e($data[$key] ?? $default);
}

function format_date(?string $date): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $ts ? date('d/m/Y', $ts) : e($date);
}

/** Suggest the next document number in the form SO-YYMMNNN */
function next_document_no(PDO $pdo): string
{
    $prefix = 'SO-' . date('ym');
    $stmt = $pdo->prepare(
        "SELECT document_no FROM sales_orders WHERE document_no LIKE :prefix ORDER BY document_no DESC LIMIT 1"
    );
    $stmt->execute(['prefix' => $prefix . '%']);
    $last = $stmt->fetchColumn();
    $seq = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;
    return $prefix . str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
}

/** Convert an integer amount to English words, e.g. "One million two hundred" */
function number_to_words(int $number): string
{
    if ($number === 0) {
        return 'Zero';
    }

    $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen'];
    $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
    $scales = ['', 'Thousand', 'Million', 'Billion', 'Trillion'];

    $threeDigits = function (int $n) use ($ones, $tens): string {
        $words = [];
        if ($n >= 100) {
            $words[] = $ones[intdiv($n, 100)] . ' Hundred';
            $n %= 100;
        }
        if ($n >= 20) {
            $tensWord = $tens[intdiv($n, 10)];
            $n %= 10;
            $words[] = $n > 0 ? "$tensWord-{$ones[$n]}" : $tensWord;
        } elseif ($n > 0) {
            $words[] = $ones[$n];
        }
        return implode(' ', $words);
    };

    $groups = [];
    while ($number > 0) {
        $groups[] = $number % 1000;
        $number = intdiv($number, 1000);
    }

    $parts = [];
    foreach (array_reverse($groups, true) as $index => $group) {
        if ($group === 0) {
            continue;
        }
        $words = $threeDigits($group);
        $parts[] = $scales[$index] ? "$words {$scales[$index]}" : $words;
    }

    return implode(', ', $parts);
}

function amount_in_words(float $amount, string $currency): string
{
    $whole = (int) floor($amount);
    $unit = match ($currency) {
        'USD' => 'US Dollar',
        'THB' => 'Thai Baht',
        'CNY' => 'Chinese Yuan',
        default => 'Kip',
    };
    return number_to_words($whole) . " $unit Only";
}
