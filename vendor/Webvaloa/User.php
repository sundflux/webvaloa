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

use Webvaloa\Auth\Password;
use Libvaloa\Db;
use UnexpectedValueException;
use RuntimeException;
use stdClass;

/**
 * Manage users.
 */
class User
{
    private $userID;
    private $object;

    /**
     * @param type $userID
     */
    public function __construct($userID = false)
    {
        $this->object = new Db\Object('user', \Webvaloa\Webvaloa::DBConnection());
        $this->userID = $userID;

        if ($this->userID) {
            $this->object->byID($this->userID);
        }
    }

    public function byUsername($username, $field = 'login')
    {
        $fields = array('login', 'email');

        if (!in_array($field, $fields)) {
            throw new UnexpectedValueException('Field not valid');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT id
            FROM user
            WHERE '.$field.' = ?
            LIMIT 1';

        $stmt = $db->prepare($query);

        $stmt->set($username);
        $stmt->execute();
        $row = $stmt->fetch();

        if (!isset($row->id)) {
            throw new RuntimeException('User not found');
        }

        $this->userID = (int) $row->id;
        $this->object->byID((int) $this->userID);

        unset($row);
    }

    public function byEmail($email)
    {
        return $this->byUsername($email, 'email');
    }

    public function __set($k, $v)
    {
        if ($k == 'password') {
            $tmp = $this->object->email;

            if (empty($tmp)) {
                throw new RuntimeException('Please set email before password');
            }
        }

        if ($k == 'password') {
            $v = Password::cryptPassword($this->object->login, $v);
        }

        $this->object->$k = $v;
    }

    public function __get($k)
    {
        return $this->object->$k;
    }

    public function save()
    {
        return $this->object->save();
    }

    public function delete()
    {
        // Delete user roles
        $roles = $this->roles();

        foreach ($roles as $k => $v) {
            $this->deleteRole($v);
        }

        // Delete user
        return $this->object->delete();
    }

    /**
     * Give role to user.
     *
     * @param type $roleID
     *
     * @return bool
     *
     * @throws RuntimeException
     */
    public function addRole($roleID)
    {
        if (!$this->userID) {
            throw new RuntimeException('UserID must be set before running addRole');
        }

        $roles = $this->roles();

        // User already has this role
        if (is_array($roles) && in_array($roleID, $roles)) {
            return true;
        }

        $object = new DB\Object('user_role', \Webvaloa\Webvaloa::DBConnection());
        $object->user_id = $this->userID;
        $object->role_id = $roleID;

        return $object->save();
    }

    /**
     * Remove user from role.
     *
     * @param type $roleID
     *
     * @return bool
     *
     * @throws RuntimeException
     */
    public function deleteRole($roleID)
    {
        if (!$this->userID) {
            throw new RuntimeException('UserID must be set before running deleteRole');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            DELETE FROM user_role
            WHERE role_id = ?
            AND user_id = ?';

        $stmt = $db->prepare($query);
        $stmt->set((int) $roleID);
        $stmt->set((int) $this->userID);

        try {
            $stmt->execute();
        } catch (Exception $e) {
        }
    }

    public function dropRoles()
    {
        // Delete user roles
        $roles = $this->roles();
        foreach ($roles as $k => $v) {
            $this->deleteRole($v);
        }
    }

    /**
     * Check if user has certain role.
     *
     * @param type $roleID
     *
     * @return bool
     */
    public function hasRole($roleID)
    {
        $roles = $this->roles();

        if (in_array($roleID, $roles)) {
            return true;
        }

        return false;
    }

    /**
     * Return all user roles.
     *
     * @return array
     */
    public function roles()
    {
        // Return public role if no user defined
        if (!$this->userID || !is_numeric($this->userID)) {
            $role = new Role();
            $roles[] = $role->getRoleID('Public');

            return $roles;
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT role_id
            FROM user_role
            WHERE user_id = ?';

        $stmt = $db->prepare($query);
        $stmt->set((int) $this->userID);

        try {
            $stmt->execute();

            foreach ($stmt as $k => $row) {
                $roles[] = $row->role_id;
            }

            if (isset($roles)) {
                return $roles;
            }

            // No roles
            return array();
        } catch (Exception $e) {
        }
    }

    public function metadata($key, $value = false)
    {
        if (!$this->userID) {
            throw new RuntimeException('UserID must be set before using metadata');
        }

        if ($value === false) {
            // Get metadata
            $meta = $this->object->meta;

            if (empty($meta)) {
                return false;
            }

            $meta = json_decode($meta);
            if (isset($meta->$key)) {
                return $meta->$key;
            }

            return false;
        } else {
            // Set metadata
            $meta = $this->object->meta;

            if (empty($meta)) {
                $meta = new stdClass();
            } else {
                $meta = json_decode($meta);
            }

            $meta->$key = $value;
            $meta = json_encode($meta);
            $this->object->meta = $meta;

            return $value;
        }
    }

    /**
     * Check if username is available. Returns true if it is, false if
     * username exists.
     *
     * @param type $username
     *
     * @return bool
     */
    public static function usernameAvailable($username)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $username = trim($username);

        $query = '
            SELECT id
            FROM user
            WHERE login = ?';

        $stmt = $db->prepare($query);
        $stmt->set($username);

        try {
            $stmt->execute();
            $row = $stmt->fetch();

            if (isset($row->id)) {
                // Username exists
                return false;
            }

            // Username is available
            return true;
        } catch (Exception $e) {
        }

        // False on failures, just in case
        return false;
    }
}
