<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2004,2018 Tarmo Alexander Sundström <ta@sundstrom.im>
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

/**
 * Authentication library.
 *
 * Handles user authentication and validation.
 */

namespace Webvaloa\Auth;

use Webvaloa\Controller\Request;

/**
 * Interface AuthIFace
 *
 * @package Webvaloa\Auth
 */
interface AuthIFace
{
    public function authenticate($username, $password);
    public function authorize($controller, $username);
    public function getUserID($username);
    public function getSessionID($username);
    public function logout();
}

/**
 * Interface PWResetIFace
 *
 * @package Webvaloa\Auth
 */
interface PWResetIFace
{
    public function updatePassword($username, $password);
}

/**
 * Class Auth
 *
 * @package Webvaloa\Auth
 */
class Auth
{
    /**
     * Settings.
     *
     * - IP changing during session not allowed if authCheckIP is set to 1 .
     * - Cache servers such as varnish use HTTP_X_FORWARDED_FOR for real client
     *   IP instead of the usual REMOTE_ADDR (which returns cache server IP
     *   instead). If set to 1, this is checked first.
     *
     * @var array
     */
    public $properties = array(
        'authCheckIP' => 0,
        'authCheckForwardFor' => 1,
    );

    /**
     * @var
     */
    private $backend;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Returns currently active authentication backend.
     *
     * @return string
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Returns client IP.
     *
     * @return mixed
     */
    public static function getClientIP()
    {
        $auth = new self();
        $properties = $auth->properties;

        // Support for cache servers such as Varnish.
        if ($properties['authCheckForwardFor'] == 1
            && (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            && !empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        ) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Override default authentication driver.
     *
     * @param string $driver Authentication driver
     */
    public function setAuthenticationDriver(AuthIFace $driver)
    {
        $this->backend = $driver;
    }

    /**
     * Authentication. Takes username+password as parameter.
     *
     * Loads authentication driver as defined in config and calls drivers
     * authentication() method..
     *
     * @param string $user Username
     * @param string $pass Password
     *
     * @return bool Boolean wether or not authentication was valid
     */
    public function authenticate($username, $password)
    {
        $username = trim($username);
        $password = trim($password);

        $auth = new $this->backend();
        $request = Request::getInstance();

        if ($auth->authenticate($username, $password)) {
            $_SESSION['User'] = $username;
            $_SESSION['UserID'] = $auth->getUserID($username);
            $_SESSION['ExternalSessionID'] = $auth->getSessionID($username);
            $_SESSION['ClientIP'] = self::getClientIP();
            $_SESSION['BaseUri'] = $request->getBaseUri(true);

            return true;
        }

        return false;
    }

    /**
     * Updates user password using available authentication driver.
     *
     * @param string $username Username
     * @param string $password Password
     *
     * @return bool Return value from auth drivers updatePassword method
     */
    public function updatePassword($username, $password)
    {
        $username = trim($username);
        $password = trim($password);

        $auth = new $this->backend();

        if ($auth instanceof PWResetIFace) {
            return $auth->updatePassword($username, $password);
        }

        return false;
    }

    /**
     * Checks if user has permissions to access a certain module
     * (groupfeature or userfeature).
     *
     * @param string $module Controller name
     *
     * @return bool True (access granted) or false (access denied)
     */
    public function authorize($controller, $userID = false)
    {
        $auth = new $this->backend();

        // trying to get from other installation on the same server
        $baseUri = Request::getInstance()->getBaseUri(true);

        if (!$controller
            || isset($_SESSION['BaseUri'])
            && $_SESSION['BaseUri'] != $baseUri
        ) {
            return false;
        }

        if ($this->properties['authCheckIP'] == 1) {
            if (!isset($_SESSION['ClientIP']) || self::getClientIP() != $_SESSION['ClientIP']) {
                return false;
            }
        }

        if (!isset($userID)) {
            return false;
        }

        return $auth->authorize($controller, $userID);
    }

    /**
     * Destroys session & redirects to default module.
     */
    public function logout()
    {
        $auth = new $this->backend();
        $pageuri = Request::getInstance();

        $auth->logout();
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 43200, '/');
        }
        session_destroy();

        return true;
    }
}
