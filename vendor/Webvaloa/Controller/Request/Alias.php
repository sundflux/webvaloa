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

namespace Webvaloa\Controller\Request;

use Libvaloa\Db;
use Webvaloa\Cache;
use stdClass;

class Alias
{
    private $db;
    private $cache;

    // Routes from override config
    private $routes;

    public $controller;

    public function __construct($alias)
    {
        $this->cache = new Cache();
        $this->controller = new stdClass();

        // Alias
        if (strlen($alias) == 0) {
            return;
        }

        $this->controller->controller = $controller = ucfirst(strtolower($alias));

        // Load from cache
        $tmpNam = "__alias{$this->controller->controller}";
        if ($tmp = $this->cache->$tmpNam) {
            $this->controller = $tmp;

            return;
        }

        // Load route overrides
        $this->loadRoutesFile();

        if (!$this->loadRoute($alias)) {
            // Load alias from DB

            $this->db = \Webvaloa\Webvaloa::DBConnection();

            $query = "
                SELECT id, controller, method, locale
                FROM alias
                WHERE alias = ?
                AND (locale = '*' OR locale = ?)
                LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->set(strtolower($this->controller->controller));
            $stmt->set(getenv('LANG'));

            try {
                $stmt->execute();
                $row = $stmt->fetch();

                if (isset($row->controller)) {
                    $this->controller = $row;
                    $this->cache->$tmpNam = $row;
                }
            } catch (PDOException $e) {
            }
        }
    }

    public function getMethod()
    {
        if (isset($this->controller->method) && !empty($this->controller->method)) {
            if (strpos($this->controller->method, '/') !== false) {
                $tmp = explode('/', $this->controller->method);

                return $tmp[0];
            }

            return $this->controller->method;
        }

        return 'index';
    }

    public function getParams()
    {
        if (isset($this->controller->method) && !empty($this->controller->method)) {
            if (strpos($this->controller->method, '/') !== false) {
                $tmp = explode('/', $this->controller->method);
                if (!isset($tmp[1])) {
                    return false;
                }

                array_shift($tmp);
                foreach ($tmp as $k => $v) {
                    $params[] = $v;
                }

                return $params;
            }
        }
    }

    private function loadRoutesFile()
    {
        if (is_readable(WEBVALOA_BASEDIR.'/config/routes.php')) {
            require_once WEBVALOA_BASEDIR.'/config/routes.php';

            if (isset(\Webvaloa\routes::$routes)) {
                $this->routes = \Webvaloa\routes::$routes;
            }
        }
    }

    public function loadRoute($alias)
    {
        if (!$this->routes) {
            return false;
        }

        $alias = strtolower($alias);

        if (isset($this->routes[$alias])) {
            $this->controller->controller    = $this->routes[$alias]['controller'];
            $this->controller->method        = $this->routes[$alias]['method'];
            $this->controller->locale        = $this->routes[$alias]['locale'];
            $this->controller->id           = -1;

            return true;
        }

        return false;
    }

    public function __toString()
    {
        return (string) $this->controller->controller;
    }
}
