<?php namespace xp;

function stringOf($value) {
  if (is_array($value)) {
    return 'array['.sizeof($value).']';
  } else if (is_object($value)) {
    return strtr(get_class($value), '\\', '.').'{}';
  } else {
    return var_export($value, true);
  }
}
