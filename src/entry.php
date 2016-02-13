<?php namespace xp;

function entry(&$argv) {
  if (is_file($argv[0])) {
    if (0 === substr_compare($argv[0], '.class.php', -10)) {
      $uri= realpath($argv[0]);
      if (null === ($cl= \lang\ClassLoader::getDefault()->findUri($uri))) {
        throw new \Exception('Cannot load '.$uri.' - not in class path');
      }
      return $cl->loadUri($uri)->literal();
    } else if (0 === substr_compare($argv[0], '.xar', -4)) {
      $cl= \lang\ClassLoader::registerPath(realpath($argv[0]));
      if (!$cl->providesResource('META-INF/manifest.ini')) {
        throw new \Exception($cl->toString().' does not provide a manifest');
      }
      $manifest= parse_ini_string($cl->getResource('META-INF/manifest.ini'));
      return strtr($manifest['main-class'], '.', '\\');
    } else {
      array_unshift($argv, 'eval');
      return 'xp\runtime\Evaluate';
    }
  } else {
    return strtr($argv[0], '.', '\\');
  }
}