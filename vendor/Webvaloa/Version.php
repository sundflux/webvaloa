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

use stdClass;
use Configuration;

class Version
{
    private $object;

    const MAX_VERSIONS = 10;
    const MAX_VERSIONS_HARD_LIMIT = 128;

    private $max_versions;

    public function __construct()
    {
        $this->object = new stdClass();
        $this->object->target_table = '';
        $this->object->target_id = 0;
        $this->object->content = '';
        $this->object->user_id = 0;

        if (isset($_SESSION['UserID'])) {
            $this->object->user_id = $_SESSION['UserID'];
        }

        $configuration = new Configuration();
        $this->max_versions = $configuration->max_versions_history;

        if (!is_numeric($this->max_versions)) {
            $this->max_versions = self::MAX_VERSIONS;
        }

        if ($this->max_versions > self::MAX_VERSIONS_HARD_LIMIT) {
            $this->max_versions = self::MAX_VERSIONS_HARD_LIMIT;
        }
    }

    public function __set($k, $v)
    {
        if (isset($this->object->$k)) {
            if ($k == 'content') {
                $v = serialize($v);
            }

            $this->object->$k = $v;
        }
    }

    public function save()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            INSERT INTO version_history (target_table, target_id, content, user_id)
            VALUES (?, ?, ? ,?)';

        try {
            $stmt = $db->prepare($query);
            $stmt->set($this->object->target_table);
            $stmt->set((int) $this->object->target_id);
            $stmt->set($this->object->content);
            $stmt->set((int) $this->object->user_id);

            $stmt->execute();
        } catch (Exception $e) {
        }

        // Clean excess versions
        $this->checkMaxVersions();
    }

    private function checkMaxVersions()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT id
            FROM version_history
            WHERE target_table = ?
            AND target_id = ?
            ORDER BY id
            DESC';

        $stmt = $db->prepare($query);

        try {
            $stmt->set($this->object->target_table);
            $stmt->set((int) $this->object->target_id);
            $stmt->execute();

            $i = 0;
            foreach ($stmt as $row) {
                ++$i;

                if ($i > $this->max_versions) {
                    $q = '
                        DELETE FROM version_history
                        WHERE id = ?';

                    $stmt2 = $db->prepare($q);
                    $stmt2->set((int) $row->id);
                    $stmt2->execute();
                }
            }
        } catch (Exception $e) {
        }
    }

    public function getVersions()
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT id, created
            FROM version_history
            WHERE target_table = ?
            AND target_id = ?
            ORDER BY id
            DESC
            LIMIT ' . $this->max_versions;

        $stmt = $db->prepare($query);
        try {
            $stmt->set($this->object->target_table);
            $stmt->set((int) $this->object->target_id);
            $stmt->execute();

            foreach ($stmt as $row) {
                $rows[] = $row;
            }

            if (isset($rows)) {
                return $rows;
            }

            return false;
        } catch (Exception $e) {
        }
    }

    public function loadVersion($id)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = '
            SELECT content
            FROM version_history
            WHERE id = ?';

        $stmt = $db->prepare($query);
        try {
            $stmt->set((int) $id);
            $stmt->execute();

            $row = $stmt->fetch();

            if (isset($row->content) && !empty($row->content)) {
                return unserialize($row->content);
            }

            return false;
        } catch (Exception $e) {
        }
    }
}
