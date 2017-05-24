# soter
This plugin checks your site for security vulnerabilities against the [WPScan Vulnerability Database](https://wpvulndb.com/) API.

Originally inspired by the [Sensio Labs Security Checker](https://github.com/sensiolabs/security-checker) and the [Friends of PHP Security Advisories](https://github.com/FriendsOfPHP/security-advisories), which unfortunately do not track WordPress vulnerabilities.

A less intrusive alternative to the [WPScan vulnerability scanner](https://wpscan.org/).

NOTE: This plugin does not verify the integrity of files on your server - it only checks installed packages by name/version against a list of known vulnerabilities provided by the WPScan API.

## Requirements
WordPress 4.7 or later, PHP 5.4 or later and Composer.

## Installation
Install using Composer:

```
$ composer require ssnepenthe/soter
```

*OR*

```
$ cd /path/to/project/wp-content/plugins
$ git clone git@github.com:ssnepenthe/soter.git
$ cd soter
$ composer install
```

## Usage
Once activated, this plugin will check your site against the WPScan API twice daily and notify you when vulnerabilties are detected.

The plugin is configurable by visiting `settings > soter` in `wp-admin`:

* Notification frequency: choose whether to receive notifications after every scan where vulnerabilities are detected or only to receive notifications when your sites status changes.
* Email address: if notifications should be sent to an email address other than your site administrator email, enter it here.
* Email type: Choose whether you prefer HTML or text emails.
* Ignored plugins and themes: Select any packages that should not be checked against the WPScan API. This is intended for custom packages which are not tracked by the API and there would generate unnecessary HTTP requests or possible false positives.

## Acknowledgements
This plugin wouldn't be possible without the work of the [WPScan team](https://github.com/wpscanteam) and their amazing [WPScan Vulnerabilities Database](https://wpvulndb.com/).

The email templates for this plugin are created from the [Postmark Transactional Email Templates](https://github.com/wildbit/postmark-templates) which are released under the MIT license.

## Similar Projects
If you are only interested in WP-CLI integration, one of the following projects may be of more interest to you:

* [WP Vulnerability Scanner](https://github.com/10up/wp-vulnerability-scanner) by 10up
* [WP-sec](https://github.com/markri/wp-sec) by Marco de Krijger
