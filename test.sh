#!/bin/sh

set -e
set -u

php=${PHP-$(which php)}
for i in `find test -name '*-test.php'` ; do 
  printf "%s: " $i
  $php -d include_path=test $i
  echo
done