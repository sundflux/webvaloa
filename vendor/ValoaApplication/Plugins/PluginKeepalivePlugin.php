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
namespace ValoaApplication\Plugins;

use Webvaloa\Controller\Request;
use Libvaloa\Debug;

class PluginKeepalivePlugin extends \Webvaloa\Plugin
{
    public function onAfterFrontControllerInit()
    {
        \Webvaloa\config::$properties['sessionMaxlifetime'] = 43200; // 12 hours
    }

    public function onBeforeRender()
    {
        if (session_status() === PHP_SESSION_NONE) {
            return;
        }

        Debug::__print('Keepalive enabled');

        $_SESSION['keepalive'] = time();
        $request = Request::getInstance();

        $dom = $this->ui->getPreprocessedTemplateDom();
        $body = $dom->getElementsByTagName('body')->item(0);
        if (!$body) {
            return;
        }

        $injectTag = $dom->createElement('script');
        $injectTag->setAttribute('type', 'text/javascript');

        $url = $request->getPath().'/login_keepalive';
        $injectTag->nodeValue = 'function keepalive() { http_request = new XMLHttpRequest(); http_request.open(\'GET\', \''.$url.'\'); http_request.send(null); }; setInterval(keepalive, 300000);';

        // And inject it to script tags
        if ($scripts = $body->getElementsByTagName('script')->item(0)) {
            $body->insertBefore($injectTag, $scripts);
        }
    }
}
