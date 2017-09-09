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

use Webvaloa\Security;

class ContentMediapickerPlugin extends \Webvaloa\Plugin
{
    public function onBeforeController()
    {
        $this->ui->addTemplate('ContentMediapickerPlugin');
        $this->ui->addCSS('/css/Loader.css');
        $this->ui->addCSS('/css/Content_Media.css');
        $this->ui->addJS('/js/Loader.js');
        $this->ui->addJS('/jquery/plugins/jquery.form.js');
        $this->ui->addJS('/jquery/plugins/jquery.uploadfile.js');
        $this->ui->addJS('/jquery/plugins/jquery.lazyload.js');
        $this->ui->addJS('/js/Content_Media.js');
        $this->view->mediaPath = LIBVALOA_PUBLICPATH.'/media';
        $this->view->token = Security::getToken();
    }

    /**
     * Inject administrator template to the DOM tree before rendering.
     */
    public function onBeforeRender()
    {
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
        $injectCallTemplate->setAttribute('name', 'ContentMediapickerPlugin');
        $injectCallTemplate->setAttribute('mode', 'plugin');

        // And inject it to before first element after body
        $body->insertBefore($injectCallTemplate, $body->getElementsByTagName('*')->item(0));
    }
}
