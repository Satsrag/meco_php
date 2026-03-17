<?php

spl_autoload_register(function ($class) {
    $prefix = 'Meco\\';
    $baseDir = __DIR__ . '/../src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require __DIR__ . '/Text.php';

use Meco\Enums\CodeType;
use Meco\TranslateService;

$group1 = [
    ['label' => 'menkLetter -> zvvnmod', 'from' => CodeType::MENK_LETTER, 'to' => CodeType::ZVVNMOD, 'input' => $menkLetter, 'expected' => $menkLetter2Zvvnmod],
    ['label' => 'delhi -> zvvnmod', 'from' => CodeType::DELEHI, 'to' => CodeType::ZVVNMOD, 'input' => $delhi, 'expected' => $delhi2Zvvnmod],
    ['label' => 'menkShape -> zvvnmod', 'from' => CodeType::MENK_SHAPE, 'to' => CodeType::ZVVNMOD, 'input' => $menkShape, 'expected' => $menkShape2Zvvnmod],
    ['label' => 'z52 -> zvvnmod', 'from' => CodeType::Z52, 'to' => CodeType::ZVVNMOD, 'input' => $z52, 'expected' => $z522Zvvnmod],
];

$group2 = [
    ['label' => 'zvvnmod -> menkLetter', 'from' => CodeType::ZVVNMOD, 'to' => CodeType::MENK_LETTER, 'input' => $zvvnmod, 'expected' => $zvvnmod2MenkLetter],
    ['label' => 'zvvnmod -> menkShape', 'from' => CodeType::ZVVNMOD, 'to' => CodeType::MENK_SHAPE, 'input' => $zvvnmod, 'expected' => $zvvnmod2MenkShape],
    ['label' => 'zvvnmod -> z52', 'from' => CodeType::ZVVNMOD, 'to' => CodeType::Z52, 'input' => $zvvnmod, 'expected' => $zvvnmod2Z52],
    ['label' => 'zvvnmod -> delhi', 'from' => CodeType::ZVVNMOD, 'to' => CodeType::DELEHI, 'input' => $zvvnmod, 'expected' => $zvvnmod2Delhi],
];

$failures = 0;

runGroup('Group 1: to zvvnmod', $group1, $failures);
runGroup('Group 2: from zvvnmod', $group2, $failures);

echo "\n";
if ($failures === 0) {
    echo "ALL TESTS PASSED\n";
    exit(0);
}

echo "TOTAL FAILURES: {$failures}\n";
exit(1);

function runGroup($title, $cases, &$failures)
{
    echo "=== {$title} ===\n";
    foreach ($cases as $case) {
        runCase($case, $failures);
    }
}

function runCase($case, &$failures)
{
    try {
        $actual = TranslateService::translate($case['from'], $case['to'], $case['input']);
    } catch (Throwable $e) {
        $failures++;
        echo "[ERROR] {$case['label']}\n";
        echo "  message: {$e->getMessage()}\n";
        echo "  at: {$e->getFile()}:{$e->getLine()}\n\n";
        return;
    }

    if ($actual === $case['expected']) {
        echo "[PASS] {$case['label']}\n";
        return;
    }

    $failures++;
    echo "[FAIL] {$case['label']}\n";
    echo "  expected: {$case['expected']}\n";
    echo "  actual:   {$actual}\n";
    echo "  expected cps: " . toCodePointString($case['expected']) . "\n";
    echo "  actual cps:   " . toCodePointString($actual) . "\n\n";
}

function toCodePointString($str)
{
    $items = [];
    $len = mb_strlen($str, 'UTF-8');
    for ($i = 0; $i < $len; $i++) {
        $ch = mb_substr($str, $i, 1, 'UTF-8');
        $cp = mb_ord($ch, 'UTF-8');
        if ($cp >= 0x20 && $cp < 0x7F) {
            $items[] = $ch;
        } else {
            $items[] = sprintf('U+%04X', $cp);
        }
    }
    return implode(' ', $items);
}
