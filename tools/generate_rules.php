<?php
/**
 * Java-to-PHP TranslateRule Generator
 *
 * Reads Java TranslateRule source files and translates them to PHP.
 * Re-run this script whenever the Java source changes.
 *
 * Usage: php tools/generate_rules.php
 */

$javaBase = __DIR__ . '/../../meco/meco/src/main/java/com/zvvnmod/meco/translate';
$phpBase  = __DIR__ . '/../src/Rules';

// ============================================================
// Java-to-PHP class/method name mappings
// ============================================================

$classMap = [
    // Mapper class references
    'FromMenkLetterCodeMapper' => 'FromMenkLetterMapper',
    'FromDelehiCodeMapper' => 'FromDelehiMapper',
    'ToMenkLetterCodeMapper' => 'ToMenkLetterMapper',
    'ToDelehiCodeMapper' => 'ToDelehiMapper',
    'FromMenkShapeCodeMapper' => 'FromMenkShapeMapper',
    'ToMenkShapeCodeMapper' => 'ToMenkShapeMapper',
    'FromZ52CodeMapper' => 'FromZ52Mapper',
    'ToZ52CodeMapper' => 'ToZ52Mapper',
    // Unicode block references
    'MglUnicodeBlock' => 'MglUnicode',
    'ZvvnModUnicodeBlock' => 'ZvvnmodUnicode',
    'Z52UnicodeBlock' => 'Z52Unicode',
    'DelehiCodeBlock' => 'DelehiCodeBlock',
];

// Java field names -> PHP method/property mappings
$fieldMap = [
    '.mapper' => '::getMapper()',
    '.chaghMapper' => '::getChaghMapper()',
    '.hundiiMapper' => '::getHundiiMapper()',
    '.saarmag' => '::getSaarmagMapper()',
    '.wWithEhshig' => '::getWWithEhshig()',
    '.codeMapper' => '::getMapper()',
    '.doubleIEhishig' => '::$doubleIEhishig',
];

// Static field sets -> PHP method calls
$setContainsMap = [
    'zvvnModCodes.contains' => 'isZvvnmodCode',
    'zvvnModPunctuations.contains' => 'isZvvnmodPunctuation',
    'zvvnModTailCodes.contains' => 'isZvvnmodTailCode',
    'z52Codes.contains' => 'isZ52Code',
    'z52CodePunctuations.contains' => 'isZ52Punctuation',
    'toZ52Punctuations.contains' => 'isToZ52Punctuation',
    'notSupportSet.contains' => 'isNotSupported',
];

// ============================================================
// Namespace mappings for PHP files
// ============================================================

$useMap = [
    'MglUnicode' => 'Meco\\Unicode\\MglUnicode',
    'ZvvnmodUnicode' => 'Meco\\Unicode\\ZvvnmodUnicode',
    'Z52Unicode' => 'Meco\\Unicode\\Z52Unicode',
    'DelehiCodeBlock' => 'Meco\\Rules\\Delehi\\DelehiCodeBlock',
    'Nature' => 'Meco\\Enums\\Nature',
    'CharType' => 'Meco\\Enums\\CharType',
    'LetterTranslateRuleFrom' => 'Meco\\Rules\\LetterTranslateRuleFrom',
    'LetterTranslateRuleTo' => 'Meco\\Rules\\LetterTranslateRuleTo',
    'ShapeTranslateRule' => 'Meco\\Rules\\ShapeTranslateRule',
];

// ============================================================
// Config: which Java files to translate
// ============================================================

