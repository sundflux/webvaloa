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
use Webvaloa\Security;
use Webvaloa\Component;
use Webvaloa\Manifest;
use Webvaloa\Configuration;
use Webvaloa\Helpers\Pagination;

class ComponentController extends \Webvaloa\Application
{
    public function __construct()
    {
        $this->ui->addJS('/js/Extension_Component.js');
        $this->ui->addTemplate('pagination');

        $this->view->token = Security::getToken();
    }

    public function index($page = 1)
    {
        $q = '';

        if (isset($_GET['search'])) {
            $this->view->search = $_GET['search'];
            $q = ' WHERE controller LIKE ?';
        }

        $pagination = new Pagination();
        $this->view->pages = $pagination->pages((int) $page, $pagination->countTable('component'));
        $this->view->pages->url = '/extension_component/';

        $query = $pagination->prepare('
            SELECT *
            FROM component '.$q);

        $stmt = $this->db->prepare($query);
        try {
            if (isset($q) && !empty($q)) {
                $stmt->set('%'.$_GET['search'].'%');
            }

            $stmt->execute();

            $this->view->components = $stmt->fetchAll();
        } catch (Exception $e) {
        }
    }

    public function toggle($componentID = false, $page = 1)
    {
        Security::verify();

        if (!$componentID || !is_numeric($componentID)) {
            throw new UnexpectedValueException();
        }

        $status = Component::getComponentStatus($componentID);

        // Block component
        if ($status == 0) {
            Component::setComponentStatus($componentID, 1);

            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('COMPONENT_DISABLED'));
        }

        // Enable component
        if ($status == 1) {
            Component::setComponentStatus($componentID, 0);

            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('COMPONENT_ENABLED'));
        }

        Redirect::to('extension_component/'.$page);
    }

    public function uninstall($controller = false)
    {
        Security::verify();

        $this->view->controller = $controller;

        // Read component manifest
        $manifest = new Manifest($controller);

        $component = new Component($controller);

        // Uninstall the component
        $component->uninstall();

        // Delete the configuration vars
        $configuration = new Configuration($controller);
        $configuration->delete();

        $this->ui->addMessage(\Webvaloa\Webvaloa::translate('COMPONENT_UNINSTALLED'));
        Redirect::to('extension_component');
    }
}
