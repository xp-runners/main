<?php namespace xp;

foreach ($bootstrap['files'] as $file) {
  require $file;
}

if (class_exists('xp', false)) {
  foreach ($bootstrap['overlay'] as $path) { \lang\ClassLoader::registerPath($path, true); }
  foreach ($bootstrap['local'] as $path) { \lang\ClassLoader::registerPath($path); }
} else if (isset($bootstrap['base'])) {
  $paths= array_merge($bootstrap['overlay'], $bootstrap['core'], $bootstrap['local']);
  require $bootstrap['base'];
} else {
  $parts= explode(PATH_SEPARATOR.PATH_SEPARATOR, get_include_path());
  throw new \Exception(sprintf(
    "Cannot locate xp-framework/core anywhere in {\n  modules:   %s\n  classpath: %s\n}",
    ($p= rtrim($parts[0], PATH_SEPARATOR)) ? "[$p]" : '(empty)',
    ($p= rtrim($parts[1], PATH_SEPARATOR)) ? "[$p]" : '(empty)'
  ));
}