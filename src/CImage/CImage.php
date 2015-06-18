<?php

/**
 * CImage
 * 
 */
class CImage {

    /**
     * Properties
     */
    private $options;
    private $in;
    private $html;
    private $maxWidth;
    private $maxHeight;
    private $img;
    private $image;
    private $cacheFileName;

    /**
     * Constructor
     * @param type $options, array of class specific options.
     */
    public function __construct() {
        $this->maxWidth = $this->maxHeight = 2000;
        $this->in = array();
        $this->img = array();
        $this->html = "";

        $this->getAndValidateInput();

        if ($this->in['verbose']) {
            $this->verboseLogHeader();
        }

        $this->openOriginalImage();
        $this->getImageInfo();

        $this->createCacheFilename();
        $this->validateCacheImage();

        $this->resizeAndCrop();

        if ($this->in['sharpen']) {
            $this->sharpenImage($this->image);
        }
        if ($this->in['saveAs']) {
            $this->saveImage();
        }
        if ($this->in['verbose']) {
            clearstatcache();
            $cacheFilesize = filesize($this->cacheFileName);
            $this->verbose("File size of cached file: {$cacheFilesize} bytes.");
            $this->verbose("Cache file has a file size of " . round($cacheFilesize / $this->img['filesize'] * 100) . "% of the original size.");
        }
        
        $this->outputImage($this->cacheFileName);
    }

    /**
     * Get the current html content.
     * @return type array of strings
     */
    public function getHtml() {
        return $this->html;
    }

    private function saveImage() {
        // Save the image
        switch ($this->in['saveAs']) {
            case 'jpeg':
            case 'jpg':
                $this->verbose("Saving image as JPEG to cache using quality = {$this->in['quality']}.");
                imagejpeg($this->image, $this->cacheFileName, $this->in['quality']);
                break;

            case 'png':
                $this->verbose("Saving image as PNG to cache.");
                imagealphablending($this->image, false);
                imagesavealpha($this->image, true);
                imagepng($this->image, $this->cacheFileName);
                break;

            default:
                $this->errorMessage('No support to save as this file extension.');
                break;
        }
    }

    private function openOriginalImage() {
        // Open up the original image from file
        $this->verbose("File extension is: {$this->in['fileExtension']}");
        switch ($this->in['fileExtension']) {
            case 'jpg':
            case 'jpeg':
                $this->image = imagecreatefromjpeg($this->in['pathToImage']);
                $this->verbose("Opened the image as a JPEG image.");
                break;

            case 'png':
                $this->image = imagecreatefrompng($this->in['pathToImage']);
                $this->verbose("Opened the image as a PNG image.");
                break;

            default: $this->errorMessage('No support for this file extension.');
        }
    }

    private function getImageInfo() {
        // Get information on the image
        $imgInfo = list($width, $height, $type, $attr) = getimagesize($this->in['pathToImage']);
        !empty($imgInfo) or $this->errorMessage("The file doesn't seem to be an image.");
        $mime = $imgInfo['mime'];
        $filesize = filesize($this->in['pathToImage']);

        $this->verbose("Image file: {$this->in['pathToImage']}");
        $this->verbose("Image information: " . print_r($imgInfo, true));
        $this->verbose("Image width x height (type): {$width} x {$height} ({$type}).");
        $this->verbose("Image file size: {$filesize} bytes.");
        $this->verbose("Image mime type: {$mime}.");

        $this->img = array(
            'width' => $width,
            'height' => $height,
            'type' => $type,
            'attr' => $attr,
            'filesize' => $filesize,
            'mime' => $mime,
        );
    }

