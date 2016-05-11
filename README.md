# soter
Check your Composer dependencies for security vulnerabilities against the [WPVulnDB](https://wpvulndb.com/) API.

Inspired by the [Sensio Labs Security Checker](https://github.com/sensiolabs/security-checker), which unfortunately does not check against known WordPress vulnerabilities.

A less intrusive alternative to the [WPScan](http://wpscan.org/) vulnerability scanner.

## Installation
Install as a dev-dependency using Composer:

```
composer require ssnepenthe/soter --dev
```

## Usage
Before using the project checker you should set any custom themes and plugins as ignored packages:

```
./vendor/bin/soter config:set package.ignored your-theme-slug your-plugin-slug
```

Next check your Composer dependencies by feeding the path of your `composer.lock` file to the `check:project` command.

```
./vendor/bin/soter check:project [<path>]
```

`<path>` default is `<pwd>/composer.lock`.

Alternatively, you can check individual plugins, themes and versions of WordPress:

```
./vendor/bin/soter check:plugin <slug> [<version>]
./vendor/bin/soter check:theme <slug> [<version>]
./vendor/bin/soter check:wordpress <version>
```

## Configuration
Remove an entry from an `addable` config property with the `config:remove` command. `package.ignored` is the only `addable` property at the moment.

```
./vendor/bin/soter config:remove <property> <value>
```

Reset a config property to the package default with the `config:reset` command.

```
./vendor/bin/soter config:reset <property>
```

Change a config value with the `config:set` command. Multiple values are accepted for `addable` properties (`package.ignored`).

```
./vendor/bin/soter config:set <property> <value> (<value>)...
```

Show the full config with the `config:show` command.

```
./vendor/bin/soter config:show
```

Valid config properties are:

`cache.directory` - default is `<package dir>/.cache`

`cache.ttl` - in seconds, default is 43200 (12 hours)

`http.useragent` - default is `Soter Security Checker | vX.X.X | https://github.com/ssnepenthe/soter`

`package.ignored` - default is an empty array - should be used for custom themes/plugins that aren't tracked by WPVulnDB.

## Cache
By default, HTTP responses are cached locally in `<package dir>/.cache` for 12 hours. To manually clear the cache:

```
./vendor/bin/soter cache:clear
```
