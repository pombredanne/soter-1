# soter
This plugin checks your site for security vulnerabilities against the [WPVulnDB](https://wpvulndb.com/) API.

Originally inspired by the [Sensio Labs Security Checker](https://github.com/sensiolabs/security-checker) and the [Friends of PHP Security Advisories](https://github.com/FriendsOfPHP/security-advisories), which unfortunately do not track WordPress vulnerabilities.

A less intrusive alternative to the [WPScan](http://wpscan.org/) vulnerability scanner.

## Installation
Install using Composer:

```
composer require ssnepenthe/soter
```

This plugin has no dependencies, so you can also download the latest release from Github and extract it to your plugins directory or install it through the WordPress dashboard.

## Usage
Once activated, this plugin will check your site against the WPVulnDB API twice daily.

Make sure to visit `settings > security` in `wp-admin` and mark any custom themes and plugins (i.e. any packages that are not tracked by WPVulnDB) as ignored so your site does not generate unnecessary requests to the API.

If a vulnerability is detected, an admin notice will be shown to users with a role of `administrator`. If you would like to receive notifications by email as well, be sure to configure it in the settings.

## WP-CLI
Once activated, this plugin makes the `security` command available in WP-CLI.

`wp security check-plugin <slug> [<version>]`

`wp security check-theme <slug> [<version>]`

`wp security check-wp <version>`

`wp security check-site`

These commands will honor the ignored themes and plugins that you have configured on the plugin settings page.

## Manual Usage
Use `SSNepenthe\Soter\WPVulnDB\Client` to manually make requests to the WPVulnDB API.

```
$client = new SSNepenthe\Soter\WPVulnDB\Client;

// Check a version of WordPress.
$response = $client->wordpresses( '4.3' );
$vulnerabilities = $response->vulnerabilities();

// Check a single plugin.
$response = $client->plugins( 'eshop' );
$vulnerabilities = $response->vulnerabilities();

if ( $vulnerabilities[0]->affects_version( '6.3.12' ) ) {
    // Version 6.3.12 is affected by this vulnearbility.
}

$vulnerabilities = $response->vulnerabilities_by_version( '6.3.12' ); // All vulnerabilities that affect version 6.3.12.

// Same for themes.
$response = $client->themes( 'pagelines' );

$vulnerabilities = $response->vulnerabilities();

if ( $vulnerabilities[0]->affects_version( '1.4.6' ) ) {
    // Version 1.4.6 is affected by this vulnearbility.
}

$vulnerabilities = $response->vulnerabilities_by_version( '1.4.6' ); // All vulnerabilities that affect version 1.4.6.
```

Non-200 responses can be identified using the `is_error()` method or by manually checking the status code:

```
$response = $client->plugins( 'not-a-real-plugin-slug' );
$response->is_error(); // true.

echo $response->status(); // 404.

if ( isset( $response->error->status_code ) ) {
    // You get the idea...
}
```

All properties returned by the API are publicly available on the `Vulnerability` object:

```
$response = $client->plugins( 'eshop' );
$vulnerability = $response->vulnerabilities()[0];

echo $vulnerability->id; // 7004.
echo $vulnerability->references->url[0] // http://seclists.org/bugtraq/2011/Aug/52.
```

Use `SSNepenthe\Soter\Checker` to manually check a site.

```
// Checks current WP version + all installed plugins and themes. Honors ignored packages set by user.
$checker = new SSNepenthe\Soter\Checker;
$vulnerabilities = $checker->check(); // array of Vulnerability objects.
```

## Notes/To-do
Untested in multi-site, but not likely to work.

Admin notices are not yet dismissable and the checker is not run after updates. The admin notice may be displayed for up to twelve hours after you update even if the site is no longer vulnerable.

HTTP responses are cached for twelve hours directly in the WP object cache rather than in the transient cache. This essentially means responses are uncached if you have not configured a persistent object cache backend such as [WP Redis](https://wordpress.org/plugins/wp-redis/) or [Memcached Object Cache](https://wordpress.org/plugins/memcached/).
