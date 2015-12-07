# soter
Check your Composer dependencies for security vulnerabilities against the WPVulnDB API.

## Usage
This package includes a basic command line utility.

From the project root:

```
./vendor/bin/soter check
```

From any other location:
```
./path/to/soter check /path/to/composer.lock
```

This will check your WordPress packages and provide output similar to the following:

```
Checking /Users/ssnepenthe/Code/vuln-test/composer.lock...
+--------------------------------+------------+--------------------------------------+
| Package Name                   | Status     | Message                              |
+--------------------------------+------------+--------------------------------------+
| johnpbloch/wordpress           | VULNERABLE | XSS vulnerability, fixed in 4.3.1    |
|                                |            | XSS vulnerability, fixed in 4.3.1    |
|                                |            | BYPASS vulnerability, fixed in 4.3.1 |
| wpackagist-plugin/cache-buddy  | SAFE       |                                      |
| wpackagist-plugin/eshop        | VULNERABLE | XSS vulnerability, fixed in 6.2.9    |
|                                |            | RCE vulnerability, fixed in 6.3.12   |
|                                |            | XSS vulnerability, not yet fixed     |
| wpackagist-theme/pagelines     | VULNERABLE | BYPASS vulnerability, not yet fixed  |
| wpackagist-theme/twentyfifteen | SAFE       |                                      |
+--------------------------------+------------+--------------------------------------+
```

You can also use it manually:

```php
use SSNepenthe\Soter\Checker;

$checker = new Checker( '/path/to/composer.lock' );
$messages = $checker->check();

var_dump( $messages );
```

Output:

```
array(5) {
  [0]=>
  array(3) {
    ["package"]=>
    string(20) "johnpbloch/wordpress"
    ["status"]=>
    string(10) "VULNERABLE"
    ["message"]=>
    string(104) "XSS vulnerability, fixed in 4.3.1
XSS vulnerability, fixed in 4.3.1
BYPASS vulnerability, fixed in 4.3.1"
  }
  [1]=>
  array(3) {
    ["package"]=>
    string(29) "wpackagist-plugin/cache-buddy"
    ["status"]=>
    string(4) "SAFE"
    ["message"]=>
    string(0) ""
  }
  [2]=>
  array(3) {
    ["package"]=>
    string(23) "wpackagist-plugin/eshop"
    ["status"]=>
    string(10) "VULNERABLE"
    ["message"]=>
    string(101) "XSS vulnerability, fixed in 6.2.9
RCE vulnerability, fixed in 6.3.12
XSS vulnerability, not yet fixed"
  }
  [3]=>
  array(3) {
    ["package"]=>
    string(26) "wpackagist-theme/pagelines"
    ["status"]=>
    string(10) "VULNERABLE"
    ["message"]=>
    string(35) "BYPASS vulnerability, not yet fixed"
  }
  [4]=>
  array(3) {
    ["package"]=>
    string(30) "wpackagist-theme/twentyfifteen"
    ["status"]=>
    string(4) "SAFE"
    ["message"]=>
    string(0) ""
  }
}
```

## Notes
Only checks packages with a type of `wordpress-core` or a name that begins with `wpackagist-`.
