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

namespace Webvaloa;

use Webvaloa\Controller\Request;
use Exception;

/**
 * Class Security.
 */
class Security
{
    /**
     * @throws Exception
     */
    public static function verify()
    {
        self::verifyReferer();
        self::verifyToken();
    }

    /**
     * Verifies security token for the request. Token may be given via GET or POST.
     *
     * @throws Exception
     */
    public static function verifyToken()
    {
        if (!isset($_GET['token']) && !isset($_POST['token'])) {
            throw new Exception('Unable to locate security token.');
        }

        $token = self::getToken();

        if ((isset($_GET['token']) && $_GET['token'] != $token) && (isset($_POST['token']) && $_POST['token'] != $token)) {
            throw new Exception('Unable to verify security token.');
        }
    }

    /**
     * Verifies referer.
     *
     * Note that referer can be spoofed (quite easily),
     * so this should not ever be used as a standalone defense for forged
     * requests, rather be 'little extra layer' against random spambots
     * and whatnot.
     *
     * @throws Exception
     */
    public static function verifyReferer()
    {
        // Note: referer can't be trusted.

        if (!isset($_SERVER['HTTP_REFERER'])) {
            return false;
        }

        $referer = $_SERVER['HTTP_REFERER'];

        if (empty($referer)) {
            throw new Exception('Unable to verify origin.');
        }

        $request = new Request();
        $origin = $request->getBaseUri();
        $tmp = substr($origin, 0, strlen($origin));

        if ($tmp != $origin) {
            throw new Exception('Unable to verify origin.');
        }
    }

    /**
     * Get security token.
     *
     * @return type
     */
    public static function getToken()
    {
        if (!isset($_SESSION['token'])) {
            $_SESSION['token'] = sha1(session_id());
        }

        return $_SESSION['token'];
    }
}
