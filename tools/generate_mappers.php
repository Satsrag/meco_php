<?php
/**
 * Java-to-PHP Mapper Generator
 *
 * Reads Java mapper source files and translates them to PHP.
 * Re-run this script whenever the Java source changes.
 *
 * Usage: php tools/generate_mappers.php
 */

$javaBase = __DIR__ . '/../../meco/meco/src/main/java/com/zvvnmod/meco/translate';
$phpBase  = __DIR__ . '/../src/Rules';

// ============================================================
// Unicode helpers
// ============================================================

/** Convert Java \uXXXX in a raw source string to actual UTF-8 chars */
function javaUnescape($s) {
    return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($m) {
        return mb_chr(hexdec($m[1]), 'UTF-8');
    }, $s);
}

/** Convert a UTF-8 string to PHP unicode literal like "\u{E000}\u{1820}" */
function toPhpLiteral($str) {
    if ($str === '') return '""';
    $result = '"';
    $len = mb_strlen($str, 'UTF-8');
    for ($i = 0; $i < $len; $i++) {
        $ch = mb_substr($str, $i, 1, 'UTF-8');
        $ord = mb_ord($ch, 'UTF-8');
        if ($ord === 0x20) {
            $result .= ' ';
        } elseif ($ord >= 0x21 && $ord <= 0x7E && $ord !== 0x22 && $ord !== 0x24 && $ord !== 0x5C) {
            $result .= $ch;
        } else {
            $result .= sprintf("\\u{%04X}", $ord);
        }
    }
    return $result . '"';
}

// ============================================================
// Java Parser / Interpreter
// ============================================================

class JavaMapperParser {
    private $lines;
    private $fields = [];      // class-level fields: name => value (string or array)
    private $localVars = [];   // method-level vars
    private $currentMap = [];   // the map being built
    private $allMaps = [];     // methodName => map
    private $methodOrder = []; // order of method calls

    public function parse($file) {
        $this->lines = file($file, FILE_IGNORE_NEW_LINES);
        $this->extractFields();
        $this->extractMethods();
        return $this->allMaps;
    }

    public function getMethodOrder() {
        return $this->methodOrder;
    }

    /** Extract class-level field definitions */
    private function extractFields() {
        $fullText = implode("\n", $this->lines);

        // Match: private static final String name = "...";
        preg_match_all('/(?:private|public)\s+static\s+(?:final\s+)?String\s+(\w+)\s*=\s*"((?:[^"\\\\]|\\\\.)*)"\s*;/', $fullText, $m);
        for ($i = 0; $i < count($m[0]); $i++) {
            $this->fields[$m[1][$i]] = javaUnescape($m[2][$i]);
        }

        // Match: List<String> name = Lists.newArrayList("...", "...", ...);
        // Can span multiple lines, so use DOTALL
        preg_match_all('/(?:private|public)\s+static\s+(?:final\s+)?List<String>\s+(\w+)\s*=\s*Lists\.newArrayList\(((?:[^)]*?))\)\s*;/s', $fullText, $m);
        for ($i = 0; $i < count($m[0]); $i++) {
            $name = $m[1][$i];
            $items = $this->parseStringList($m[2][$i]);
            $this->fields[$name] = $items;
        }
    }

    /** Parse a comma-separated list of "..." strings */
    private function parseStringList($raw) {
        preg_match_all('/"((?:[^"\\\\]|\\\\.)*)"/', $raw, $m);
        return array_map('javaUnescape', $m[1]);
    }

    /** Extract build/get methods and the static initializer method order */
    private function extractMethods() {
        $fullText = implode("\n", $this->lines);

        // Find method call order from static block or constructor
        // Pattern: codeMapper.combine(buildXxx()) or mapper.combine(getXxx())
        preg_match_all('/(?:combine|merge)\s*\(\s*(\w+)\s*\(\s*\)\s*\)/', $fullText, $m);
        if (!empty($m[1])) {
            $this->methodOrder = $m[1];
        }

        // Also find direct calls like buildMapper1(); getMapper1();
        preg_match_all('/^\s+(?:build\w+|get\w+)\s*\(\s*\)\s*;/m', $fullText, $m2);
        foreach ($m2[0] as $call) {
            preg_match('/(\w+)\s*\(/', $call, $cm);
            if ($cm && !in_array($cm[1], $this->methodOrder)) {
                $this->methodOrder[] = $cm[1];
            }
        }

        // Also find mapper.put() calls at class level (in static blocks or direct methods)
        // For simpler mappers like ToMenkShape that use mapper.put() directly
        // We handle this by treating the calling method as a container

        // Extract each method body
        // Pattern: private static (Map<String, String>|void) methodName() { ... }
        $pattern = '/(?:private|public)\s+static\s+(?:Map<String,\s*String>|void)\s+(\w+)\s*\(\s*\)\s*\{/';
        preg_match_all($pattern, $fullText, $methods, PREG_OFFSET_CAPTURE);

        for ($i = 0; $i < count($methods[0]); $i++) {
            $methodName = $methods[1][$i][0];
            $startOffset = $methods[0][$i][1] + strlen($methods[0][$i][0]);
            $body = $this->extractBlock($fullText, $startOffset);
            $this->allMaps[$methodName] = $this->executeMethod($body);
        }
    }