    private function resizeAndCrop() {
        // Calculate new width and height for the image
        $aspectRatio = $this->img['width'] / $this->img['height'];
        $newWidth = $this->in['newWidth'];
        $newHeight = $this->in['newHeight'];
        $width = $this->img['width'];
        $height = $this->img['height'];

        if ($this->in['cropToFit'] && $newWidth && $newHeight) {
            $targetRatio = $newWidth / $newHeight;
            $cropWidth = $targetRatio > $aspectRatio ? $width : round($height * $targetRatio);
            $cropHeight = $targetRatio > $aspectRatio ? round($width / $targetRatio) : $height;
            $this->verbose("Crop to fit into box of {$newWidth}x{$newHeight}. Cropping dimensions: {$cropWidth}x{$cropHeight}.");
        } else if ($newWidth && !$newHeight) {
            $newHeight = round($newWidth / $aspectRatio);
            $this->verbose("New width is known {$newWidth}, height is calculated to {$newHeight}.");
        } else if (!$newWidth && $newHeight) {
            $newWidth = round($newHeight * $aspectRatio);
            $this->verbose("New height is known {$newHeight}, width is calculated to {$newWidth}.");
        } else if ($newWidth && $newHeight) {
            $ratioWidth = $width / $newWidth;
            $ratioHeight = $height / $newHeight;
            $ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
            $newWidth = round($width / $ratio);
            $newHeight = round($height / $ratio);
            $this->verbose("New width & height is requested, keeping aspect ratio results in {$newWidth}x{$newHeight}.");
        } else {
            $newWidth = $width;
            $newHeight = $height;
            $this->verbose("Keeping original width & heigth.");
        }

        // Resize the image if needed
        if ($this->in['cropToFit']) {
            $this->verbose("Resizing, crop to fit.");

            $cropX = round(($width - $cropWidth) / 2);
            $cropY = round(($height - $cropHeight) / 2);
            $imageResized = $this->createImageKeepTransparency($this->in['newWidth'], $this->in['newHeight']);
            imagecopyresampled($imageResized, $this->image, 0, 0, $cropX, $cropY, $this->in['newWidth'], $this->in['newHeight'], $cropWidth, $cropHeight);
            $this->image = $imageResized;
            $width = $this->in['newWidth'];
            $height = $this->in['newHeight'];
        } else if (!($newWidth == $width && $newHeight == $height)) {
            $this->verbose("Resizing, new height and/or width.");

            $imageResized = $this->createImageKeepTransparency($newWidth, $newHeight);
            imagecopyresampled($imageResized, $this->image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            $this->image = $imageResized;
            $width = $this->in['newWidth'];
            $height = $this->in['newHeight'];
        }

        $this->img['width'] = $width;
        $this->img['height'] = $height;
    }

    /**
     * Create new image and keep transparency
     *
     * @param resource $image the image to apply this filter on.
     * @return resource $image as the processed image.
     */
    private function createImageKeepTransparency($width, $height) {
        $img = imagecreatetruecolor($width, $height);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        return $img;
    }

    /**
     * Output an image together with last modified header.
     *
     * @param string $file as path to the image.
     * @param boolean $verbose if verbose mode is on or off.
     */
    private function outputImage($file) {
        //$file = $this->cacheFileName;
        $info = getimagesize($file);
        !empty($info) or $this->errorMessage("The file doesn't seem to be an image.");
        $mime = $info['mime'];

        $lastModified = filemtime($file);
        $gmdate = gmdate("D, d M Y H:i:s", $lastModified);

        $this->verbose("Memory peak: " . round(memory_get_peak_usage() / 1024 / 1024) . "M");
        $this->verbose("Memory limit: " . ini_get('memory_limit'));
        $this->verbose("Time is {$gmdate} GMT.");


        if (!$this->in['verbose'])
            header('Last-Modified: ' . $gmdate . ' GMT');
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified) {
            if ($this->in['verbose']) {
                $this->verbose("Would send header 304 Not Modified, but its verbose mode.");
                exit;
            }
            header('HTTP/1.0 304 Not Modified');
        } else {
            if ($this->in['verbose']) {
                $this->verbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode.");
                exit;
            }
            header('Content-type: ' . $mime);
            readfile($file);
        }
    }

    /**
     * Sharpen image as http://php.net/manual/en/ref.image.php#56144
     * http://loriweb.pair.com/8udf-sharpen.html
     *
     * @param resource $image the image to apply this filter on.
     * @return resource $image as the processed image.
     */
    private function sharpenImage() {
        $matrix = array(
            array(-1, -1, -1,),
            array(-1, 16, -1,),
            array(-1, -1, -1,)
        );
        $divisor = 8;
        $offset = 0;
        imageconvolution($this->image, $matrix, $divisor, $offset);
    }

    /**
     * Create a filename for the cache version of the image.
     */
    private function createCacheFilename() {
        $this->in['saveAs'] = is_null($this->in['saveAs']) ? $this->in['fileExtension'] : $this->in['saveAs'];
        $quality_ = is_null($this->in['quality']) ? null : "_q{$this->in['quality']}";
        $cropToFit_ = is_null($this->in['cropToFit']) ? null : "_cf";
        $sharpen_ = is_null($this->in['sharpen']) ? null : "_s";
        $dirName = preg_replace('/\//', '-', dirname($this->in['src']));
        $this->cacheFileName = CACHE_PATH . "-{$dirName}-{$this->in['parts']['filename']}_"
                . "{$this->in['newWidth']}_{$this->in['newHeight']}{$quality_}"
                . "{$cropToFit_}{$sharpen_}.{$this->in['saveAs']}";
        $this->cacheFileName = preg_replace('/^a-zA-Z0-9\.-_/', '', $this->cacheFileName);

        $this->verbose("Cache file is: {$this->cacheFileName}");
    }

