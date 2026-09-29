<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__.'/../vendor/autoload.php';

$spreadsheet = IOFactory::load(__DIR__.'/../data-real/kqrs.xlsx');
echo 'Sheets: '.implode(', ', $spreadsheet->getSheetNames())."\n";

$sheet = $spreadsheet->getSheetByName('Bổ sung') ?? $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();
$highestCol = $sheet->getHighestColumn();

echo "Highest Row: $highestRow, Highest Col: $highestCol\n\n";

$cols = range('A', 'P');
for ($r = 1; $r <= min($highestRow, 30); $r++) {
    $rowVals = [];
    foreach ($cols as $c) {
        $val = $sheet->getCell($c.$r)->getCalculatedValue();
        $rowVals[] = str_replace("\n", ' ', (string) $val);
    }
    echo sprintf("%2d: %s\n", $r, implode(' | ', $rowVals));
}
