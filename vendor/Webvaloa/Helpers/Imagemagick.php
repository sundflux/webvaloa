<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.im>
 *
 * All Rights Reserved.
 *
 * Contributor(s):
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */

namespace Webvaloa\Helpers;

use Imagick;
use Webvaloa\Controller\Request;

/**
 * Class Imagemagick
 *
 * @package Webvaloa\Helpers
 */
class Imagemagick
{
    /**
     * @var string
     */
    public $cachePath;

    /**
     * @var
     */
    private $file;

    /**
     * @var bool
     */
    private $cached;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var bool
     */
    private $crop;

    /**
     * @var string
     */
    private $format;

    /**
     * @var int
     */
    private $quality;

    /**
     * @var
     */
    private $imagick;

    /**
     * Imagemagick constructor.
     *
     * @param $file
     */
    public function __construct($file)
    {
        $this->cachePath = LIBVALOA_PUBLICPATH.'/cache';

        // Check first if file exists in public path
        $publicPath = LIBVALOA_PUBLICPATH;
        if (substr($publicPath, -1) != '/') {
            // Make sure trailing / is there
            $publicPath = $publicPath.'/';
        }
        $publicPath = $publicPath.'media/';

        // Strip off / from filename if it has one
        $tmp = $file;
        if (substr($tmp, 0, 1) == '/') {
            $tmp = substr($tmp, 1);
        }

        $filename = $publicPath.$tmp;
        if (file_exists($filename) && is_file($filename) && is_readable($filename)) {
            $this->file = $filename;
        } else {
            // Wasn't in the public path, so use filename as-is

            $this->file = $file;
        }

        $this->width = 320;
        $this->height = 200;
        $this->crop = true;
        $this->cached = false;
        $this->background = 'white';
        $this->flatten = false;
        $this->quality = 90;
        $this->format = 'jpg';
    }

    /**
     * @param $f
     */
    public function setFormat($f)
    {
        $this->format = $f;
    }

    /**
     * @param $c
     */
    public function setCrop($c)
    {
        $this->crop = (bool) $c;
    }

    /**
     * @param $c
     */
    public function setFlatten($c)
    {
        $this->flatten = (bool) $c;
    }

    /**
     * @param $c
     */
    public function setBackground($c)
    {
        $this->background = (string) $c;
    }

    /**
     * @param $w
     */
    public function setWidth($w)
    {
        $this->width = (int) $w;
    }

    /**
     * @param $h
     */
    public function setHeight($h)
    {
        $this->height = (int) $h;
    }

    /**
     * @param int $q
     */
    public function setQuality($q = 100)
    {
        $this->quality = (int) $q;
    }

    /**
     * @return bool|string
     * @throws \ImagickException
     */
    public function resize()
    {
        // File doesn't exist, it's not a file or it's not readable
        if (!file_exists($this->file) || !is_file($this->file) || !is_readable($this->file)) {
            return false;
        }

        $fileInfo = pathinfo($this->file);
        $cacheFilename = $fileInfo['filename']
            .crc32($fileInfo['filename'].$fileInfo['extension'].$this->width.$this->height)
            .'.'.$this->format;

        if (file_exists($this->cachePath.'/'.$cacheFilename)) {
            return $cacheFilename;
        }

        if (!is_writable($this->cachePath)) {
            return false;
        }

        $this->imagick = new Imagick();
        $this->imagick->setBackgroundColor(new \ImagickPixel($this->background));
        $this->imagick->readImage($this->file);
        if (($this->flatten || $this->format == 'jpg') && $this->imagick->getImageAlphaChannel()) {
            try {
                $this->imagick->setImageAlphaChannel(11);
                $this->imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            } catch (\Exception $e) {
                // Don't die if the imagick support is missing features
            }
        }
        $this->imagick->setImageFormat($this->format);
        $this->imagick->setInterlaceScheme(Imagick::INTERLACE_PLANE);
        $this->imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $this->imagick->setImageCompressionQuality($this->quality);
        $this->imagick->stripImage();
        $this->imagick->setSamplingFactors(array('2x2', '1x1', '1x1'));

        if ($this->crop) {
            $this->imagick->cropThumbnailImage($this->width, $this->height);
        } else {
            $this->imagick->scaleImage($this->width, $this->height);
        }

        if ($this->imagick->writeImage($this->cachePath.'/'.$cacheFilename)) {
            return $cacheFilename;
        } else {
            return false;
        }
    }

    /**
     * @param  $image
     * @param  int    $width
     * @param  int    $height
     * @param  string $format
     * @return string
     * @throws \ImagickException
     */
    public static function scale($image, $width = 320, $height = 200, $format = 'jpg')
    {
        $im = new self($image);
        $im->format = $format;
        $im->setCrop(false);
        $im->setWidth($width);
        $im->setHeight($height);

        if (!$ret = $im->resize()) {
            return 'https://placehold.it/'.$width.'x'.$height;
        }

        $request = Request::getInstance();
        $path = $request->getPath().'/public/cache/';

        return $path.$ret;
    }

    /**
     * @param  $image
     * @param  int    $width
     * @param  int    $height
     * @param  string $format
     * @return string
     * @throws \ImagickException
     */
    public static function crop($image, $width = 320, $height = 200, $format = 'jpg')
    {
        $im = new self($image);
        $im->format = $format;
        $im->setCrop(true);
        $im->setWidth($width);
        $im->setHeight($height);

        if (!$ret = $im->resize()) {
            return 'https://placehold.it/'.$width.'x'.$height;
        }

        $request = Request::getInstance();
        $path = $request->getPath().'/public/cache/';

        return $path.$ret;
    }
}
