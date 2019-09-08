<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.io>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.io>
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

use Webvaloa\Controller\Redirect;
use Webvaloa\Plugin;
use Webvaloa\Security;
use Webvaloa\Helpers\Pagination;
use UnexpectedValueException;

class PluginController extends \Webvaloa\Application
{
    public function __construct()
    {
        $this->ui->addJS('/js/Extension_Plugin.js');
        $this->ui->addTemplate('pagination');
    }

    public function index($page = 1)
    {
        $this->view->token = Security::getToken();

        $q = '';

        if (isset($_GET['search'])) {
            $this->view->search = $_GET['search'];
            $q = ' WHERE plugin LIKE ?';
        }

        $pagination = new Pagination();
        $this->view->pages = $pagination->pages((int) $page, $pagination->countTable('plugin'));
        $this->view->pages->url = '/extension_plugin/';

        $query = $pagination->prepare(
            '
            SELECT *
            FROM plugin '.$q
        );

        $stmt = $this->db->prepare($query);
        try {
            if (isset($q) && !empty($q)) {
                $stmt->set('%'.$_GET['search'].'%');
            }

            $stmt->execute();

            $this->view->plugins = $stmt->fetchAll();
        } catch (Exception $e) {
        }
    }

    public function edit()
    {
        Security::verify();

        if (!isset($_POST['plugin_id']) || !is_numeric($_POST['plugin_id'])) {
            throw new UnexpectedValueException();
        }

        $pluginID = (int) $_POST['plugin_id'];
        $priority = (int) $_POST['priority'];

        Plugin::setPluginOrder($pluginID, $priority);

        $this->ui->addMessage(\Webvaloa\Webvaloa::translate('PLUGIN_SAVED'));
        Redirect::to('extension_plugin/');
    }

    public function toggle($pluginID = false, $page = 1)
    {
        Security::verify();

        if (!$pluginID || !is_numeric($pluginID)) {
            throw new UnexpectedValueException();
        }

        $status = Plugin::getPluginStatus($pluginID);

        // Block plugin
        if ($status == 0) {
            Plugin::setPluginStatus($pluginID, 1);

            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('PLUGIN_DISABLED'));
        }

        // Enable plugin
        if ($status == 1) {
            Plugin::setPluginStatus($pluginID, 0);

            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('PLUGIN_ENABLED'));
        }

        Redirect::to('extension_plugin/'.$page);
    }

    public function uninstall($plugin = false)
    {
        Security::verify();

        $this->view->plugin = $plugin;

        $p = new Plugin($plugin);
        $p->uninstall();

        $this->ui->addMessage(\Webvaloa\Webvaloa::translate('PLUGIN_UNINSTALLED'));
        Redirect::to('extension_plugin');
    }
}
