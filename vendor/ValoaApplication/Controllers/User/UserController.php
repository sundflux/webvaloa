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
use Webvaloa\Controller\Redirect;
use Webvaloa\User;
use Webvaloa\Role;
use Webvaloa\Security;
use Webvaloa\Helpers\Pagination;
use RuntimeException;
use stdClass;

class UserController extends \Webvaloa\Application
{
    public function __construct()
    {
        $this->ui->addJS('/js/Loader.js');
        $this->ui->addJS('/js/User.js');
        $this->ui->addCSS('/css/User.css');
        $this->ui->addCSS('/css/Loader.css');
        $this->ui->addTemplate('pagination');

        $this->view->token = Security::getToken();
        $this->role = new Role();

        if (file_exists(WEBVALOA_BASEDIR.'/config/usermeta.json')) {
            $this->view->usermeta = 1;
        }
    }

    private function usermeta()
    {
        $usermeta = WEBVALOA_BASEDIR.'/config/usermeta.json';

        if (!is_readable($usermeta)) {
            return false;
        }

        $file = file_get_contents($usermeta);
        $json = json_decode($file, true);

        return $json;
    }

    public function index($page = 1)
    {
        $q = '';

        if (isset($_GET['search'])) {
            $this->view->search = $_GET['search'];
            $q = ' WHERE login LIKE ? OR email LIKE ? OR firstname LIKE ? or lastname LIKE ?';
        }

        $this->view->roles = $this->role->roles();

        $pagination = new Pagination();
        $this->view->pages = $pagination->pages((int) $page, $pagination->countTable('user'));
        $this->view->pages->url = '/user/';

        $query = $pagination->prepare('
            SELECT *
            FROM user '.$q);

        $stmt = $this->db->prepare($query);
        try {
            if (isset($q) && !empty($q)) {
                $stmt->set('%'.$_GET['search'].'%');
                $stmt->set('%'.$_GET['search'].'%');
                $stmt->set('%'.$_GET['search'].'%');
                $stmt->set('%'.$_GET['search'].'%');
            }

            $stmt->execute();

            $users = $stmt->fetchAll();

            foreach ($users as $k => $v) {
                $this->view->users[$k] = $v;
                $this->view->users[$k]->gravatar = '//www.gravatar.com/avatar/'.md5(strtolower(trim($v->email))).'?s=32';
            }
        } catch (Exception $e) {
        }

        if (isset($_SESSION['UserController'])) {
            foreach ($_SESSION['UserController'] as $k => $v) {
                $this->view->$k = $v;
            }

            unset($_SESSION['UserController']);
        }

        $this->view->user_id = $_SESSION['UserID'];
    }

    public function edit()
    {
        Security::verify();

        $_SESSION['UserController'] = $_POST;

        if (empty($_POST['firstname'])) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('FIRSTNAME_REQUIRED'));

            return;
        }

