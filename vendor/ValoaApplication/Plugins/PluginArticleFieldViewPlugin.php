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

use Webvaloa\Helpers\ArticleAssociation;
use Webvaloa\Helpers\ArticleStructure;
use Webvaloa\Field\Value;
use Webvaloa\Field\Field;
use Webvaloa\Field\Fields;
use stdClass;

/**
 * Fetches article data for Article field to view.
 */
class PluginArticleFieldViewPlugin extends \Webvaloa\Plugin
{
    public function onAfterController()
    {
        if (!isset($this->view->article->fields) || (!is_object($this->view->article->fields) && !is_array($this->view->article->fields))) {
            return;
        }

        foreach ($this->view->article->fields as $k => $field) {
            if ($field->type === 'Articlepicker') {
                $id = (int) $field->value;
                // Try loading associated article
                $association = new ArticleAssociation($id);
                $association->setLocale(\Webvaloa\Webvaloa::getLocale());
                if ($associatedID = $association->getAssociatedId()) {
                    $id = $associatedID;
                }
                $structure = new ArticleStructure($id);
                $article = $structure->getArticle();
                if ($article->article !== false) {
                    $this->view->article->fields[$k]->article = new stdClass();
                    $this->view->article->fields[$k]->article->article = $article->article;
                    $this->view->article->fields[$k]->article->fields = $structure->getFields();
                    $this->view->article->fields[$k]->article->fieldTypes = $structure->getFieldTypes();
                }
            }
        }
    }
}
