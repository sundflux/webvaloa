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
namespace ValoaApplication\Controllers\Setup;

// Libvaloa classes
use Libvaloa\Db;
// Webvaloa classes
use Webvaloa;
use Webvaloa\User;
use Webvaloa\Role;
use Webvaloa\Manifest;
use Webvaloa\Component;
use Webvaloa\Controller\Redirect;
use Webvaloa\Locale\Locales;
// Standard classes
use stdClass;
use PDOException;
use RuntimeException;

class SetupController extends \Webvaloa\Application
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
        foreach(glob($this->manifest->controllerPath.'/profiles/*/profile.json') as $profileFile) {
		$profile = json_decode(file_get_contents($profileFile));
                $profile->directory = basename(substr($profileFile,0,-12));
                $this->profiles[] = $profile;
        }
    }

    public function index($locale = false)
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
        } catch (PDOException $e) {
        } catch (RuntimeException $e) {
        }

        if (!isset($_SESSION['setup'])) {
            $_SESSION['setup'] = array();
        }

        // Initial config file trickery

        if (!file_exists(WEBVALOA_BASEDIR.'/config/config.php') || file_get_contents(WEBVALOA_BASEDIR.'/config/config.php') == '') {
            // Copy stub config for setup
            if (@!file_put_contents(WEBVALOA_BASEDIR.'/config/config.php', file_get_contents(WEBVALOA_BASEDIR.'/config/config.php-stub'))) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('CONFIG_NOT_WRITABLE'));

                return;
            }
        }

        if (!is_writable(WEBVALOA_BASEDIR.'/config/config.php') && !is_writable(WEBVALOA_BASEDIR.'/config')) {
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

            // Validations
            $required = array(
                'db_server',
                'db_host',
                'db_user',
                'db_pass',
                'db_db',
                'db_profile'
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
            \Webvaloa\config::$properties['db_server'] = trim($_POST['db_server']);
            \Webvaloa\config::$properties['db_host'] = trim($_POST['db_host']);
            \Webvaloa\config::$properties['db_user'] = trim($_POST['db_user']);
            \Webvaloa\config::$properties['db_pass'] = trim($_POST['db_pass']);
            \Webvaloa\config::$properties['db_db'] = trim($_POST['db_db']);

            try {
                $query = 'SELECT 1';
                $stmt = $this->db->prepare($query);
                $stmt->execute();
            } catch (PDOException $e) {
                $this->ui->addError($e->getMessage());

                return;
            }

            Redirect::to('setup/admin');
        }

        if (isset($_SESSION['setup']['db'])) {
            $this->view = $_SESSION['setup']['db'];
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
            //Redirect::to('setup/memcached');
            Redirect::to('setup/database');
        }

        if (isset($_SESSION['setup']['admin'])) {
            foreach ($_SESSION['setup']['admin'] as $k => $v) {
                $this->view->$k = $v;
            }
        }

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
        if (!isset($_SESSION['setup'])) {
            Redirect::to('setup');
        }

        if (!isset($_SESSION['setup'])) {
            Redirect::to('setup');
        }

        $setup = $_SESSION['setup'];

        // Write the configuration file
        $configFile = WEBVALOA_BASEDIR.'/config/config.php';
        if (!is_writable($configFile)) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('CONFIG_NOT_WRITABLE'));

            return;
        }

        $locale = getenv('LANG');
        // Remove encoding from locale string
        preg_match("/([^\.]+)[^\.]/", $locale, $locale);
        $locale = $locale[0];
        $configData = array(
                'default_controller' => 'login',
                'default_controller_authed' => 'login_logout',
                'webvaloa_auth' => 'Webvaloa\Auth\Db'
        );
        $currentProfile = $this->getProfileByName($setup['db']['db_profile']);

        if(is_object($currentProfile->config)) {
            foreach($currentProfile->config as $configKey => $configValue) {
                $configData[$configKey]=$configValue;
            }
        }

        $config = "<?php\n";
        $config .= "namespace Webvaloa;\n\n";
        $config .= "class config\n";
        $config .= "{\n";
        $config .= "\tpublic static ".'$properties'."= array(\n";
        $config .= "\t\t'db_server'\t\t\t\t\t\t=> '".$setup['db']['db_server']."',\n";
        $config .= "\t\t'db_host'\t\t\t\t\t\t=> '".$setup['db']['db_host']."',\n";
        $config .= "\t\t'db_user'\t\t\t\t\t\t=> '".$setup['db']['db_user']."',\n";
        $config .= "\t\t'db_pass'\t\t\t\t\t\t=> '".$setup['db']['db_pass']."',\n";
        $config .= "\t\t'db_db'\t\t\t\t\t\t\t=> '".$setup['db']['db_db']."',\n\n";

        foreach($configData as $configKey => $configValue) {
            $config .= "\t\t'{$configKey}'\t=> '{$configValue}',\n";
	}

