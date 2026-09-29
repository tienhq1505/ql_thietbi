<?php

require __DIR__.'/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__.'/../data-real/PL2_TBYT.xlsx';

if (! file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit(1);
}

$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

echo "Highest Row: $highestRow, Highest Column: $highestColumn\n";

$mergeRanges = $sheet->getMergeCells();
echo 'Merge Ranges Count: '.count($mergeRanges)."\n";
foreach ($mergeRanges as $range) {
    if (str_contains($range, 'F') || str_contains($range, 'E') || str_contains($range, 'A')) {
        echo "Merge range: $range -> Value: ".$sheet->getCell(explode(':', $range)[0])->getValue()."\n";
    }
}

echo "\n--- First 20 Rows ---\n";
for ($r = 1; $r <= min(20, $highestRow); $r++) {
    $rowVals = [];
    for ($c = 'A'; $c <= 'M'; $c++) {
        $cell = $sheet->getCell($c.$r);
        $val = $cell->getCalculatedValue();
        $rowVals[] = "$c:$val";
    }
    echo "Row $r: ".implode(' | ', $rowVals)."\n";
}
