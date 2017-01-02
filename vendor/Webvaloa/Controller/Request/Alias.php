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
use stdClass;

class Alias
{
    private $db;

    // Routes from override config
    private $routes;

    // URL params
    private $params;

    public $controller;

    public function __construct($alias)
    {
        $this->controller = new stdClass();

        // Alias
        if (strlen($alias[0]) == 0) {
            return;
        }
        $this->params = $alias;
        $this->controller->controller = $controller = ucfirst(strtolower($this->params[0]));

        // Load route overrides
        $this->loadRoutesFile();

        if (!$this->loadRoute()) {
            $this->db = \Webvaloa\Webvaloa::DBConnection();

            // Aliases must work without db connection
            if (!method_exists($this->db, 'prepare')) {
                return;
            }

            // Try loading route
            $lastRouteItem = array_slice($this->params, -1)[0];
            $parentRouteItem = array_slice($this->params, -2)[0];

            // Get parent route
            if (!empty($parentRouteItem) && ($parentRouteItem != $lastRouteItem)) {
                $query = 'SELECT id,type FROM structure WHERE alias = ?';
                $stmt = $this->db->prepare($query);
                $stmt->set($parentRouteItem);
                try {
                    $stmt->execute();
                    $row = $stmt->fetch();
                    if (isset($row->id)) {
                        $parentId = $row->id;
                        $parentType = $row->type;
                    }
                } catch (PDOException $e) {
                }
            }

            // Load the route

            if (!empty($parentId)) {
                $query = "
                    SELECT type, parent_id, target_id, target_url, locale
                    FROM structure
                    WHERE alias = ?
                    AND (locale = '*' OR locale = ?)
                    AND parent_id = ?
                    LIMIT 1";
            } else {
                $query = "
                    SELECT type, parent_id, target_id, target_url, locale
                    FROM structure
                    WHERE alias = ?
                    AND (locale = '*' OR locale = ?)                
                    LIMIT 1";
            }

            $stmt = $this->db->prepare($query);
            $stmt->set($lastRouteItem);
            $stmt->set(getenv('LANG'));

            if (!empty($parentId)) {
                $stmt->set((int) $parentId);
            }

            try {
                $stmt->execute();
                $row = $stmt->fetch();

                // If parent is article listing, route to the article_view instead
                if (!empty($parentType) && $parentType == 'content_listing') {
                    $row->type = 'content';
                    $row->target_id = $lastRouteItem;
                }

                $this->buildContentRoute($row);

                return;
            } catch (PDOException $e) {
            }

            // Try loading alias

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

    public function loadRoute()
    {
        $alias = $this->params[0] = strtolower($this->params[0]);

        // Load from routes file if available;

        if (!$this->routes) {
            return false;
        }

        if (isset($this->routes[$alias])) {
            $this->controller->controller = $this->routes[$alias]['controller'];
            $this->controller->method = $this->routes[$alias]['method'];
            $this->controller->locale = $this->routes[$alias]['locale'];
            $this->controller->id = -1;

            return true;
        }

        return false;
    }

    private function buildContentRoute($row)
    {
        if (!isset($row->type)) {
            return false;
        }

        switch ($row->type) {
            case 'content':
                $this->controller->controller = 'Article_View';
                $this->controller->method = 'index/'.$row->target_id;
                $this->controller->locale = $row->locale;
                $this->controller->id = -1;

                break;

            case 'content_listing':
                $this->controller->controller = 'Article_List';
                $this->controller->method = 'index/'.$row->target_id;
                $this->controller->locale = $row->locale;
                $this->controller->id = -1;

                break;

            case 'component':
                $query = 'SELECT controller FROM component WHERE id = ?';
                $stmt = $this->db->prepare($query);
                $stmt->set((int) $row->target_id);

                try {
                    $stmt->execute();
                    $res = $stmt->fetch();
                    $this->controller->id = $row->target_id;
                    $this->controller->controller = $res->controller;
                    $this->controller->method = 'index';
                    $this->controller->locale = $row->locale;
                } catch (PDOException $e) {
                }

                break;

            default:
                break;
        }
    }

    public function __toString()
    {
        return (string) $this->controller->controller;
    }
}
