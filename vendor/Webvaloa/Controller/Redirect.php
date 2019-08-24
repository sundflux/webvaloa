<?php

/*
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.io>
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2011 Tarmo Alexander Sundström <ta@sundstrom.io>
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
 * Redirecter.
 *
 * @uses Controller_Request
 */

namespace Webvaloa\Controller;

class Redirect
{
    /**
     * Redirect client to the given controller or url.
     * Prepends full base url to the header redirection unless
     * second parameter $omitBase is given.
     *
     * @param type $url
     * @param type $omitBase
     */
    public static function to($url = '', $omitBase = false)
    {
        $request = Request::getInstance();
        $prepend = '';

        // Default to current url if no url was given
        if (empty($url)) {
            $url = $_SERVER['REQUEST_URI'];
        }

        $pos = strpos($url, '://');
        if (!$omitBase) {
            // Prepend the url only if no protocol defined in the url
            if ($pos == false) {
                $prepend = $request->getBaseUri().'/';
            }
        }

        header('location: '.$prepend.$url);
        exit;
    }

    /**
     * Redirect client to the current url. This is just an alias for to().
     */
    public function reload()
    {
        self::to();
    }
}
