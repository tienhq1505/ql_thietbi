<?php

require __DIR__.'/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = __DIR__.'/../data-real/PL2_TBYT.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();

$countryAlias = [
    'trung quốc' => 'Trung Quốc', 'china' => 'Trung Quốc', 'trung quoc' => 'Trung Quốc',
    'nhật bản' => 'Nhật Bản', 'nhật' => 'Nhật Bản', 'japan' => 'Nhật Bản', 'nhat ban' => 'Nhật Bản',
    'hàn quốc' => 'Hàn Quốc', 'hàn' => 'Hàn Quốc', 'korea' => 'Hàn Quốc', 'han quoc' => 'Hàn Quốc',
    'đức' => 'Đức', 'germany' => 'Đức', 'duc' => 'Đức',
    'mỹ' => 'Mỹ', 'usa' => 'Mỹ', 'my' => 'Mỹ',
    'ý' => 'Ý', 'italy' => 'Ý', 'italia' => 'Ý',
    'malaysia' => 'Malaysia',
    'việt nam' => 'Việt Nam', 'vietnam' => 'Việt Nam', 'viet nam' => 'Việt Nam',
    'thụy sĩ' => 'Thụy Sĩ', 'thụy sỹ' => 'Thụy Sĩ', 'switzerland' => 'Thụy Sĩ',
    'pháp' => 'Pháp', 'france' => 'Pháp', 'phap' => 'Pháp',
    'anh' => 'Anh', 'uk' => 'Anh', 'england' => 'Anh',
    'đài loan' => 'Đài Loan', 'taiwan' => 'Đài Loan',
    'ấn độ' => 'Ấn Độ', 'india' => 'Ấn Độ',
    'thái lan' => 'Thái Lan', 'thailand' => 'Thái Lan',
    'tây ban nha' => 'Tây Ban Nha', 'spain' => 'Tây Ban Nha',
    'thụy điển' => 'Thụy Điển', 'sweden' => 'Thụy Điển',
    'hà lan' => 'Hà Lan', 'netherlands' => 'Hà Lan',
    'đan mạch' => 'Đan Mạch', 'denmark' => 'Đan Mạch',
    'bỉ' => 'Bỉ', 'belgium' => 'Bỉ',
    'singapore' => 'Singapore',
    'israel' => 'Israel',
    'úc' => 'Úc', 'australia' => 'Úc',
    'phần lan' => 'Phần Lan', 'finland' => 'Phần Lan',
    'thổ nhĩ kỳ' => 'Thổ Nhĩ Kỳ', 'turkey' => 'Thổ Nhĩ Kỳ',
];

function parseColumnE(string $text, array $countryAlias): array
{
    $text = trim($text);
    if ($text === '') {
        return ['country' => null, 'year' => null, 'model' => null, 'mfg' => null, 'confidence' => 'thap'];
    }

    // 1. Match Country
    $foundCountry = null;
    $matchedAliasStr = null;

    foreach ($countryAlias as $alias => $canonicalCountry) {
        if (preg_match('/\b'.preg_quote($alias, '/').'\b/ui', $text)) {
            $foundCountry = $canonicalCountry;
            $matchedAliasStr = $alias;
            break;
        }
    }

    if (! $foundCountry) {
        foreach ($countryAlias as $alias => $canonicalCountry) {
            if (mb_stripos($text, $alias) !== false) {
                $foundCountry = $canonicalCountry;
                $matchedAliasStr = $alias;
                break;
            }
        }
    }

    // 2. Match Year
    $foundYear = null;
    if (preg_match('/\b(19\d{2}|20\d{2})\b/', $text, $m)) {
        $foundYear = (int) $m[1];
    }

    // 3. Clean text for model & mfg
    $clean = $text;
    if ($matchedAliasStr) {
        $clean = preg_replace('/'.preg_quote($matchedAliasStr, '/').'/ui', '', $clean);
    }
    if ($foundYear) {
        $clean = preg_replace('/\b'.$foundYear.'\b/', '', $clean);
    }
    $clean = trim(preg_replace('/\s+/', ' ', $clean));

    // 4. Tokenize & find model
    $tokens = array_values(array_filter(explode(' ', $clean)));
    $foundModel = null;
    $mfgTokens = [];

    foreach ($tokens as $token) {
        if (! $foundModel && preg_match('/\d/', $token)) {
            $foundModel = $token;
        } else {
            $mfgTokens[] = $token;
        }
    }

    $foundMfg = trim(implode(' ', $mfgTokens));

    if (! $foundModel && ! empty($mfgTokens)) {
        $foundModel = array_shift($mfgTokens);
        $foundMfg = trim(implode(' ', $mfgTokens));
    }

    // Confidence:
    // 'cao' if country found AND (model or mfg found and text is specific)
    // 'thap' if text is very short/vague or missing country/model
    $confidence = 'cao';
    if (! $foundCountry || empty($foundModel) || (empty($foundMfg) && strlen($clean) < 5)) {
        $confidence = 'thap';
    }

    return [
        'country' => $foundCountry,
        'year' => $foundYear,
        'model' => $foundModel,
        'mfg' => $foundMfg,
        'confidence' => $confidence,
    ];
}

$highConf = 0;
$lowConf = 0;

for ($r = 6; $r <= $highestRow; $r++) {
    $stt = trim((string) $sheet->getCell("A$r")->getCalculatedValue());
    if ($stt === '' || ! is_numeric($stt)) {
        continue;
    }

    $colE = trim((string) $sheet->getCell("E$r")->getCalculatedValue());
    $res = parseColumnE($colE, $countryAlias);
    if ($res['confidence'] === 'cao') {
        $highConf++;
    } else {
        $lowConf++;
    }

    if ($res['confidence'] === 'thap') {
        echo "Row $r (STT $stt) [LOW CONF] | Col E: \"$colE\" -> Country: {$res['country']}, Model: {$res['model']}, Mfg: {$res['mfg']}\n";
    }
}

echo "\nHigh Confidence: $highConf, Low Confidence: $lowConf\n";
