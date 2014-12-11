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

namespace ValoaApplication\Controllers\Extension;

use Libvaloa\Debug;
use Libvaloa\Controller\Redirect;

use Webvaloa\Security;
use Webvaloa\Component;
use Webvaloa\Configuration;
use Webvaloa\Manifest;
use Webvaloa\Plugin;

class InstallController extends \Webvaloa\Application
{

    public function __construct()
    {

    }

    public function index()
    {
        $component = new Component;
        $this->view->components = $component->discover();

        $plugin = new Plugin;
        $this->view->plugins = $plugin->discover();

        if (empty($this->view->components) && empty($this->view->plugins)) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('COMPONENTS_NOT_FOUND'));
        }

        Debug::__print($this->view->components);
        Debug::__print($this->view->plugins);
    }

    public function install($controller = false)
    {
        Security::verifyReferer();
        Security::verifyToken();

        if (isset($_GET['plugin'])) {
            $plugin = new Plugin($controller);
            $plugin->install();

            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('PLUGIN_INSTALLED'));
        } else {
            // Install the component
            $component = new Component($controller);
            $component->install();

            // Insert configuration variables
            $configuration = new Configuration($controller);
            $manifest = new Manifest($controller);
            $tmp = $manifest->configuration;

            if ($tmp && (is_array($tmp) || is_object($tmp))) {
                foreach ($tmp as $k => $v) {
                    foreach ($v as $configurationKey => $configurationValue) {
                        $configuration->{$configurationKey} = $configurationValue;
                        Debug::__print('Inserted ' . $configurationKey . ' with value ' . $configurationValue);
                    }
                }
            }

            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('COMPONENT_INSTALLED'));
        }

        Redirect::to('extension_install');
    }

}