    /** Extract a balanced {} block starting after the opening { */
    private function extractBlock($text, $offset) {
        $depth = 1;
        $start = $offset;
        $len = strlen($text);
        $i = $offset;
        while ($i < $len && $depth > 0) {
            if ($text[$i] === '{') $depth++;
            elseif ($text[$i] === '}') $depth--;
            $i++;
        }
        return substr($text, $start, $i - $start - 1);
    }

    /** Execute a method body and return the resulting map */
    private function executeMethod($body) {
        $this->localVars = [];
        $this->currentMap = [];

        // Split into statements (handling nested blocks for lambdas/loops)
        $statements = $this->splitStatements($body);

        foreach ($statements as $stmt) {
            $this->executeStatement(trim($stmt));
        }

        return $this->currentMap;
    }

    /** Split body into top-level statements */
    private function splitStatements($body) {
        $stmts = [];
        $current = '';
        $depth = 0;
        $inString = false;
        $len = strlen($body);
        for ($i = 0; $i < $len; $i++) {
            $ch = $body[$i];
            // Track string literals to avoid false brace/semicolon matching
            if ($ch === '"' && ($i === 0 || $body[$i - 1] !== '\\')) {
                $inString = !$inString;
            }
            if (!$inString) {
                if ($ch === '{') $depth++;
                elseif ($ch === '}') $depth--;
            }
            $current .= $ch;
            if (!$inString && $ch === ';' && $depth === 0) {
                $stmts[] = $current;
                $current = '';
            }
            // For bare blocks like for(...){...} that don't end with ;
            // Only split if the next non-whitespace is NOT ); (forEach lambda)
            if (!$inString && $ch === '}' && $depth === 0 && strpos($current, '{') !== false) {
                // Look ahead: skip whitespace, check for );
                $rest = ltrim(substr($body, $i + 1));
                if (strlen($rest) >= 2 && $rest[0] === ')' && ($rest[1] === ';' || $rest[1] === ')')) {
                    // This is part of a forEach lambda: });  — don't split, continue
                    continue;
                }
                if (strlen($rest) >= 1 && $rest[0] === ')') {
                    continue;
                }
                $stmts[] = $current;
                $current = '';
            }
        }
        if (trim($current) !== '') $stmts[] = $current;
        return $stmts;
    }

    /** Split function arguments at top-level commas (respecting strings and parens) */
    private function splitArgs($argsStr) {
        $args = [];
        $current = '';
        $depth = 0;
        $inString = false;
        $len = strlen($argsStr);
        for ($i = 0; $i < $len; $i++) {
            $ch = $argsStr[$i];
            if ($ch === '"' && ($i === 0 || $argsStr[$i-1] !== '\\')) $inString = !$inString;
            if (!$inString) {
                if ($ch === '(' || $ch === '{') $depth++;
                elseif ($ch === ')' || $ch === '}') $depth--;
                if ($ch === ',' && $depth === 0) {
                    $args[] = trim($current);
                    $current = '';
                    continue;
                }
            }
            $current .= $ch;
        }
        if (trim($current) !== '') $args[] = trim($current);
        return $args;
    }

    /** Extract arguments from a function call like func(arg1, arg2, ...) */
    private function extractCallArgs($stmt, $funcPattern) {
        if (preg_match($funcPattern, $stmt, $m, PREG_OFFSET_CAPTURE)) {
            $afterMatch = $m[0][1] + strlen($m[0][0]);
            // Find the balanced parenthesized args
            $rest = substr($stmt, $afterMatch);
            if ($rest[0] === '(') {
                $depth = 0;
                $len = strlen($rest);
                for ($i = 0; $i < $len; $i++) {
                    if ($rest[$i] === '(') $depth++;
                    elseif ($rest[$i] === ')') { $depth--; if ($depth === 0) break; }
                }
                $inner = substr($rest, 1, $i - 1);
                return ['args' => $this->splitArgs($inner), 'match' => $m];
            }
        }
        return null;
    }

