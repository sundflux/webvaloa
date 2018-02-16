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

use Libvaloa\Db;
use Libvaloa\Debug;
use Webvaloa\Helpers\Filesystem;
use Webvaloa\Helpers\Path;
use RuntimeException;

/**
 * Handles Webvaloa components.
 */
class Component
{
    private $id;
    private $controller;
    private $component;
    private $roles;

    public static $properties = array(
        'vendor' => 'ValoaApplication',
    );

    /**
     * Constructor, give controller name for actions.
     *
     * @param string $controller
     */
    public function __construct($controller = false)
    {
        $this->controller = $controller;
        $this->id = false;
        $this->component = false;
    }

    public function __set($k, $v)
    {
        if (!$this->component) {
            $this->loadComponent();
        }

        // No properties for this component
        if ($this->component == '__NULL__') {
            return;
        }

        $this->component->$k = $v;
    }

    public function __get($k)
    {
        if (!$this->component) {
            $this->loadComponent();
        }

        if (isset($this->component->$k)) {
            return $this->component->$k;
        }

        return false;
    }

    public function byID($componentID)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT controller
            FROM component
            WHERE id = ?';

        try {
            $stmt = $db->prepare($query);
            $stmt->set((int) $componentID);
            $stmt->execute();
            $row = $stmt->fetch();

            $this->controller = $row->controller;
            $this->loadComponent();

            $this->id = $componentID;
        } catch (Exception $e) {
        }
    }

    /**
     * Loads component data.
     */
    private function loadComponent()
    {
        // Don't load anything for anonymous components
        if ($this->controller) {
            $manifest = new Manifest($this->controller);
            if ($manifest->anonymous == 1) {
                return;
            }
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT component.*
            FROM component
            WHERE component.controller = ?
            LIMIT 1';

        $stmt = $db->prepare($query);
        $stmt->set($this->controller);

        try {
            $stmt->execute();
            $this->component = $stmt->fetch();
        } catch (Exception $e) {
        }
    }

    /**
     * Returns list of roles for this component.
     *
     * @return array Roles
     */
    public function roles()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT component_role.role_id
            FROM component_role, component
            WHERE component_role.component_id = component.id
            AND component.controller = ?';

        $stmt = $db->prepare($query);
        $stmt->set($this->controller);

        try {
            $stmt->execute();
            foreach ($stmt as $row) {
                $roles[] = $row->role_id;
            }
        } catch (Exception $e) {
        }

        if (isset($roles)) {
            return $roles;
        }

        return array();
    }

    /**
     * Return all components.
     *
     * @return type
     */
    public function components()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT *
            FROM component';

        $stmt = $db->prepare($query);

        try {
            $stmt->execute();
            foreach ($stmt as $row) {
                $components[] = $row;
            }
        } catch (Exception $e) {
        }

        if (isset($components)) {
            return $components;
        }

        return array();
    }

    /**
     * Adds role to component.
     *
     * @param type $roleID
     *
     * @return int roleid
     */
    public function addRole($roleID)
    {
        $roles = $this->roles();

        // Already has the role
        if (in_array($roleID, $roles)) {
            return true;
        }

        // No component loaded yet, load before adding roles
        if (!$this->id) {
            return false;
        }

        // Database connection
        $db = \Webvaloa\Webvaloa::DBConnection();

        // Insert role object
        $object = new Db\Object('component_role', $db);
        $object->component_id = $this->id;
        $object->role_id = $roleID;

        return $object->save();
    }

    public function dropRoles()
    {
        if (!$this->component) {
            $this->loadComponent();
        }

        if (!is_numeric($this->id)) {
            throw new RuntimeException('Component not loaded');
        }

        // Database connection
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            DELETE FROM component_role
            WHERE component_id = ?';

        $stmt = $db->prepare($query);
        $stmt->set((int) $this->id);

        try {
            $stmt->execute();
        } catch (Exception $e) {
        }
    }

    public function isPublic()
    {
        $role = new Role();

        $componentRoles = $this->roles();
        if (in_array($role->getRoleID('Public'), $componentRoles)) {
            return true;
        }

        return false;
    }

    /**
     * Install a component.
     *
     * @return int ComponentID
     */
    public function install()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        // Read component manifest
        $manifest = new Manifest($this->controller);

        if ($manifest->anonymous == 1) {
            return true;
        }

        // Install database
        $sqlSchema = $manifest->controllerPath.'/schema-'.$manifest->version.'.'.\Webvaloa\config::$properties['db_server'].'.sql';

        if (file_exists($sqlSchema)) {
            $query = file_get_contents($sqlSchema);

            $db->exec($query);
        }

        // Install component
        $object = new Db\Object('component', $db);
        $object->controller = $this->controller;
        $object->system_component = 0;
        $object->blocked = 0;
        $this->id = $object->save();

        return $this->id;
    }

    /**
     * Uninstall a component.
     */
    public function uninstall()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        // Read component manifest
        $manifest = new Manifest($this->controller);

        if ($manifest->anonymous == 1) {
            return true;
        }

        // Uninstall the component
        $query = 'DELETE FROM component '
                .'WHERE controller = ? '
                .'AND system_component = 0';

        $stmt = $db->prepare($query);
        $stmt->set($this->controller);
        $stmt->execute();

        // Uninstall database
        $sqlSchema = $manifest->controllerPath.'/schema-'.$manifest->version.'.'.\Webvaloa\config::$properties['db_server'].'.uninst.sql';

        if (file_exists($sqlSchema)) {
            $query = file_get_contents($sqlSchema);

            try {
                $db->exec($query);
            } catch (Exception $e) {
            }
        }
    }

    public static function getComponentStatus($componentID)
    {
        $query = '
            SELECT blocked
            FROM component
            WHERE system_component = 0
            AND id = ?';

        try {
            $db = \Webvaloa\Webvaloa::DBConnection();

            $stmt = $db->prepare($query);
            $stmt->set((int) $componentID);
            $stmt->execute();

            $row = $stmt->fetch();

            if (isset($row->blocked)) {
                return $row->blocked;
            }

            return false;
        } catch (PDOException $e) {
        }
    }

    public static function setComponentStatus($componentID, $status = 0)
    {
        $query = '
            UPDATE component
            SET blocked = ?
            WHERE id = ?';

        try {
            $db = \Webvaloa\Webvaloa::DBConnection();

            $stmt = $db->prepare($query);
            $stmt->set((int) $status);
            $stmt->set((int) $componentID);
            $stmt->execute();
        } catch (PDOException $e) {
        }
    }

    public function discover()
    {
        $pathHelper = new Path();

        // Installed components
        $tmp = $this->components();
        foreach ($tmp as $v => $component) {
            $installedComponents[] = $component->controller;
        }

        // Look for new components
        foreach ($pathHelper->getControllerPaths() as $path) {
            if (!is_readable($path)) {
                Debug::__print('Controller path not readable:');
                Debug::__print($path);

                continue;
            }

            try {
                $fs = new Filesystem($path);
                $folders = $fs->folders();

                foreach ($folders as $folder) {
                    if (!is_readable($path. '/' . $folder . '/manifest.json')) {
                        continue;
                    }

                    $manifest = new Manifest($folder);

                    if ($manifest->anonymous == 1) {
                        continue;
                    }

                    if (!isset($controllers)) {
                        $controllers = array();
                    }

                    if (in_array($folder, $installedComponents)) {
                        continue;
                    }

                    $controllers[] = $folder;
                }
            } catch (\Exception $e) {
                Debug::__print($e->getMessage());
                Debug::__print($path);
            }
        }

        if (isset($controllers)) {
            return $controllers;
        }

        return array();
    }
}
