<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2019 Tarmo Alexander Sundström <ta@sundstrom.io>
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

namespace ValoaApplication\Controllers\Installer;

use Libvaloa\Debug\Debug;
use Wujunze\Colors;

/**
 * Class InstallerController
 * @package ValoaApplication\Controllers\Installer
 */
class InstallerController extends \Webvaloa\Application
{
    private $textColorFormatter;

    public function __construct()
    {
        if (!\Webvaloa\Webvaloa::isCommandLine()) {
            die();
        }

        $this->ui->setMainTemplate('cli');
        $this->textColorFormatter = new Colors();
        $this->printHeader();
    }

    public function index($command = false, $key = false, $value = false)
    {
        switch($command) {
            case 'setup':
                $this->installationPreChecks($key);
                break;
            default:
                $this->printHelp();
        }
    }

    public function extension($controller = false, $action = false)
    {

    }

    public function plugin($controller = false, $action = false)
    {
        
    }

    private function installationPreChecks($profileName)
    {

    }

    private function printHeader()
    {
        $this->view->title = $this->textColorFormatter->getColoredString('Webvaloa installer tool.', 'green', 'black');
    }

    private function printHelp()
    {
        $this->view->help = true;
    }

}
