<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.io>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014, 2019 Tarmo Alexander Sundström <ta@sundstrom.io>
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

namespace ValoaApplication\Controllers\Setup;

use Symfony\Component\Yaml\Yaml;

use Libvaloa\Db;
use Libvaloa\Debug\Debug;
use Webvaloa\Application;
use Webvaloa\Configuration;
use Webvaloa\User;
use Webvaloa\Role;
use Webvaloa\Manifest;
use Webvaloa\Component;
use Webvaloa\Controller\Redirect;
use Webvaloa\Locale\Locales;
use stdClass;
use PDOException;
use RuntimeException;

class SetupController extends Application
{
    private $backend;
    private $locale;
    private $profiles;
    private $manifest;
    public $view;

    public function __construct()
    {
        $locales = new Locales();
        $this->locales = $locales->locales();

        $this->ui->addJS('/js/Setup.js');
        $this->ui->addCSS('/css/Setup.css');
        $this->backend = '\Webvaloa\Auth\Db';

        // Specific to setup
        $this->view = new stdClass();

        $this->manifest = new Manifest('Setup');
        foreach (glob($this->manifest->controllerPath.'/profiles/*/manifest.yaml') as $profileFile) {
            Debug::__print('Loading profile '.$profileFile);
            $profile = (object) Yaml::parse(file_get_contents($profileFile));
            $profile->directory = basename(substr($profileFile, 0, - strlen('manifest.yaml')));
            $this->profiles[$profile->name] = $profile;
        }
    }

