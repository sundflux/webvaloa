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

// Libvaloa classes

/**
 * Read manifests.
 *
 * Manifests contain information about components.
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

        // Default to installpath
        if (is_readable(LIBVALOA_INSTALLPATH.'/'.self::$properties['vendor'].'/Controllers/'.$controller.'/manifest.json')) {
            $this->schema = LIBVALOA_INSTALLPATH.'/'.self::$properties['vendor'].'/Controllers/'.$controller.'/manifest.json';
            $this->controllerPath = LIBVALOA_INSTALLPATH.'/'.self::$properties['vendor'].'/Controllers/'.$controller;
        }

        // Override with extensionspath
        if (is_readable(LIBVALOA_EXTENSIONSPATH.'/'.self::$properties['vendor'].'/Controllers/'.$controller.'/manifest.json')) {
            $this->schema = LIBVALOA_EXTENSIONSPATH.'/'.self::$properties['vendor'].'/Controllers/'.$controller.'/manifest.json';
            $this->controllerPath = LIBVALOA_EXTENSIONSPATH.'/'.self::$properties['vendor'].'/Controllers/'.$controller;
        }

        if ($this->schema) {
            $this->manifest = json_decode(file_get_contents($this->schema));
        }
    }

    /**
     * @param $k
     * @return bool|string
     */
    public function __get($k)
    {
        if (isset($this->manifest->$k)) {
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
