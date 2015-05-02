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

namespace Webvaloa;

use Libvaloa\Db;
use stdClass;
use RuntimeException;
use UnexpectedValueException;

/**
 * Handles Webvaloa articles.
 */
class Article
{
    public $article;
    private $loaded;
    private $fieldsloaded;

    const GLOBAL_GROUP_ID = 0;

    /**
     * Constructor, give controller name for actions.
     *
     * @param string $controller
     */
    public function __construct($id = false)
    {
        $this->loaded = false;
        $this->fieldsloaded = false;
        $this->article = new stdClass();

        $this->article->id = $id;
        if (is_numeric($id) || $id === 0) {
            $this->loadArticle();
            $this->loadFields();
            $this->loaded = true;
        } else {
            $this->initEmpty();
        }
    }

    public function initEmpty()
    {
        $this->article->published = 1;
        $this->article->publish_up = 'NOW()';
        $this->article->publish_down = null;
        $this->article->locale = Webvaloa::getLocale();

        if (isset($_SESSION['UserID'])) {
            $this->article->user_id = $_SESSION['UserID'];
        } else {
            $this->article->user_id = null;
        }
    }

    public function __set($k, $v)
    {
        if (isset($this->article->$k)) {
            $this->article->$k = $v;
        }
    }

    public function __get($k)
    {
        if (!$this->loaded) {
            $this->loadArticle();
            $this->loadFields();
        }

        if ($k == 'article') {
            return $this->article;
        }

        if (isset($this->article->$k)) {
            return $this->article->$k;
        }

        return false;
    }