$configs = [
    [
        'java' => $javaBase . '/letter/from/menk/MenkLetterTranslateRuleFrom.java',
        'php'  => $phpBase . '/Menk/MenkLetterTranslateRuleFrom.php',
        'ns'   => 'Meco\\Rules\\Menk',
        'cls'  => 'MenkLetterTranslateRuleFrom',
        'impl' => 'LetterTranslateRuleFrom',
        'localUse' => ['FromMenkLetterMapper', 'MenkCodeBlock'],
    ],
    [
        'java' => $javaBase . '/letter/from/delehi/DelehiTranslateRuleFrom.java',
        'php'  => $phpBase . '/Delehi/DelehiTranslateRuleFrom.php',
        'ns'   => 'Meco\\Rules\\Delehi',
        'cls'  => 'DelehiTranslateRuleFrom',
        'impl' => 'LetterTranslateRuleFrom',
        'localUse' => ['FromDelehiMapper', 'DelehiCodeBlock'],
    ],
    [
        'java' => $javaBase . '/letter/to/menk/MenkTranslateRuleTo.java',
        'php'  => $phpBase . '/Menk/MenkLetterTranslateRuleTo.php',
        'ns'   => 'Meco\\Rules\\Menk',
        'cls'  => 'MenkLetterTranslateRuleTo',
        'impl' => 'LetterTranslateRuleTo',
        'localUse' => ['ToMenkLetterMapper'],
    ],
    [
        'java' => $javaBase . '/letter/to/delehi/DelehiTranslateRuleTo.java',
        'php'  => $phpBase . '/Delehi/DelehiTranslateRuleTo.php',
        'ns'   => 'Meco\\Rules\\Delehi',
        'cls'  => 'DelehiTranslateRuleTo',
        'impl' => 'LetterTranslateRuleTo',
        'localUse' => ['ToDelehiMapper'],
    ],
    [
        'java' => $javaBase . '/shape/from/menk/MenkShapeTranslateRuleFrom.java',
        'php'  => $phpBase . '/Menk/MenkShapeTranslateRuleFrom.php',
        'ns'   => 'Meco\\Rules\\Menk',
        'cls'  => 'MenkShapeTranslateRuleFrom',
        'impl' => 'ShapeTranslateRule',
        'localUse' => ['FromMenkShapeMapper', 'MenkShapeUnicodeBlock'],
    ],
    [
        'java' => $javaBase . '/shape/to/menk/MenkShapeTranslateRuleTo.java',
        'php'  => $phpBase . '/Menk/MenkShapeTranslateRuleTo.php',
        'ns'   => 'Meco\\Rules\\Menk',
        'cls'  => 'MenkShapeTranslateRuleTo',
        'impl' => 'ShapeTranslateRule',
        'localUse' => ['ToMenkShapeMapper'],
    ],
    [
        'java' => $javaBase . '/shape/from/z52/Z52TranslateRuleFrom.java',
        'php'  => $phpBase . '/Z52/Z52TranslateRuleFrom.php',
        'ns'   => 'Meco\\Rules\\Z52',
        'cls'  => 'Z52TranslateRuleFrom',
        'impl' => 'ShapeTranslateRule',
        'localUse' => ['FromZ52Mapper'],
    ],
    [
        'java' => $javaBase . '/shape/to/z52/Z52TranslateRuleTo.java',
        'php'  => $phpBase . '/Z52/Z52TranslateRuleTo.php',
        'ns'   => 'Meco\\Rules\\Z52',
        'cls'  => 'Z52TranslateRuleTo',
        'impl' => 'ShapeTranslateRule',
        'localUse' => ['ToZ52Mapper'],
    ],
];

// ============================================================
// Translation functions
// ============================================================