    public function index($locale = 'en_US')
    {
        // Change locale
        if ($locale && in_array($locale, $this->locales)) {
            $_SESSION['locale'] = $locale;
            $_SESSION['setup']['locale'] = $locale;

            Redirect::to('setup');
        }

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
                Redirect::to();
            }
        } catch (\Libvaloa\Db\DBException $e) {
        } catch (RuntimeException $e) {
        }

        if (!isset($_SESSION['setup'])) {
            $_SESSION['setup'] = array();
        }

        // Generate salt for this installation
        if (!isset($_SESSION['setup']['salt'])) {
            $_SESSION['setup']['salt'] = bin2hex(random_bytes(16));
        }

        // Initial config file trickery

        if (!file_exists(WEBVALOA_BASEDIR.'/config/.env') || file_get_contents(WEBVALOA_BASEDIR.'/config/.env') == '') {
            // Copy stub config for setup
            if (@!file_put_contents(WEBVALOA_BASEDIR.'/config/.env', file_get_contents(WEBVALOA_BASEDIR.'/config/.env.dist'))) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('CONFIG_NOT_WRITABLE'));

                return;
            }
        }

        if (!is_writable(WEBVALOA_BASEDIR.'/config/.env') && !is_writable(WEBVALOA_BASEDIR.'/config')) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('CONFIG_NOT_WRITABLE'));

            return;
        }

        if (isset($_POST['continue'])) {
            Redirect::to('setup/database');
        }
    }

    public function database()
    {
        if (!isset($_SESSION['setup'])) {
            Redirect::to('setup');
        }

        if (isset($_POST['back'])) {
            Redirect::to('setup');
        }

        if (isset($_POST['continue'])) {
            $_SESSION['setup']['db'] = $_POST;
            $this->view = (object) $_SESSION['setup']['db'];
            $this->view->profiles = $this->profiles;

            Debug::__print($this->view->profiles);

            // Validations
            $required = array(
                'db',
                'db_server',
                'db_host',
                'db_user',
                'db_pass',
                'db_profile',
            );

            foreach ($required as $k => $v) {
                if (!isset($_POST[$v]) || empty($_POST[$v])) {
                    $errors[] = $v;
                }
            }

            if (isset($errors)) {
                $this->view->errors = $errors;
                $this->ui->addError(\Webvaloa\Webvaloa::translate('DB_SETTINGS_MISSING'));

                return;
            }

            // All good, test connection
            putenv('DB="'.trim($_POST['db']).'"');
            putenv('DB_SERVER="'.trim($_POST['db_server']).'"');
            putenv('DB_HOST="'.trim($_POST['db_host']).'"');
            putenv('DB_USER="'.trim($_POST['db_user']).'"');
            putenv('DB_PASS="'.trim($_POST['db_pass']).'"');

            try {
                $config = new Configuration();

                $db = new \Libvaloa\Db\Db(
                    $config->db_host,
                    $config->db_user,
                    $config->db_pass,
                    $config->db,
                    $config->db_server);

                $query = 'SELECT 1';
                $stmt = $db->prepare($query);
                $stmt->execute();
            } catch (PDOException $e) {
                $this->ui->addError($e->getMessage());

                return;
            }

            Redirect::to('setup/admin');
        }

        if (isset($_SESSION['setup']['db'])) {
            $this->view = (object) $_SESSION['setup']['db'];
            $this->view->profiles = $this->profiles;
        }

        if (!isset($this->view->db_host) || empty($this->view->db_host)) {
            $this->view = new stdClass();
            $this->view->profiles = $this->profiles;
            $this->view->db_host = 'localhost';
        }

        $this->view->profiles = $this->profiles;
    }

    public function admin()
    {
        if (!isset($_SESSION['setup'])) {
            Redirect::to('setup');
        }

        if (isset($_POST['back'])) {
            Redirect::to('setup/database');
        }

        if (isset($_SESSION['setup']['admin'])) {
            foreach ($_SESSION['setup']['admin'] as $k => $v) {
                $this->view->$k = $v;
            }
        }

        Debug::__print($_SESSION['setup']);

        $this->view->timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $this->view->timezone = date_default_timezone_get();

        if (!isset($_POST['continue'])) {
            return;
        }

        foreach ($_POST as $k => $v) {
            $this->view->$k = $v;
        }

        $_SESSION['setup']['admin'] = $_POST;

        $check = array(
            'admin_password',
            'admin_password2',
        );

        foreach ($check as $k => $v) {
            if (!isset($_POST[$v]) || empty($_POST[$v]) || strlen($_POST[$v]) < 8) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('PASSWORD_TOO_SHORT'));

                return;
            }
        }

        if ($_POST['admin_password'] != $_POST['admin_password2']) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('CHECK_PASSWORD'));

            return;
        }

        if (empty($_POST['admin_firstname'])) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('FIRSTNAME_REQUIRED'));

            return;
        }

        if (empty($_POST['admin_lastname'])) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('LASTNAME_REQUIRED'));

            return;
        }

        if (isset($_POST['continue'])) {
            Redirect::to('setup/install');
        }
    }

    public function install()
    {
        $configData = [];
        $configDataQuoted = [];
        $configDataString = "";

        if (!isset($_SESSION['setup'])) {
            Redirect::to('setup');
        }

        if (!isset($_SESSION['setup'])) {
            Redirect::to('setup');
        }

        $setup = $_SESSION['setup'];

        // Write the configuration file
        $configFile = WEBVALOA_BASEDIR.'/config/.env';
        if (!is_writable($configFile)) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('CONFIG_NOT_WRITABLE'));

            return;
        }

        $currentProfile = $this->getProfileByName($setup['db']['db_profile']);

        if (isset($currentProfile->config) && is_array($currentProfile->config)) {
            foreach ($currentProfile->config as $configKey => $configValue) {
                $configData[strtoupper($configKey)] = $configValue;
            }
        }

        $configData['DB'] = $setup['db']['db'];
        $configData['DB_SERVER'] = $setup['db']['db_server'];
        $configData['DB_HOST'] = $setup['db']['db_host'];
        $configData['DB_SERVER'] = $setup['db']['db_server'];
        $configData['DB_USER'] = $setup['db']['db_user'];
        $configData['DB_PASS'] = $setup['db']['db_pass'];
        $configData['SALT'] = $setup['salt'];

        if (isset($_SESSION['setup']['locale'])) {
            $configData['LANG'] = $_SESSION['setup']['locale'];
        }

        foreach ($configData as $k => $v) {
            $configDataQuoted[$k] = '"'.addslashes($v).'"'."\n";
        }

        foreach ($configDataQuoted as $k => $v) {
            $configDataString .= "{$k}={$v}";
        }

        file_put_contents($configFile, $configDataString);

        $_SESSION['config_created'] = true;

        Redirect::to('setup/installdb');
    }

    public function installdb()
    {
        if (!isset($_SESSION['setup'])) {
            Redirect::to('setup');
        }

        if (!isset($_SESSION['config_created'])) {
            Redirect::to('setup');
        }

        $setup = $_SESSION['setup'];

        // Profile schema
        $profile = $this->getProfileByName($setup['db']['db_profile']);

        try {
            $this->db->beginTransaction();

            if (!$profile) {
                throw new RuntimeException('Profile not found.');
            }

            if (empty($profile->components)) {
                throw new \RuntimeException('Could not find any components to install.');
            }

            // Create models from profile:
            foreach ($profile->components as $component) {
                $installer = new Component($component);
                $installer->installModels();
            }

            // Install components:
            foreach ($profile->components as $component) {
                $installer = new Component($component);
                $installer->install();
                $installer->installConfiguration();
            }

            // Install all system plugins from profile:
            if ($profile->system_plugins) {
                foreach ($profile->system_plugins as $plugin) {
                    $object = new Db\Item('plugin', $this->db);
                    $object->plugin = $plugin;
                    $object->system_plugin = 1;
                    $object->blocked = 0;
                    $object->ordering = 10;
                    $object->save();
                }
            }

            // Install all plugins from profile:
            if (!$profile->plugins) {
                foreach ($profile->plugins as $plugin) {
                    $object = new Db\Item('plugin', $this->db);
                    $object->plugin = $plugin;
                    $object->system_plugin = 0;
                    $object->blocked = 0;
                    $object->ordering = 20;
                    $object->save();
                }
            }

            // Create roles
            $role = new Role();
            $role->addSystemRole('Administrator');
            $role->addSystemRole('Logged in');
            $role->addSystemRole('Public');

            // Create user
            $user = new User();
            $user->email = $setup['admin']['admin_email'];

            if (!empty($setup['admin']['admin_username'])) {
                $user->login = $setup['admin']['admin_username'];
            } else {
                $user->login = $setup['admin']['admin_email'];
            }

            if (isset($setup['locale']) && !empty($setup['locale'])) {
                $user->locale = $setup['locale'];
            } else {
                $user->locale = 'en_US';
            }

            $user->firstname = $setup['admin']['admin_firstname'];
            $user->lastname = $setup['admin']['admin_lastname'];
            $user->password = $setup['admin']['admin_password'];
            $user->blocked = 0;
            $userID = $user->save();

            // Add administrator role for the user
            $user = new User($userID);

            $role = new Role();
            $user->addRole($role->getRoleID('Administrator'));

            // Set all components in setup profile as system components
            $query = '
                UPDATE component
                SET system_component = 1';

            $this->db->exec($query);
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();

            throw new \RuntimeException($e->getMessage());
        }

        $_SESSION['setup_ready'] = true;
        Redirect::to('setup/ready');
    }

    public function ready()
    {
        if (!isset($_SESSION['setup_ready'])) {
            Redirect::to('setup');
        }

        if (!isset($_SESSION['setup'])) {
            Redirect::to('setup');
        }

        unset($_SESSION['setup_ready']);
        unset($_SESSION['config_created']);
        unset($_SESSION['setup']);
    }

    /**
     * @param $name
     * @return bool
     */
    private function getProfileByName($name)
    {
        $find = false;
        array_walk(
            $this->profiles, function ($profile) use ($name, &$find) {
                if ($profile->name == $name) {
                    return $find = $profile;
                }
            }
        );

        return $find;
    }
}
