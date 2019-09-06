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

use Libvaloa\Debug\Debug;
use Webvaloa\Configuration;

/*
 * Handle templates and template overrides
 */
class PluginTemplatePlugin extends \Webvaloa\Plugin
{
    /*
     * Set the main layout from settings
     */
    public function onAfterFrontControllerInit()
    {
        $template = 'default';

        $configuration = new Configuration();
        if ($configuration->template) {
            $template = $configuration->template;
        }

        $this->_properties['layout'] = $template;
    }

    /*
     * Handle template overrides
     */
    public function onAfterController()
    {
        // Set the override layout (layout == controller XSL)
        if (isset($this->ui->properties['override_layout']) && $this->ui->properties['override_layout'] !== false) {
            // ignoreTemplate lets us tell the UI to ignore default templates with this controller name
            $this->ui->ignoreTemplate($this->request->getChildController());
            $this->ui->ignoreTemplate($this->request->getMainController());

            // ..and override it with value set from the controller
            $this->ui->addTemplate($this->ui->properties['override_layout']);

            Debug::__print('Overriding layout with '.$this->ui->properties['override_layout']);
            Debug::__print($this->ui->properties['override_layout']);
        }

        // Set the override XSL (main .xsl from the template dir)
        if (isset($this->ui->properties['override_template']) && $this->ui->properties['override_template'] !== false) {
            $this->ui->setMainTemplate($this->ui->properties['override_template']);

            Debug::__print('Overriding template with '.$this->ui->properties['override_template']);
        }
    }
}