function translateJavaToPhp($javaSource, $cfg) {
    global $classMap, $fieldMap, $setContainsMap, $useMap;

    $lines = explode("\n", $javaSource);
    $methods = extractJavaMethods($javaSource);

    // Build PHP
    $php = "<?php\n\nnamespace {$cfg['ns']};\n\n";

    // Collect use statements
    $uses = [];
    if (isset($useMap[$cfg['impl']])) {
        $uses[] = $useMap[$cfg['impl']];
    }

    // Scan source for needed imports
    foreach (['MglUnicode', 'ZvvnmodUnicode', 'Z52Unicode', 'DelehiCodeBlock', 'Nature', 'CharType'] as $cls) {
        $javaName = array_search($cls, $classMap) ?: $cls;
        if (strpos($javaSource, $javaName) !== false && isset($useMap[$cls])) {
            $uses[] = $useMap[$cls];
        }
    }
    // Also check for Nature enum
    if (strpos($javaSource, 'Nature') !== false && isset($useMap['Nature'])) {
        $uses[] = $useMap['Nature'];
    }

    $uses = array_unique($uses);
    sort($uses);
    foreach ($uses as $u) {
        $php .= "use $u;\n";
    }
    $php .= "\n";

    $php .= "class {$cfg['cls']} implements {$cfg['impl']}\n{\n";

    // Singleton pattern
    $php .= "    private static \$instance = null;\n\n";
    $php .= "    public static function getInstance()\n";
    $php .= "    {\n";
    $php .= "        if (self::\$instance === null) {\n";
    $php .= "            self::\$instance = new self();\n";
    $php .= "        }\n";
    $php .= "        return self::\$instance;\n";
    $php .= "    }\n";

    // Translate each method
    foreach ($methods as $method) {
        $phpMethod = translateMethod($method, $cfg);
        if ($phpMethod !== null) {
            $php .= "\n" . $phpMethod . "\n";
        }
    }

    if ($cfg['cls'] === 'MenkShapeTranslateRuleFrom') {
        $php = str_replace(
            "        if (notSupportSet.contains(\$c)) {\n            return false;\n        }\n        return (\$c >= \"\\u{e263}\" && \$c <= \"\\u{e34a}\") || (\$c >= \"\\u{e234}\" && \$c <= \"\\u{e261}\");",
            "        return MenkShapeUnicodeBlock::isTranslateCodePoint(\$c);",
            $php
        );
        $php = str_replace(
            "        return \$c >= \"\\u{e264}\" && \$c <= \"\\u{e34f}\";",
            "        return MenkShapeUnicodeBlock::isWordCodePoint(\$c);",
            $php
        );
        $php = str_replace(
            "        return isWordCodePoint(\$ch) ? CharType::MONGOLIAN : CharType::OTHER;",
            "        return \$this->isWordCodePoint(\$ch) ? CharType::MONGOLIAN : CharType::OTHER;",
            $php
        );
    }

    $php .= "}\n";
    return $php;
}

function extractJavaMethods($source) {
    $methods = [];
    // Match method signatures and extract balanced bodies
    $pattern = '/((?:@Override\s+)?(?:public|private|protected)\s+(?:static\s+)?(?:[\w<>,\s]+)\s+(\w+)\s*\([^)]*\)\s*\{)/s';
    preg_match_all($pattern, $source, $matches, PREG_OFFSET_CAPTURE);

    for ($i = 0; $i < count($matches[0]); $i++) {
        $sig = $matches[1][$i][0];
        $name = $matches[2][$i][0];
        $offset = $matches[0][$i][1] + strlen($matches[0][$i][0]);

        // Find matching closing brace
        $depth = 1;
        $len = strlen($source);
        $j = $offset;
        $inString = false;
        while ($j < $len && $depth > 0) {
            $ch = $source[$j];
            if ($ch === '"' && ($j === 0 || $source[$j-1] !== '\\')) $inString = !$inString;
            if (!$inString) {
                if ($ch === '{') $depth++;
                elseif ($ch === '}') $depth--;
            }
            $j++;
        }

        $body = substr($source, $offset, $j - $offset - 1);
        $methods[] = [
            'signature' => $sig,
            'name' => $name,
            'body' => $body,
            'full' => substr($source, $matches[0][$i][1], $j - $matches[0][$i][1]),
        ];
    }
    return $methods;
}

