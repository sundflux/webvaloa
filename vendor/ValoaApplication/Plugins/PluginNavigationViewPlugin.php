<?php

/**
 * The Initial Developer of the Original Code is
 * Toni Lähdekorpi <toni@lygon.net>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2016 Toni Lähdekorpi <toni@lygon.net>
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

use stdClass;
use Webvaloa\Helpers\Navigation;

/**
 * Load global fields to view.
 */
class PluginNavigationViewPlugin extends \Webvaloa\Plugin
{
    public function onBeforeController()
    {
        $this->ui->addTemplate('navigation');
        $navigation = new Navigation();
        $this->view->_navigation = new stdClass();
        $this->view->_navigation->basepath = $this->request->getBasePath();
        $this->view->_navigation->navigation = $navigation->get();
    }
}
