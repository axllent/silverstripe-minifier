<?php

use MatthiasMullie\Minify;
use SilverStripe\Dev\SapphireTest;

class MinifierTest extends SapphireTest
{

    public function testCSSMinification()
    {
        $minifier = new Minify\CSS();
        $minifier->add('body { color: #000000; }');
        $minified = $minifier->minify();
        $this->assertEquals($minified, 'body{color:#000}');
    }

    public function testJSMinification()
    {
        $minifier = new Minify\JS();
        $minifier->add('
            var test = "value";
            function myfunc() {
                alert( test );
            }
        ');
        $minified = $minifier->minify();
        $this->assertEquals($minified, 'var test="value";function myfunc(){alert(test)}');
    }
}
