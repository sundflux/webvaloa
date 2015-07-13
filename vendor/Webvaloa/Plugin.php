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
namespace Webvaloa;

use Libvaloa\Db;
use RuntimeException;

/**
 * Manage and run plugins.
 */
class Plugin
{
    private $db;
    private $plugins;
    private $runnablePlugins;
    private $plugin;

    // Objects that plugins can access
    public $_properties;
    public $ui;
    public $controller;
    public $request;
    public $view;
    public $xhtml;

    public static $properties = array(
        // Vendor tag
        'vendor' => 'ValoaApplication',

        // Events
        'events' => array(
            'onAfterFrontControllerInit',
            'onBeforeController',
            'onAfterController',
            'onBeforeRender',
            'onAfterRender',
        ),

        // Skip plugins in these controllers
        'skipControllers' => array(
            'Setup',
        ),
    );

    public function __construct($plugin = false)
    {
        $this->plugin = $plugin;

        $this->event = false;
        $this->plugins = false;
        $this->runnablePlugins = false;

        // Plugins can access and modify these
        $this->_properties = false;
        $this->ui = false;
        $this->controller = false;
        $this->request = false;
        $this->view = false;
        $this->xhtml = false;

        try {
            $this->db = \Webvaloa\Webvaloa::DBConnection();
        } catch (Exception $e) {
        }
    }

    public function setEvent($e)
    {
        if (in_array($e, self::$properties['events'])) {
            $this->event = $e;
        }
    }

    public function plugins()
    {
        if (!method_exists($this->db, 'prepare')) {
            // Just bail out
            return false;
        }

        if (method_exists($this->request, 'getMainController') && (in_array($this->request->getMainController(), self::$properties['skipControllers']))) {
            return false;
        }

        $query = '
            SELECT id, plugin, system_plugin
            FROM plugin
            WHERE blocked = 0
            ORDER BY ordering ASC';

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $this->plugins = $stmt->fetchAll();

            return $this->plugins;
        } catch (PDOException $e) {
        }
    }

    public function pluginExists($name)
    {
        $name = trim($name);

        foreach ($this->plugins as $k => $plugin) {
            if ($plugin->plugin == $name) {
                return true;
            }
        }

        return false;
    }

    public function hasRunnablePlugins()
    {
        // Return runnable plugins if we already gathered them
        if ($this->runnablePlugins) {
            return $this->runnablePlugins;
        }

        if (!$this->request) {
            throw new RuntimeException('Instance of request is required');
        }

        if (in_array($this->request->getMainController(), self::$properties['skipControllers'])) {
            return false;
        }

        // Load plugins
        if (!$this->plugins) {
            $this->plugins();
        }

        if (!is_array($this->plugins)) {
            return false;
        }

        $controller = $this->request->getMainController();

        // Look for executable plugins
        foreach ($this->plugins as $k => $plugin) {
            if ($controller && strpos($plugin->plugin, $controller) === false
                && strpos($plugin->plugin, 'Plugin') === false) {
                continue;
            }

            $this->runnablePlugins[] = $plugin;
        }

        return (bool) ($this->runnablePlugins && !empty($this->runnablePlugins)) ? $this->runnablePlugins : false;
    }

    public function runPlugins()
    {
        if (!$this->runnablePlugins || empty($this->runnablePlugins)) {
            return false;
        }

        $e = $this->event;

        foreach ($this->runnablePlugins as $k => $v) {
            $p = '\\'.self::$properties['vendor'].'\Plugins\\'.$v->plugin.'Plugin';
            $plugin = new $p();

            $plugin->view = &$this->view;
            $plugin->ui = &$this->ui;
            $plugin->request = &$this->request;
            $plugin->controller = &$this->controller;
            $plugin->xhtml = &$this->xhtml;
            $plugin->_properties = &$this->_properties;

            if (method_exists($plugin, $e)) {
                $plugin->{$e}();
            }
        }
    }

    public static function getPluginStatus($pluginID)
    {
        $query = '
            SELECT blocked
            FROM plugin
            WHERE system_plugin = 0
            AND id = ?';

        try {
            $db = \Webvaloa\Webvaloa::DBConnection();

            $stmt = $db->prepare($query);
            $stmt->set((int) $pluginID);
            $stmt->execute();

            $row = $stmt->fetch();

            if (isset($row->blocked)) {
                return $row->blocked;
            }

            return false;
        } catch (PDOException $e) {
        }
    }

    public static function setPluginStatus($pluginID, $status = 0)
    {
        $query = '
            UPDATE plugin
            SET blocked = ?
            WHERE id = ?';

        try {
            $db = \Webvaloa\Webvaloa::DBConnection();

            $stmt = $db->prepare($query);
            $stmt->set((int) $status);
            $stmt->set((int) $pluginID);
            $stmt->execute();
        } catch (PDOException $e) {
        }
    }

    public static function setPluginOrder($pluginID, $ordering = 0)
    {
        $query = '
            UPDATE plugin
            SET ordering = ?
            WHERE id = ?';

        try {
            $db = \Webvaloa\Webvaloa::DBConnection();

            $stmt = $db->prepare($query);
            $stmt->set((int) $ordering);
            $stmt->set((int) $pluginID);
            $stmt->execute();
        } catch (PDOException $e) {
        }
    }

    public function install()
    {
        if (!$this->plugin) {
            return false;
        }

        $installable = $this->discover();

        if (!in_array($this->plugin, $installable)) {
            return false;
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        // Install plugin
        $object = new Db\Object('plugin', $db);
        $object->plugin = $this->plugin;
        $object->system_plugin = 0;
        $object->blocked = 0;
        $object->ordering = 1;

        $id = $object->save();

        return $id;
    }

    public function uninstall()
    {
        if (!$this->plugin) {
            return false;
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            DELETE FROM plugin
            WHERE system_plugin = 0
            AND plugin = ?';

        $stmt = $db->prepare($query);

        try {
            $stmt->set($this->plugin);
            $stmt->execute();

            return true;
        } catch (Exception $e) {
        }

        return false;
    }

    public function discover()
    {
        // Installed plugins
        $tmp = $this->plugins();

        foreach ($tmp as $v => $plugin) {
            $plugins[] = $plugin->plugin;
        }

        // Discovery paths
        $paths[] = LIBVALOA_INSTALLPATH.DIRECTORY_SEPARATOR.self::$properties['vendor'].DIRECTORY_SEPARATOR.'Plugins';
        $paths[] = LIBVALOA_EXTENSIONSPATH.DIRECTORY_SEPARATOR.self::$properties['vendor'].DIRECTORY_SEPARATOR.'Plugins';

        $skip = array(
            '.',
            '..',
        );

        $plugins = array_merge($plugins, $skip);

        // Look for new plugins
        foreach ($paths as $path) {
            if ($handle = opendir($path)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry == '.' || $entry == '..') {
                        continue;
                    }

                    if (substr($entry, -3) != 'php') {
                        continue;
                    }

                    $pluginName = str_replace('Plugin.php', '', $entry);

                    if (!isset($installablePlugins)) {
                        $installablePlugins = array();
                    }

                    if (!in_array($pluginName, $plugins) && !in_array($pluginName, $installablePlugins)) {
                        $installablePlugins[] = $pluginName;
                    }
                }

                closedir($handle);
            }
        }

        if (isset($installablePlugins)) {
            return $installablePlugins;
        }

        return array();
    }
}
