<?php
/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>
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

namespace ValoaApplication\Plugins;

use Libvaloa\Debug;
use Libvaloa\Controller\Request;
use Webvaloa\Cache;
use Webvaloa\Manifest;

/*
 * Full pagecache plugin
 */
class PluginPagecachePlugin extends \Webvaloa\Plugin
{

    public function onAfterFrontControllerInit()
    {
        $request = Request::getInstance();
        $controller = $request->getMainController();

        // System controllers can't be cached. Also check if the
        // controller manifest has preventcaching: 1
        $manifest = new Manifest($controller);
        if ( ($manifest->systemcontroller && $manifest->systemcontroller == 1)
            || ($manifest->preventcaching && $manifest->preventcaching == 1) ) {
            return;
        }

        // Use the url as cache key
        $uri = $request->getBaseUri() . $_SERVER['REQUEST_URI'];

        // Most optimal here is to use memcached as backend.
        $cache = new Cache;
        if (!$response = $cache->get($uri) ) {
            // No cached version found

            Debug::__print('No cache found');

            return;
        }

        Debug::__print('Cache found, exiting');
        Debug::__print($uri);

        header("Content-type: text/html; charset=utf-8");
        header("Vary: Accept");
        echo $response;
        exit;
    }

    public function onAfterRender()
    {
        if (isset($_SESSION['UserID'])) {
            // Only cache pages when NOT logged in.
            return;
        }

        // Write the cache after page rendering is done.

        Debug::__print('Writing cache');
        $request = Request::getInstance();

        // Use the url as cache key
        $uri = $request->getBaseUri() . $_SERVER['REQUEST_URI'];
        $cache = new Cache;
        $cache->set($uri, $this->xhtml);
    }

}
