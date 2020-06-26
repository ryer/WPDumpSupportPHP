# WPDumpSupportPHP

This is a simple library that loading [wpdump](https://github.com/ryer/wpdump
)'s output in php.

## Usage

```php
require "WPDumpSupportPHP/vendor/autoload.php";
$wpDump = new WPDumpSupport\WPDump("/path/to/wpdumps/save/dir");
$wpDump->load();
echo $wpDump->posts[8455]->author->name;
```

## Installation

```
$ composer install --no-dev
```

## See

https://github.com/ryer/wpdump

## License

MIT

## Author

ryer (@ryer)
