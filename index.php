<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2004 - 2014 Tarmo Alexander Sundström <ta@sundstrom.im>
 *
 * All Rights Reserved.
 *
 * Contributor(s):
 * 2009, 2010 Joni Halme <jontsa@amigaone.cc>
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

namespace Webvaloa;

use Libvaloa\Debug;
use Libvaloa\Db;
use Libvaloa\I18n;
use Webvaloa\Locale\Locales;
use Webvaloa\Controller\Request;
use stdClass;
use PDOException;
use Exception;

// Base path on the server
define('WEBVALOA_BASEDIR', realpath(dirname(__FILE__)));

// Include separate config-file
if (is_readable(WEBVALOA_BASEDIR.'/config/config.php')) {
    require_once WEBVALOA_BASEDIR.'/config/config.php';
}

// Core paths
if (!defined('LIBVALOA_INSTALLPATH')) {
    define('LIBVALOA_INSTALLPATH', WEBVALOA_BASEDIR.DIRECTORY_SEPARATOR.'vendor');
}

// Extensions
if (!defined('LIBVALOA_EXTENSIONSPATH')) {
    define('LIBVALOA_EXTENSIONSPATH', WEBVALOA_BASEDIR.DIRECTORY_SEPARATOR.'vendor');
}

// Public media
if (!defined('LIBVALOA_PUBLICPATH')) {
    define('LIBVALOA_PUBLICPATH', WEBVALOA_BASEDIR.DIRECTORY_SEPARATOR.'public');
}

// Include paths
set_include_path(LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.PATH_SEPARATOR.get_include_path());
set_include_path(LIBVALOA_INSTALLPATH.DIRECTORY_SEPARATOR.PATH_SEPARATOR.get_include_path());

/**
 * Webvaloa kernel class.
 *
 * Handles session starting, setting excpetion handlers, locales, database connection.
 *
 * @uses \Webvaloa\config
 * @uses \Webvaloa\Cache
 * @uses \Libvaloa\Db\Db
 * @uses \Webvaloa\Controller\Request
 */
class Webvaloa
{
    /**
     * Database connection.
     */
    public static $db = false;

    /**
     * Static var to track if Webvaloa kernel has been loaded.
     */
    public static $loaded = false;

    /**
     * Current locale.
     */
    public static $locale = false;

    /**
     * Session.
     */
    public static $session = false;

    /**
     * Properties array.
     *
     * startSession         - defines if the kernel should start session.
     * sessionMaxlifetime   - sets the session length with ini_set. Defaults to 1 hour.
     * ui                   - defines the user interface driver. By default webvaloa uses XSL ui driver.
     * layout               - template name for ui
     */
    public static $properties = array(
        'startSession'        => 1,
        'sessionMaxlifetime'  => 3600,
        'ui'                  => 'Libvaloa\Ui\Xml',
        'vendor'              => 'ValoaApplication',
        'layout'              => 'default',
    );

    /**
     * Sets up libvaloa environment and registers base classes/functions.
     */
    public function __construct()
    {
        // Register class autoloader.
        spl_autoload_register(array('Webvaloa\Webvaloa', 'autoload'));

        // Uncaught exception handler.
        set_exception_handler(array('Webvaloa\Webvaloa', 'exceptionHandler'));

        self::$loaded = true;
    }

    public static function initializeSession()
    {
        // Start the session

        if (Webvaloa::$properties['startSession'] > 0 && !self::$session) {
            // Set session lifetime from config, if available

            if (class_exists('\\Webvaloa\\config') && isset(\Webvaloa\config::$properties['sessionMaxlifetime']) && !empty(\Webvaloa\config::$properties['sessionMaxlifetime'])) {
                $sessionMaxlifetime = (string) \Webvaloa\config::$properties['sessionMaxlifetime'];
            } else {
                $sessionMaxlifetime = (string) Webvaloa::$properties['sessionMaxlifetime'];
            }

            if (function_exists('ini_set')) {
                ini_set('session.gc_maxlifetime', $sessionMaxlifetime);
            }

            session_set_cookie_params($sessionMaxlifetime);
            session_start();

            Debug::__print('Using '.ini_get('session.save_handler').' session handler');

            self::$session = true;
        }
    }

