<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.io>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2011 Tarmo Alexander Sundström <ta@sundstrom.io>
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

use Libvaloa\Db;
use PDO;
use stdClass;

/**
 * Class Pagination.
 */
class Pagination
{
    /**
     * @var
     */
    private $pages;

    /**
     * Get pagination.
     *
     * @param int $page  Current page
     * @param int $total Total number of entries
     * @param int $limit Entries per page
     *
     * @return object $this->pages Page counts n stuff
     */
    public function pages($page = 1, $total = 0, $limit = 10)
    {
        $this->pages = new stdClass();

        // First page if not defined
        if (!$page) {
            $page = 1;
        }

        // Total number of entries
        $this->pages->entries = $total;

        // Count total number of pages
        $this->pages->pages = ceil($total / ($limit));

        // Take in account case of having last post
        // 20 of 20 posts for example, so it doesn't
        // leak to nonexisting page.
        if ($page > $this->pages->pages) {
            $page = $this->pages->pages;
        }

        // Current page
        $this->pages->page = $page;

        // Previous page
        if ($page > 1) {
            $this->pages->pagePrev = $page - 1;
        }

        // Next page
        if ((($page) * $limit) < $total) {
            $this->pages->pageNext = $page + 1;
        }

        if ($this->pages->page == $this->pages->pages) {
            $this->pages->last = true;
        }

        // Offset.
        $this->pages->offset = 0;
        if ($page > 0) {
            $this->pages->offset = (int) ($page * $limit) - $limit;
        }

        $this->pages->limit = (int) $limit;

        return $this->pages;
    }

    /**
     * @param $query
     *
     * @return string
     */
    public function prepare($query)
    {
        return $query.' LIMIT '.(int) $this->pages->limit.' OFFSET '.(int) $this->pages->offset;
    }

    /**
     * @param $table
     * @param string $where
     *
     * @return int
     */
    public function countTable($table, $where = '')
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $object = new Db\Item($table, $db);
        $pk = $object->primaryKey;

        if (!$pk || empty($pk)) {
            // Table was not found or doesn't have a primary key
            return 0;
        }

        // Since we can't bind table names with PDO, just to be noted
        // this is an unescaped query. However getting past Db\Item
        // it should be safe, and this function should generally be
        // called from trusted controllers.

        $query = '
            SELECT COUNT(?) as c
            FROM '.$table.' '.$where;

        $stmt = $db->prepare($query);
        $stmt->set($pk);

        try {
            $stmt->execute();

            $row = $stmt->fetch();
            if (isset($row->c)) {
                return $row->c;
            }

            return 0;
        } catch (Exception $e) {
        }
    }
}
