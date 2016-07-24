#!/bin/sh

php=${XP_RT-php}
tz=${TZ-UTC}

# Export environment
XP_EXE=$0
XP_COMMAND=run
XP_MODEL=default
XP_VERSION=0.0.0-travis

# Run PHP
exec $php -C -q -d include_path="." -d date.timezone="$tz" $(dirname $0)/class-main.php "$@"