    /**
     * Check if there is a valid cached image in cache directory.
     */
    private function validateCacheImage() {
        $imageModifiedTime = filemtime($this->in['pathToImage']);
        $cacheModifiedTime = is_file($this->cacheFileName) ? filemtime($this->cacheFileName) : null;

        // If cached image is valid, output it.
        if (!$this->in['ignoreCache'] && is_file($this->cacheFileName) && $imageModifiedTime < $cacheModifiedTime) {
            $this->verbose("Cache file is valid, output it.");
            $this->outputImage($this->cacheFileName);
        }
        $this->verbose("Cache is not valid, process image and create a cached version of it.");
    }

    /**
     * Display error message.
     *
     * @param string $message the error message to display.
     */
    private function errorMessage($message) {
        header("Status: 404 Not Found");
        die('img.php says 404 - ' . htmlentities($message));
    }

    private function verboseLogHeader() {
        // Start displaying log if verbose mode & create url to current image
        $query = array();
        parse_str($_SERVER['QUERY_STRING'], $query);
        unset($query['verbose']);
        $url = '?' . http_build_query($query);

        echo <<<EOD
<html lang='en'>
<meta charset='UTF-8'/>
<title>img.php verbose mode</title>
<h1>Verbose mode</h1>
<p><a href=$url><code>$url</code></a><br>
<img src='{$url}' /></p>
EOD;
    }

    /**
     * Display log message.
     *
     * @param string $message the log message to display.
     */
    private function verbose($message) {
        if ($this->in['verbose']) {
            echo "<p>" . htmlentities($message) . "</p>";
        }
    }

    private function getAndValidateInput() {
        $in = array();

        // Get the incoming arguments
        $in['src'] = isset($_GET['src']) ? $_GET['src'] : null;
        $in['verbose'] = isset($_GET['verbose']) ? true : null;
        $in['saveAs'] = isset($_GET['save-as']) ? $_GET['save-as'] : null;
        $in['quality'] = isset($_GET['quality']) ? $_GET['quality'] : 60;
        $in['ignoreCache'] = isset($_GET['no-cache']) ? true : null;
        $in['newWidth'] = isset($_GET['width']) ? $_GET['width'] : null;
        $in['newHeight'] = isset($_GET['height']) ? $_GET['height'] : null;
        $in['cropToFit'] = isset($_GET['crop-to-fit']) ? true : null;
        $in['sharpen'] = isset($_GET['sharpen']) ? true : null;
        $in['pathToImage'] = realpath(IMG_PATH . $in['src']);

        // Validate incoming arguments
        is_dir(IMG_PATH) || $this->errorMessage('The image dir is not a valid directory. ');
        is_writable(CACHE_PATH) || $this->errorMessage('The cache dir is not a writable directory.');
        isset($in['src']) || $this->errorMessage('Must set src-attribute.');
        preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $in['src']) || $this->errorMessage('Filename contains invalid characters.');
        substr_compare(IMG_PATH, $in['pathToImage'], 0, strlen(IMG_PATH)) == 0 || $this->errorMessage('Security constraint: Source image is not directly below the directory IMG_PATH.');
        is_null($in['saveAs']) || in_array($in['saveAs'], array('png', 'jpg', 'jpeg')) || $this->errorMessage('Not a valid extension to save image as');
        is_null($in['quality']) || (is_numeric($in['quality']) && $in['quality'] > 0 && $in['quality'] <= 100) || $this->errorMessage('Quality out of range');
        is_null($in['newWidth']) || (is_numeric($in['newWidth']) && $in['newWidth'] > 0 && $in['newWidth'] <= $this->maxWidth) || $this->errorMessage('Width out of range');
        is_null($in['newHeight']) || (is_numeric($in['newHeight']) && $in['newHeight'] > 0 && $in['newHeight'] <= $this->maxHeight) || $this->errorMessage('Height out of range');
        is_null($in['cropToFit']) || ($in['cropToFit'] and $in['newWidth'] && $in['newHeight']) || $this->errorMessage('Crop to fit needs both width and height to work');

        $in['parts'] = pathinfo($in['pathToImage']);
        $in['fileExtension'] = $in['parts']['extension'];

        $this->in = $in;
    }

}
