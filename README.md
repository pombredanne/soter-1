# soter
Check your Composer dependencies for security vulnerabilities against the WPVulnDB API.

Original inspiration comes from the [Sensio Labs Security Checker](https://github.com/sensiolabs/security-checker).

## Usage
```
./vendor/bin/soter check:project /path/to/composer.lock
```

This will check your WordPress packages against the WPVulnDB API and provide a listing of potential vulnerabilities.

You can also check individual plugins, themes and version of WordPress:

```
./vendor/bin/soter check:plugin eshop 6.3.13
./vendor/bin/soter check:theme pagelines 1.4.6
```

Note that the version argument is optional for both commands.

```
./vendor/bin/soter check:wordpress 4.3
```

## Config
You can configure this tool using the following commands:

```
./vendor/bin/soter config:set <property> <value>
./vendor/bin/soter config:remove <property> <value>
./vendor/bin/soter config:reset <property>
```

Valid properties are:

`cache.directory` - default is `[package dir]/.cache`

`cache.ttl` - in seconds, default is 43200 (12 hours)

`http.useragent` - default is `Soter Security Checker | vX.X.X | https://github.com/ssnepenthe/soter`

`package.ignored` - default is an empty array - should be used for custom themes/plugins that aren't tracked by WPVulnDB.

Example:

```
./vendor/bin/soter config:set package.ignored my-custom-plugin my-custom-theme
```

## Cache
HTTP responses are cached locally in the package directory. To clear the cache:

```
./vendor/bin/soter cache:clear
```
