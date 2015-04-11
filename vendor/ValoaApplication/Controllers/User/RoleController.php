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

namespace ValoaApplication\Controllers\User;

use Libvaloa\Debug;
use Libvaloa\Controller\Redirect;
use Webvaloa\Security;
use Webvaloa\Component;
use Webvaloa\Role;
use Webvaloa\Helpers\Pagination;
use Exception;

class RoleController extends \Webvaloa\Application
{
    public function __construct()
    {
        $this->ui->addJS('/js/Role.js');
        $this->ui->addJS('/js/Loader.js');
        $this->ui->addCSS('/css/Loader.css');
        $this->ui->addCSS('/css/Role.css');
        $this->ui->addTemplate('pagination');

        $this->view->token = Security::getToken();
    }

    public function index($page = 1)
    {
        $q = '';

        if (isset($_GET['search'])) {
            $this->view->search = $_GET['search'];
            $q = ' WHERE role LIKE ?';
        }

        $pagination = new Pagination();
        $this->view->pages = $pagination->pages((int) $page, $pagination->countTable('role'));
        $this->view->pages->url = '/user_role/';

        $query = $pagination->prepare('
            SELECT *
            FROM role '.$q);

        $stmt = $this->db->prepare($query);
        try {
            if (isset($q) && !empty($q)) {
                $stmt->set('%'.$_GET['search'].'%');
            }

            $stmt->execute();

            $this->view->roles = $stmt->fetchAll();
        } catch (Exception $e) {
        }
    }

    public function controllers($roleID = false)
    {
        $component = new Component();
        $this->view->components = $component->components();

        if (is_numeric($roleID)) {
            $role = new Role($roleID);
            $roleComponents = $role->components();

            foreach ($this->view->components as $k => $v) {
                if (in_array($v->id, $roleComponents)) {
                    $this->view->components[$k]->selected = 'selected';
                }
            }
        }

        Debug::__print($this->view->components);
    }

    public function add()
    {
        Security::verify();

        if (!isset($_POST['role']) || empty($_POST['role'])) {
            throw new Exception();
        }

        if (isset($_POST['role_id']) && !empty($_POST['role_id'])) {
            // Saving existing role, drop components
            $roleID = (int) $_POST['role_id'];
            $role = new Role($roleID);
            $role->dropComponents();
            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('ROLE_SAVED'));
        } else {
            // Adding new role
            $role = new Role();
            $roleID = $role->addRole($_POST['role']);
            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('ROLE_ADDED'));
        }

        // Add components to role
        if (isset($_POST['components']) && is_array($_POST['components'])) {
            foreach ($_POST['components'] as $k => $v) {
                $component = new Component();
                $component->byID($v);
                $component->addRole($roleID);

                Debug::__print('Adding role '.$roleID.' to component '.$v);
            }
        }

        Redirect::to('user_role');
    }

    public function delete($roleID)
    {
        Security::verify();

        $role = new Role((int) $roleID);
        $role->delete();

        $this->ui->addMessage(\Webvaloa\Webvaloa::translate('ROLE_DELETED'));

        Redirect::to('user_role');
    }
}
