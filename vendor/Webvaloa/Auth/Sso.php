<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.io>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.io>
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

use Webvaloa\Auth;
use Webvaloa;
use Webvaloa\User;

/**
 * Class Sso.
 *
 * Authentication driver, implementing libvaloa's authentication
 * for single-sign-on plugins. Authenticate immediatly logs in the
 * user, so external sso plugins should handle the actual
 * authentication process, and use this class only to initialize
 * webvaloa session.
 */
class Sso implements AuthIFace, PWResetIFace
{
    /**
     * Sso constructor.
     */
    public function __construct()
    {
        $this->auth = new Db();
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
        // Authentication should be handled in the SSO plugin
        return true;
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
        return $this->auth->authorize($controller, $userID);
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
        return $this->auth->getUserID($user);
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
