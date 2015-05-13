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

namespace ValoaApplication\Controllers\Register;

use Libvaloa\Debug;
use Webvaloa\Controller\Redirect;
use Webvaloa\Auth\Auth;
use Webvaloa\Cache;
use Webvaloa\User;
use Webvaloa\Role;
use Webvaloa\Mail\Mail;
use Webvaloa\Configuration;
use stdClass;
use UnexpectedValueException;
use RuntimeException;

class RegisterController extends \Webvaloa\Application
{
    public $message;

    private $cache;
    private $user;
    private $mail;
    private $admin;
    private $sitename;

    public function __construct()
    {
        $this->cache = new Cache();

        // Check for site configuration
        $configuration = new Configuration();
        $this->admin = $configuration->webmaster_email->value;
        $this->sitename = $configuration->sitename->value;
    }

    public function index()
    {
        if ($tmp = $this->cache->_get('registration')) {
            foreach ($tmp as $k => $v) {
                $this->view->$k = $v;
            }
        }
    }

    public function register()
    {
        Debug::__print($_POST);

        // Cache post
        $this->cache->_set('registration',  $_POST);

        // Validate inputs
        $require = array(
            'firstname',
            'lastname',
            'email',
            'confirm_email',
        );

        foreach ($require as $k => $v) {
            if (!isset($_POST[$v]) || empty($_POST[$v])) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('SOMETHING_MISSING'));
                Redirect::to('register');
            }
        }

        $email = trim($_POST['email']);
        $confirm = trim($_POST['confirm_email']);

        if ($email != $confirm) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('EMAILS_DONT_MATCH'));
            Redirect::to('register');
        }

        if (!\Webvaloa\User::usernameAvailable($email)) {
            // Check if user is still stuck in registration limbo
            $user = new User();
            $user->byEmail($email);
            $token = $user->metadata('token');
            Debug::__print($token);

            if (isset($token) && !empty($token) && $user->blocked == 1) {
                // User has token, and is blocked, resend the email.

                // Url for verifying the account
                $link = $this->request->getBaseUri().'/register/verify/'.base64_encode($user->id.':'.$token);

                // Send registration email
                $this->sendEmail($email, $user->firstname, $user->lastname, $link);

                Redirect::to('register/info');
            }

            // Nope, show error..
            $this->ui->addError(\Webvaloa\Webvaloa::translate('USERNAME_TAKEN'));
            Redirect::to('register');
        }

        if (empty($this->admin)) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('WEBMASTER_EMAIL_NOT_SET'));
            Redirect::to('register');
        }

        if (empty($this->sitename)) {
            $this->ui->addError(\Webvaloa\Webvaloa::translate('SITENAME_NOT_SET'));
            Redirect::to('register');
        }

        // All good beyond this point

        // Hash for verification
        $hash = sha1(time().rand(0, 9).microtime());

        // Create user
        $user = new User();
        $user->login = $user->email = $email;

        if (isset($_SESSION['locale']) && !empty($_SESSION['locale'])) {
            $user->locale = $_SESSION['locale'];
        } else {
            $user->locale = 'en_US';
        }

        $user->firstname = $_POST['firstname'];
        $user->lastname = $_POST['lastname'];
        $user->password = null;
        $user->blocked = 1;

        $meta = new stdClass();
        $meta->token = $hash;
        $user->meta = json_encode($meta);

        // Insert user
        $userID = $user->save();

        // Add registered role for the user
        $user = new User($userID);

        $role = new Role();
        $user->addRole($role->getRoleID('Registered'));

        // Url for verifying the account
        $link = $this->request->getBaseUri().'/register/verify/'.base64_encode($userID.':'.$hash);

        // Send registration email
        $this->sendEmail($email, $_POST['firstname'], $_POST['lastname'], $link);

        Redirect::to('register/info');
    }

    private function sendEmail($email, $firstname, $lastname, $link)
    {
        // Allow overriding the message with plugins
        if (!isset($this->message) ||  empty($this->message)) {
            $this->message = \Webvaloa\Webvaloa::translate('VERIFY_ACCOUNT_MAIL_1');
            $this->message .= '<br><br>';
            $this->message .= '<a href="'.$link.'"> '.\Webvaloa\Webvaloa::translate('VERIFY_ACCOUNT').' </a>';
            $this->message .= '<br><br>';
            $this->message .= \Webvaloa\Webvaloa::translate('VERIFY_ACCOUNT_MAIL_2');
        }

        try {
            $mailer = new Mail();
            $send = $mailer->setTo($email, $firstname.' '.$lastname)
                    ->setSubject(\Webvaloa\Webvaloa::translate('REGISTRATION_CONFIRM'))
                    ->setFrom($this->admin, $this->sitename)
                    ->addGenericHeader('MIME-Version', '1.0')
                    ->addGenericHeader('X-Mailer', 'Webvaloa')
                    ->addGenericHeader('Content-Type', 'text/html; charset="utf-8"')
                    ->setMessage($this->message)
                    ->setWrap(100)
                    ->send();

            $val = (string) $send;

            if (!$val) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('MAIL_SENDING_FAILED'));
                Redirect::to('register');
            }
        } catch (\InvalidArgumentException $e) {
            Debug::__print('Sending failed');
            Debug::__print($e->getMessage());
            Debug::__print($e);

            $this->ui->addError(\Webvaloa\Webvaloa::translate('MAIL_SENDING_FAILED'));

            Redirect::to('register');
        } catch (\Exception $e) {
            $this->ui->addError($e->getMessage());

            Redirect::to('register');
        }
    }

    public function info()
    {
    }

    public function verify($hash = false)
    {
        $this->view->hash = $hash;

        if (!$hash) {
            throw new UnexpectedValueException($this->ui->addError(\Webvaloa\Webvaloa::translate('HASH_MISSING')));
        }

        $data = explode(':', base64_decode($hash));
        $user = new User((int) $data[0]);
        $token = $user->metadata('token');

        if (!isset($token) || empty($token) || $token != $data[1]) {
            throw new UnexpectedValueException($this->ui->addError(\Webvaloa\Webvaloa::translate('HASH_NOT_MATCH')));
        }

        if ($user->blocked != 1) {
            throw new UnexpectedValueException($this->ui->addError(\Webvaloa\Webvaloa::translate('PASSWORD_ALREADY_SET')));
        }

        // Token matches

        if (isset($_POST['password'])) {
            if (!isset($_POST['password']) || empty($_POST['password']) || strlen($_POST['password']) < 8) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('PASSWORD_TOO_SHORT'));
                Redirect::to('register/verify/'.$hash);
            }

            if (!isset($_POST['password2']) || $_POST['password'] != $_POST['password2']) {
                $this->ui->addError(\Webvaloa\Webvaloa::translate('CHECK_PASSWORD'));
                Redirect::to('register/verify/'.$hash);
            }

            // All good, set password and unblock the user
            $user->password = $_POST['password'];
            $user->blocked = 0;
            $meta->token = '';
            $user->meta = json_encode($meta);
            $user->save();

            $this->ui->addMessage(\Webvaloa\Webvaloa::translate('READY'));

            // Login the user after verification
            try {
                $backend = \Webvaloa\config::$properties['webvaloa_auth'];

                $auth = new Auth();
                $auth->setAuthenticationDriver(new $backend());

                if (!$auth->authenticate($user->login, $_POST['password'])) {
                    throw new RuntimeException(\Webvaloa\Webvaloa::translate('LOGIN_FAILED'));
                }
            } catch (RuntimeException $e) {
                $this->ui->addError($e->getMessage());
            }

            Redirect::to(\Webvaloa\config::$properties['default_controller']);
        }
    }
}
