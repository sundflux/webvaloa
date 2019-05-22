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

use stdClass;
use PDOException;
use RuntimeException;
use Libvaloa\Debug\Debug;
use Webvaloa\Manifest;
use Wujunze\Colors;
use Symfony\Component\Yaml\Yaml;

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
        // Installer should be only ran from command line.

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
            // Run setup:
            case 'setup':
                $this->addPrecheckMessage('Running installation prechecks...');

                // Check if we can run setup
                if ($this->installationPreChecks($key)) {
                    $this->addPrecheckMessage('Installation prechecks OK.');
                    $this->addPrecheckMessage('Running setup for profile: ' . $key);


                    // Load available install profiles:
                    $this->loadAvailableProfiles();

                    // Run setup with given profile:
                    $this->installWithProfile($key);
                } else {
                    // Something failed in setup prechecks:
                    $this->addPrecheckError('Prechecks for installation failed.');                    
                }
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

    /**
     * Run installation prechecks
     */
    private function installationPreChecks($profileName)
    {
        if (!$this->checkDatabaseConnection()) {
            $this->addPrecheckError('Database connection not available.');                    
            return false;
        }

        if ($this->isSetupDone()) {
            $this->addPrecheckError('Setup is already ran for this database..');                    
            return false;
        }

        return true;
    }

    /**
     * Check for database connection.
     */
    private function checkDatabaseConnection()
    {
        $this->addPrecheckMessage('Checking database connection...');

        // If $this->db is not defined in controller namespace,
        // that usually means database configuration is not set.
        if (!method_exists($this->db, 'prepare')) {
            $this->addPrecheckError('Could not find active database connection!');

            return false;
        }

        return true;
    }

    /**
     * Check if setup is already done for current database.
     */
    private function isSetupDone()
    {
        $this->addPrecheckMessage('Checking for installation...');

        // Although checking for user table/user count
        // is not technically bullet-proof method to know
        // if setup has been run (core itself can run
        // without database), it's adequate enough for
        // preventing accidental setup runs for typical cms
        // installations
        try {
            $query = '
                SELECT id 
                FROM user 
                LIMIT 1';

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $id = $stmt->fetchColumn();

            if ($id) {
                $this->addPrecheckError('Cannot perform installation in existing database!');

                return true;
            }
        } catch (\Libvaloa\Db\DBException $e) {
            if (strpos($e->getMessage(), 'Base table or view not found')) {
                return false;
            }
        }
    }

    /**
     * Load available installation profiles.
     * 
     * @TODO add support for installation profiles from systempaths maybe
     */
    private function loadAvailableProfiles() 
    {
        $this->addPrecheckMessage('Loading available profiles...');

        $this->manifest = new Manifest('Installer');
        foreach (glob($this->manifest->controllerPath.'/profiles/*/manifest.yaml') as $profileFile) {
            $profile = (object) Yaml::parse(file_get_contents($profileFile));
            $profile->directory = basename(substr($profileFile, 0, - strlen('manifest.yaml')));
            $this->profiles[$profile->directory] = $profile;
        }
    }

    private function installWithProfile($profile)
    {
        // Run installer with given profile:

        if (!isset($this->profiles[$profile])) {
            throw new RuntimeException('Profile not found.');
        }

        print_r($this->profiles[$profile]);
        
    }

    private function addPrecheckMessage($message)
    {
        $msg = new stdClass;
        $msg->message = $this->textColorFormatter->getColoredString($message, 'cyan');
        $this->view->precheckmessages[] = $msg;
    }

    private function addPrecheckError($message)
    {
        $msg = new stdClass;
        $msg->message = $this->textColorFormatter->getColoredString($message, 'red');
        $this->view->precheckmessages[] = $msg;
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
