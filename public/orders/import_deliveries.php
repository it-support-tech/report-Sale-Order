<?php
require_once __DIR__ . '/../../src/bootstrap.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

header('Content-Type: application/json; charset=utf-8');

function parse_text_date(string $value): string
{
    $value = trim($value);
    foreach (['d/m/Y', 'd-m-Y', 'd/m/y', 'd-m-y', 'Y-m-d'] as $format) {
        $date = DateTime::createFromFormat($format, $value);
        if ($date !== false) {
            return $date->format('Y-m-d');
        }
    }
    $timestamp = strtotime($value);
    return $timestamp ? date('Y-m-d', $timestamp) : '';
}

function respond(array $payload): never
{
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(['success' => false, 'message' => 'Method not allowed']);
}

$file = $_FILES['excel_file'] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    respond(['success' => false, 'message' => 'ບໍ່ພົບ file ຫຼືອັບໂຫລດລົ້ມເຫລວ']);
}

$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['xlsx', 'xls'], true)) {
    respond(['success' => false, 'message' => 'ຮອງຮັບສະເພາະ file .xlsx ຫຼື .xls']);
}

try {
    $spreadsheet = IOFactory::load($file['tmp_name']);
} catch (Throwable $e) {
    respond(['success' => false, 'message' => 'ບໍ່ສາມາດອ່ານ file Excel ນີ້ໄດ້']);
}

$sheet = $spreadsheet->getActiveSheet();
$rows = [];

foreach ($sheet->getRowIterator(2) as $row) {
    $cells = [];
    $cellIterator = $row->getCellIterator('A', 'F');
    $cellIterator->setIterateOnlyExistingCells(false);
    foreach ($cellIterator as $cell) {
        $cells[] = $cell;
    }

    [$dateCell, $soCell, $litersCell, $noteCell, $arCell, $taxCell] = $cells;

    $dateValue = $dateCell->getCalculatedValue();
    $deliveryDate = '';
    if ($dateValue !== null && $dateValue !== '') {
        // Excel always stores dates as a numeric day-count internally, even when the
        // cell wasn't styled with a date number format (e.g. pasted as plain numbers) —
        // so treat any numeric value here as a date serial rather than trusting isDateTime().
        if (is_numeric($dateValue)) {
            try {
                $deliveryDate = ExcelDate::excelToDateTimeObject((float) $dateValue)->format('Y-m-d');
            } catch (Throwable $e) {
                $deliveryDate = '';
            }
        } else {
            $deliveryDate = parse_text_date((string) $dateValue);
        }
    }

    $salesOrderNo = trim((string) $soCell->getCalculatedValue());
    $liters = trim((string) $litersCell->getCalculatedValue());
    $deliveryNoteNo = trim((string) $noteCell->getCalculatedValue());
    $arInvoiceNo = trim((string) $arCell->getCalculatedValue());
    $taxNo = trim((string) $taxCell->getCalculatedValue());

    if ($deliveryDate === '' && $salesOrderNo === '' && $liters === '' && $deliveryNoteNo === '' && $arInvoiceNo === '' && $taxNo === '') {
        continue;
    }

    $rows[] = [
        'delivery_date' => $deliveryDate,
        'sales_order_no' => $salesOrderNo,
        'liters' => $liters !== '' ? (string) (float) $liters : '',
        'delivery_note_no' => $deliveryNoteNo,
        'ar_invoice_no' => $arInvoiceNo,
        'tax_no' => $taxNo,
    ];
}

respond(['success' => true, 'rows' => $rows]);
