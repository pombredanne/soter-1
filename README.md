# soter
Check your Composer dependencies for security vulnerabilities against the WPVulnDB API.

## Usage
This package includes a basic command line utility for checking your (WordPress) Composer dependencies for security vulnerabilities. It can be used on its own or via WP-CLI.


**Standalone:**

`./vendor/bin/soter check /path/to/composer.lock`


**WP-CLI (must be using the composer autoloader):**

`wp soter check /path/to/composer.lock`

This will check your WordPress packages against the WPVulnDB API and provide a listing of potential vulnerabilities.

You can also use it directly in your own code:

```php
use SSNepenthe\Soter\Checker;
use SSNepenthe\Soter\Http\CurlClient;

require_once 'vendor/autoload.php';

$checker = new Checker(
	'/path/to/composer.lock',
	new CurlClient( 'https://wpvulndb.com/api/v2/' )
);
$messages = $checker->check();

var_dump( $messages );
```

Note the second parameter passed to the Checker constructor can be any instance of `SSNepenthe\Soter\Contracts\Http`, and that the class `SSNepenthe\Soter\Http\WPClient` is included for use within WordPress (it uses the WP HTTP API instead of cURL).

**Output will look a little like this:**

```
array(4) {
  ["error"]=>
  array(1) {
    ["vendor/package"]=>
    array(2) {
      ["version"]=>
      string(3) "x.y.z"
      ["advisories"]=>
      array(1) {
        [0]=>
        string(83) "Error explanation, likely a 404"
      }
    }
  }
  ["ok"]=>
  array(1) {
    ["vendor/package"]=>
    array(2) {
      ["version"]=>
      string(9) "x.y.z"
      ["advisories"]=>
      array(1) {
        [0]=>
        string(51) "There are no known vulnerabilities in this package"
      }
    }
  }
  ["unknown"]=>
  array(1) {
  	["vendor/package"]=>
    array(2) {
      ["version"]=>
      string(9) "dev-trunk"
      ["advisories"]=>
      array(1) {
        [0]=>
        string(51) "One entry per vulnerability with title, reference urls and the version in which it was fixed"
      }
    }
  }
  ["vulnerable"]=>
  array(1) {
  	["vendor/package"]=>
    array(2) {
      ["version"]=>
      string(9) "x.y.z"
      ["advisories"]=>
      array(1) {
        [0]=>
        string(51) "One entry per vulnerability with title, reference urls and the version in which it was fixed
        "
      }
    }
  }
}
```

Individual packages will be listed as follows:
* Under the `error` key if the http client throws an exception (most likely a non-200 status code)
* Under the `ok` key if there are no known vulnerabilities
* Under the `unknown` key if the package has ever had any vulnerabilities, but the version used in your project cannot be easily determined (i.e. you are on `dev-{master,trunk}`)
* Under the `vulnerable` key if the package is determined to be vulnerable

## Notes
Only checks packages with a type of `wordpress-core` or a name that begins with `wpackagist-`.

The output isn't very nice looking, but the WP-CLI version in particular is shit.