    public function loadArticle()
    {
        if ($this->loaded) {
            return;
        }

        if (!is_numeric($this->article->id)) {
            throw new RuntimeException('Article '.$this->article->id.' not loadable');
        }

        // Global group handling
        if ($this->article->id === 0) {
            $this->loaded = true;

            return;
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT *
            FROM content
            WHERE id = ?
            AND published > -1';

        $stmt = $db->prepare($query);
        $stmt->set((int) $this->article->id);

        try {
            $stmt->execute();
            $this->article = $stmt->fetch();
            $this->loaded = true;
        } catch (Exception $e) {

        }
    }

    public function trash()
    {
        return $this->setPublished(-1);
    }

    public function publish()
    {
        return $this->setPublished(1);
    }

    public function unpublish()
    {
        return $this->setPublished(0);
    }

    public function setTitle($title)
    {
        if (!isset($this->article->id) || !is_numeric($this->article->id) || empty($this->article->id)) {
            throw new RuntimeException('Article not loadable');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            UPDATE content SET title = ?
            WHERE id = ?';

        $stmt = $db->prepare($query);
        $stmt->set($title);
        $stmt->set((int) $this->article->id);

        try {
            $stmt->execute();
        } catch (Exception $e) {

        }
    }

    public function setPublished($i)
    {
        if (!isset($this->article->id) || !is_numeric($this->article->id) || empty($this->article->id)) {
            throw new RuntimeException('Article not loadable');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $values = array(
            -1,
            0,
            1,
        );

        if (!in_array($i, $values)) {
            throw new OutOfBoundsException('Not a valid publish state');
        }

        $query = '
            UPDATE content SET published = ?
            WHERE id = ?';

        $stmt = $db->prepare($query);
        $stmt->set($i);
        $stmt->set((int) $this->article->id);

        try {
            $stmt->execute();
        } catch (Exception $e) {

        }
    }

    public function setPublishUp($i)
    {
        return $this->setPublish($i);
    }

    public function setPublishDown($i)
    {
        return $this->setPublish($i, true);
    }

    private function setPublish($i, $down = false)
    {
        if (!isset($this->article->id) || !is_numeric($this->article->id) || empty($this->article->id)) {
            throw new RuntimeException('Article not loadable');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        if ($down) {
            $d = 'publish_down';
        } else {
            $d = 'publish_up';
        }

        $query = "
            UPDATE content SET {$d} = ?
            WHERE id = ?";

        $stmt = $db->prepare($query);
        $stmt->set($i);
        $stmt->set((int) $this->article->id);
        $stmt->execute();
    }

    public function setAssociation($id)
    {
        if (!isset($this->article->id) || !is_numeric($this->article->id) || empty($this->article->id)) {
            throw new RuntimeException('Article not loadable');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();
        $id = (int) $id;

        $query = '
            UPDATE content SET associated_content_id = ?
            WHERE id = ?';

        $stmt = $db->prepare($query);
        $stmt->set($id);
        $stmt->set((int) $this->article->id);

        try {
            $stmt->execute();
        } catch (Exception $e) {

        }
    }

    public function alias($a)
    {
        if (!isset($this->article->id) || !is_numeric($this->article->id) || empty($this->article->id)) {
            // NOTE: we can't throw exception here or saving global fields fails
            return false;
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $a = preg_replace('/[^A-Za-z0-9\-]/', '', strtolower(str_replace(' ', '-', $a)));

        $query = '
            UPDATE content SET alias = ?
            WHERE id = ?';

        $stmt = $db->prepare($query);
        $stmt->set($a);
        $stmt->set((int) $this->article->id);

        try {
            $stmt->execute();
        } catch (Exception $e) {

        }
    }

    public function loadFields()
    {
        if ($this->fieldsloaded) {
            return;
        }

        if (!is_object($this->article)) {
            return;
        }

        if ((!isset($this->article->id) || !is_numeric($this->article->id) || empty($this->article->id)) && $this->article->id != 0) {
            throw new RuntimeException('Article not loadable');
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT
                field.id as field_id,
                field.name as name,
                field.type as type,
                field.field_group_id as field_group_id,
                content_field_value.id as content_field_value_id,
                content_field_value.value as value,
                content_field_value.locale as locale,
                content_field_value.ordering as repeatable_ordering

            FROM
                field,
                content_field_value

            WHERE
                field.id = content_field_value.field_id
                AND content_field_value.locale = ?
                AND content_field_value.content_id = ?
                ORDER BY content_field_value.id ASC';

        $stmt = $db->prepare($query);
        $stmt->set(Webvaloa::getLocale());
        $stmt->set((int) $this->article->id);

        try {
            $stmt->execute();
            $this->article->fields = $stmt->fetchAll();

            // Sort fields by group
            foreach ($this->article->fields as $k => $field) {
                if (!isset($group[$field->repeatable_ordering])) {
                    $group[$field->repeatable_ordering] = new stdClass();
                }

                $group[$field->repeatable_ordering]->field_group_id = $field->field_group_id;
                $group[$field->repeatable_ordering]->repeatable_ordering = $field->repeatable_ordering;

                // Format field value for viewing
                $fieldClass = '\Webvaloa\Field\Fields\\'.$field->type;
                $f = new $fieldClass($field->field_id);
                $m = 'onLoad';
                if (method_exists($f, $m)) {
                    $field->value = $f->{$m}($field->value);
                }

                $group[$field->repeatable_ordering]->fieldValues[$field->name][] = $field->value;
                $values[$field->name][] = $field->value;
            }

            if (isset($group)) {
                $this->article->fieldValues = $group;
            }

            if (isset($values)) {
                $this->article->values = $values;
            }

            if (!isset($this->article->fieldValues)) {
                $this->article->fieldValues = array();
            }

            $this->fieldsloaded = true;
        } catch (Exception $e) {

        }
    }

    public function insert()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            INSERT INTO content (
                `published` ,
                `publish_up` ,
                `locale` ,
                `user_id`
            )
            VALUES (
                ?,
                NOW(),
                ?,
                ?
            )';

        $stmt = $db->prepare($query);
        $stmt->set($this->article->published);
        $stmt->set($this->article->locale);
        $stmt->set($this->article->user_id);

        try {
            $stmt->execute();
            $this->article->id = $db->lastInsertID();
            $this->loaded = true;

            return $this->article->id;
        } catch (Exception $e) {

        }
    }

    public function addCategory($category_id)
    {
        if (!$this->loaded) {
            $this->loadArticle();
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $c = new Db\Object('content_category', $db);
        $c->category_id = $category_id;
        $c->content_id = $this->article->id;

        return $c->save();
    }

    public function getCategory()
    {
        if (!$this->loaded) {
            $this->loadArticle();
        }

        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT category_id
            FROM content_category
            WHERE content_id = ?
            ORDER BY id ASC';

        $stmt = $db->prepare($query);
        $stmt->set((int) $this->article->id);
        try {
            $stmt->execute();

            foreach ($stmt as $row) {
                $categories[] = $row->category_id;
            }
        } catch (Exception $e) {

        }

        if (isset($categories)) {
            return $categories;
        }

        return false;
    }

    public function getArticles($category_id = false)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $q = '';
        if ($category_id) {
            if (!is_array($category_id)) {
                $category_id = (array) $category_id;
            }

            foreach ($category_id as $k => $v) {
                if (!is_numeric($v)) {
                    throw new UnexpectedValueException('Malformed category id');
                }
            }

            $q = 'AND content_category.category_id IN ( '.implode(',', $category_id).' )';
        }

        $query = "
            SELECT content.id, user_id, publish_up, publish_down, title, locale, published
            FROM content, content_category
            WHERE content.id = content_category.content_id
            AND published > -1
            AND associated_content_id IS NULL
            {$q}
            ORDER BY content.id DESC";

        try {
            $stmt = $db->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (Exception $e) {
        }

        return false;
    }
}