    /** Execute a single statement */
    private function executeStatement($stmt) {
        $stmt = trim($stmt);
        if ($stmt === '' || $stmt === 'return' || strpos($stmt, 'return ') === 0) return;
        // Strip leading single-line comments (they may precede real code in the same statement)
        $stmt = preg_replace('/^\/\/[^\n]*\n\s*/m', '', $stmt);
        $stmt = trim($stmt);
        if ($stmt === '') return;

        // Local List<String> var = Lists.newArrayList(...)
        if (preg_match('/List<String>\s+(\w+)\s*=\s*Lists\.newArrayList\((.*)\)\s*;/s', $stmt, $m)) {
            $this->localVars[$m[1]] = $this->parseStringList($m[2]);
            return;
        }

        // Local String var = "..." or String var = expr
        if (preg_match('/String\s+(\w+)\s*=\s*(.*?)\s*;/s', $stmt, $m)) {
            $this->localVars[$m[1]] = $this->resolveExpr($m[2]);
            return;
        }

        // Local Map<String, String> var = new HashMap<>(...); 
        if (preg_match('/Map<String,\s*String>\s+\w+\s*=\s*new HashMap/', $stmt)) return;

        // --- Loops MUST be checked BEFORE buildLocateChar/putAll/put ---

        // list.forEach(item -> { ... });  (multi-line lambda)
        if (preg_match('/(\w+)\.forEach\s*\(\s*(\w+)\s*->\s*\{(.*)\}\s*\)\s*;?/s', $stmt, $m)) {
            $list = $this->resolveVar($m[1]);
            $iterVar = $m[2];
            $lambdaBody = $m[3];
            if (is_array($list)) {
                foreach ($list as $item) {
                    $this->localVars[$iterVar] = $item;
                    $innerStmts = $this->splitStatements($lambdaBody);
                    foreach ($innerStmts as $inner) {
                        $this->executeStatement(trim($inner));
                    }
                }
                unset($this->localVars[$iterVar]);
            }
            return;
        }

        // list.forEach(item -> singleExpr);  (single-line lambda)
        if (preg_match('/(\w+)\.forEach\s*\(\s*(\w+)\s*->\s*(?!\{)(.+)\)\s*;/s', $stmt, $m)) {
            $list = $this->resolveVar($m[1]);
            $iterVar = $m[2];
            $lambdaExpr = trim($m[3]);
            if (is_array($list)) {
                foreach ($list as $item) {
                    $this->localVars[$iterVar] = $item;
                    $this->executeStatement($lambdaExpr . ';');
                }
                unset($this->localVars[$iterVar]);
            }
            return;
        }

        // for (String s : list) { ... }
        if (preg_match('/for\s*\(\s*String\s+(\w+)\s*:\s*(\w+)\s*\)\s*\{(.*)\}/s', $stmt, $m)) {
            $iterVar = $m[1];
            $list = $this->resolveVar($m[2]);
            $loopBody = $m[3];
            if (is_array($list)) {
                foreach ($list as $item) {
                    $this->localVars[$iterVar] = $item;
                    $innerStmts = $this->splitStatements($loopBody);
                    foreach ($innerStmts as $inner) {
                        $this->executeStatement(trim($inner));
                    }
                }
                unset($this->localVars[$iterVar]);
            }
            return;
        }

        // --- Simple calls (after loops) ---

        // buildLocateChar(map, expr1, expr2);
        if (preg_match('/buildLocateChar\s*\(/', $stmt)) {
            $r = $this->extractCallArgs($stmt, '/buildLocateChar/');
            if ($r && count($r['args']) >= 3) {
                $key = $this->resolveExpr($r['args'][1]);
                $val = $this->resolveExpr($r['args'][2]);
                $this->addLocateChar($key, $val);
                return;
            }
        }

        // putAll(map, expr1, expr2);
        if (preg_match('/putAll\s*\(/', $stmt)) {
            $r = $this->extractCallArgs($stmt, '/putAll/');
            if ($r && count($r['args']) >= 3) {
                $key = $this->resolveExpr($r['args'][1]);
                $val = $this->resolveExpr($r['args'][2]);
                $this->addLocateChar($key, $val);
                return;
            }
        }

        // map.put(expr1, expr2);  or mapper.put / codeMapper.put / etc.
        if (preg_match('/\w+\.put\s*\(/', $stmt) && !preg_match('/\.putAll/', $stmt)) {
            $r = $this->extractCallArgs($stmt, '/\w+\.put/');
            if ($r && count($r['args']) >= 2) {
                $key = $this->resolveExpr($r['args'][0]);
                $val = $this->resolveExpr($r['args'][1]);
                $this->currentMap[$key] = $val;
                return;
            }
        }

        // notSupportSet.add(...) - ignore
        if (preg_match('/notSupportSet\.add/', $stmt)) return;

        // Ignore single-line comments
        if (preg_match('/^\/\//', $stmt)) return;

        // Debug: show unhandled statements
        $short = substr(preg_replace('/\s+/', ' ', $stmt), 0, 120);
        fprintf(STDERR, "  UNHANDLED: %s\n", $short);
    }

