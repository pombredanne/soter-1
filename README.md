# soter
This plugin checks your site for security vulnerabilities against the [WPScan Vulnerability Database](https://wpvulndb.com/) API.

Originally inspired by the [Sensio Labs Security Checker](https://github.com/sensiolabs/security-checker) and the [Friends of PHP Security Advisories](https://github.com/FriendsOfPHP/security-advisories), which unfortunately do not track WordPress vulnerabilities.

A less intrusive alternative to the [WPScan vulnerability scanner](https://wpscan.org/).

NOTE: This plugin does not verify the integrity of files on your server - it only checks installed packages by name/version against a list of known vulnerabilities provided by the WPScan API.

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
Once activated, this plugin will check your site against the WPScan API twice daily.

Make sure to visit `settings > soter` in `wp-admin` and mark any custom themes and plugins (i.e. any packages that are not tracked by WPScan) as ignored to prevent your site from making unnecessary API requests.

If a vulnerability is detected, an admin notice will be shown to users with a role of `administrator`. If you would like to receive notifications by email as well, this can also be configured in the plugin settings.

## WP-CLI
Once activated, the following commands will be available in WP-CLI:

```
$ wp security check-plugin <slug> [<version>] [--format=<format>] [--fields=<fields>]
$ wp security check-theme <slug> [<version>] [--format=<format>] [--fields=<fields>]
$ wp security check-wordpress <version> [--format=<format>] [--fields=<fields>]
$ wp security check-site [--format=<format>] [--fields=<fields>]
```

`<format>` can be any of `count`, `csv`, `ids`, `json`, `standard`, `table`, `yaml` or `yml`.

`<fields>` should be a comma delimited list of fields. Valid fields are `id`, `title`, `created_at`, `updated_at`, `published_date`, `vuln_type` and `fixed_in`.

The `check-site` command will honor the plugin settings as defined in `Settings > Security`.

### Examples

**Full site check with standard formatting**

```
$ wp security check-site
Checking 18 packages  100% [=====================================] 0:01 / 0:01


  WARNING: 5 vulnerabilities detected


Contact Form 7 <= 3.7.1 - Security Bypass
https://wpvulndb.com/vulnerabilities/7020
Fixed in v3.7.2

Contact Form 7 <= 3.5.2 - File Upload Remote Code Execution
https://wpvulndb.com/vulnerabilities/7022
Fixed in v3.5.3

WordPress 4.2.0-4.7.1 - Press This UI Available to Unauthorised Users
Published 26 January 2017
https://wpvulndb.com/vulnerabilities/8729
Fixed in v4.7.2

WordPress 3.5-4.7.1 - WP_Query SQL Injection
Published 26 January 2017
https://wpvulndb.com/vulnerabilities/8730
Fixed in v4.7.2

WordPress 4.3.0-4.7.1 - Cross-Site Scripting (XSS) in posts list table
Published 26 January 2017
https://wpvulndb.com/vulnerabilities/8731
Fixed in v4.7.2
```

**Check all versions of Contact Form 7 and format as CSV**

```
$ wp security check-plugin contact-form-7 --format=csv
title,published_date,fixed_in
"Contact Form 7 <= 3.7.1 - Security Bypass ",,3.7.2
"Contact Form 7 <= 3.5.2 - File Upload Remote Code Execution",,3.5.3
```

**Check version 1.1 of twentyfifteen, display only title, vulnerability type and fixed in version, format as JSON**

```
$ wp security check-theme twentyfifteen 1.1 --format=json --fields=title,vuln_type,fixed_in
[{"title":"Twenty Fifteen Theme <= 1.1 - DOM Cross-Site Scripting (XSS)","vuln_type":"XSS","fixed_in":"1.2"}]
```

**Check WordPress version 4.7.1, display only title and fixed in version, format as YAML**

```
$ wp security check-wordpress 4.7.1 --format=yaml --fields=title,fixed_in
---
-
  title: 'WordPress 4.2.0-4.7.1 - Press This UI Available to Unauthorised Users'
  fixed_in: 4.7.2
-
  title: 'WordPress 3.5-4.7.1 - WP_Query SQL Injection'
  fixed_in: 4.7.2
-
  title: 'WordPress 4.3.0-4.7.1 - Cross-Site Scripting (XSS) in posts list table'
  fixed_in: 4.7.2
```

## Acknowledgements
This plugin wouldn't be possible without the work of the [WPScan team](https://github.com/wpscanteam) and their amazing [WPScan Vulnerabilities Database](https://wpvulndb.com/).

The email templates for this plugin are created from the [Postmark Transactional Email Templates](https://github.com/wildbit/postmark-templates) which are released under the MIT license.

## Similar Projects
If you are only interested in WP-CLI integration, one of the following projects may be of more interest to you:

* [WP Vulnerability Scanner](https://github.com/10up/wp-vulnerability-scanner) by 10up
* [WP-sec](https://github.com/markri/wp-sec) by Marco de Krijger

There are also a number of [plugins on the WordPress.org plugin repo](https://wordpress.org/plugins/search.php?q=wpscan) that can check a site against the WPScan API.

The following are some of the reasons that I created Soter rather than using any of these plugins:

* Portability - some of these plugins use cURL directly rather than the WP HTTP API.
* Scheduled scans - some of these plugins offer on-demand scanning only.
* WP-CLI integration - none of the plugins I found offer WP-CLI integration.
* Caching - Most of these plugins employ no form of caching - since the WPScan Vulnerabilities Database is an free service, measures should be taken to minimize load generated against their servers.
* Completeness - many of these plugins only check plugins, themes or WordPress, but not all three.
