# WPDumpSupportPHP

This is a simple library that loading [wpdump](https://github.com/ryer/wpdump)'s output in php.

## Usage

```php
require_once "vendor/autoload.php";

use WPDumpSupport\WPDump;

// Initialize WPDump with the JSON directory and cache directory.
$wpDump = new WPDump(['jsonDir' => '/path/to/wp-json/save/dir', 'cacheDir' => '/path/to/cache/dir']);

// Load reference data (tags, categories, users, etc.) into memory for quick access.
// This is necessary to resolve references from posts or pages.
$wpDump->load();

// Stream posts one by one to avoid memory exhaustion with large datasets.
foreach ($wpDump->streamPosts() as $post) {
  // Process each post object.
  // Author and other references are resolved.
  echo "Post Title: " . $post->title . "\n";
  if ($post->author) {
    echo "Author Name: " . $post->author->name . "\n";
  }
}
```

## Documentation

For more detailed information on the architecture and internal workings of this library, please see the internal documentation.

- [`INTERNAL.ja.md`](INTERNAL.ja.md)

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