//        $config .= "        'default_controller'        => 'login',\n";
//        $config .= "        'default_controller_authed' => 'login_logout',\n";
//        $config .= "        'webvaloa_auth'             => 'Webvaloa\Auth\Db',\n";

        $config .= "\n\t\t'time_zone'\t\t\t\t\t\t=> '".$setup['admin']['tz']."'\n";
        $config .= "\t);\n";
        $config .= "\n";
        $config .= '}';
        $config .= "\n\n";
        $config .= "putenv('LANG={$locale}');\n";
        $config .= "setlocale(LC_ALL, '{$locale}.UTF-8');\n";
        $config .= "setlocale(LC_MESSAGES, '{$locale}.UTF-8');\n";

        file_put_contents($configFile, $config);

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

        \Webvaloa\config::$properties['db_server'] = $setup['db']['db_server'];
        \Webvaloa\config::$properties['db_host'] = $setup['db']['db_host'];
        \Webvaloa\config::$properties['db_user'] = $setup['db']['db_user'];
        \Webvaloa\config::$properties['db_pass'] = $setup['db']['db_pass'];
        \Webvaloa\config::$properties['db_db'] = $setup['db']['db_db'];

        // Install database
        $sqlSchema = $this->manifest->controllerPath.'/schema-'.$this->manifest->version.'_'.$setup['db']['db_server'].'.sql';

        // Profile schema
        $profile = $this->getProfileByName($setup['db']['db_profile']);
        if(isset($profile->directory) && !empty($profile->directory)) {
            $profileSql = $this->manifest->controllerPath.'/profiles/'.$profile->directory.'/db.sql';
        }

        try {
            $this->db->beginTransaction();

            if (!file_exists($sqlSchema)) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('SQL_SCHEMA_NOT_FOUND'));

                return;
            }

            $query = file_get_contents($sqlSchema);

            // Inject additional schema from profile
            if(file_exists($profileSql)) {
                $query .= "\n".file_get_contents($profileSql);
            }

            $this->db->exec($query);

            // Create user

            $user = new User();
            $user->email = $setup['admin']['admin_email'];

            if (isset($setup['admin']['admin_username']) && !empty($setup['admin']['admin_username'])) {
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

            // System plugins

            $object = new Db\Object('plugin', $this->db);
            $object->plugin = 'PluginAdministrator';
            $object->system_plugin = 1;
            $object->blocked = 0;
            $object->ordering = 1;
            $object->save();

            $object = new Db\Object('plugin', $this->db);
            $object->plugin = 'ContentField';
            $object->system_plugin = 1;
            $object->blocked = 0;
            $object->ordering = 10;
            $object->save();

            $object = new Db\Object('plugin', $this->db);
            $object->plugin = 'ContentMediapicker';
            $object->system_plugin = 1;
            $object->blocked = 0;
            $object->ordering = 10;
            $object->save();

            $object = new Db\Object('plugin', $this->db);
            $object->plugin = 'PluginTemplate';
            $object->system_plugin = 1;
            $object->blocked = 0;
            $object->ordering = 10;
            $object->save();

            $object = new Db\Object('plugin', $this->db);
            $object->plugin = 'PluginNavigationView';
            $object->system_plugin = 1;
            $object->blocked = 0;
            $object->ordering = 10;
            $object->save();

            // System components
            $component = new Component('Content');
            $component->install();
            $component->addRole($role->getRoleID('Administrator'));

            $component = new Component('Extension');
            $component->install();
            $component->addRole($role->getRoleID('Administrator'));

            $component = new Component('Password');
            $component->install();
            $component->addRole($role->getRoleID('Administrator'));

            $component = new Component('Profile');
            $component->install();
            $component->addRole($role->getRoleID('Administrator'));

            $component = new Component('Settings');
            $component->install();
            $component->addRole($role->getRoleID('Administrator'));

            $component = new Component('User');
            $component->install();
            $component->addRole($role->getRoleID('Administrator'));

            // Set all components as system components
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

    private function getProfileByName($name)
    {
	$find = false;
        array_walk($this->profiles, function($profile) use($name, &$find) {
            if($profile->name == $name) {
		return $find = $profile;
            }
        });
	return $find;
    }
}
