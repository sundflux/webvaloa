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
use Webvaloa\Article;
use Webvaloa\Category;
use Webvaloa\Helpers\Article as ArticleHelper;
use Webvaloa\Helpers\ArticleAssociation;
use Webvaloa\Field\Group;
use Webvaloa\Field\Value;
use Webvaloa\Field\Field;
use Webvaloa\Field\Fields;
use stdClass;

class ViewController extends \Webvaloa\Application
{
    private $cache;

    public function __construct()
    {
        $this->cache = new Cache();
    }

    public function index($id = false)
    {
        $group = new Group();
        $value = new Value();
        $globals = $group->globals();
        $globalValues = array();
        foreach ($globals as $global) {
            $globalGroup = new Group($global->id);
            $fields = $globalGroup->fields();
            foreach ($fields as $field) {
                $globalValues[$field->name] = $value->getValues($field->id);
            }
        }
        $this->view->globals = $globalValues;

        //var_dump($globalValues);
        //die();

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
            if (isset($globalValues['default_front_page'][0])) {
                $id = $globalValues['default_front_page'][0]->value;
            } else {
                $id = false;
            }
        }

        // If requesting
        if (!is_numeric($id)) {
            if (isset($globalValues['default_404_page'][0])) {
                $id = $globalValues['default_404_page'][0]->value;
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

        $article = new Article($id);
        if ($article->article === false) {
            if (isset($globalValues['default_404_page'][0])) {
                $id = $globalValues['default_404_page'][0]->value;
                $article = new Article($id);
            } else {
                header('HTTP/1.0 404 Not Found');
                exit;
            }
        }
        //$articleHelper = new ArticleHelper($id);
        $this->view->id = $id;
        $this->view->articleID = $id;
        $this->view->article = $article->article; //$articleHelper->article;

        // Set template overrides
        $catId = $article->getCategory();

        $category = new Category($catId[0]);
        $category->loadCategory();

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

        $this->initializeFieldsView($catId[0]);

        Debug::__print($this->view);
    }

    private function initializeFieldsView($categoryID)
    {
        $category = new Category($categoryID);
        $this->view->category = $category->category;
        // Always include these fields:
        $this->view->fieldTypes[] = 'Datetimepicker';
        $groups = $category->groups();
        $fields = $category->fields();
        // Initialize empty fields for groups
        foreach ($groups as $k => $v) {
            // Keep index by field group id
            if (@!isset($index[$v->field_group_id])) {
                $groupindex[$v] = new stdClass();
                $groupindex[$v]->i = 0;
            }
            $i = $groupindex[$v]->i;
            if (!isset($repeatables[$v])) {
                $repeatables[$v] = new stdClass();
            }
            if (!isset($repeatables[$v]->repeatable[$i])) {
                $repeatables[$v]->repeatable[$i] = new stdClass();
            }
            foreach ($fields as $field) {
                if ($v != $field->field_group_id) {
                    continue;
                }
                $repeatables[$v]->repeatable[$i]->fields[$field->name] = clone $field;
                $repeatables[$v]->repeatable[$i]->fields[$field->name]->uniqid = uniqid();
                $repeatables[$v]->repeatable[$i]->fields[$field->name]->values[0] = '';
                // Get params
                $fieldClass = '\Webvaloa\Field\Fields\\'.$field->type;

                $f = new $fieldClass($field->id, $this->view->articleID);
                $repeatables[$v]->repeatable[$i]->fields[$field->name]->params = $f->getParams();
                // Collect field types
                $this->view->fieldTypes[] = $field->type;
            }
            $i++;
            $groupindex[$v]->i = $i;
        }
        // Put data to fields
        if (isset($this->view->article->fieldValues)) {
            foreach ($this->view->article->fieldValues as $repeatable => $v) {
                // Keep index by field group id
                if (!isset($index[$v->field_group_id])) {
                    $index[$v->field_group_id] = new stdClass();
                    $index[$v->field_group_id]->i = 0;
                }
                $i = $index[$v->field_group_id]->i;
                if (!isset($repeatables[$v->field_group_id])) {
                    $repeatables[$v->field_group_id] = new stdClass();
                }
                if (!isset($repeatables[$v->field_group_id]->repeatable[$i])) {
                    $repeatables[$v->field_group_id]->repeatable[$i] = new stdClass();
                }
                // First group is always guaranteed to have initial field schema.
                // However repeated groups might differ IF the saved field is empty,
                // so make sure every field inside the repeated groups has at LEAST
                // the initial fields.
                if ($i > 0 && isset($repeatables[$v->field_group_id]->repeatable[0]->fields)) {
                    foreach ($repeatables[$v->field_group_id]->repeatable[0]->fields as $initialField => $initialFieldObject) {
                        if (!isset($v->fieldValues[$initialField])) {
                            $v->fieldValues[$initialField][0] = '';
                        }
                    }
                }
                // Fill values to fields
                foreach ($v->fieldValues as $fieldName => $value) {
                    if (!is_object($fields[$fieldName])) {
                        continue;
                    }
                    $field = clone $fields[$fieldName];
                    $field->values = $value;
                    $field->uniqid = uniqid();
                    // Fields with repeatable > 1 are 'special',
                    // data is loaded with ajax or other methods.
                    if ($field->repeatable > 1) {
                        $field->values = '';
                    }
                    foreach ($field as $_fieldName => $_fieldValue) {
                        if (!isset($repeatables[$v->field_group_id]->repeatable[$i]->fields[$fieldName])) {
                            $repeatables[$v->field_group_id]->repeatable[$i]->fields[$fieldName] = new stdClass();
                        }
                        $repeatables[$v->field_group_id]->repeatable[$i]->fields[$fieldName]->$_fieldName = $_fieldValue;
                    }
                }
                $i++;
                $index[$v->field_group_id]->i = $i;
            }
        }
        // Sort groups and set repeatables data to group
        foreach ($groups as $k => $v) {
            try {
                $group = new Group($v);
                $tmp[$v] = new stdClass();
                $tmp[$v]->id = $v;
                $tmp[$v]->uniqid = uniqid();
                $tmp[$v]->name = $group->name;
                $tmp[$v]->translation = $group->translation;
                $tmp[$v]->repeatable = $group->repeatable;
                if (isset($repeatables[$v])) {
                    $tmp[$v]->repeatable_group = $repeatables[$v];
                }
            } catch (OutOfBoundsException $e) {
                // Group assigned to this groups has been deleted
                Debug::__print('Group not found!');
                Debug::__print($e->getMessage);
            }
        }

        // Put fields to view
        $this->view->fields = $tmp;
    }
}
