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
namespace ValoaApplication\Plugins;

use Webvaloa\Auth\Auth;
use Webvaloa\User;
use Webvaloa\Role;
use Webvaloa\Configuration;
use Webvaloa\Plugin;
use Webvaloa\Category;
use Webvaloa\Field\Group;
use stdClass;

/**
 * Plugin to show top administrator bar.
 */
class PluginAdministratorPlugin extends \Webvaloa\Plugin
{
    private $showAdminBar;
    private $user;

    public function __construct()
    {
        $this->showAdminBar = false;

        if (isset($_SESSION['UserID'])) {
            // User has to have permission to any of these for admin bar to show.
            $controllers = array(
                'Content',
                'User',
                'Extension',
                'Settings',
            );

            foreach ($controllers as $k => $v) {
                if ($this->authorize($v)) {
                    $this->showAdminBar = true;
                    break;
                }
            }

            $this->user = new User($_SESSION['UserID']);
        }
    }

    private function authorize($controller)
    {
        $backend = \Webvaloa\config::$properties['webvaloa_auth'];

        $auth = new Auth();
        $auth->setAuthenticationDriver(new $backend());

        $userid = (isset($_SESSION['UserID']) ? $_SESSION['UserID'] : false);

        if ($auth->authorize($controller, $userid)) {
            return true;
        }

        return false;
    }

    /**
     * Prepare data for administrator bar after controller is done.
     */
    public function onAfterController()
    {
        if ($this->showAdminBar) {
            $this->ui->addTemplate('PluginAdministratorPlugin');
            $this->ui->addCSS('/css/PluginAdministratorPlugin.css');
            $this->ui->addJS('/js/PluginAdministratorPlugin.js');

            // Configuration
            $config = new Configuration();
            $this->view->_settings = new stdClass();

            // Fixed admin menu
            $this->view->_settings->webvaloa_fixed_administrator_bar = $config->webvaloa_fixed_administrator_bar->value;

            // Custom branding
            $this->view->_settings->webvaloa_branding = $config->webvaloa_branding->value;

            // Hide developer tools
            $this->view->_settings->webvaloa_hide_developer_tools = $config->webvaloa_hide_developer_tools->value;

            // Security token
            $this->view->token = \Webvaloa\Security::getToken();

            // Profile
            $this->view->_name = $this->user->firstname.' '.$this->user->lastname;
            $this->view->_email = $this->user->email;
            $this->view->_gravatar = '//www.gravatar.com/avatar/'.md5(strtolower(trim($this->user->email))).'&s=140';

            // Permissions
            $permissions = new stdClass();

            // Check for admin
            $role = new Role();
            if ($this->user->hasRole($role->getRoleID('Administrator'))) {
                $permissions->isAdmin = '1';
            }

            if ($this->authorize('Content')) {
                $permissions->showQuickAdd = true;
                $permissions->showContent = true;

                $category = new Category();
                $this->view->_shortcuts = $category->getStarred();
            }

            if ($this->authorize('User')) {
                $permissions->showUsers = true;
            }

            if ($this->authorize('Extension')) {
                $permissions->showExtensions = true;
            }

            if ($this->authorize('Settings')) {
                $permissions->showSettings = true;
            }

            if ($this->authorize('Profile')) {
                $permissions->showProfile = true;
            }

            $this->view->_permissions = $permissions;

            // Global groups
            $group = new Group();
            $this->view->_groups = $group->globals();
        }
    }

    /**
     * Inject administrator template to the DOM tree before rendering.
     */
    public function onBeforeRender()
    {
        if ($this->showAdminBar) {
            // Get preprocessed template (all XSL files ready)
            $dom = $this->ui->getPreprocessedTemplateDom();

            // Get body tag from DOM tree
            $body = $dom->getElementsByTagName('body')->item(0);

            // No body found
            if (!$body instanceof \DOMNodeList && !$body instanceof \DOMElement) {
                return;
            }

            // Create <xsl:call-template name="x"> tag
            $injectCallTemplate = $dom->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:call-template');
            $injectCallTemplate->setAttribute('name', 'PluginAdministratorPlugin');
            $injectCallTemplate->setAttribute('mode', 'plugin');

            // And inject it to before first element after body
            $body->insertBefore($injectCallTemplate, $body->getElementsByTagName('*')->item(0));
        }
    }
}
