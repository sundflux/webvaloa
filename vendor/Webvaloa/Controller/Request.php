<?php

/**
 * The Initial Developer of the Original Code is
 * Joni Halme <jontsa@amigaone.cc>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2006 Joni Halme <jontsa@amigaone.cc>
 *
 * All Rights Reserved.
 *
 * Contributor(s):
 * 2008,2009,2013,2014 Tarmo Alexander Sundström <ta@sundstrom.im>
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

/**
 * Controller Request object.
 *
 * $uri must always contain:
 * host[/path][/index.php]/controller[/method][/params][?getparams]
 * without http[s]:// prefix.
 *
 * If method is not found, it is appended to parameters and no method is called automatically.
 * If controller is not found, it is appended to parameters and default controller is opened
 * Parameters can be used as variable1/value1/variable2/value2 or value1/value2/value3 etc
 */

namespace Webvaloa\Controller;

use Libvaloa\Debug\Debug;

/**
 * Class Request
 * @package Webvaloa\Controller
 */
class Request
{
    /**
     * @var bool|Request
     */
    private static $instance = false;

    /**
     * @var bool|string
     */
    private $basepath;

    /**
     * host (with http[s]:// prefix) and path
     *
     * @var array
     */
    private $baseuri = array();

    /**
     * requested controller to load
     *
     * @var bool|string
     */
    private $controller = false;

    /**
     * requested method to call from controller
     *
     * @var mixed|string
     */
    private $method = 'index';

    /**
     * parameters for controller
     *
     * @var array
     */
    private $parameters = array();

    /**
     * @var string
     */
    private $protocol = 'http';

    /**
     * @var bool
     */
    private $ajax = false;

    /**
     * @var bool
     */
    private $json = false;

    /**
     * @var bool
     */
    private $cli = false;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        if (\Webvaloa\Webvaloa::isCommandLine()) {
            $_SERVER['HTTP_HOST'] = 'localhost';
            $_SERVER['REQUEST_URI'] = '';

            $this->cli = true;
            $this->mapCommandLine();
        }

        $script = str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']);
        $uri = $_SERVER['HTTP_HOST'].$script.str_replace(str_replace('index.php', '', $script), '/', $_SERVER['REQUEST_URI']);