function translateMethod($method, $cfg) {
    $name = $method['name'];
    $body = $method['body'];
    $sig = $method['signature'];

    // Skip constructor, static initializer, logger
    if ($name === 'buildChagh' || $name === 'buildHundii' || $name === 'buildSaarmag' ||
        $name === 'buildMapper' || $name === 'buildWWithEhshig') {
        return null;
    }

    // Translate signature
    $phpSig = translateSignature($sig);
    if ($phpSig === null) return null;

    // Extract parameter names for variable tracking
    $paramNames = [];
    if (preg_match('/\(([^)]*)\)/', $sig, $pm)) {
        preg_match_all('/\b(\w+)\s*[,)]/', $pm[1] . ')', $pn);
        foreach ($pn[1] as $p) $paramNames[$p] = true;
    }

    // Translate body
    $phpBody = translateBody($body, $cfg, $paramNames);

    return $phpSig . "\n    {\n" . $phpBody . "    }";
}

function translateSignature($sig) {
    // Remove @Override
    $sig = preg_replace('/@Override\s+/', '', $sig);

    // Extract: visibility, return_type, name, params
    if (!preg_match('/(public|private|protected)\s+(?:static\s+)?(\S+)\s+(\w+)\s*\(([^)]*)\)/', $sig, $m)) {
        return null;
    }

    $visibility = $m[1];
    $name = $m[3];
    $params = trim($m[4]);

    // Translate parameters
    $phpParams = translateParams($params);

    // For LetterTranslateRuleTo.getMapperCode, builder is pass-by-reference
    if ($name === 'getMapperCode' && strpos($phpParams, '$builder') !== false) {
        $phpParams = str_replace('$builder', '&$builder', $phpParams);
    }

    return "    $visibility function $name($phpParams)";
}

function translateParams($params) {
    if (empty($params)) return '';

    $parts = preg_split('/\s*,\s*/', $params);
    $phpParts = [];
    foreach ($parts as $part) {
        $part = trim($part);
        $part = preg_replace('/\bfinal\s+/', '', $part);
        // Extract variable name (last word)
        if (preg_match('/(\w+)$/', $part, $m)) {
            $phpParts[] = '$' . $m[1];
        }
    }
    return implode(', ', $phpParts);
}

function translateBody($body, $cfg, $paramNames = []) {
    global $classMap, $fieldMap, $setContainsMap;

    // Collect all variable names from the method body + parameters
    $varNames = $paramNames;
    // Local variable declarations: Type varName
    preg_match_all('/\b(?:String|Character|boolean|char|int|MapperResult)\s+(\w+)/', $body, $vm);
    foreach ($vm[1] as $v) $varNames[$v] = true;
    // Also common var names from for-each loops
    preg_match_all('/for\s*\([^)]*\s+(\w+)\s*:/', $body, $vm);
    foreach ($vm[1] as $v) $varNames[$v] = true;

    $lines = explode("\n", $body);
    $result = '';

    foreach ($lines as $line) {
        $translated = translateLine($line, $cfg, $varNames);
        if ($translated !== null) {
            $result .= $translated . "\n";
        }
    }
    return $result;
}

