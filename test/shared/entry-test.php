<?php namespace xp\test;

$test= require 'test.php';
$path= require 'path.php';
$scan= require __DIR__.'/../../src/entry.php';

exit($test->run([
  '@before' => function() use($path) {
    $this->classpath= $path->compose(__DIR__, '/entry-test-tmp/');
    @mkdir($this->classpath);
    file_put_contents($path->compose($this->classpath, 'Test.class.php'), '<?php class Test { }');
    file_put_contents($path->compose($this->classpath, 'NotInClassPath.class.php'), '<?php class NotInClassPath { }');
    file_put_contents($path->compose($this->classpath, 'test.xar'), 'CCA...');
    file_put_contents($path->compose($this->classpath, 'notrunnable.xar'), 'CCA...');
    file_put_contents($path->compose($this->classpath, 'Test.script.php'), '<?php ');

    class_exists('lang\\ClassLoader') || eval('namespace lang; class ClassLoader {
      private $path;
      public function __construct($path) { $this->path= $path; }

      public static function getDefault() { return new self("."); }
      public static function registerPath($path) { return new self($path); }

      public function providesResource($resource) { return strstr($this->path, "test.xar"); }
      public function getResource($resource) { return "[archive]\nmain-class=Test"; }
      public function loadClass($class) { return $class; }
      public function findUri($uri) { return strstr($uri, "Test.class.php") ? new self(dirname($uri)) : null; }
      public function loadUri($uri) { return substr(basename($uri), 0, -strlen(".class.php")); }

      public function toString() { return "MockCL({$this->path})"; }
    }');
  },

  '@after' => function() use($path) {
    if (is_dir($this->classpath)) {
      $path->remove($this->classpath);
    }
  },

  'class entry point' => function() {
    $argv= ['Test'];
    $this->assertEquals('Test', \xp\entry($argv));
  },

  'class file entry point' => function() use($path) {
    $argv= [$path->compose($this->classpath, 'Test.class.php')];
    $this->assertEquals('Test', \xp\entry($argv));
  },

  'class file must be in class path' => function() use($path) {
    $argv= [$path->compose($this->classpath, 'NotInClassPath.class.php')];
    $this->assertException(
      'Exception',
      '/Cannot load .+ - not in class path/',
      function() use($argv) { \xp\entry($argv); }
    );
  },

  'xar entry point' => function() use($path) {
    $argv= [$path->compose($this->classpath, 'test.xar')];
    $this->assertEquals('Test', \xp\entry($argv));
  },

  'xar entry point without META-INF/manifest.ini' => function() use($path) {
    $argv= [$path->compose($this->classpath, 'notrunnable.xar')];
    $this->assertException(
      'Exception',
      '/.+ does not provide a manifest/',
      function() use($argv) { \xp\entry($argv); }
    );
  },

  'script file entry point' => function() use($path) {
    $argv= [$path->compose($this->classpath, 'Test.script.php')];
    $this->assertEquals('xp.runtime.Evaluate', \xp\entry($argv));
  },
]));