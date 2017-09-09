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

// Webvaloa classes
use Webvaloa\Component;
// Standard classes
use DOMDocument;

/**
 * Plugin to show top administrator bar.
 */
class LoginCreateAccountPlugin extends \Webvaloa\Plugin
{
    public function __construct()
    {
    }

    public function onAfterRender()
    {
        $component = new Component('Register');

        if (!isset($_SESSION['UserID']) && $component->blocked == 0) {
            $dom = new DOMDocument();
            $dom->loadHTML($this->xhtml);

            // A link
            $injectTag = $dom->createElement('a', \Webvaloa\Webvaloa::translate('CREATE_ACCOUNT'));
            $injectTag->setAttribute('id', 'register');
            $injectTag->setAttribute('href', $this->request->getBaseUri().'/register');
            $injectTag->setAttribute('class', 'text-center new-account');

            // Insert link to login form
            $form = $dom->getElementById('form-signin');

            if ($form) {
                $form->appendChild($injectTag);

                // Insert <br> before the button
                $br = $dom->createElement('br');
                $form->insertBefore($br, $dom->getElementById('register'));

                // Modified xhtml
                $this->xhtml = $dom->saveHTML();
            }
        }
    }
}
