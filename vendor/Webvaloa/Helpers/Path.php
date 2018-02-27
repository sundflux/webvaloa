<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2017 Tarmo Alexander Sundström <ta@sundstrom.im>
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

use Libvaloa\Debug;

/**
 * Class Path
 * @package Webvaloa\Helpers
 */
class Path
{
    /**
     * @var array
     */
    public static $properties = array(
        'vendor' => 'ValoaApplication',
    );

    /**
     * @var array
     */
    private $paths;

    /**
     * @var array|string
     */
    private $basePath;

    /**
     * @var array|string
     */
    private $publicPath;

    /**
     * @var array|string
     */
    private $configPath;

    /**
     * @var bool
     */
    private $systemPaths;

    /**
     * @var bool
     */
    private $layoutPaths;

    /**
     * @var bool
     */
    private $pluginPaths;

    /**
     * @var bool
     */
    private $controllerPaths;

    /**
     * Path constructor.
     */
    public function __construct()
    {
        $this->basePath = self::trimPath(WEBVALOA_BASEDIR);
        $this->publicPath = self::trimPath(LIBVALOA_PUBLICPATH);
        $this->configPath = self::trimPath(WEBVALOA_BASEDIR.'/config');

        $this->systemPaths = false;
        $this->layoutPaths = false;
        $this->pluginPaths = false;
        $this->controllerPaths = false;

        $this->paths[] = self::trimPath(LIBVALOA_INSTALLPATH);
        $this->paths[] = self::trimPath(LIBVALOA_EXTENSIONSPATH);
        $this->paths = array_merge($this->paths, explode(':', get_include_path()));
        $this->paths = self::trimPath($this->paths);
        $this->paths = array_unique($this->paths);

        Debug::__print('Scanning following paths for files:');
        Debug::__print($this->paths);

        $this->scanPaths();
    }

    /**
     * @param $path
     * @return array|string
     */
    public static function trimPath($path)
    {
        if (is_array($path)) {
            foreach ($path as $k => $v) {
                $path[$k] = rtrim($v);
                $path[$k] = rtrim($path[$k], '/');
            }
        } elseif (is_string($path)) {
            $path = rtrim($path);
            $path = rtrim($path, '/');
        }

        return $path;
    }

    /**
     *
     */
    public function scanPaths()
    {
        $this->systemPaths = \Webvaloa\Webvaloa::getSystemPaths();

        Debug::__print('Scanning following system paths for files:');
        Debug::__print($this->systemPaths);
    }

    /**
     * @return string $paths Returns path to Webvaloa base installation path (where index.php is)
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @return string $paths Returns path to /public directory
     */
    public function getPublicPath()
    {
        return $this->publicPath;
    }

    /**
     * @return string $paths Returns path to /config directory
     */
    public function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * @return array Returns array of system paths (vendor dirs)
     */
    public function getSystemPaths()
    {
        return $this->systemPaths;
    }

    /**
     * @return array Returns array of controller root paths
     */
    public function getControllerPaths()
    {
        if ($this->controllerPaths) {
            return $this->controllerPaths;
        }

        foreach ($this->systemPaths as $path) {
            if (file_exists($path.'/Controllers')) {
                $this->controllerPaths[] = $path.'/Controllers';
            }
        }

        return self::trimPath($this->controllerPaths);
    }

    /**
     * @return array Returns array of plugin root paths (vendor dirs)
     */
    public function getPluginPaths()
    {
        if ($this->pluginPaths) {
            return $this->pluginPaths;
        }

        foreach ($this->systemPaths as $path) {
            if (file_exists($path.'/Plugins')) {
                $this->pluginPaths[] = $path.'/Plugins';
            }
        }

        return self::trimPath($this->pluginPaths);
    }

    /**
     * @return array Returns array of layout paths
     */
    public function getLayoutPaths()
    {
        if ($this->layoutPaths) {
            return $this->layoutPaths;
        }

        foreach ($this->systemPaths as $path) {
            if (file_exists($path.'/Plugins')) {
                $this->layoutPaths[] = $path.'/Layout';
            }
        }

        return self::trimPath($this->layoutPaths);
    }
}
