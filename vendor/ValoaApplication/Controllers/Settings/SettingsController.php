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

namespace ValoaApplication\Controllers\Settings;

use Libvaloa\Debug;
use Libvaloa\Controller\Redirect;

use Webvaloa\Configuration;
use Webvaloa\Security;

class SettingsController extends \Webvaloa\Application
{
    private $settings;

    public function __construct()
    {
    }

    public function index($component = false)
    {
        $this->view->token = Security::getToken();

        $configuration = new Configuration($component);

        if ($component) {
            $this->view->component = $component;
        }

        $tmp = $configuration->configuration();
        if (!$tmp) {
            return;
        }

        foreach ($tmp as $k => $v) {
            // Skip controller options
            if (!$component && $v->component_id > 0) {
                continue;
            }

            $tmp = $v;
            $tmp->key_translated = \Webvaloa\Webvaloa::translate($tmp->key);

            // Translate values
            if (!empty($tmp->values)) {
                if (isset($translatedValues)) {
                    unset($translatedValues);
                }

                foreach ($tmp->values as $_tmp => $value) {
                    $value->translation = \Webvaloa\Webvaloa::translate($value->translation);
                    $translatedValues[] = $value;
                }

                if (isset($translatedValues)) {
                    $tmp->values = $translatedValues;
                }
            }

            $this->view->settings[] = $tmp;
        }
    }

    public function save()
    {
        Security::verify();

        $configuration = new Configuration($_POST["component"]);

        foreach ($_POST as $k => $v) {
            if (empty($k) || $k == "component") {
                continue;
            }

            try {
                $configuration->$k = $v;
            } catch (Exception $e) {
                Debug::__print($e->getMessage());
            }
        }
        $this->ui->addMessage(\Webvaloa\Webvaloa::translate('SETTINGS_SAVED'));

        Redirect::to('settings/' . $_POST["component"]);
    }

}
