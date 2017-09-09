<?php

/**
 * The Initial Developer of the Original Code is
 * Joni Halme <jontsa@amigaone.cc>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2009 Joni Halme <jontsa@amigaone.cc>
 *
 * All Rights Reserved.
 *
 * Contributor(s):
 * 2010,2013,2014 Tarmo Alexander Sundström <ta@sundstrom.im>
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

use Webvaloa\Controller\Request;
use Webvaloa\Controller\Redirect;
use Webvaloa\Auth\Auth;
use ValoaApplication\Plugins;
use Webvaloa\Controller\Request\Alias;
use Webvaloa\Locale\Locales;
use ReflectionClass;
use BadMethodCallException;
use RuntimeException;

/**
 * FrontController, runs components and plugins.
 */
class FrontController
{
    private $plugin;

    public static $properties = array(
        'defaultController' => 'index',
        'defaultControllerAuthed' => 'index',
        'layout' => 'default',
        'vendor' => 'ValoaApplication',
    );

    /**
     * Loads & runs specified controller.
     *
     *
     * @uses        Controller_Request
     */
    public function __construct()
    {
        $this->plugin = new Plugin();
    }

    public function runController()
    {
        self::defaults();

        $locale = false;
        $request = Request::getInstance();

        // Force protocol
        if (class_exists('\\Webvaloa\\config') && isset(\Webvaloa\config::$properties['force_protocol']) && !empty(\Webvaloa\config::$properties['force_protocol'])) {
            $request->setProtocol(\Webvaloa\config::$properties['force_protocol']);
        }

        if (strlen($request->getParam(0)) > 0) {
            // Check for locale
            $locales = new Locales();
            $codes = $locales->localeCodes();

            // Found locale in url
            if (in_array($locale = strtolower($request->getParam(0)), $codes)) {
                // Set locale override
                putenv('LANG='.$locales->getLocale($locale));

                // Shift the language parameter out of the request
                $request->shiftParam();
                if ($request->getParam(0) == false) {
                    // Load default controller if the request has no
                    // further parameters
                    self::defaults();
                } else {
                    if (self::controllerExists($request->getParam(0)) === true) {
                        // Second parameter was a valid controller, so replace it in request
                        $request->setController($request->getParam(0));
                    } else {
                        // Could not find valid controller, set controller as default
                        if (isset($_SESSION['UserID']) && !empty($_SESSION['UserID'])) {
                            $controller = $childController = self::$properties['defaultControllerAuthed'];
                        } else {
                            $controller = $childController = self::$properties['defaultController'];
                        }

                        $request->setController($controller);
                    }
                }

                // Re-sort controller, method and parameters
                $controller = $request->getController();
            }

            // Check for url aliases
            $alias = new Alias($request->getParams());

            if (isset($alias->controller->id)) {
                // Set controlled and method
                $request->setController((string) $alias);
                $request->setMethod($alias->getMethod());

                // Shift first parameter off at controller (method)
                $request->shiftParam();

                // Set params for controller, if any
                if ($params = $alias->getParams()) {

                    // Append parameters from current route
                    if ($currentRequestParams = $request->getParams()) {
                        foreach ($currentRequestParams as $k => $v) {
                            $params[] = $v;
                        }
                    }

                    // Set new parameters for controller
                    $request->setParams($params);
                }
            }
        }

        $controller = $request->getMainController();
        $childController = $request->getChildController();

        if (!$controller || empty($controller)) {
            if (isset($_SESSION['UserID']) && !empty($_SESSION['UserID'])) {
                $controller = $childController = self::$properties['defaultControllerAuthed'];
            } else {
                $controller = $childController = self::$properties['defaultController'];
            }
        }

        // Controller name
        $application = '\\'.self::$properties['vendor'].'\Controllers\\'
            .$controller.'\\'
            .$childController.'Controller';

        if (!self::controllerExists()) {
            throw new RuntimeException('Controller '.$application.' not found');
        }

        if (!in_array($request->getMethod(), get_class_methods($application), true)
            || substr($request->getMethod(), 0, 2) === '__') {
            $request->shiftMethod();
            if (in_array('index', get_class_methods($application))) {
                $request->setMethod('index');
            }
        }

        $manifest = new Manifest($controller);

        // System event: onFrontcontrollerInit
        $this->plugin->request = &$request;
        if ($this->plugin->hasRunnablePlugins()) {
            $this->plugin->setEvent('onAfterFrontControllerInit');

            // Give stuff for plugins to modify
            $this->plugin->ui = false;
            $this->plugin->view = false;
            $this->plugin->controller = false;
            $this->plugin->xhtml = false;
            $this->plugin->_properties = &self::$properties;

            // Run plugins
            $this->plugin->runPlugins();
        }

        // Start session
        if ($manifest->session !== '0') {
            \Webvaloa\Webvaloa::initializeSession();

            // Also replace locale in session if necessary
            if ($locale && isset($_SESSION['locale']) && $_SESSION['locale'] != $locales->getLocale($locale) && $locales->getLocale($locale)) {
                $_SESSION['locale'] = $locales->getLocale($locale);
            }
        }

        // Set layout
        if ($manifest->systemcontroller && $manifest->systemcontroller == 1) {
            // System controller

            // Set backend template only if defined so
            $configuration = new Configuration();
            if (@$configuration->template_backend->value == 'yes') {
                \Webvaloa\Webvaloa::$properties['layout'] = self::$properties['layout'];
            }
        } else {
            // Regular controller, set the template
            \Webvaloa\Webvaloa::$properties['layout'] = self::$properties['layout'];
        }

        // Make sure locale is set
        \Webvaloa\Webvaloa::getLocale();

        // Check for authentication. Anonymous components don't need permission checks.
        if (!$manifest->anonymous || $manifest->anonymous == 0) {

            // Check if component is public
            $component = new Component($controller);

            // Not public, check for authorization
            if (!$component->isPublic()) {
                $backend = \Webvaloa\config::$properties['webvaloa_auth'];

                $auth = new Auth();
                $auth->setAuthenticationDriver(new $backend());

                $userid = (isset($_SESSION['UserID']) ? $_SESSION['UserID'] : false);

                if (!$auth->authorize($controller, $userid)) {
                    if ($userid === false && isset(\Webvaloa\config::$properties['default_controller_login'])) {
                        Redirect::to(\Webvaloa\config::$properties['default_controller_login']);
                    }
                    if ($userid !== false && isset(\Webvaloa\config::$properties['default_controller_denied'])) {
                        Redirect::to(\Webvaloa\config::$properties['default_controller_denied']);
                    }
                    throw new RuntimeException('Access denied');
                }
            }
        }

        // Initialize application
        $application = new $application();
        $method = $request->getMethod();

        // Plugin event: onBeforeController
        $this->plugin->request = &$request;

        if ($this->plugin->hasRunnablePlugins()) {
            $this->plugin->setEvent('onBeforeController');

            // Give stuff for plugins to modify
            $this->plugin->_properties = false;
            $this->plugin->ui = &$application->ui;
            $this->plugin->view = &$application->view;
            $this->plugin->controller = &$application;
            $this->plugin->xhtml = false;

            // Run plugins
            $this->plugin->runPlugins();
        }

        if ($method) {
            // Get expected parameters

            $reflection = new ReflectionClass($application);
            $expectedParams = $reflection->getMethod($method)->getNumberOfParameters();

            if ($expectedParams > 0) {
                for (; $expectedParams != 0; --$expectedParams) {
                    // Params start from 0 in Controller_Request
                    $params[] = $request->getParam($expectedParams - 1);
                }
                $params = array_reverse($params);
            }

            // Execute the controller
            if (isset($params)) {
                call_user_func_array(array($application, $method), $params);
            } else {
                $application->{$method}();
            }
        }

        return $application;
    }

