php-simple-html-dom-parser
==========================

This package is a fork of [sunra/php-simple-html-dom-parser](https://github.com/sunra/php-simple-html-dom-parser) with improvements and fixes. The original work was created by:
- S.C. Chen (http://sourceforge.net/projects/simplehtmldom/)
- Sunra (https://github.com/sunra)

This fork is distributed under the MIT License, same as the original project.


Install
-------

 ```shell
 composer require tuxonice/php-simple-html-dom-parser:^2.0
```

Usage
-----

```php
use Tlab\HtmlDomParser\HtmlDomParser;

// Parse from a string
$dom = HtmlDomParser::str_get_html($str);
// or parse from a file
$dom = HtmlDomParser::file_get_html($file_name);

// Find elements using CSS selectors
$elems = $dom->find($elem_name);

// Example: Find all links
$links = $dom->find('a');
foreach($links as $link) {
    echo $link->href . "\n";
}
```
