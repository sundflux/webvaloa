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

use Libvaloa\Debug;
use Webvaloa\Locale\Locales;
use Webvaloa\Configuration;

/**
 * Plugin to switch language on-fly.
 */
class PluginLanguageSwitcherPlugin extends \Webvaloa\Plugin
{
    /**
     * Change the language before running the controller.
     */
    public function onBeforeController()
    {
        if (isset($_GET['locale'])) {
            $locales = new Locales();
            $l = $locales->locales();

            if (in_array($_GET['locale'], $l)) {
                Debug::__print('Locale changed to');
                $_SESSION['locale'] = $_GET['locale'];
                Debug::__print($_SESSION['locale']);
            }
        }

        // Locale already set
        if (isset($_SESSION['locale'])) {
            return;
        }

        // Load default locale to session
        $config = new Configuration();
        $default = $config->locale;

        if (!$default || empty($default)) {
            // Default to english
            $default = 'en_US';
        }

        $_SESSION['locale'] = $default;
    }
}
