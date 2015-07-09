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

namespace Webvaloa\Helpers;

use Webvaloa\Field\Field;
use stdClass;

/**
 * Category helper.
 */
class Category
{
    private $id;
    private $publish_up;
    private $publish_down;
    private $publish_null;
    private $published;
    private $page;
    private $locale;
    private $limit;
    private $fieldFilters;

    /**
     * Constructor, give controller name for actions.
     *
     * @param string $controller
     */
    public function __construct($id = false)
    {
        $this->id = $id;
        $this->publish_up = DateFormat::toMySQL(time());
        $this->publish_down = $this->publish_up;
        $this->publish_null = '0000-00-00 00:00:00';
        $this->published = 1;
        $this->page = 1;
        $this->limit = 10;
        $this->locale = \Webvaloa\Webvaloa::getLocale();
        $this->fieldFilters = false;
    }

    public function __set($k, $v)
    {
        if (isset($this->$k)) {
            $this->$k = $v;
        }
    }

    public function __get($k)
    {
        if (isset($this->$k)) {
            return $this->$k;
        }
    }

    public function addFieldFilter($fieldName, $value)
    {
        $field = new Field();
        $f = $field->findByName($fieldName);

        // Field not found
        if (!isset($f->id) || !$f->id) {
            return false;
        }

        $this->fieldFilters[$f->id] = $value;
    }

    public function getArticles()
    {
        $pagination = new Pagination();

        $db = \Webvaloa\Webvaloa::DBConnection();

        // Include content field value in query
        $filter = '';
        $q = '';

        if ($this->fieldFilters) {
            $filter = ', content_field_value';

            foreach ($this->fieldFilters as $k => $v) {
                $q .= '
                    AND content.id = content_field_value.content_id
                    AND content_field_value.field_id = '.(int)$k.'
                    AND content_field_value.value = ?  ';
            }
        }

        $queryCount = '
            SELECT COUNT(content.id) as total
            FROM content, content_category, category'.$filter.'
            WHERE
            content.publish_up <= ?
            AND (content.publish_down <= ? OR content.publish_down = ?)
            AND content.published = ?
            AND content.locale = ?
            AND content.id = content_category.content_id
            AND content_category.category_id = category.id
            AND category.deleted = 0
            AND category.id = ?
            '.$q.'
            ORDER BY content.publish_up DESC';

        $query = '
            SELECT content.*
            FROM content, content_category, category'.$filter.'
            WHERE
            content.publish_up <= ?
            AND (content.publish_down <= ? OR content.publish_down = ?)
            AND content.published = ?
            AND content.locale = ?
            AND content.id = content_category.content_id
            AND content_category.category_id = category.id
            AND category.deleted = 0
            AND category.id = ?
            '.$q.'
            ORDER BY content.publish_up DESC';

        try {
            $stmt = $db->prepare($queryCount);
            $stmt->set($this->publish_up);
            $stmt->set($this->publish_down);
            $stmt->set($this->publish_null);
            $stmt->set((int) $this->published);
            $stmt->set($this->locale);
            $stmt->set((int) $this->id);
            foreach($this->fieldFilters as $k => $v) {
				$stmt->set($v);
			}
            $stmt->execute();
            $count = $stmt->fetch();

            $retval = new stdClass();
            $retval->pages = $pagination->pages((int) $this->page, $count->total, $this->limit);

            $query = $pagination->prepare($query);
            $stmt = $db->prepare($query);
            $stmt->set($this->publish_up);
            $stmt->set($this->publish_down);
            $stmt->set($this->publish_null);
            $stmt->set((int) $this->published);
            $stmt->set($this->locale);
            $stmt->set((int) $this->id);
            foreach($this->fieldFilters as $k => $v) {
				$stmt->set($v);
			}
            $stmt->execute();

            $retval->items = $stmt->fetchAll();
        } catch (Exception $e) {
        }

        if (isset($retval)) {
            return $retval;
        }

        return false;
    }
}
