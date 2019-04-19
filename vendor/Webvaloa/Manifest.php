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

namespace Webvaloa;

use Symfony\Component\Yaml\Yaml;
use \Libvaloa\Debug\Debug;

/**
 * Reads and parses manifest files from components.
 *
 * Manifests contain information about components. 
 * Both YAML and JSON are supported. YAML is recommended
 * and takes priority over JSON.
 *
 * @package Webvaloa
 */
class Manifest
{
    /**
     * @var mixed
     */
    private $manifest;

    /**
     * @var string
     */
    private $schema;

    /**
     * @var string
     */
    private $controllerPath;

    /**
     * @var array
     */
    public static $properties = array(
        'vendor' => 'ValoaApplication',
    );

    /**
     * Load manifest data for controller.
     *
     * @param type $controller
     */
    public function __construct($controller)
    {
        $this->schema = false;

        $paths = \Webvaloa\Webvaloa::getSystemPaths();
        
        foreach ($paths as $path) {
            // Yaml config
            if (is_readable($path . '/Controllers/'.$controller.'/manifest.yaml')) {
                Debug::__print('Loaded ' . $path . '/Controllers/'.$controller.'/manifest.yaml');

                $this->schema = $path . '/Controllers/'.$controller.'/manifest.yaml';
                $this->controllerPath = $path . '/Controllers/'.$controller;
                $this->manifest = (object) Yaml::parse(file_get_contents($this->schema));
                break;
            }

            // Json config
            if (is_readable($path . '/Controllers/'.$controller.'/manifest.json')) {
                Debug::__print('Loaded ' . $path . '/Controllers/'.$controller.'/manifest.json');

                $this->schema = $path . '/Controllers/'.$controller.'/manifest.json';
                $this->controllerPath = $path . '/Controllers/'.$controller;
                $this->manifest = json_decode(file_get_contents($this->schema));
                break;
            }
        }

        Debug::__print($this->manifest);
    }

    /**
     * @param $k
     * @return bool|string
     */
    public function __get($k)
    {
        if (is_object($this->manifest) && isset($this->manifest->$k)) {
            return $this->manifest->$k;
        }

        if ($k == 'schemaFile') {
            return $this->schema;
        }

        if ($k == 'controllerPath') {
            return $this->controllerPath;
        }

        return false;
    }
}
