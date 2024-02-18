# CSS & JavaScript minifier for Silverstripe

Automatically minify combined CSS & JavaScript files in Silverstripe 5 when running `Requirements::process_combined_files()`.
Internally it uses [matthiasmullie/minify](https://github.com/matthiasmullie/minify) to remove whitespace, strips comments and combines files.

This is useful if you do not require any JavaScript bundling or transpiling (eg: webpak, esbuild etc) but you still wish to minify the combined CSS and JavaScript files.


## Requirements

- Silverstripe ^5


## Installation

```shell
composer require axllent/silverstripe-minifier
```

This module is plug-and-play, no configuration required after installing and running a `?flush`.


## Usage example

```php
<?php

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\View\Requirements;

class PageController extends ContentController
{
    /**
     * Init function
     *
     * @return void
     */
    protected function init()
    {
        parent::init();

        $css[] = 'themes/site/css/file1.css';
        $css[] = 'themes/site/css/file2.css';
        $css[] = 'themes/site/css/file3.css';
        Requirements::combine_files('combined.css', $css);
        Requirements::process_combined_files();

        $js[] = 'themes/site/js/file1.js';
        $js[] = 'themes/site/js/file2.js';
        $js[] = 'themes/site/js/file3.js';
        Requirements::combine_files('combined.js', $js);
        Requirements::process_combined_files();
    }
}

```
