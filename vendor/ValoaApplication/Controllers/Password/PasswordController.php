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
namespace ValoaApplication\Controllers\Password;

use Webvaloa\Controller\Redirect;
use Webvaloa\Auth\Auth;
use Webvaloa\User;
use Webvaloa\Security;
use Webvaloa\Configuration;

class PasswordController extends \Webvaloa\Application
{
    public function __construct()
    {
    }

    public function index()
    {
        $this->view->token = Security::getToken();
    }

    public function save()
    {
        Security::verify();
        $config = new Configuration();

        // Old password must be set
        if (!isset($_POST['old_password']) || empty($_POST['old_password'])) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('PASSWORD_CHANGE_FAILED'));
            Redirect::to('password');
        }

        // Check old password
        $backend = $config->webvaloa_auth;

        $auth = new Auth();
        $auth->setAuthenticationDriver(new $backend());

        if (!$auth->authenticate($_SESSION['User'], $_POST['old_password'])) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('PASSWORD_CHANGE_FAILED'));
            Redirect::to('password');
        }

        // Validate new password
        $check = array(
            'new_password',
            'new_password2',
        );

        foreach ($check as $k => $v) {
            if (!isset($_POST[$v]) || empty($_POST[$v]) || strlen($_POST[$v]) < 8) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('PASSWORD_CHANGE_FAILED'));
                Redirect::to('password');
            }
        }

        if ($_POST['new_password'] != $_POST['new_password']) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('PASSWORD_CHANGE_FAILED'));
            Redirect::to('password');
        }

        // Change password
        $user = new User($_SESSION['UserID']);
        $user->password = trim($_POST['new_password']);

        try {
            $user->save();
            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('PASSWORD_CHANGED'));
        } catch (\Exception $e) {
            $this->ui->addMessage($e->getMessage());
        } finally {
            Redirect::to('password');
        }
    }
}
