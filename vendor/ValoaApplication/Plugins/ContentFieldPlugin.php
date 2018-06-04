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

use Libvaloa\Debug;
use Webvaloa\Helpers\Path;
use DOMDocument;
use DOMXpath;

/**
 * Handle including fields in article edit view.
 */
class ContentFieldPlugin extends \Webvaloa\Plugin
{
    /**
     * Extend UI include paths and add Fields path there.
     */
    public function onBeforeController()
    {
        if ($this->request->getChildController() !== 'Article') {
            return;
        }
        $pathHelper = new Path;
        foreach ($pathHelper->getSystemPaths() as $path) {
            $this->ui->addIncludePath($path.'/'.'Webvaloa'.'/'.'Field'.'/'.'Fields');
        }
    }

    /**
     * Include all field XSL templates on the article editing page
     * before rendering it.
     */
    public function onBeforeRender()
    {
        if ($this->request->getChildController() !== 'Article') {
            return;
        }

        if (!isset($this->view->fieldTypes) || empty($this->view->fieldTypes)) {
            return;
        }

        // Template dom
        $dom = $this->ui->getPreprocessedTemplateDom();

        // Get XSL templates
        $xpath = new DOMXpath($dom);
        $includes = $xpath->query('xsl:include');

        if ($includes->length == 0) {
            Debug::__print('xsl:includes not found');

            return;
        }

        // Look for controller template
        foreach ($includes as $include) {
            $template = $include->getAttribute('href');

            // Read the template XSL for injecting
            $pos = strpos($template, '/Controllers/Content/Views/Article.xsl');

            if ($pos !== false) {
                // Remove the xsl:include
                $include->parentNode->removeChild($include);

                // Load controller template
                $templateDom = new DOMDocument();
                $templateDom->load($template);

                $xpath = new DOMXpath($templateDom);
                $templateInjectEls = $xpath->query("//*[@id='injectholder']");

                if ($templateInjectEls->length == 0) {
                    Debug::__print('injectholder not found');
                }

                foreach ($templateInjectEls as $templateInjectEl) {
                    foreach ($this->view->fieldTypes as $k => $field) {
                        $fragment = $templateDom->createDocumentFragment();
                        $fragment->appendXml($this->getField($field));
                        $templateInjectEl->parentNode->insertBefore($fragment, $templateInjectEl);
                    }

                    // Remove inject tag
                    $templateInjectEl->parentNode->removeChild($templateInjectEl);
                }

                // Inject controller template back to dom
                foreach ($templateDom->documentElement->childNodes as $node) {
                    $importNode = $dom->importNode($node, true);
                    $dom->documentElement->appendChild($importNode);
                }
            }
        }
    }

    /**
     * Wrapper template to run call:templates for every field on the page.
     *
     * @todo rewrite this with DOM?
     *
     * @param type $field
     *
     * @return type
     */
    private function getField($field)
    {
        $xsl = '
        <xsl:if test="type = \'{FIELDNAME}\'" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
            <xsl:for-each select="values" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
                <div class="repeatable-holder">
                    <xsl:call-template name="{FIELDNAME}" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
                        <xsl:with-param name="id" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:value-of select="../id" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" /></xsl:with-param>
                        <xsl:with-param name="uniqid" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:value-of select="../uniqid" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" /></xsl:with-param>
                        <xsl:with-param name="name" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:value-of select="../name" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/></xsl:with-param>
                        <xsl:with-param name="translation" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:value-of select="../translation" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/></xsl:with-param>
                        <xsl:with-param name="value" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:value-of select="." xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/></xsl:with-param>
                        <xsl:with-param name="default_value" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:value-of select="../default_value" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/></xsl:with-param>
                        <xsl:with-param name="validation" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:value-of select="../validation" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/></xsl:with-param>
                        <xsl:with-param name="params" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"><xsl:value-of select="../params" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/></xsl:with-param>
                    </xsl:call-template>

                    <xsl:if test="position() &gt; 1" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
                        <xsl:call-template name="delete-button" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>
                    </xsl:if>
                </div>
            </xsl:for-each>
        </xsl:if>';

        return str_replace('{FIELDNAME}', $field, $xsl);
    }
}
