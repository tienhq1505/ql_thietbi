<?php

require __DIR__.'/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__.'/../data-real/PL2_TBYT.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();

$sttCount = 0;
$totalQty = 0;

for ($r = 1; $r <= $highestRow; $r++) {
    $val = $sheet->getCell("A$r")->getCalculatedValue();
    $strVal = trim((string) $val);
    if ($strVal !== '' && is_numeric($strVal)) {
        $sttCount++;
        $qtyVal = $sheet->getCell("D$r")->getCalculatedValue();
        $qty = (int) $qtyVal;
        if ($qty <= 0) {
            $qty = 1;
        }
        $totalQty += $qty;
    }
}

echo "Total STT rows: $sttCount\n";
echo "Total Units (Qty sum): $totalQty\n";
