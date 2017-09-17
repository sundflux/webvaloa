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

class Path
{
    public static $properties = array(
        'vendor' => 'ValoaApplication',
    );

    private $paths;

    private $basePath;
    private $publicPath;
    private $configPath;

    private $systemPaths;
    private $layoutPaths;
    private $pluginPaths;
    private $controllerPaths;

    public function __construct()
    {
        $this->basePath = WEBVALOA_BASEDIR;
        $this->publicPath = WEBVALOA_BASEDIR.'/public';
        $this->configPath = WEBVALOA_BASEDIR.'/config';

        $this->systemPaths = false;
        $this->layoutPaths = false;
        $this->pluginPaths = false;
        $this->controllerPaths = false;

        $this->paths[] = LIBVALOA_INSTALLPATH;
        $this->paths[] = LIBVALOA_EXTENSIONSPATH;
        $this->paths = array_merge($this->paths, explode(':', get_include_path()));

        Debug::__print('Scanning following paths for system files:');
        Debug::__print($this->paths);

        $this->scanPaths();
    }

    public function scanPaths()
    {
        $this->systemPaths = \Webvaloa\Webvaloa::getSystemPaths();
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

        return $this->controllerPaths;
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

        return $this->pluginPaths;
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

        return $this->layoutPaths;
    }
}
