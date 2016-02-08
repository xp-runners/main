<?php namespace xp;

function entry(&$argv) {
  $ext= substr($argv[0], -4, 4);
  if ('.php' === $ext) {
    if (false === ($uri= realpath($argv[0]))) {
      throw new \Exception('Cannot load '.$argv[0].' - does not exist');
    }
    if (null === ($cl= \lang\ClassLoader::getDefault()->findUri($uri))) {
      throw new \Exception('Cannot load '.$argv[0].' - not in class path');
    }
    return $cl->loadUri($uri);
  } else if ('.xar' === $ext) {
    if (false === ($uri= realpath($argv[0]))) {
      throw new \Exception('Cannot load '.$argv[0].' - does not exist');
    }
    $cl= \lang\ClassLoader::registerPath($uri);
    if (!$cl->providesResource('META-INF/manifest.ini')) {
      throw new \Exception($cl->toString().' does not provide a manifest');
    }
    return $cl->loadClass(parse_ini_string($cl->getResource('META-INF/manifest.ini'))['main-class']);
  } else {
    return \lang\ClassLoader::getDefault()->loadClass($argv[0]);
  }
}