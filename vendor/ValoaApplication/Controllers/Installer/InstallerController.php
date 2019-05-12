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
    private $profiles;
    private $manifest;

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
        $this->isSetupDone();
    }

    private function isSetupDone()
    {
        // Check is setup is completed already
        try {
            if (!method_exists($this->db, 'prepare')) {
                // Just bail out
                throw new RuntimeException();
            }

            $query = 'SELECT id FROM user LIMIT 1';
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $id = $stmt->fetchColumn();

            if ($id) {
                die('Cannot perform setup against existing database.');
            }
        } catch (PDOException $e) {
        } catch (RuntimeException $e) {
        }
    }

    private function asd() 
    {
        $this->manifest = new Manifest('Installer');
        foreach (glob($this->manifest->controllerPath.'/profiles/*/manifest.yaml') as $profileFile) {
            $profile = (object) Yaml::parse(file_get_contents($profileFile));
            $profile->directory = basename(substr($profileFile, 0, - strlen('manifest.yaml')));
            $this->profiles[] = $profile;
        }
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