    /** Resolve a Java expression to a PHP string value */
    private function resolveExpr($expr) {
        $expr = trim($expr);

        // String concatenation: split by +
        $parts = $this->splitConcat($expr);
        if (count($parts) > 1) {
            $result = '';
            foreach ($parts as $part) {
                $result .= $this->resolveExpr($part);
            }
            return $result;
        }

        // Quoted string
        if (preg_match('/^"((?:[^"\\\\]|\\\\.)*)"$/', $expr, $m)) {
            return javaUnescape($m[1]);
        }

        // Character literal '\uXXXX'
        if (preg_match("/^'((?:[^'\\\\]|\\\\.)*)'$/", $expr, $m)) {
            return javaUnescape($m[1]);
        }

        // Variable reference
        return $this->resolveVar($expr);
    }

    /** Split expression by + respecting quotes */
    private function splitConcat($expr) {
        $parts = [];
        $current = '';
        $inQuote = false;
        $len = strlen($expr);
        for ($i = 0; $i < $len; $i++) {
            $ch = $expr[$i];
            if ($ch === '"' && ($i === 0 || $expr[$i - 1] !== '\\')) {
                $inQuote = !$inQuote;
                $current .= $ch;
            } elseif ($ch === '+' && !$inQuote) {
                $parts[] = trim($current);
                $current = '';
            } else {
                $current .= $ch;
            }
        }
        if (trim($current) !== '') $parts[] = trim($current);
        return $parts;
    }

    /** Resolve a variable name to its value */
    private function resolveVar($name) {
        $name = trim($name);
        if (isset($this->localVars[$name])) return $this->localVars[$name];
        if (isset($this->fields[$name])) return $this->fields[$name];
        return $name;
    }

    /** Add 4 space-variant entries (buildLocateChar / putAll pattern) */
    private function addLocateChar($key, $val) {
        $this->currentMap[" " . $key] = $val;
        $this->currentMap[$key] = $val;
        $this->currentMap[$key . " "] = $val;
        $this->currentMap[" " . $key . " "] = $val;
    }
}

// ============================================================
// PHP Source Generator
// ============================================================

function generatePhpSource($namespace, $className, $maps, $methodOrder, $isSimple = false) {
    // For simple mappers (To*), all entries go in one flat array
    // For complex mappers (From*), we merge all method results

    $allEntries = [];
    foreach ($methodOrder as $method) {
        if (isset($maps[$method])) {
            $allEntries = array_merge($allEntries, $maps[$method]);
        }
    }
    // Also include methods not in order (fallback)
    foreach ($maps as $name => $entries) {
        if (!in_array($name, $methodOrder)) {
            $allEntries = array_merge($allEntries, $entries);
        }
    }

    $src = "<?php\n\nnamespace $namespace;\n\nclass $className\n{\n";
    $src .= "    private static \$mapper = null;\n\n";
    $src .= "    public static function getMapper()\n    {\n";
    $src .= "        if (self::\$mapper === null) {\n";
    $src .= "            self::\$mapper = [\n";
    foreach ($allEntries as $k => $v) {
        $src .= "                " . toPhpLiteral($k) . " => " . toPhpLiteral($v) . ",\n";
    }
    $src .= "            ];\n";
    $src .= "        }\n";
    $src .= "        return self::\$mapper;\n";
    $src .= "    }\n";
    $src .= "}\n";
    return $src;
}