        // http/https autodetect
        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            $this->protocol = 'https';
        }
        $prefix = $this->protocol.'://';

        // url should be without http[s]:// prefix and contain
        // host[/path][/index.php]/controller[/method][/params][?getparams]
        $this->baseuri['host'] = $prefix.$_SERVER['HTTP_HOST'];

        // Route when rewrite..
        if (strpos($uri, 'index.php') === false) {
            $uri = str_replace(
                $_SERVER['HTTP_HOST'],
                $_SERVER['HTTP_HOST'].'index.php',
                $uri
            );
        }

        list($host, $route) = explode('index.php', $uri, 2);
        $route = str_replace('index.php', '', $route);
        $this->baseuri['path'] = str_ireplace($_SERVER['HTTP_HOST'], '', $host);

        // strip GET parameters, we will add them later
        list($route) = explode('?', $route, 2);

        if (substr($route, 0, 1) === '/') {
            $route = substr($route, 1);
        }

        $this->basepath = $route;

        $route = explode('/', $route);

        // get controller from route
        if (isset($route[0]) && !empty($route[0])) {
            $this->controller = ucfirst(array_shift($route));
            $this->method = array_shift($route);
        }

        // rest are parameters
        $this->parameters = array_map(array($this, 'decodeRouteParam'), $route);

        // ajax autodetect
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            $this->ajax = true;

            if (isset($_SERVER['HTTP_ACCEPT']) && in_array(
                'application/json',
                explode(',', $_SERVER['HTTP_ACCEPT']),
                true
            )) {
                $this->json = true;
            }
        }

        self::$instance = $this;
    }

    /**
     * Map command line parameters to Commando
     */
    public function mapCommandLine()
    {
        Debug::__print('Command Line Debug: Command line support enabled.');

        $this->isJson(true);

        $this->cli = new \Commando\Command();

        $this->cli->option('controller')
            ->aka('c')
            ->require()
            ->describedAs('Controller to run');

        $this->cli->option('method')
            ->aka('m')
            ->require()
            ->describedAs('Controller method to run');

        $this->cli->option('parameters')
            ->aka('p')
            ->describedAs('Controller method to run');

        $this->setController($this->cli['controller']);
        $this->setMethod($this->cli['method']);

        $params = $this->cli['parameters'];

        if (!empty($params)) {
            $params = explode('/', $params);
            $this->setParams($params);
        }

        Debug::__print('Mapped:');
        Debug::__print($this->cli['controller']);
        Debug::__print($this->cli['method']);
        Debug::__print($this->cli['parameters']);
    }

    /**
     * Returns Request instance.
     *
     * @return Request
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }

        return new self();
    }

    /**
     * This method is called from controller if selected method does not exist.
     * We assume that second parameter is not meant as method but as a parameter.
     *
     * @note This method should never ever be called after shiftController().
     */
    public function shiftMethod()
    {
        if ($this->method && $this->method != 'index') {
            array_unshift($this->parameters, $this->method);
        }

        $this->method = false;
    }

    /**
     * This method is called from controller if selected controller does not exist.
     * We assume that first parameter is not meant as controller name but as
     * a parameter.
     */
    public function shiftController()
    {
        if ($this->controller) {
            array_unshift($this->parameters, $this->controller);
        }

        $this->controller = false;
    }

    /**
     *
     */
    public function shiftParam()
    {
        array_shift($this->parameters);
    }

    /**
     * Sets controller to load.
     */
    public function setController($controller)
    {
        $this->controller = ucfirst($controller);
    }

    /**
     * Sets method to call from controller.
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Sets parameters for controller.
     */
    public function setParams($params)
    {
        if (is_array($params)) {
            $this->parameters = $params;
        } else {
            $this->parameters = explode('/', $params);
        }
    }

    /**
     * Set protocol
     */
    public function setProtocol($protocol)
    {
        $protocols = array(
            'http',
            'https',
            'h2-17', // http/2 secure, draft 17
            'h2-14', // http/2 secure, draft 14
            'h2c-17', // http/2 non-secure, draft 17
            'h2c-14', // http/2 non-secure, draft 17
        );

        if (!in_array($protocol, $protocols)) {
            $protocol = 'http';
        }

        $this->protocol = $protocol;
    }

    /**
     * Returns the parameters and their values from current request.
     *
     * @param bool $string If true, return value is request string,
     *                     otherwise its an array
     *
     * @return mixed
     */
    public function getParams($string = false)
    {
        if (!$string) {
            return $this->parameters;
        }

        return '/'.implode('/', $this->parameters);
    }

    /**
     * Returns name of requested controller.
     */
    public function getController($full = true)
    {
        if (!$full) {
            $tmp = explode('_', $this->controller);

            return ucfirst($tmp[0]);
        }

        return ucfirst($this->controller);
    }

    /**
     * @return string
     */
    public function getMainController()
    {
        return $this->getController(false);
    }

    /**
     * @return string
     */
    public function getChildController()
    {
        $tmp = explode('_', $this->controller);

        if (isset($tmp[1])) {
            return ucfirst($tmp[1]);
        }

        return ucfirst($this->getMainController());
    }

    /**
     * Returns name of requested method.
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns a single parameter by its position in parameters or by its key.
     */
    public function getParam($k)
    {
        if (is_int($k)) {
            return isset($this->parameters[$k]) ? $this->parameters[$k] : false;
        } else {
            $k = array_search($k, $this->parameters);
            if ($k !== false && isset($this->parameters[$k + 1])) {
                return $this->parameters[$k + 1];
            }

            return false;
        }
    }

    /**
     * Returns the host-part of the current request IF available.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->baseuri['host'];
    }

    /**
     * Returns the base path.
     * The path does not contain index.php.
     *
     * @return string
     */
    public function getPath()
    {
        return rtrim(dirname(substr(
            $_SERVER['SCRIPT_FILENAME'],
            strlen($_SERVER['DOCUMENT_ROOT'])
        )), '/');
    }

    /**
     * Returns the full route to the current request without the leading /.
     * For example "my_controller/method/param1/value1".
     * Parameters in route are encoded.
     *
     * @return string
     */
    public function getCurrentRoute()
    {
        $params = array_map('self::encodeRouteParam', $this->parameters);

        return $this->controller.'/'.($this->method !== false
            && $this->method != 'index' ? $this->method.'/' : '').
            implode('/', $params);
    }

    /**
     * Returns host and path to the website with http[s]:// prefix.
     *
     * @param bool $noautoindex If true, index.php will not be
     *                          automatically appended to url.
     *
     * @return string
     */
    public function getBaseUri($noautoindex = false)
    {
        // Basehref
        return $this->protocol.'://'.$_SERVER['HTTP_HOST'].$this->getPath();
    }

    /**
     * Returns full URI of the current website with controller,
     * method and controller parameters.
     */
    public function getUri()
    {
        return $this->getBaseUri().'/'.$this->getCurrentRoute();
    }

    /**
     * @return bool|string
     */
    public function getBasePath()
    {
        return $this->basepath;
    }

    /**
     * @param null $val
     * @return bool
     */
    public function isAjax($val = null)
    {
        if ($val !== null) {
            $this->ajax = (bool) $val;
        }

        return $this->ajax;
    }

    /**
     * @param null $val
     * @return bool
     */
    public function isJson($val = null)
    {
        if ($val !== null) {
            $this->json = (bool) $val;
        }

        return $this->json;
    }

    /**
     * @param $val
     * @return bool|string
     */
    private function decodeRouteParam($val)
    {
        if (substr($val, 0, 5) === '$enc$') {
            return base64_decode(str_replace(
                '.',
                '/',
                urldecode(substr($val, 5))
            ));
        } else {
            return urldecode($val);
        }
    }

    /**
     * @param $val
     * @return string
     */
    public static function encodeRouteParam($val)
    {
        if (strpos($val, '/') !== false) {
            return '$enc$'.urlencode(str_replace('/', '.', base64_encode($val)));
        } else {
            return urlencode($val);
        }
    }
}
