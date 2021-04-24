<?php namespace xp;

require 'stringof.php';

set_exception_handler(function($e) {
  if ($e instanceof \lang\Throwable) {
    fputs(STDERR, 'Uncaught exception: '.$e->toString());
  } else if (-1 === $e->getCode()) {
    fputs(STDERR, $e->getMessage());
  } else {
    fprintf(
      STDERR,
      "Uncaught exception: %s (%s)\n  at <source> [line %d of %s]\n  at <main>(%s) [line 0 of %s]\n",
      get_class($e),
      $e->getMessage(),
      $e->getLine(),
      str_replace(getcwd(), '.', $e->getFile()),
      implode(', ', array_map('\xp\stringOf', array_slice($_SERVER['argv'], 1))),
      basename($_SERVER['argv'][0])
    );
    foreach ($e->getTrace() as $trace) {
      fprintf(STDERR,
        "  at %s%s%s(%s) [line %d of %s]\n",
        isset($trace['class']) ? strtr($trace['class'], '\\', '.') : '<main>',
        isset($trace['type']) ? $trace['type'] : '::',
        isset($trace['function']) ? $trace['function'] : '<main>',
        isset($trace['args']) ? implode(', ', array_map('\xp\stringOf', $trace['args'])) : '',
        isset($trace['line']) ? $trace['line'] : 0,
        isset($trace['file']) ? basename($trace['file']) : '(unknown)'
      );
    }
  }
  exit(0xff);
});

ini_set('display_errors', 'false');
register_shutdown_function(function() {
  static $types= array(
    E_ERROR         => 'Fatal error',
    E_USER_ERROR    => 'Fatal error',
    E_CORE_ERROR    => 'Core error',
    E_PARSE         => 'Parse error',
    E_COMPILE_ERROR => 'Compile error'
  );

  $e= error_get_last();
  if (null !== $e && isset($types[$e['type']])) {
    fprintf(
      STDERR,
      "Uncaught error: %s (%s)\n  at <source> [line %d of %s]\n  at <main>(%s) [line 0 of %s]\n",
      $types[$e['type']],
      $e['message'],
      $e['line'],
      str_replace(getcwd(), '.', $e['file']),
      implode(', ', array_map('\xp\stringOf', array_slice($_SERVER['argv'], 1))),
      str_replace('.', DIRECTORY_SEPARATOR, $_SERVER['argv'][0]).'.class.php'
    );
  }
});

// Set CLI specific handling
$home= getenv('HOME');
$cwd= '.';

if ('cgi' === PHP_SAPI || 'cgi-fcgi' === PHP_SAPI) {
  ini_set('html_errors', 0);
  define('STDIN', fopen('php://stdin', 'rb'));
  define('STDOUT', fopen('php://stdout', 'wb'));
  define('STDERR', fopen('php://stderr', 'wb'));
} else if ('cli' !== PHP_SAPI) {
  throw new \Exception('[bootstrap] Cannot be run under '.PHP_SAPI.' SAPI');
}

require 'xar-support.php';
require 'scan-path.php';

$bootstrap= require 'bootstrap.php', bootstrap($cwd, $home);

require 'class-path.php';

array_shift($argv);
if (defined('ICONV_IMPL')) {
  foreach ($argv as $i => $val) {
    $argv[$i]= iconv('utf-7', \xp::ENCODING, $val);
  }
} else if (defined('MB_CASE_LOWER')) {
  foreach ($argv as $i => $val) {
    $argv[$i]= mb_convert_encoding($val, \xp::ENCODING, 'utf-7');
  }
} else {
  throw new \Exception('[bootstrap] Neither iconv nor mbstring present');
}

$class= require 'entry.php', entry($argv);
$_SERVER['argv']= $argv;

if (!is_callable([$class, 'main'])) {
  throw new \Exception('Class `'.strtr($class, '\\', '.').'\' does not have a main() method');
}

try {
  exit($class::main(array_slice($argv, 1)));
} catch (\lang\SystemExit $e) {
  if ($message= $e->getMessage()) echo $message, "\n";
  exit($e->getCode());
}