    /**
     * Class autoloader.
     *
     * @access public
     *
     * @param string $name Class name
     */
    public static function autoload($name)
    {
        // Autoloading standard:
        // http://www.php-fig.org/psr/psr-0/
        $className = ltrim($name, '\\');
        $fileName  = '';
        $namespace = '';

        if ($lastNsPos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
        }

        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';

        // Look first from the extensionspath, then fallback to core installpath
        $search[] = LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.$fileName;
        $search[] = LIBVALOA_INSTALLPATH.DIRECTORY_SEPARATOR.$fileName;

        // Include classes if found
        foreach ($search as $v) {
            if (!is_readable($v)) {
                continue;
            }
            require_once $v;

            return;
        }
    }

    /**
     * Opens database connection.
     *
     * @access      static
     *
     * @return DB database connection
     *
     * @uses        DB
     */
    public static function DBConnection()
    {
        if (!self::$db instanceof \Libvaloa\Db\Db) {
            try {
                // Don't try to load database connection if config doesn't exist.
                if (!class_exists('\\Webvaloa\\config')) {
                    return false;
                }

                // Make sure we use UTF-8
                if (\Webvaloa\config::$properties['db_server'] != 'sqlite') {
                    $initquery = "SET NAMES 'UTF8'";
                } else {
                    $initquery = '';
                }

                if (!isset(\Webvaloa\config::$properties['db_db'])) {
                    return self::$db = false;
                }

                // Initialize the db connection
                self::$db = new \Libvaloa\Db\Db(
                    \Webvaloa\config::$properties['db_host'],
                    \Webvaloa\config::$properties['db_user'],
                    \Webvaloa\config::$properties['db_pass'],
                    \Webvaloa\config::$properties['db_db'],
                    \Webvaloa\config::$properties['db_server'],
                    false,
                    $initquery);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage());
            }
        }

