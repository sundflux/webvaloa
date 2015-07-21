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

class Imagemagick
{
    public $cachePath;

    private $file;
    private $cached;
    private $width;
    private $height;
    private $crop;
    private $format;
    private $quality;

    private $imagick;

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
        $this->quality = 95;
        $this->format = 'jpg';
    }

    public function setFormat($f)
    {
        $this->format = $f;
    }

    public function setCrop($c)
    {
        $this->crop = (bool) $c;
    }

    public function setFlatten($c)
    {
        $this->flatten = (bool) $c;
    }

    public function setBackground($c)
    {
        $this->background = (string) $c;
    }

    public function setWidth($w)
    {
        $this->width = (int) $w;
    }

    public function setHeight($h)
    {
        $this->height = (int) $h;
    }

    public function setQuality($q = 100)
    {
        $this->quality = (int) $q;
    }

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
        $this->imagick->setBackgroundColor(new \ImagickPixel());
        $this->imagick->readImage($this->file);
        if ($this->flatten || $this->format == 'jpg') {
            $this->imagick = $this->imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        }
        $this->imagick->setImageFormat($this->format);
        $this->imagick->setInterlaceScheme(Imagick::INTERLACE_PLANE);
        $this->imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $this->imagick->setImageCompressionQuality($this->quality);

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

    public static function scale($image, $width = 320, $height = 200)
    {
        $im = new self($image);
        $im->setCrop(false);
        $im->setWidth($width);
        $im->setHeight($height);

        if (!$ret = $im->resize()) {
            return 'http://placehold.it/'.$width.'x'.$height;
        }

        $request = Request::getInstance();
        $path = $request->getPath().'/public/cache/';

        return $path.$ret;
    }

    public static function crop($image, $width = 320, $height = 200)
    {
        $im = new self($image);
        $im->setCrop(true);
        $im->setWidth($width);
        $im->setHeight($height);

        if (!$ret = $im->resize()) {
            return 'http://placehold.it/'.$width.'x'.$height;
        }

        $request = Request::getInstance();
        $path = $request->getPath().'/public/cache/';

        return $path.$ret;
    }
}