function generatePhpSourceMultiMap($namespace, $className, $mapDefs) {
    // $mapDefs: array of ['field' => name, 'method' => name, 'entries' => [...]]
    $src = "<?php\n\nnamespace $namespace;\n\nclass $className\n{\n";
    foreach ($mapDefs as $def) {
        $src .= "    private static \$" . $def['field'] . " = null;\n";
    }
    $src .= "\n";
    foreach ($mapDefs as $def) {
        $src .= "    public static function " . $def['method'] . "()\n    {\n";
        $src .= "        if (self::\$" . $def['field'] . " === null) {\n";
        $src .= "            self::\$" . $def['field'] . " = [\n";
        foreach ($def['entries'] as $k => $v) {
            $src .= "                " . toPhpLiteral($k) . " => " . toPhpLiteral($v) . ",\n";
        }
        $src .= "            ];\n";
        $src .= "        }\n";
        $src .= "        return self::\$" . $def['field'] . ";\n";
        $src .= "    }\n\n";
    }
    $src .= "}\n";
    return $src;
}

// ============================================================
// Main: Generate all mappers
// ============================================================

$configs = [
    [
        'java' => $javaBase . '/shape/from/menk/FromMenkShapeCodeMapper.java',
        'php'  => $phpBase . '/Menk/FromMenkShapeMapper.php',
        'ns'   => 'Meco\\Rules\\Menk',
        'cls'  => 'FromMenkShapeMapper',
    ],
    [
        'java' => $javaBase . '/shape/to/menk/ToMenkShapeCodeMapper.java',
        'php'  => $phpBase . '/Menk/ToMenkShapeMapper.php',
        'ns'   => 'Meco\\Rules\\Menk',
        'cls'  => 'ToMenkShapeMapper',
    ],
    [
        'java' => $javaBase . '/shape/from/z52/FromZ52CodeMapper.java',
        'php'  => $phpBase . '/Z52/FromZ52Mapper.php',
        'ns'   => 'Meco\\Rules\\Z52',
        'cls'  => 'FromZ52Mapper',
    ],
    [
        'java' => $javaBase . '/shape/to/z52/ToZ52CodeMapper.java',
        'php'  => $phpBase . '/Z52/ToZ52Mapper.php',
        'ns'   => 'Meco\\Rules\\Z52',
        'cls'  => 'ToZ52Mapper',
    ],
    [
        'java' => $javaBase . '/letter/from/menk/FromMenkLetterCodeMapper.java',
        'php'  => $phpBase . '/Menk/FromMenkLetterMapper.php',
        'ns'   => 'Meco\\Rules\\Menk',
        'cls'  => 'FromMenkLetterMapper',
    ],
    [
        'java' => $javaBase . '/letter/to/menk/ToMenkLetterCodeMapper.java',
        'php'  => $phpBase . '/Menk/ToMenkLetterMapper.php',
        'ns'   => 'Meco\\Rules\\Menk',
        'cls'  => 'ToMenkLetterMapper',
    ],
    [
        'java' => $javaBase . '/letter/from/delehi/FromDelehiCodeMapper.java',
        'php'  => $phpBase . '/Delehi/FromDelehiMapper.php',
        'ns'   => 'Meco\\Rules\\Delehi',
        'cls'  => 'FromDelehiMapper',
    ],
    [
        'java' => $javaBase . '/letter/to/delehi/ToDelehiCodeMapper.java',
        'php'  => $phpBase . '/Delehi/ToDelehiMapper.php',
        'ns'   => 'Meco\\Rules\\Delehi',
        'cls'  => 'ToDelehiMapper',
    ],
];

