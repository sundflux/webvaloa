<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.io>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2014 Tarmo Alexander Sundström <ta@sundstrom.io>
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

namespace Webvaloa\Helpers;

use Libvaloa\Debug\Debug;
use Webvaloa\Category as CategoryHelper;
use Webvaloa\Field\Group;
use Webvaloa\Field\Field;
use Webvaloa\Field\Fields;
use Webvaloa\Article as ArticleHelper;
use stdClass;
use RuntimeException;

/**
 * Class ArticleStructure.
 */
class ArticleStructure
{
    /**
     * @var bool
     */
    public $id;

    /**
     * @var bool
     */
    public $associatedId;

    /**
     * @var
     */
    public $categoryId;

    /**
     * @var stdClass
     */
    public $article;

    /**
     * @var
     */
    public $fields;

    /**
     * ArticleStructure constructor.
     *
     * @param bool $articleId
     */
    public function __construct($articleId = false)
    {
        $this->id = $articleId;
        $this->associatedId = false;
        $this->article = new stdClass();

        if ($articleId) {
            $this->loadArticle($this->id);
        }
    }

    /**
     *
     */
    private function loadArticle()
    {
        if (!is_numeric($this->id)) {
            throw new RuntimeException('Could not load article');
        }

        // Try loading associated article
        $association = new ArticleAssociation($this->id);
        $association->setLocale(\Webvaloa\Webvaloa::getLocale());
        $id = $this->id;
        if ($associatedId = $association->getAssociatedId()) {
            $id = $associatedId;
            $this->associatedId = $associatedId;
        }

        $this->article = new ArticleHelper($id);
        $categories = $this->article->getCategory();

        if (!empty($categories[0])) {
            $this->initializeFieldsView($categories[0]);
            $this->categoryId = $categories[0];
        }
    }

    /**
     * @return bool
     */
    public function getArticleId()
    {
        return $this->id;
    }

    /**
     * @return stdClass
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @return bool
     */
    public function getCategory()
    {
        if (isset($this->fields->category)) {
            return $this->fields->category;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getFields()
    {
        if (isset($this->fields->fields)) {
            return $this->fields->fields;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getFieldTypes()
    {
        if (isset($this->fields->fieldTypes)) {
            return $this->fields->fieldTypes;
        }

        return false;
    }

    /**
     * @param bool $categoryId
     *
     * @return stdClass
     */
    public function initializeFieldsView($categoryId = false)
    {
        $_fields = new stdClass();

        if (!$categoryId) {
            $categoryId = $this->categoryId;
        }

        $category = new CategoryHelper($categoryId);
        $_fields->category = $category->category;

        // Always include these fields:
        $_fields->fieldTypes[] = 'Datetimepicker';

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

                // Articleid not set when adding new one
                if (!isset($this->articleId)) {
                    $this->articleId = false;
                }

                $f = new $fieldClass($field->id, $this->articleId);
                $repeatables[$v]->repeatable[$i]->fields[$field->name]->params = $f->getParams();

                // Collect field types
                $_fields->fieldTypes[] = $field->type;
            }

            ++$i;
            $groupindex[$v]->i = $i;
        }

        // Put data to fields
        if (isset($this->article->article->fieldValues)) {
            foreach ($this->article->article->fieldValues as $repeatable => $v) {
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
                    if (!isset($fields[$fieldName]) || !is_object($fields[$fieldName])) {
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

                ++$i;
                $index[$v->field_group_id]->i = $i;
            }
        } else {
            Debug::__print('Notice: This article has no fieldValues');
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

        if (isset($tmp)) {
            $_fields->fields = $tmp;
        }
        $this->fields = $_fields;

        return $this->fields;
    }
}
