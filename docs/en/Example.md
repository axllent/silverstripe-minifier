# An example using Minifier in SilverStripe 4

```php
<?php

use SilverStripe\CMS\Controllers\ContentController;
use SilverStripe\View\Requirements;

class PageController extends ContentController
{
    protected function init()
    {
        parent::init();
        Requirements::backend()->setWriteHeaderComment(false);
        $css = [];
        $css[] = 'themes/mysite/css/bootstrap.css';
        $css[] = 'themes/mysite/css/stylesheet.css';
        Requirements::combine_files('combined.css', $css);
        Requirements::process_combined_files();

        $js = [];
        $js[] = 'themes/mysite/javascript/jquery.css';
        $js[] = 'themes/mysite/javascript/bootstrap.css';
        $js[] = 'themes/mysite/javascript/javascript.css';
        Requirements::combine_files('javascript.js', $js);
        Requirements::process_combined_files();
    }
}
```

You must do a `?flush` in live mode to generate the minified assets.