foreach ($configs as $cfg) {
    $javaFile = $cfg['java'];
    echo "Processing: " . basename($javaFile) . "\n";

    if (!file_exists($javaFile)) {
        echo "  ERROR: File not found: $javaFile\n";
        continue;
    }

    $parser = new JavaMapperParser();
    $maps = $parser->parse($javaFile);
    $order = $parser->getMethodOrder();

    echo "  Methods found: " . implode(', ', array_keys($maps)) . "\n";
    echo "  Method order: " . implode(', ', $order) . "\n";

    // Special handling for ToMenkLetterMapper (has 3 maps: mapper, chaghMapper, hundiiMapper)
    if ($cfg['cls'] === 'ToMenkLetterMapper') {
        $mainEntries = [];
        $chaghEntries = [];
        $hundiiEntries = [];
        foreach ($order as $method) {
            if (isset($maps[$method])) {
                if (strpos($method, 'Chagh') !== false || strpos($method, 'chagh') !== false) {
                    $chaghEntries = array_merge($chaghEntries, $maps[$method]);
                } elseif (strpos($method, 'Hundii') !== false || strpos($method, 'hundii') !== false) {
                    $hundiiEntries = array_merge($hundiiEntries, $maps[$method]);
                } else {
                    $mainEntries = array_merge($mainEntries, $maps[$method]);
                }
            }
        }
        $totalEntries = count($mainEntries) + count($chaghEntries) + count($hundiiEntries);
        echo "  Total entries: $totalEntries (mapper=" . count($mainEntries) . ", chagh=" . count($chaghEntries) . ", hundii=" . count($hundiiEntries) . ")\n";
        $src = generatePhpSourceMultiMap($cfg['ns'], $cfg['cls'], [
            ['field' => 'mapper', 'method' => 'getMapper', 'entries' => $mainEntries],
            ['field' => 'chaghMapper', 'method' => 'getChaghMapper', 'entries' => $chaghEntries],
            ['field' => 'hundiiMapper', 'method' => 'getHundiiMapper', 'entries' => $hundiiEntries],
        ]);
    }
    // Special handling for From*LetterMapper (has chagh, hundii, saarmag extra maps)
    elseif (in_array($cfg['cls'], ['FromMenkLetterMapper', 'FromDelehiMapper'])) {
        $mainEntries = [];
        $chaghEntries = isset($maps['buildChagh']) ? $maps['buildChagh'] : [];
        $hundiiEntries = isset($maps['buildHundii']) ? $maps['buildHundii'] : [];
        $saarmagEntries = isset($maps['buildSaarmag']) ? $maps['buildSaarmag'] : [];
        $wWithEhshigEntries = isset($maps['buildWWithEhshig']) ? $maps['buildWWithEhshig'] : [];
        $skipMethods = ['buildChagh', 'buildHundii', 'buildSaarmag', 'buildMapper', 'buildWWithEhshig'];
        foreach ($order as $method) {
            if (isset($maps[$method]) && !in_array($method, $skipMethods)) {
                $mainEntries = array_merge($mainEntries, $maps[$method]);
            }
        }
        $totalEntries = count($mainEntries) + count($chaghEntries) + count($hundiiEntries) + count($saarmagEntries);
        echo "  Total entries: $totalEntries (mapper=" . count($mainEntries) . ", chagh=" . count($chaghEntries) . ", hundii=" . count($hundiiEntries) . ", saarmag=" . count($saarmagEntries) . ")\n";

        $mapDefs = [
            ['field' => 'mapper', 'method' => 'getMapper', 'entries' => $mainEntries],
            ['field' => 'chaghMapper', 'method' => 'getChaghMapper', 'entries' => $chaghEntries],
            ['field' => 'hundiiMapper', 'method' => 'getHundiiMapper', 'entries' => $hundiiEntries],
            ['field' => 'saarmagMapper', 'method' => 'getSaarmagMapper', 'entries' => $saarmagEntries],
        ];
        if (!empty($wWithEhshigEntries)) {
            $mapDefs[] = ['field' => 'wWithEhshig', 'method' => 'getWWithEhshig', 'entries' => $wWithEhshigEntries];
        }

        $src = generatePhpSourceMultiMap($cfg['ns'], $cfg['cls'], $mapDefs);

        // Add doubleIEhishig property
        $doubleI = $cfg['cls'] === 'FromDelehiMapper'
            ? '    public static $doubleIEhishig = ["\\u{1820}", "\\u{1821}", "\\u{1822}", "\\u{1823}", "\\u{1824}"];' . "\n"
            : '    public static $doubleIEhishig = ["\\u{1820}", "\\u{1821}", "\\u{1823}", "\\u{1824}"];' . "\n";
        $src = str_replace("\n\n    public static function getMapper", "\n" . $doubleI . "\n    public static function getMapper", $src);
    }
    else {
        $allEntries = [];
        foreach ($order as $method) {
            if (isset($maps[$method])) {
                $allEntries = array_merge($allEntries, $maps[$method]);
            }
        }
        // Fallback: add any methods not in order
        foreach ($maps as $name => $entries) {
            if (!in_array($name, $order)) {
                $allEntries = array_merge($allEntries, $entries);
            }
        }
        echo "  Total entries: " . count($allEntries) . "\n";
        $src = generatePhpSource($cfg['ns'], $cfg['cls'], $maps, $order);
    }

    file_put_contents($cfg['php'], $src);
    echo "  Written: " . $cfg['php'] . "\n\n";
}

echo "Done!\n";
