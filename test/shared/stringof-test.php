<?php namespace xp\test;

$test= require 'test.php';
$fixture= require __DIR__.'/../../src/stringof.php';

exit($test->run([

  'empty-array' => function() {
    $this->assertEquals('array[0]', \xp\stringOf([]));
  },

  'array' => function() {
    $this->assertEquals('array[3]', \xp\stringOf([1, 2, 3]));
  },

  'object' => function() {
    $this->assertEquals('xp.Run{}', \xp\stringOf($this));
  },

  'empty-string' => function() {
    $this->assertEquals("''", \xp\stringOf(''));
  },

  'string' => function() {
    $this->assertEquals("'Test'", \xp\stringOf('Test'));
  },

  'null' => function() {
    $this->assertEquals('NULL', \xp\stringOf(null));
  },

  'bool_true' => function() {
    $this->assertEquals('true', \xp\stringOf(true));
  },

  'bool_false' => function() {
    $this->assertEquals('false', \xp\stringOf(false));
  },

  'integer' => function() {
    $this->assertEquals('1', \xp\stringOf(1));
  },

  'integer_zero' => function() {
    $this->assertEquals('0', \xp\stringOf(0));
  },

  'negative_integer' => function() {
    $this->assertEquals('-1', \xp\stringOf(-1));
  },

  'double' => function() {
    $this->assertEquals(PHP_VERSION < 7 ? '1' : '1.0', \xp\stringOf(1.0));
  },

  'double_zero' => function() {
    $this->assertEquals(PHP_VERSION < 7 ? '0' : '0.0', \xp\stringOf(0.0));
  },

  'negative_double' => function() {
    $this->assertEquals(PHP_VERSION < 7 ? '-1' : '-1.0', \xp\stringOf(-1.0));
  },
]));
