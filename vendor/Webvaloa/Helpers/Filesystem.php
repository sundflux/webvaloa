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

use stdClass;
use DirectoryIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

/**
 * Class Filesystem
 * @package Webvaloa\Helpers
 */
class Filesystem
{
    /**
     * @var
     */
    private $path;

    /**
     * @var
     */
    private $files;

    /**
     * @var
     */
    private $folders;

    /**
     * Filesystem constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
        $this->_readdir();
    }

    /**
     *
     */
    public function _readdir()
    {
        if (!is_readable($this->path)) {
            throw new RuntimeException('Path is not readable.');
        }

        $fs = new DirectoryIterator($this->path);

        foreach ($fs as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isDir()) {
                $this->folders[] = $fileInfo->getFilename();
            } else {
                $tmp = new stdClass();
                $tmp->filename = $fileInfo->getFilename();
                $tmp->fileinfo = pathinfo($this->path.'/'.$tmp->filename);
                $tmp->fullpath = $tmp->fileinfo['dirname'].'/'.$tmp->fileinfo['basename'];
                $tmp->filesize = $this->formatFilesize(filesize($this->path.'/'.$tmp->filename));
                $tmp->extension = strtolower($tmp->fileinfo['extension']);
                $this->files[] = $tmp;
            }
        }
    }

    /**
     * @param $n
     * @return bool
     */
    public function createDirectory($n)
    {
        return mkdir($this->path.'/'.$n);
    }

    /**
     * @return mixed
     */
    public function files()
    {
        return $this->files;
    }

    /**
     * @return mixed
     */
    public function folders()
    {
        return $this->folders;
    }

    /**
     * @param $bytes
     * @param int $decimals
     * @return string
     */
    public function formatFilesize($bytes, $decimals = 2)
    {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).@$sz[$factor];
    }

    /**
     * @param $dir
     * @param $file
     * @return string
     */
    public function getAvailableFilename($dir, $file)
    {
        $i = 0;
        $f = $file;
        while (file_exists($dir.$f)) {
            ++$i;
            $fileInfo = pathinfo($dir.$f);
            $f = $fileInfo['filename'].'-'.$i.'.'.$fileInfo['extension'];
        }

        return $dir.$f;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->path,
                RecursiveDirectoryIterator::SKIP_DOTS
            ),
                RecursiveIteratorIterator::CHILD_FIRST
        ) as $dir => $fileInfo) {
            if ($fileInfo->isDir()) {
                $children[] = str_replace($this->path, '', $dir);
            }
        }

        if (isset($children)) {
            return array_reverse($children);
        }

        return array();
    }

    /**
     * @return bool
     */
    public function rmdir()
    {
        if (is_dir($this->path) && is_writable($this->path)) {
            return @rmdir($this->path);
        }

        throw new RuntimeException('Directory not empty or writeable.');
    }

    /**
     * @param $filename
     */
    public function delete($filename)
    {
        $path = pathinfo($this->path.'/'.$filename);
        $_filename = realpath($this->path).'/'.$path['basename'];

        if (!file_exists($_filename)) {
            throw new RuntimeException('File not found');
        }

        @unlink($_filename);
    }

    /**
     * @param $filename
     * @param string $mimetype
     */
    private function download($filename, $mimetype = 'application/octet-stream')
    {
        // Based on techniques described here:
        // http://www.media-division.com/the-right-way-to-handle-file-downloads-in-php/

        $path = pathinfo($this->path.'/'.$filename);
        $_filename = realpath($this->path).'/'.$path['basename'];

        if (!file_exists($_filename)) {
            throw new RuntimeException('File not found');
        }

        if (empty($mimetype)) {
            $mimetype = 'application/octet-stream';
        }

        $size = filesize($_filename);
        $time = date('r', filemtime($_filename));

        $handle = @fopen($_filename, 'rb');
        if (!$handle) {
            throw new RuntimeException('Could not read file');
        }

        $begin = 0;
        $end = $size;

        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
                $begin = intval($matches[0]);

                if (!empty($matches[1])) {
                    $end = intval($matches[1]);
                }

                // TODO: Validate range, should output:
                // header('HTTP/1.1 416 Requested Range Not Satisfiable');
                // in case of nonsatisfiable range
            }
        }

        if ($begin > 0 || $end < $size) {
            header('HTTP/1.0 206 Partial Content');
        } else {
            header('HTTP/1.0 200 OK');
        }

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        header('Content-Length: '.($end - $begin));
        header('Content-Range: bytes '.($begin - $end / $size));
        header('Content-Disposition: inline; filename="'.$filename.'"');
        header('Content-Type: '.$mimetype);
        header('Last-Modified: '.$time);
        header('Connection: close');

        $cur = $begin;
        fseek($handle, $begin, 0);
        while (!feof($fm) && $cur < $end && (connection_status() == 0)) {
            echo fread($fm, min(1024 * 16, $end - $cur));
            $cur += 1024 * 16;
        }
    }
}