        if (empty($_POST['lastname'])) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('LASTNAME_REQUIRED'));

            return;
        }

        $user = new User($_POST['id']);

        $check = array(
            'password',
            'password2',
        );

        $user->email = $_POST['email'];

        if (isset($_POST['username']) && !empty($_POST['username'])) {
            $user->login = $_POST['username'];
        } else {
            $user->login = $_POST['email'];
        }

        if (!empty($_POST['password']) && !empty($_POST['password2'])) {
            foreach ($check as $k => $v) {
                if (!isset($_POST[$v]) || empty($_POST[$v]) || strlen($_POST[$v]) < 8) {
                    $this->ui->addError(\Webvaloa\Webvaloa::translate('PASSWORD_TOO_SHORT'));
                    Redirect::to('user');
                }
            }

            if ($_POST['password'] != $_POST['password2']) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('CHECK_PASSWORD'));
                Redirect::to('user');
            }

            $user->password = $_POST['password'];
        }

        if (isset($_SESSION['locale']) && !empty($_SESSION['locale'])) {
            $user->locale = $_SESSION['locale'];
        } else {
            $user->locale = 'en_US';
        }

        $user->firstname = $_POST['firstname'];
        $user->lastname = $_POST['lastname'];

        if (file_exists(WEBVALOA_BASEDIR.'/config/usermeta.json')) {
            $json = $this->usermeta();

            foreach ($json as $key => $v) {
                $user->metadata($key, $_POST[$key]);
            }
        }

        $user->blocked = 0;
        $userID = $user->save();

        // Add Registered role for the user
        $user = new User($userID);

        $user->dropRoles();

        $role = new Role();
        $user->addRole($role->getRoleID('Registered'));

        if (isset($_POST['roles'])) {
            foreach ($_POST['roles'] as $k => $v) {
                $user->addRole($v);
            }
        }

        unset($_SESSION['UserController']);

        $this->ui->addMessage(\Webvaloa\Webvaloa::translate('USER_EDITED'));
        Redirect::to('user');
    }

    public function roles($userID = false)
    {
        $role = new Role();
        $this->view->_roles = $role->roles();
        $this->view->_userid = $userID;

        foreach ($this->view->_roles as $k => $v) {
            if (isset($v->selected)) {
                unset($v->selected);
            }

            $this->view->_roles[$k] = $v;
        }
        Debug::__print($this->view->_roles);

        if (is_numeric($userID)) {
            $user = new User($userID);
            $userRoles = $user->roles();
            Debug::__print($userRoles);

            foreach ($this->view->_roles as $k => $v) {
                if (in_array($v->id, $userRoles)) {
                    $this->view->_roles[$k]->selected = 'selected';
                }
            }
        }

        Debug::__print($this->view->_roles);
    }

    public function meta($userID = false)
    {
        $json = $this->usermeta();

        if ($json === false) {
            return false;
        }

        if (is_numeric($userID)) {
            $user = new User($userID);
            foreach ($json as $k => $v) {
                $meta = new stdClass();
                $meta->name = $k;
                $meta->value = $user->metadata($k);

                $this->view->meta[] = $meta;
            }
        } else {
            foreach ($json as $k => $v) {
                $meta = new stdClass();
                $meta->name = $k;

                $this->view->meta[] = $meta;
            }
        }
    }

    public function add()
    {
        Security::verify();

        $_SESSION['UserController'] = $_POST;

        if (empty($_POST['firstname'])) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('FIRSTNAME_REQUIRED'));

            return;
        }

        if (empty($_POST['lastname'])) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('LASTNAME_REQUIRED'));

            return;
        }

        $check = array(
            'password',
            'password2',
        );

        foreach ($check as $k => $v) {
            if (!isset($_POST[$v]) || empty($_POST[$v]) || strlen($_POST[$v]) < 8) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('PASSWORD_TOO_SHORT'));
                Redirect::to('user');
            }
        }

        if ($_POST['password'] != $_POST['password2']) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('CHECK_PASSWORD'));
            Redirect::to('user');
        }

        // Create user
        $user = new User();
        $user->email = $_POST['email'];

        if (isset($_POST['username']) && !empty($_POST['username'])) {
            $user->login = $_POST['username'];
        } else {
            $user->login = $_POST['email'];
        }

        if (isset($_SESSION['locale']) && !empty($_SESSION['locale'])) {
            $user->locale = $_SESSION['locale'];
        } else {
            $user->locale = 'en_US';
        }

        $user->firstname = $_POST['firstname'];
        $user->lastname = $_POST['lastname'];
        $user->password = $_POST['password'];
        $user->blocked = 0;
        $userID = $user->save();

        // Save meta for user
        if (file_exists(WEBVALOA_BASEDIR.'/config/usermeta.json')) {
            $json = $this->usermeta();
            $user = new User($userID);

            foreach ($json as $key => $v) {
                $user->metadata($key, $_POST[$key]);
            }

            $userID = $user->save();
        }

        // Add Registered role for the user
        $user = new User($userID);

        $role = new Role();
        $user->addRole($role->getRoleID('Registered'));

        if (isset($_POST['roles'])) {
            foreach ($_POST['roles'] as $k => $v) {
                $user->addRole($v);
            }
        }

        unset($_SESSION['UserController']);

        $this->ui->addMessage(\Webvaloa\Webvaloa::translate('USER_ADDED'));
        Redirect::to('user');
    }

    public function delete($id = false)
    {
        Security::verify();

        if ($id == $_SESSION['UserID']) {
            throw new RuntimeException(\Webvaloa\Webvaloa::translate('CANNOT_DELETE_SELF'));
        }

        $user = new User($id);
        $user->delete();

        $this->ui->addMessage(\Webvaloa\Webvaloa::translate('USER_DELETED'));
        Redirect::to('user');
    }
}
