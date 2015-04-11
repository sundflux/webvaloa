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

namespace Webvaloa\Auth;

use Libvaloa\Auth;
use Libvaloa\Auth\Password;
use Libvaloa\Auth\AuthIFace;
use Libvaloa\Auth\PWResetIFace;
use Webvaloa;
use Webvaloa\Component;
use Webvaloa\User;

/**
 * Authentication driver, implementing libvaloa's authentication
 * and password reset interfaces.
 */
class Db implements AuthIFace, PWResetIFace
{
    public function __construct()
    {
    }

    /**
     * Authenticate user.
     *
     * @param type $user
     * @param type $pass
     *
     * @return bool
     */
    public function authenticate($user = false, $pass = false)
    {
        // Database connection
        $db = \Webvaloa\Webvaloa::DBConnection();

        // Query for UserID and password
        try {
            $query = '
                SELECT id, password
                FROM user
                WHERE login = ?';

            $stmt = $db->prepare($query);
            $stmt->set($user);
            $stmt->execute();

            $row = $stmt->fetch();
        } catch (Exception $e) {
            return false;
        }

        // Empty passwords may be disabled accounts.
        if (empty($row->password)) {
            return false;
        }

        // Verify password
        if (Password::verify($user, $pass, $row->password)) {
            // All good here
            return true;
        }

        return false;
    }

    /**
     * Authorize controller. Defaults to currently logged in user,
     * alternatively UserID may be used as second parameter.
     *
     * @param type $controller
     * @param type $userID
     *
     * @return bool
     */
    public function authorize($controller, $userID = false)
    {
        $component = new Component($controller);

        // Check if the component is found in users roles
        if ($userID) {
            $user = new User($userID);
        } else {
            $user = new User();
        }

        // Get user roles
        $userRoles = $user->roles();

        // Get component roles
        $componentRoles = $component->roles();

        // Default to disallow
        $allowed = false;

        // Check if any user roles match
        foreach ($userRoles as $k => $v) {
            if (in_array($v, $componentRoles)) {
                // Found
                $allowed = true;
                break;
            }
        }

        return $allowed;
    }

    /**
     * Return UserID by login name. Returns false on failure, so
     * this can be used for checking if user exists too.
     *
     * @param type $user
     *
     * @return int
     */
    public function getUserID($user)
    {
        // Database connection
        $db = \Webvaloa\Webvaloa::DBConnection();

        try {
            $query = '
                SELECT id
                FROM user
                WHERE login = ?';

            $stmt = $db->prepare($query);
            $stmt->set($user);
            $stmt->execute();

            $row = $stmt->fetch();

            // UserID found
            if (isset($row->id)) {
                return $row->id;
            }

            // None found, return false
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns current session id for given user.
     * For DB driver the current session_id() is returned, but
     * stuff such as REST apis or whatnot could return something
     * else.
     *
     * @param type $user
     *
     * @return mixed
     */
    public function getSessionID($user = false)
    {
        return session_id();
    }

    /**
     * Change users password.
     *
     * @param type $user
     * @param type $pass
     *
     * @return bool
     */
    public function updatePassword($user, $pass)
    {
        // Database connection
        $db = Webvaloa\Webvaloa::DBConnection();

        $hash = Password::cryptPassword($user, $pass);
        try {
            $query = '
                UPDATE user
                SET password = ?
                WHERE login = ?';

            $stmt = $db->prepare($query);
            $stmt->bind($pass)->bind($user)->execute();

            return true;
        } catch (Exception $e) {
            // Password change failed
            return false;
        }

        // Password change failed
        return false;
    }

    /**
     * Check if user is allowed to log out. More complex
     * auth drivers could do any necessary logout functions here,
     * for example unsetting certain cookies or killing remote
     * api sessions.
     *
     * @return bool
     */
    public function logout()
    {
        return true;
    }
}