function translateLine($line, $cfg, $varNames = []) {
    global $classMap, $fieldMap, $setContainsMap;

    $trimmed = trim($line);

    // Skip logger lines
    if (strpos($trimmed, 'logger.') !== false) return null;
    // Skip throw lines and their continuation (multi-line throw statements)
    if (strpos($trimmed, 'throw new') !== false) return null;
    if (preg_match('/^\s*".*in mapper rule"/', $trimmed)) return null;

    // Preserve empty lines and comments
    if ($trimmed === '' || strpos($trimmed, '//') === 0) {
        return $line;
    }

    $out = $line;

    // Unicode escapes: \uXXXX -> \u{XXXX}
    $out = preg_replace('/\\\\u([0-9a-fA-F]{4})/', '\\u{$1}', $out);
    // Fix single-quoted unicode: '\u{XXXX}' -> "\u{XXXX}" (PHP needs double quotes)
    $out = preg_replace("/\'((?:\\\\u\\{[0-9a-fA-F]{4}\\})+)\'/", '"$1"', $out);

    // Variable declarations: Type varName = ... -> $varName = ...
    $out = preg_replace('/\b(?:String|Character|boolean|char|int|MapperResult)\s+(\w+)\s*=/', '\$$1 =', $out);
    $out = preg_replace('/\b(?:String|Character|boolean|char|int|MapperResult)\s+(\w+)\s*;/', '\$$1 = null;', $out);

    // for-each: for (Type var : expr) -> foreach (expr as $var)
    if (preg_match('/for\s*\(\s*(?:char|Character|String|ShapeWordFragment)\s+(\w+)\s*:\s*(.+)\)\s*\{?/', $out, $fm)) {
        $iterVar = $fm[1];
        $collection = trim($fm[2]);
        // Handle method calls: obj.method() -> $obj->method()
        if (preg_match('/^(\w+)\.(.+)$/', $collection, $cm)) {
            $objName = $cm[1];
            $rest = $cm[2];
            if (isset($varNames[$objName]) || preg_match('/^[a-z]/', $objName)) {
                $collection = '$' . $objName . '->' . $rest;
            }
        } elseif (isset($varNames[$collection]) || preg_match('/^[a-z]/', $collection)) {
            $collection = '$' . $collection;
        }
        // Replace the entire for statement
        $out = preg_replace('/for\s*\(\s*(?:char|Character|String|ShapeWordFragment)\s+\w+\s*:\s*.+\)/',
            "foreach ($collection as \$$iterVar)", $out);
    }

    // MapperResult -> array [ret, value]
    $out = preg_replace('/new MapperResult\(false\)/', '[false, null]', $out);
    $out = preg_replace('/new MapperResult\(true,\s*(.+?)\)/', '[true, $1]', $out);
    $out = preg_replace('/new MapperResult\(true\)/', '[true, null]', $out);

    // Resolve mapper field references BEFORE variable prefixing
    foreach ($classMap as $javaClass => $phpClass) {
        // doubleIEhishig property
        $out = str_replace($javaClass . '.doubleIEhishig', $phpClass . '::$doubleIEhishig', $out);

        foreach ($fieldMap as $jField => $phpAccess) {
            $javaRef = preg_quote($javaClass . $jField, '/');
            $phpRef = $phpClass . $phpAccess;
            // .get(expr) -> (PhpClass::method()[expr] ?? null) — handle nested parens
            $out = preg_replace('/' . $javaRef . '\.get\(([^()]*(?:\([^()]*\)[^()]*)*)\)/',
                '(' . $phpRef . '[$1] ?? null)', $out);
            // .containsKey(expr) -> isset(PhpClass::method()[expr])
            $out = preg_replace('/' . $javaRef . '\.containsKey\(([^()]*(?:\([^()]*\)[^()]*)*)\)/',
                'isset(' . $phpRef . '[$1])', $out);
        }

        // Static set.contains patterns
        foreach ($setContainsMap as $setMethod => $phpMethod) {
            $out = preg_replace('/' . preg_quote($javaClass . '.' . $setMethod, '/') . '\(([^)]+)\)/',
                $phpClass . '::' . $phpMethod . '($1)', $out);
        }

        // Regular static method calls: Class.method(args) -> PhpClass::method(args)
        $out = preg_replace('/' . preg_quote($javaClass, '/') . '\.(\w+)\(/', $phpClass . '::$1(', $out);
    }

    // Enum references
    $out = str_replace('Nature.', 'Nature::', $out);
    $out = str_replace('CharType.', 'CharType::', $out);

    // Now add $ prefix to ALL known variable names
    // Build a list of all variable names (parameters + locals)
    // We do this by matching word boundaries for known var names
    $allVarNames = array_keys($varNames);
    // Sort by length descending to avoid partial matches
    usort($allVarNames, function($a, $b) { return strlen($b) - strlen($a); });

    foreach ($allVarNames as $var) {
        // Skip if already has $ prefix, and don't match inside strings or class names
        // Use word boundary matching, but not after $ or :: or ->
        $out = preg_replace('/(?<![$\w:>])(?<!\->)\b' . preg_quote($var, '/') . '\b(?!\s*\()/', '\$' . $var, $out);
    }

    // .equals / .contains — process NEGATED forms FIRST to prevent partial match
    $out = preg_replace('/!(\$\w+)\.equals\("([^"]*)"\)/', '$1 !== "$2"', $out);
    $out = preg_replace('/(\$\w+)\.equals\("([^"]*)"\)/', '$1 === "$2"', $out);
    // .equals with single-quoted char arg (Java char literals)
    $out = preg_replace("/!(\\$\\w+)\\.equals\\('([^']*)'\\)/", '$1 !== "$2"', $out);
    $out = preg_replace("/(\\$\\w+)\\.equals\\('([^']*)'\\)/", '$1 === "$2"', $out);
    // .equals with enum/constant arg: $var.equals(Nature::X) -> $var === Nature::X
    $out = preg_replace('/(\$\w+)\.equals\((Nature::\w+|CharType::\w+)\)/', '$1 === $2', $out);
    // Negated .contains before non-negated
    $out = preg_replace('/!(\$\w+)\.contains\("([^"]*)"\)/', 'mb_strpos($1, "$2") === false', $out);
    $out = preg_replace('/(\$\w+)\.contains\("([^"]*)"\)/', 'mb_strpos($1, "$2") !== false', $out);

    // .isRet() / .getSb() for MapperResult arrays
    $out = preg_replace('/(\$\w+)\.isRet\(\)/', '$1[0]', $out);
    $out = preg_replace('/(\$\w+)\.getSb\(\)/', '$1[1]', $out);

    // List/array methods on $-prefixed vars
    $out = preg_replace('/(\$\w+)\.get\((\$\w+)\.size\(\)\s*-\s*(\d+)\)/', '$1[count($1) - $3]', $out);
    $out = preg_replace('/(\$\w+)\.get\((\d+)\)/', '$1[$2]', $out);
    $out = preg_replace('/(\$\w+)\.size\(\)/', 'count($1)', $out);
    // IMPORTANT: convert .length() BEFORE .charAt() so charAt args are resolved
    $out = preg_replace('/(\$\w+)\.length\(\)/', 'mb_strlen($1, \'UTF-8\')', $out);
    // .charAt() patterns — simple args first, then complex
    $out = preg_replace('/(\$\w+)\.charAt\((\$\w+)\s*-\s*(\d+)\)/', 'mb_substr($1, $2 - $3, 1, \'UTF-8\')', $out);
    $out = preg_replace('/(\$\w+)\.charAt\((\d+)\)/', 'mb_substr($1, $2, 1, \'UTF-8\')', $out);
    // .charAt() with mb_strlen expression as arg
    $out = preg_replace('/(\$\w+)\.charAt\((mb_strlen\([^)]+\)\s*-\s*\d+)\)/', 'mb_substr($1, $2, 1, \'UTF-8\')', $out);
    $out = preg_replace('/(\$\w+)\.substring\((\d+),\s*mb_strlen\((\$\w+),\s*\'UTF-8\'\)\s*-\s*(\d+)\)/',
        'mb_substr($1, $2, mb_strlen($3, \'UTF-8\') - $4 - $2, \'UTF-8\')', $out);
    $out = preg_replace('/(\$\w+)\.endsWith\("([^"]*)"\)/',
        'mb_substr($1, -mb_strlen("$2", \'UTF-8\'), null, \'UTF-8\') === "$2"', $out);
    $out = preg_replace('/(\$\w+)\.replace\("([^"]*)",\s*"([^"]*)"\)/', 'str_replace("$2", "$3", $1)', $out);

    // Collection utils
    $out = preg_replace('/CollectionUtils\.isEmpty\((\$\w+)\)/', 'empty($1)', $out);
    $out = preg_replace('/!Strings\.isEmpty\((\$\w+)\)/', '($1 !== null && $1 !== \'\')', $out);
    $out = preg_replace('/Strings\.isEmpty\((\$\w+)\)/', '($1 === null || $1 === \'\')', $out);

    // .getKey() / .getContent() / etc. on object vars -> ->getKey()
    $out = preg_replace('/(\$\w+)\.(\w+)\(/', '$1->$2(', $out);

    // Handle chained ->method().equals("...") after method conversion
    $out = preg_replace('/->(\w+)\(\)\.equals\("([^"]*)"\)/', '->$1() === "$2"', $out);

    // StringBuilder.append($expr) -> .= $expr
    $out = preg_replace('/(\$\w+)->append\(([^)]+)\)\s*;/', '$1 .= $2;', $out);
    // StringBuilder.deleteCharAt(len - 1) -> remove last char
    $out = preg_replace('/(\$\w+)->deleteCharAt\(mb_strlen\(\1,\s*\'UTF-8\'\)\s*-\s*1\)\s*;/',
        '$1 = mb_substr($1, 0, -1, \'UTF-8\');', $out);

    // Nature comparison: $x === Nature::Y (already handled above)
    // Also: $x == Nature::Y
    $out = preg_replace('/(\$\w+)\s*==\s*Nature::(\w+)/', '$1 === Nature::$2', $out);

    // Private method calls without qualifier -> $this->
    $out = preg_replace('/(?<!=\s)(?<=\s)(resolv\w+|reslov\w+|resolov\w+|get|concatAnd202f|isWordCodePoint)\(/', '$this->$1(', $out);
    $out = preg_replace('/=\s*(resolv\w+|reslov\w+|resolov\w+|get|concatAnd202f|isWordCodePoint)\(/', '= $this->$1(', $out);

    // null checks: != null -> !== null, == null -> === null
    $out = preg_replace('/(?<!==)!=\s*null/', '!== null', $out);
    $out = preg_replace('/(?<![!=])==\s*null/', '=== null', $out);

    // Fix double $$ and bad patterns
    $out = preg_replace('/\$\$/', '$', $out);
    // Fix $Nature:: -> Nature::
    $out = preg_replace('/\$Nature::/', 'Nature::', $out);
    // Fix $CharType:: -> CharType::
    $out = preg_replace('/\$CharType::/', 'CharType::', $out);
    // Fix class references that got $-prefixed
    foreach (['MglUnicode', 'ZvvnmodUnicode', 'Z52Unicode', 'DelehiCodeBlock',
              'FromMenkLetterMapper', 'FromDelehiMapper', 'ToMenkLetterMapper', 'ToDelehiMapper',
              'FromMenkShapeMapper', 'ToMenkShapeMapper', 'FromZ52Mapper', 'ToZ52Mapper',
              'MenkCodeBlock', 'MenkShapeUnicodeBlock'] as $cls) {
        $out = str_replace('$' . $cls . '::', $cls . '::', $out);
    }

    // Fix $this that became $$this
    $out = str_replace('$$this', '$this', $out);

    // Java string concat + -> PHP .
    $out = str_replace(' + "', ' . "', $out);
    $out = str_replace('" + ', '" . ', $out);
    // + between ) and $ or between $var and $var
    $out = preg_replace('/\) \+ \$/', ') . $', $out);
    $out = preg_replace('/return (\$\w+) \+ (\$\w+);/', 'return $1 . $2;', $out);

    return $out;
}

// ============================================================
// Main
// ============================================================

foreach ($configs as $cfg) {
    $javaFile = $cfg['java'];
    echo "Processing: " . basename($javaFile) . " -> " . $cfg['cls'] . ".php\n";

    if (!file_exists($javaFile)) {
        echo "  ERROR: File not found: $javaFile\n";
        continue;
    }

    $javaSource = file_get_contents($javaFile);
    $phpSource = translateJavaToPhp($javaSource, $cfg);

    file_put_contents($cfg['php'], $phpSource);
    echo "  Written: " . $cfg['php'] . "\n";
}

echo "\nDone! Please review the generated files for any needed manual adjustments.\n";