        return self::$db;
    }

    /**
     * Catches uncaught exceptions and displays error message.
     */
    public static function exceptionHandler($e)
    {
        print '<h3>An error occured which could not be fixed.</h3>';
        printf('<p>%s</p>', $e->getMessage());
        if ($e->getCode()) {
            print ' ('.$e->getCode().')';
        }
        if (error_reporting() == E_ALL) {
            printf('<p><b>Location:</b> %s line %s.</p>', $e->getFile(), $e->getLine());
            print '<h4>Exception backtrace:</h4>';
            print '<pre>';
            print_r($e->getTrace());
            print '</pre>';
        }
    }

    /**
     * Returns current locale.
     *
     * @return string self::$locale
     */
    public static function getLocale()
    {
        if (!self::$locale) {
            // Get current system locale
            $systemLocale = getenv('LANG');

            // Get available locales
            $locales = new Locales();
            $available = $locales->locales();

            // Set the locale
            if (isset($_SESSION['locale'])) {
                // Set locale from session
                self::$locale = $_SESSION['locale'];
            } elseif ((!isset($_SESSION['locale']) || empty($_SESSION['locale'])) && in_array($systemLocale, $available)) {
                // Set locale from system
                // Default locale
                self::$locale = $_SESSION['locale'] = $systemLocale;
            } else {
                self::$locale = 'en_US';
            }

            // Set the locale to envvars
            putenv('LANG='.self::$locale);
            setlocale(LC_MESSAGES, self::$locale);
        }

        return self::$locale;
    }

    /**
     * Translate a string.
     */
    public static function translate()
    {
        $args = func_get_args();

        if (isset($args[1])) {
            $domain = $args[1];
        } else {
            // Controller translations
            $request = Request::getInstance();
            $domain = $request->getMainController();
        }

        $translate = new I18n\Translate($args);

        // Default to installpath
        if (file_exists(LIBVALOA_INSTALLPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Locale'.DIRECTORY_SEPARATOR.self::getLocale().DIRECTORY_SEPARATOR.'LC_MESSAGES'.DIRECTORY_SEPARATOR.$domain.'.ini')) {
            $path = LIBVALOA_INSTALLPATH;
        }

        // Override from extensionspath if found
        if (file_exists(LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Locale'.DIRECTORY_SEPARATOR.self::getLocale().DIRECTORY_SEPARATOR.'LC_MESSAGES'.DIRECTORY_SEPARATOR.$domain.'.ini')) {
            $path = LIBVALOA_EXTENSIONSPATH;
        }

        // No translation found
        if (!isset($path)) {
            return $args[0];
        }

        $translate->bindTextDomain($domain, $path.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Locale');
        $t = (string) $translate;

        return $t;
    }
}

/**
 * Set up the user interface.
 *
 * Loads the UI driver, sets up paths for the given UI driver, sets up properties,
 * and returns instace of the UI.
 *
 * @uses \Webvaloa\Controller\Request
 * @uses \Libvaloa\Ui
 */
class ApplicationUI
{
    private static $instance = false;

    /**
     * Returns ApplicationUI instance.
     *
     * @return Request
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }

        $request = Request::getInstance();

        // Force protocol
        if (class_exists('\\Webvaloa\\config') && isset(\Webvaloa\config::$properties['force_protocol']) && !empty(\Webvaloa\config::$properties['force_protocol'])) {
            $request->setProtocol(\Webvaloa\config::$properties['force_protocol']);
        }

        // UI
        $uiInterface = Webvaloa::$properties['ui'];
        $ui = new $uiInterface();

        // File paths for the UI

        // Layout and overrides path
        $ui->includePath(LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Layout'.DIRECTORY_SEPARATOR.Webvaloa::$properties['layout']);
        $ui->includePath(LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Layout'.DIRECTORY_SEPARATOR.Webvaloa::$properties['layout'].DIRECTORY_SEPARATOR.'Views');
        $ui->includePath(LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Layout'.DIRECTORY_SEPARATOR.Webvaloa::$properties['layout'].DIRECTORY_SEPARATOR.$request->getMainController().DIRECTORY_SEPARATOR.'Views');

        // Controller
        $ui->includePath(LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.$request->getMainController().DIRECTORY_SEPARATOR.'Views');

        // Plugins
        $ui->includePath(LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Plugins');

        // As above, but from core installation
        $ui->includePath(LIBVALOA_INSTALLPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Layout'.DIRECTORY_SEPARATOR.Webvaloa::$properties['layout']);
        $ui->includePath(LIBVALOA_INSTALLPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Layout'.DIRECTORY_SEPARATOR.Webvaloa::$properties['layout'].DIRECTORY_SEPARATOR.'Views');
        $ui->includePath(LIBVALOA_INSTALLPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.$request->getMainController().DIRECTORY_SEPARATOR.'Views');
        $ui->includePath(LIBVALOA_INSTALLPATH.DIRECTORY_SEPARATOR.Webvaloa::$properties['vendor'].DIRECTORY_SEPARATOR.'Plugins');

        // Public media paths
        $ui->includePath(LIBVALOA_PUBLICPATH.DIRECTORY_SEPARATOR.'Layout'.DIRECTORY_SEPARATOR.Webvaloa::$properties['layout']);
        $ui->includePath(LIBVALOA_PUBLICPATH.DIRECTORY_SEPARATOR.'Layout');

        // Empty template for ajax requests
        if ($request->isAjax()) {
            $ui->setMainTemplate('empty');
        }

        // UI properties
        $ui->properties['controller'] = $request->getController();
        $ui->properties['parentController'] = $request->getMainController();
        $ui->properties['route'] = $request->getCurrentRoute();
        if (isset($_SESSION['locale'])) {
            $ui->properties['locale'] = $_SESSION['locale'];
        }

        // Base paths
        $ui->properties['basehref'] = $request->getBaseUri();
        $ui->properties['basepath'] = $request->getPath();
        $ui->properties['layout'] = Webvaloa::$properties['layout'];

        // User info
        if (isset($_SESSION['UserID'])) {
            $ui->properties['userid'] = $_SESSION['UserID'];
        }
        if (isset($_SESSION['User'])) {
            $ui->properties['user'] = $_SESSION['User'];
        }

        return self::$instance = $ui;
    }
}

/**
 * Base class which modules extend.
 */
class Application
{
    protected $params = false;

    public function __get($k)
    {
        // Core classes available for controllers/applications

        if ($k === 'request') {
            $this->request = Request::getInstance();

            // Force protocol
            if (class_exists('\\Webvaloa\\config') && isset(\Webvaloa\config::$properties['force_protocol']) && !empty(\Webvaloa\config::$properties['force_protocol'])) {
                $this->request->setProtocol(\Webvaloa\config::$properties['force_protocol']);
            }

            return $this->request;
        } elseif ($k === 'ui') {
            $this->ui = ApplicationUI::getInstance();

            return $this->ui;
        } elseif ($k === 'view') {
            $this->view = new stdClass();

            return $this->view;
        } elseif ($k === 'db') {
            return \Webvaloa\Webvaloa::DBConnection();
        } elseif ($k === 'locale') {
            return \Webvaloa\Webvaloa::getLocale();
        } elseif ($k === 'plugin') {
            $this->plugin = new Plugin();

            return $this->plugin;
        } elseif (!empty($this->params)) {
            $this->parseParameters();
        }

        if (isset($this->{$k})) {
            return $this->{$k};
        }

        trigger_error('Call to an undefined property '.get_class($this)."::\${$k}", E_USER_WARNING);

        return;
    }

    public function __isset($k)
    {
        if (!empty($this->params)) {
            $this->parseParameters();
        }

        return isset($this->{$k});
    }

    public function __toString()
    {
        // Set page root (template name)
        if (!$this->ui->issetPageRoot() && $this->request->getMethod()) {
            $this->ui->setPageRoot($this->request->getMethod());
        }

        try {
            // Plugin event: onAfterController
            $this->plugin->request = & $this->request;

            if ($this->plugin->hasRunnablePlugins()) {
                $this->plugin->setEvent('onAfterController');

                // Give stuff for plugins to modify
                $this->plugin->ui           = & $this->ui;
                $this->plugin->view         = & $this->view;
                $this->plugin->controller   = false; // Controller cannot be modified at this point
                $this->plugin->xhtml        = false; // Xhtml output is not available at this point
                $this->plugin->_properties  = false;

                // Run plugins
                $this->plugin->runPlugins();
            }

            // Set view data from the controller after plugins are adone
            $this->ui->addObject($this->view);

            if ($this->request->getChildController()) {
                // Load resources for child controller, /application_subapplication
                $this->ui->addTemplate($this->request->getChildController());
            } else {
                // Load resources for main application, /application
                $this->ui->addTemplate($this->request->getMainController());
            }

            // Preprocess the XSL template
            $this->ui->preProcessTemplate();

            // Plugin event: onBeforeRender
            if ($this->plugin->hasRunnablePlugins()) {
                $this->plugin->setEvent('onBeforeRender');

                // Run plugins
                $this->plugin->runPlugins();
            }

            // Page complete, send headers and output:

            // Headers
            header('Content-type: '.$this->ui->properties['contenttype'].'; charset=utf-8');
            header('Vary: Accept');

            // Rendered XHTML
            $xhtml = (string) $this->ui;

            // Plugin event: onAfterRender
            if ($this->plugin->hasRunnablePlugins()) {
                $this->plugin->setEvent('onAfterRender');

                // Give stuff for plugins to modify
                $this->plugin->ui           = false; // UI cannot be modified at this point
                $this->plugin->view         = $this->view; // View is available after render for reading, but not modifiable at this point
                $this->plugin->controller   = false; // Controller cannot be modified at this point
                $this->plugin->xhtml        = & $xhtml;
                $this->plugin->_properties  = false;

                // Run plugins
                $this->plugin->runPlugins();
            }

            Debug::__print('Executed '.\Libvaloa\Db\Db::$querycount.' sql queries.');
            Debug::__print('Webvaloa finished');

            return $xhtml;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    private function parseParameters()
    {
        if (is_array($this->params)) {
            foreach ($this->params as $k => $v) {
                if ($v) {
                    $this->{$v} = $this->request->getParam($k);
                }
            }
        }

        $this->params = false;
    }
}

// Load the kernel
new Webvaloa();

// Wake up frontcontroller
$frontcontroller = new FrontController();

// Set up default controllers
if (class_exists('\\Webvaloa\\config')) {
    // Default controller
    if (isset(\Webvaloa\config::$properties['default_controller'])) {
        $frontcontroller::$properties['defaultController'] = \Webvaloa\config::$properties['default_controller'];
    }

    // Default controller when logged in
    if (isset(\Webvaloa\config::$properties['default_controller_authed'])) {
        $frontcontroller::$properties['defaultControllerAuthed'] = \Webvaloa\config::$properties['default_controller_authed'];
    }
}

// Run the frontcontroller
echo $frontcontroller->runController();
