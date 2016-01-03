# FuelPHP Log extension package

Override Fuel\Core\Log

### Config

You can separate config for each modules.

./config/log.yml

```yaml
modules:
    app:
        handler: file
        processor: web
        format: ltsv
        path: %APPPATH%/logs/app
        level: 100
```

- modules
 - List module names.
- handler
 - Exporting handler name.
 Now it only supports file handler.
- format
 - Formatter name
 Now it only supports LTSV( http://ltsv.org ).
- path
 - Export log directory
 e.g.
 ```
 %path%/yyyy/MM/%module%-yyyyMMdd.log
 ```
- level
  - Set Monolog\Logger::LOG_LEVEL
    - 100: debug
    - 200: info
    - 250: notice
    - 300: warning
    - 400: error
    - 500: critical
    - 550: alert
    - 600: emergency

## Usage

```php
<?php
\Log\Log::d('debug log');
\Log\Log::i('info log');
\Log\Log::w('warning log');
\Log\Log::e('error log');
```

```
datetime:2016-01-03 22:37:13	level_name:DEBUG	file:/path/to/fuel/app/classes/controller/search.php	line:6	message:debug log
datetime:2016-01-03 22:37:13	level_name:INFO	file:/path/to/fuel/app/classes/controller/search.php	line:6	message:info log
datetime:2016-01-03 22:37:13	level_name:WARGING	file:/path/to/fuel/app/classes/controller/search.php	line:6	message:warning log
datetime:2016-01-03 22:37:13	level_name:ERROR	file:/path/to/fuel/app/classes/controller/search.php	line:6	message:error log
```
