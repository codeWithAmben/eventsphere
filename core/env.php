<?php
// Minimal env loader for local development. Only one function is defined to avoid
// duplicate definitions across files.
if (!function_exists('load_dotenv_single')) {
    function load_dotenv_single($path = null) {
        $path = $path ?: __DIR__ . '/../.env';
        if (!is_file($path) || !is_readable($path)) return false;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) continue;
            if (strpos($line, 'export ') === 0) $line = substr($line, 7);
            if (strpos($line, '=') === false) continue;
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key); $value = trim($value);
            if ((substr($value,0,1)==='"'&&substr($value,-1)==='"') || (substr($value,0,1)==="'"&&substr($value,-1)==="'")) $value=substr($value,1,-1);
            if (getenv($key) !== false) continue;
            if (isset($_ENV[$key]) || isset($_SERVER[$key])) continue;
            putenv("$key=$value"); $_ENV[$key]=$value; $_SERVER[$key]=$value;
        }
        return true;
    }
}
load_dotenv_single();
?>
