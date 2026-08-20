<?php
require_once __DIR__ . '/../../src/bootstrap.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$headers = ['Date', 'Sales Order No', 'Liters', 'Delivery Note No', 'AR Invoice No', 'TAX Invoice No'];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Delivery Detail');
$sheet->fromArray($headers, null, 'A1');
$sheet->getStyle('A1:F1')->getFont()->setBold(true);
foreach (range('A', 'F') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}
$sheet->getStyle('A2:A50')->getNumberFormat()->setFormatCode('yyyy-mm-dd');

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="delivery_detail_template.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
