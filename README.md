XP Runners
==========
[![Build status on GitHub](https://github.com/xp-runners/main/workflows/Tests/badge.svg)](https://github.com/xp-runners/main/actions)
[![BSD License](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-runners/reference/blob/master/LICENSE.md)


These are the framework entry points. The code here is packaged and distributed alongside the [reference implementation](https://github.com/xp-runners/reference). Therefore this repository doesn't have a separate changelog. 

Development
-----------
Code in `src/` is valid PHP 5.3 syntax with one exception: The extended require statement:

```php
// Statement will be replaced as if code from xar-support.php had been
// copy&pasted here.
require 'xar-support.php';

// Code inside entry.php except that inside the entry function will be
// inserted here. Code inside the entry function is inlined to a do...while
// loop, and return statements are replaced by $class= ...; break;
$class= require 'entry.php', entry($argv);
```

To run the inlining process, execute the following:

```sh
$ cat src/class-main.php | perl inline.pl src > class-main.php
```

*The code for this was written in Perl because that is available inside Travis-CI's language: csharp environment.*

Tests
-----
Tests can be run using the test.sh shell script:

```sh
$ sh test.sh
test/shared/bootstrap-classpath-test.php: [........]

OK: 8 test(s) run, 0 ignored
0.014 seconds taken, 483.76 kB peak memory usage

# ...
```

*The environment variable `PHP` can be used to control which PHP runtime is used by the tests.*