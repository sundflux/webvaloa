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

namespace ValoaApplication\Controllers\Login;

use Libvaloa\Debug\Debug;
use Webvaloa\Controller\Redirect;
use Webvaloa\User;
use Webvaloa\Mail\Mail;
use Webvaloa\Configuration;
use Webvaloa\Security;
use stdClass;
use UnexpectedValueException;

class PasswordresetController extends \Webvaloa\Application
{
    public $message;

    public function __construct()
    {
        $this->ui->addCSS('/css/Login.css');

        $this->message = '';
    }

    public function index()
    {
        $this->view->token = Security::getToken();

        $config = new Configuration();
        $this->view->config = new stdClass();

        // Custom branding
        if ($config->webvaloa_branding) {
            $this->view->config->webvaloa_branding = $config->webvaloa_branding;
        }

        // Custom branding
        if ($config->enable_registration) {
            $this->view->config->enable_registration = $config->enable_registration;
        }

        // Site name
        if ($config->site_name) {
            $this->view->config->site_name = $config->site_name;
        }

        // Send password reset request
        if (isset($_POST['username']) && !empty($_POST['username'])) {
            Security::verify();

            // User not found
            if (User::usernameAvailable($_POST['username'])) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('USER_NOT_FOUND'));

                return;
            }

            // Get user id
            $query = '
                SELECT id, email
                FROM user
                WHERE login = ?';

            $stmt = $this->db->prepare($query);
            $stmt->set($_POST['username']);

            try {
                $stmt->execute();
                $row = $stmt->fetch();

                // User not found
                if (!isset($row->id)) {
                    $this->ui->addError(\Webvaloa\Webvaloa::translate('USER_NOT_FOUND'));

                    return;
                }

                // Check for site configuration
                $configuration = new Configuration();

                $admin = $configuration->webmaster_email->value;
                if (empty($admin)) {
                    $this->ui->addError(\Webvaloa\Webvaloa::translate('WEBMASTER_EMAIL_NOT_SET'));
                    Redirect::to('login_passwordreset');
                }

                $sitename = $configuration->sitename->value;
                if (empty($sitename)) {
                    $this->ui->addError(\Webvaloa\Webvaloa::translate('SITENAME_NOT_SET'));
                    Redirect::to('login_passwordreset');
                }

                // Write reset hash to user metadata
                $user = new User($row->id);
                $hash = sha1(uniqid(rand(10000, 99999)));
                $user->metadata('PasswordResetTime', time());
                $user->metadata('PasswordResetHash', $hash);
                $user->save();

                // Send the mail
                $link = $this->request->getBaseUri().'/login_passwordreset/verify/'.base64_encode($row->id.':'.$hash);

                // Allow overriding the message with plugins
                if (!isset($this->message) || empty($this->message)) {
                    $this->message = \Webvaloa\Webvaloa::translate('RESET_PASSWORD_MAIL_1');
                    $this->message .= '<br><br>';
                    $this->message .= '<a href="'.$link.'"> '.\Webvaloa\Webvaloa::translate('RESET_PASSWORD').' </a>';
                    $this->message .= '<br><br>';
                    $this->message .= \Webvaloa\Webvaloa::translate('RESET_PASSWORD_MAIL_2');
                }

                $mailer = new Mail();
                $send = $mailer->setTo($row->email, $user->firstname.' '.$user->lastname)
                    ->setSubject(\Webvaloa\Webvaloa::translate('RESET_PASSWORD_CONFIRM').' '.$this->request->getBaseUri())
                    ->setFrom($admin, $sitename)
                    ->addGenericHeader('X-Mailer', 'Webvaloa')
                    ->addGenericHeader('Content-Type', 'text/html; charset="utf-8"')
                    ->setMessage($this->message)
                    ->setWrap(100)
                    ->send();

                $val = (string) $send;

                if (!$val) {
                    $this->ui->addError(\Webvaloa\Webvaloa::translate('MAIL_SENDING_FAILED'));
                    Redirect::to('login_passwordreset');
                }

                $this->ui->addMessage(\Webvaloa\Webvaloa::translate('PASSWORD_RESET_REQUEST_SENT'));
            } catch (Exception $e) {
            }
        }
    }

    public function verify($hash = false)
    {
        $this->view->hash = $hash;

        if (!$hash) {
            throw new UnexpectedValueException($this->ui->addError(\Webvaloa\Webvaloa::translate('HASH_MISSING')));
        }

        $data = explode(':', base64_decode($hash));
        $user = new User((int) $data[0]);
        $userhash = $user->metadata('PasswordResetHash');

        Debug::__print('Hashes:');
        Debug::__print($data);
        Debug::__print($userhash);

        if (!isset($userhash) || empty($userhash) || $userhash != $data[1]) {
            throw new UnexpectedValueException($this->ui->addError(\Webvaloa\Webvaloa::translate('HASH_NOT_MATCH')));
        }

        if ($user->blocked > 0) {
            throw new UnexpectedValueException($this->ui->addError(\Webvaloa\Webvaloa::translate('USER_BLOCKED')));
        }

        if (isset($_POST['password']) && !empty($_POST['password'])) {
            if (!isset($_POST['password']) || empty($_POST['password']) || strlen($_POST['password']) < 8) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('PASSWORD_TOO_SHORT'));
                Redirect::to('login_passwordreset/verify/'.$hash);
            }

            if (!isset($_POST['password2']) || $_POST['password'] != $_POST['password2']) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('CHECK_PASSWORD'));
                Redirect::to('login_passwordreset/'.$hash);
            }

            // All good, set password and unblock the user
            $user->password = $_POST['password'];
            $user->metadata('PasswordResetHash', '');
            $user->save();

            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('PASSWORD_CHANGED'));

            $config = new Configuration();
            Redirect::to($config->default_controller);
        }
    }
}