    /**
     * Sets default routes.
     */
    public static function defaults()
    {
        $request = Request::getInstance();

        // Force protocol
        if (class_exists('\\Webvaloa\\config') && isset(\Webvaloa\config::$properties['force_protocol']) && !empty(\Webvaloa\config::$properties['force_protocol'])) {
            $request->setProtocol(\Webvaloa\config::$properties['force_protocol']);
        }

        if (!$request->getController() || !self::controllerExists()) {
            $request->shiftMethod();
            $request->shiftController();

            // Get default controller
            if (isset($_SESSION['UserID']) && !empty($_SESSION['UserID'])) {
                $params = explode('/', self::$properties['defaultControllerAuthed']);
                $request->setController($params[0]);
            } else {
                $params = explode('/', self::$properties['defaultController']);
                $request->setController($params[0]);
            }

            if (!self::controllerExists()) {
                throw new BadMethodCallException('Application not found.');
            } else {
                unset($params[0]);
                if (isset($params[1])) {
                    $request->setMethod(array_shift($params));
                }
            }

            unset($params);
        }
    }

    /**
     * Checks wether or not the requested controller exists.
     *
     * @return bool
     */
    public static function controllerExists($controllerName = false)
    {
        $request = Request::getInstance();

        if ($controllerName === false) {
            $controller = $request->getMainController();
            $childController = $request->getChildController();
        } else {
            $controller = self::getMainControllerName($controllerName);
            $childController = self::getChildControllerName($controllerName);
        }

        if (!$controller || empty($controller)) {
            $controller = $childController = self::$properties['defaultController'];
        }

        $application = '\\'.self::$properties['vendor'].'\Controllers\\'
            .$controller
            .'\\'.$childController
            .'Controller';

        $exists = (bool) class_exists($application);

        return $exists;
    }

    public static function getControllerName($controller, $pos = 0)
    {
        $controller = strtolower($controller);

        if (strpos($controller, '_') !== false) {
            $parts = explode('_', $controller);
            if (!empty($parts[$pos])) {
                return ucfirst($parts[$pos]);
            }

            return '';
        } else {
            return ucfirst($controller);
        }
    }

    public static function getMainControllerName($controller)
    {
        return self::getControllerName($controller, 0);
    }

    public static function getChildControllerName($controller)
    {
        return self::getControllerName($controller, 1);
    }
}
