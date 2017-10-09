<?php

namespace Axllent\Minifier;

use MatthiasMullie\Minify;
use SilverStripe\View\Requirements_Minifier;

class Minifier implements Requirements_Minifier
{
    /**
     * Minify the given content
     *
     * @param string $content
     * @param string $type Either js or css
     * @param string $filename Name of file to display in case of error
     * @return string minified content
     */
    public function minify ($content, $type, $fileName)
    {
        if ($type == 'css') {
            $minifier = new Minify\CSS();
            $minifier->add($content);
            return $minifier->minify();
        } elseif ($type == 'js') {
            $minifier = new Minify\JS();
            $minifier->add($content);
            return $minifier->minify();
        }
        return $content;
    }
}
