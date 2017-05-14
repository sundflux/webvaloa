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
namespace ValoaApplication\Controllers\Article;

use Libvaloa\Debug;
use Webvaloa\Cache;
use Webvaloa\Category;
use Webvaloa\Helpers\ArticleAssociation;
use Webvaloa\Helpers\ArticleStructure;
use Webvaloa\Helpers\ContentAccess;
use Webvaloa\Field\Value;
use Webvaloa\Field\Field;
use Webvaloa\Field\Fields;
use Webvaloa\User;

class ViewController extends \Webvaloa\Application
{
    private $cache;

    public function __construct()
    {
        $this->cache = new Cache();
    }

    public function index($id = false)
    {
        // Check if we got alias instead
        if (!is_numeric($id) && strlen($id) > 0) {
            $query = '
                SELECT id
                FROM content
                WHERE alias = ?
                AND published = 1
                ORDER BY id DESC';

            $stmt = $this->db->prepare($query);
            $stmt->set($id);
            $stmt->execute();
            $row = $stmt->fetch();
            if (isset($row->id)) {
                $id = $row->id;
            }
        }

        // If requesting without id, return default
        if ($id === false || empty($id)) {
            if (isset($this->view->_globals->default_front_page[0])) {
                $id = $this->view->_globals->default_front_page[0]->value;
            } else {
                $id = false;
            }
        }

        // If requesting
        if (!is_numeric($id)) {
            if (isset($this->view->_globals->default_404_page[0])) {
                $id = $this->view->_globals->default_404_page[0]->value;
            } else {
                $id = false;
            }
        }

        // Fallback 404
        if ($id === false) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        // Try loading associated article
        $association = new ArticleAssociation($id);
        $association->setLocale(\Webvaloa\Webvaloa::getLocale());
        if ($associatedID = $association->getAssociatedId()) {
            $id = $associatedID;
        }

        $structure = new ArticleStructure($id);
        $article = $structure->getArticle();
        if ($article->article === false) {
            if (isset($this->view->_globals->default_404_page[0])) {
                $id = $this->view->_globals->default_404_page[0]->value;
                $article = new Article($id);
            } else {
                header('HTTP/1.0 404 Not Found');
                exit;
            }
        }

        if (!$this->checkPermissions($article)) {
            Debug::__print('Oops, no permissions');

            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $this->view->id = $this->view->articleID = $id;
        $this->view->article = $article->article;

        // Set template overrides
        $categoryID = $structure->getCategoryId();
        $category = new Category($categoryID);
        $category->loadCategory();
        $this->view->categoryID = $categoryID;

        // Template override
        if ($tmp = $category->getTemplate()) {
            $tmp = str_replace('.xsl', '', $tmp);
            if ($tmp && !empty($tmp)) {
                $this->ui->properties['override_template'] = $tmp;
            }
        }

        // Layout override
        if ($tmp = $category->getLayout()) {
            $tmp = str_replace('.xsl', '', $tmp);
            if ($tmp && !empty($tmp)) {
                $this->ui->properties['override_layout'] = $tmp;
            }
        }

        // Load field structure
        $this->view->fields = $structure->getFields();
        $this->view->fieldsObjects = $this->view->fields;

        if (is_object($this->view->fieldsObjects)) {
            foreach ($this->view->fieldsObjects as $fieldsKey => $fields) {
                foreach ($fields->repeatable_group->repeatable as $repeatableKey => $repeatable) {
                    foreach ($repeatable->fields as $fieldName => $field) {
                        if (!isset($this->view->fieldsObjects[$fieldsKey]->repeatable_group->repeatable[$repeatableKey]->fieldsObject)) {
                            $this->view->fieldsObjects[$fieldsKey]->repeatable_group->repeatable[$repeatableKey]->fieldsObject = new \stdClass();
                        }

                        $this->view->fieldsObjects[$fieldsKey]->repeatable_group->repeatable[$repeatableKey]->fieldsObject->{$fieldName} = $field;
                    }
                }
            }
        }

        $this->view->fieldTypes = $structure->getFieldTypes();
    }

    private function checkPermissions($article)
    {
        try {
            $contentAccess = new ContentAccess($article);
            return $contentAccess->checkPermissions();
        } catch(\RuntimeException $e) {
            Debug::__print($e->getMessage());
        } catch(\Exception $e) {
            Debug::__print($e->getMessage());
        }

        return false;
    }
}
