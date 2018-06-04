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
use RuntimeException;

/**
 * Class Role
 * @package Webvaloa
 */
class Role
{
    /**
     * @var
     */
    private $role;

    /**
     * @var array
     */
    private $roles;

    /**
     * @var bool|type
     */
    private $roleID;

    /**
     * Role constructor.
     * @param bool $roleID
     */
    public function __construct($roleID = false)
    {
        $this->roles = $this->roles();
        $this->roleID = $roleID;
    }

    /**
     * Returns roles as array.
     *
     * @return array
     */
    public function roles()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT id, role, system_role
            FROM role';

        $stmt = $db->prepare($query);

        try {
            $stmt->execute();
            foreach ($stmt as $row) {
                $roles[$row->id] = $row;
            }
        } catch (Exception $e) {
        }

        if (isset($roles)) {
            return $roles;
        }

        return array();
    }

    /**
     * @param $roleName
     * @return mixed
     */
    public function getRoleId($roleName)
    {
        foreach ($this->roles as $k => $v) {
            if ($v->role == $roleName) {
                return $v->id;
            }
        }
    }

    /**
     * @return array
     */
    public function components()
    {
        if (!$this->roleID) {
            return array();
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT component_id
            FROM component_role
            WHERE role_id = ?';

        $stmt = $db->prepare($query);
        $stmt->set($this->roleID);

        try {
            $stmt->execute();
            $rows = $stmt->fetchAll();
            foreach ($rows as $row) {
                $components[] = $row->component_id;
            }

            if (isset($components)) {
                return $components;
            }

            return array();
        } catch (Exception $e) {
        }
    }

    /**
     *
     */
    public function dropComponents()
    {
        if (!$this->roleID) {
            throw new RuntimeException('RoleID not found');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            DELETE FROM component_role
            WHERE role_id = ?';

        $stmt = $db->prepare($query);

        try {
            $stmt->set((int) $this->roleID);
            $stmt->execute();
        } catch (Exception $e) {
        }
    }

    /**
     * @param $name
     * @return bool|type
     */
    public function addRole($name)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        // Install plugin
        $object = new Db\Item('role', $db);
        $object->role = $name;
        $object->system_role = 0;
        $this->roleID = $object->save();

        return $this->roleID;
    }

    /**
     *
     */
    public function delete()
    {
        if (!$this->roleID) {
            throw new RuntimeException('RoleID not found');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        // Drop components
        $this->dropComponents();

        // Delete role from users
        $query = '
            DELETE FROM user_role
            WHERE role_id = ?';

        $stmt = $db->prepare($query);

        try {
            $stmt->set((int) $this->roleID);
            $stmt->execute();
        } catch (Exception $e) {
        }

        // Delete role
        $query = '
            DELETE FROM role
            WHERE id = ?';

        $stmt = $db->prepare($query);

        try {
            $stmt->set((int) $this->roleID);
            $stmt->execute();
        } catch (Exception $e) {
        }
    }
}
