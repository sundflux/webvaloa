<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2015 Tarmo Alexander Sundström <ta@sundstrom.im>
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

class Field
{
    public function formatName($name)
    {
        $name = trim($name);
        $name = str_replace(' ', '_', $name); // automatically undersore spaces
        $name = preg_replace('/[^A-Za-z0-9_]/i', '', $name); // remove all other chars than A-Za-Z_

        return $name;
    }

    public function fieldExists($name, $group = false)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        if (!$group) {
            $table = 'field';
        } else {
            $table = 'field_group';
        }

        $query = '
            SELECT COUNT(id) as c
            FROM '.$table.'
            WHERE name = ?';

        $stmt = $db->prepare($query);
        $stmt->set($name);

        try {
            $stmt->execute();
            $row = $stmt->fetch();

            return $row->c;
        } catch (Exception $e) {
        }

        // Field not found
        return 0;
    }

    public function groupExists($name)
    {
        return $this->fieldExists($name, true);
    }
}
