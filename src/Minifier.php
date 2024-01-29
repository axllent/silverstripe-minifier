<?php

namespace Axllent\Minifier;

use MatthiasMullie\Minify\CSS;
use MatthiasMullie\Minify\JS;
use SilverStripe\Assets\File;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Path;
use SilverStripe\View\Requirements_Backend;
use SilverStripe\View\SSViewer;
use SilverStripe\View\ThemeResourceLoader;

class Minifier extends Requirements_Backend
{
    use Configurable;

    /**
     * Custom CSS compilers for things like scss & less
     *
     * @config
     *
     * @var array
     */
    private static $compilers = [];

    /**
     * All compilers by extension
     *
     * @var array
     */
    private $compilerEngine = [];

    /**
     * Class constructor
     */
    public function __construct()
    {
        foreach (self::config()->uninherited('compilers') as $ext => $class) {
            if (!isset($this->compilerEngine[$ext])) {
                $this->compilerEngine[$ext] = singleton($class);
            }
        }
    }

    /**
     * Process CSS file
     *
     * @param string     $file
     * @param null|mixed $media
     * @param array      $options
     *
     * @return void
     */
    public function css($file, $media = null, $options = [])
    {
        // run through a custom CSS compiler if set, returns compiled file path
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (isset($this->compilerEngine[$ext])) {
            $file = $this->compilerEngine[$ext]->process($file, $media, $options);
        }

        return parent::css($file, $media, $options);
    }

    /**
     * Return path and type of given combined file
     *
     * @param array|string $file Either a file path, or an array spec
     *
     * @return array with two elements, path and type of file
     */
    protected function parseCombinedFile($file)
    {
        // run through a custom CSS compiler if set, returns compiled file path
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (isset($this->compilerEngine[$ext])) {
            $file = $this->compilerEngine[$ext]->process($file);
        }

        return parent::parseCombinedFile($file);
    }

    /**
     * Given a set of files, combine them (as necessary) and return the url
     *
     * @param string $combinedFile Filename for this combined file
     * @param array  $fileList     List of files to combine
     * @param string $type         Either 'js' or 'css'
     *
     * @return null|string URL to this resource, if there are files to combine
     *
     * @throws Exception
     */
    protected function getCombinedFileURL($combinedFile, $fileList, $type)
    {
        // Skip empty lists
        if (empty($fileList)) {
            return null;
        }

        // Generate path (Filename)
        $hashQueryString = Config::inst()->get(static::class, 'combine_hash_querystring');
        if (!$hashQueryString) {
            $combinedFile = $this->hashedCombinedFilename($combinedFile, $fileList);
        }
        $combinedFileID = File::join_paths($this->getCombinedFilesFolder(), $combinedFile);

        $minifier = 'js' == $type ? new JS() : new CSS();

        // Send file combination request to the backend, with an optional callback to perform regeneration
        $combinedURL = $this
            ->getAssetHandler()
            ->getContentURL(
                $combinedFileID,
                function () use ($fileList, $minifier) {
                    // Physically combine all file content
                    foreach ($fileList as $file) {
                        $filePath = Director::getAbsFile($file);
                        if (!file_exists($filePath ?? '')) {
                            throw new \InvalidArgumentException("Combined file {$file} does not exist");
                        }

                        $minifier->add($filePath);
                    }

                    return $minifier->minify();
                }
            );

        // If the name isn't hashed, we will need to append the query string m= parameter instead
        // Since url won't be automatically suffixed, add it in here
        if ($hashQueryString && $this->getSuffixRequirements()) {
            $hash = $this->hashOfFiles($fileList);
            $q    = false === stripos($combinedURL ?? '', '?') ? '?' : '&';
            $combinedURL .= "{$q}m={$hash}";
        }

        return $combinedURL;
    }

    /**
     * Registers the given themeable stylesheet as required.
     *
     * A CSS file in the current theme path name 'themeName/css/$name.css' is first searched for,
     * and it that doesn't exist and the module parameter is set then a CSS file with that name in
     * the module is used.
     *
     * @param string $name  The name of the file - eg '/css/File.css' would have the name 'File'
     * @param string $media Comma-separated list of media types to use in the link tag
     *                      (e.g. 'screen,projector')
     */
    public function themedCSS($name, $media = null)
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        // append ".css" to filename if no recognised extension detected
        if ('css' != $ext && !isset($this->compilerEngine[$ext])) {
            $name .= '.css';
            $ext = 'css';
        }

        $loader = ThemeResourceLoader::inst();

        $filename = $loader->findThemedResource("css/{$name}", SSViewer::get_themes());
        if (null === $filename) {
            $filename = $loader->findThemedResource($name, SSViewer::get_themes());
        }

        if ($filename) {
            $this->css($filename, $media);
        } else {
            throw new \InvalidArgumentException(
                "The {$ext} file doesn't exist. Please check if the file {$name} exists in any context or search for "
                . 'themedCSS references calling this file in your templates.'
            );
        }
    }
}
