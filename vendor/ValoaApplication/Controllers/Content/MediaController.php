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
namespace ValoaApplication\Controllers\Content;

use Webvaloa\Controller\Redirect;
use Webvaloa\Security;
use Webvaloa\Helpers\Filesystem;
use UnexpectedValueException;
use RuntimeException;

class MediaController extends \Webvaloa\Application
{
    public function __construct()
    {
        if ($this->request->isAjax()) {
            $this->view->isAjax = true;
        }

        $this->view->mediaPath = LIBVALOA_PUBLICPATH.'/media';
    }

    public function index()
    {
        $fs = new Filesystem($this->view->mediaPath);
        $this->view->path = str_replace(WEBVALOA_BASEDIR, '', $this->view->mediaPath);
        $this->view->children = $fs->getChildren();
    }

    public function listing()
    {
        Security::verify();

        $srcPath = '';
        if (isset($_POST['path'])) {
            if (strpos($_POST['path'], '..') !== false) {
                throw new UnexpectedValueException();
            }

            $srcPath = urldecode($_POST['path']);
        }

        $_SESSION['upload_subdir'] = $srcPath = $this->formatPath($srcPath);
        $this->view->currentPath = $srcPath;

        $path = realpath($this->view->mediaPath.'/'.$srcPath);
        if (file_exists($path)) {
            $fs = new Filesystem($path);
            $this->view->files = $fs->files();
        }

        $this->view->mediapicker = 0;
        if (isset($_POST['mediapicker'])) {
            $this->view->mediapicker = (int) $_POST['mediapicker'];
        }
    }

    public function upload()
    {
        if (!is_writable($this->view->mediaPath)) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('MEDIA_NOT_WRITABLE'));
        }

        // Must be set by media manager
        $this->view->uploadPath = '';
        if (isset($_SESSION['upload_subdir'])) {
            $this->view->uploadPath = $this->formatPath($_SESSION['upload_subdir']);
        }

        $this->view->path = str_replace(WEBVALOA_BASEDIR, '', $this->view->mediaPath.'/'.$this->view->uploadPath);
    }

    public function delete()
    {
        Security::verify();

        // Delete file
        if (isset($_GET['file']) && !empty($_GET['file'])) {
            $file = $_GET['file'];

            // Must be set by media manager
            $uploadPath = '';
            if (isset($_SESSION['upload_subdir'])) {
                $uploadPath = $this->formatPath($_SESSION['upload_subdir']);
            }

            $path = realpath($this->view->mediaPath.'/'.$uploadPath);
            $mediaPath = realpath($this->view->mediaPath);
            $filename = $path.DIRECTORY_SEPARATOR.$file;

            $pos = strpos($filename, $mediaPath);
            if ($pos === false) {
                throw new RuntimeException('File not in media path');
            }

            $filesystem = new Filesystem($path);

            return $filesystem->delete($file);
        }

        // Delete folfder
        if (isset($_GET['folder']) && !empty($_GET['folder'])) {
            if ($_GET['folder'] != $_SESSION['upload_subdir']) {
                throw new UnexpectedValueException('Folder does not match upload_subdir');
            }

            // Must be set by media manager
            $uploadPath = '';
            if (isset($_SESSION['upload_subdir'])) {
                $uploadPath = $this->formatPath($_SESSION['upload_subdir']);
            }

            $path = realpath($this->view->mediaPath.'/'.$uploadPath);
            $mediaPath = realpath($this->view->mediaPath);

            $pos = strpos($path, $mediaPath);
            if ($pos === false) {
                throw new RuntimeException('Folder not in media path');
            }

            $filesystem = new Filesystem($path);

            return $filesystem->rmdir();
        }

        throw new RuntimeException('File not found');
    }

    public function store()
    {
        Security::verify();

        $fs = new Filesystem($this->view->mediaPath);

        $a = '';

        // Must be set by media manager
        if (isset($_SESSION['upload_subdir'])) {
            $a = $this->formatPath($_SESSION['upload_subdir']);
        }

        $dir = $this->view->mediaPath.'/'.$a;

        if (isset($_FILES['files'])) {
            $ret = array();
            $error = $_FILES['files']['error'];

            if (!is_array($_FILES['files']['name'])) {
                $filename = $_FILES['files']['name'];

                move_uploaded_file($_FILES['files']['tmp_name'], $fs->getAvailableFilename($dir, $filename));
                $ret[] = $filename;
            } else {
                $fileCount = count($_FILES['files']['name']);

                for ($i = 0; $i < $fileCount; ++$i) {
                    $filename = $_FILES['files']['name'][$i];

                    move_uploaded_file($_FILES['files']['tmp_name'][$i], $fs->getAvailableFilename($dir, $filename));
                    $ret[] = $filename;
                }
            }

            echo json_encode($ret);
        }

        exit;
    }

    public function create()
    {
        Security::verify();

        $a = '';

        // Must be set by media manager
        if (isset($_SESSION['upload_subdir'])) {
            $a = $this->formatPath($_SESSION['upload_subdir']);
        }

        $path = realpath($this->view->mediaPath.'/'.$a);
        $fs = new Filesystem($path);
        if (isset($_POST['folder'])) {
            if ($fs->createDirectory($_POST['folder'])) {
                $this->ui->addMessage(\Webvaloa\Webvaloa::translate('ADDED_NEW_FOLDER'));
            } else {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('COULD_NOT_ADD_FOLDER').' '.$path.'/'.$_POST['folder']);
            }
        }

        Redirect::to('content_media#/'.$a.$_POST['folder']);
    }

    private function formatPath($p)
    {
        if (substr($p, -1) != '/') {
            $p .= '/';
        }

        if (substr($p, 0, 1) == '/') {
            $p = substr($p, 1);
        }

        return $p;
    }
}
