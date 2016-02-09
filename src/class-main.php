<?php namespace xp;

set_exception_handler(function($e) {
  if ($e instanceof \lang\Throwable) {
    fputs(STDERR, 'Uncaught exception: '.$e->toString());
  } else {
    fputs(STDERR, 'Uncaught exception: '.get_class($e).' ('.$e->getMessage().")\n");
    $stringOf= class_exists('xp', false) ? ['xp', 'stringOf'] : function($val) { return var_export($val, 1); };
    foreach ($e->getTrace() as $trace) {
      fprintf(STDERR,
        "  at %s%s%s(%s) [line %d of %s]\n",
        isset($trace['class']) ? strtr($trace['class'], '\\', '.') : '<main>',
        isset($trace['type']) ? $trace['type'] : '::',
        isset($trace['function']) ? $trace['function'] : '<main>',
        isset($trace['args']) ? implode(', ', array_map($stringOf, $trace['args'])) : '',
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
    if (class_exists('xp', false)) {
      __error($e['type'], $e['message'], $e['file'], $e['line']);
      $stringOf= ['xp', 'stringOf'];
    } else {
      $stringOf= function($val) { return var_export($val, 1); };
    }
    fprintf(
      STDERR,
      "Uncaught error: %s (%s)\n  at <source> [line %d of %s]\n  at <main>(%s) [line 0 of %s]\n",
      $types[$e['type']],
      $e['message'],
      $e['line'],
      str_replace(getcwd(), '.', $e['file']),
      implode(', ', array_map($stringOf, array_slice($_SERVER['argv'], 1))),
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

// Start I/O layers
PHP_VERSION < '5.6' && iconv_set_encoding('internal_encoding', \xp::ENCODING);
array_shift($argv);
foreach ($argv as $i => $val) {
  $argv[$i]= iconv('utf-7', \xp::ENCODING, $val);
}

require 'xar-support.php';
require 'scan-path.php';
require 'bootstrap.php';
require 'class-path.php';

$class= require 'entry.php', entry($argv);
$_SERVER['argv']= $argv;
try {
  exit($class->getMethod('main')->invoke(null, array(array_slice($argv, 1))));
} catch (\lang\SystemExit $e) {
  if ($message= $e->getMessage()) echo $message, "\n";
  exit($e->getCode());
}
