# SilverStripe Minifier

An extension to integrate [matthiasmullie/minify](https://github.com/matthiasmullie/minify)
minification into SilverStripe 4 to minify all combined JavaScript and CSS files.


## Requirements

- SilverStripe 4+


## Installation via composer

`composer require axllent/silverstripe-minifier`


## Usage

The module is currently just plug-and-play. Once installed it will automatically minify
all combined JavaScript and CSS files in `live` mode. Do not forget to `?flush` after installation.

Please refer to the [PageController example](docs/en/Example.md) to see example usage in
your PageController.
