# soter
This plugin checks your site for security vulnerabilities against the [WPScan Vulnerability Database](https://wpvulndb.com/) API.

Originally inspired by the [Sensio Labs Security Checker](https://github.com/sensiolabs/security-checker) and the [Friends of PHP Security Advisories](https://github.com/FriendsOfPHP/security-advisories), which unfortunately do not track WordPress vulnerabilities.

A less intrusive alternative to the [WPScan vulnerability scanner](https://wpscan.org/).

NOTE: This plugin does not verify the integrity of files on your server - it only checks installed packages by name/version against a list of known vulnerabilities provided by the WPScan API.

## Requirements
WordPress 4.7 or later, PHP 5.4 or later and Composer.

## Installation
```
$ composer require ssnepenthe/soter
```

## Usage
Once activated, this plugin will check your site against the WPScan API twice daily and notify you when vulnerabilties are detected.

The plugin is configurable by visiting `settings > soter` in `wp-admin`:

* Notification frequency: Choose whether to receive notifications after every scan where vulnerabilities are detected or only to receive notifications when your sites status changes.
* Ignored plugins and themes: Select any packages that should not be checked against the WPScan API. This is intended for custom packages which are not tracked by the API and therefore would generate unnecessary HTTP requests or possible false positives.
* Send email notifications: Enable/disable email notifications.
* Email address: Provide an email address to notify if other than your site administrator email.
* Email type: Choose whether you prefer HTML or text emails.
* Send Slack notifications: Enable/disable Slack notifications.
* Slack WebHook URL: Provide a URL for a Slack "Incoming WebHook" integration if you wish to receive Slack notifications.

## Extending
There are two ways to extend the functionality of this plugin.

### Via Pimple
Use the [Pimple `extend()`](https://pimple.symfony.com/#modifying-services-after-definition) method to add additional notifiers to the manager instance.

This is the preferred method as it will automatically honor the notification frequency setting configured by the site admin.

```
class Sms_Notifier implements Soter\Notifier_Interface {
    public function is_enabled() {
        // Return boolean indicating whether this notifier is currently enabled.
    }

    public function notify( Soter_Core\Vulnerabilities $vulnerabilities ) {
        // Build and send the message.
    }
}

_soter_instance()->extend( 'notifier_manager', function( $manager, $container ) {
    $manager->add( new Sms_Notifier );

    return $manager;
} );
```

### Via WordPress hooks
After a scan has been run against the WPScan API, the `soter_check_complete` action is triggered. You can use this hook to perform any custom logic:

```
add_action( 'soter_check_complete', function( Soter_Core\Vulnerabilities $vulnerabilities ) {
    // Do something crazy...
} );
```

## Acknowledgements
This plugin wouldn't be possible without the work of the [WPScan team](https://github.com/wpscanteam) and their amazing [WPScan Vulnerabilities Database](https://wpvulndb.com/).

The email templates for this plugin are created from the [Postmark Transactional Email Templates](https://github.com/wildbit/postmark-templates) which are released under the MIT license.

## Similar Projects
If you are interested in WP-CLI integration, one of the following projects may be of more interest to you:

* [WP Vulnerability Scanner](https://github.com/10up/wp-vulnerability-scanner) by 10up
* [WP-sec](https://github.com/markri/wp-sec) by Marco de Krijger
