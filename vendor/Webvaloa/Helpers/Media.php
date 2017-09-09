<?php

/**
 * The Initial Developer of the Original Code is
 * Tarmo Alexander Sundström <ta@sundstrom.im>.
 *
 * Portions created by the Initial Developer are
 * Copyright (C) 2016 Tarmo Alexander Sundström <ta@sundstrom.im>
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

class Media
{
    public static function getTitle($filename)
    {
        $string = self::getField('title', $filename);
        if ($string === false) {
            $string = '';
        }

        return $string;
    }

    public static function getAlt($filename)
    {
        $string = self::getField('alt', $filename);
        if ($string === false) {
            $string = '';
        }

        return $string;
    }

    private static function getField($field, $filename)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $fields = array('alt', 'title');

        if (!in_array($field, $fields)) {
            return '';
        }

        $query = "SELECT `{$field}` FROM media WHERE `filename` = ? LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->set($filename);
        try {
            $stmt->execute();

            $row = $stmt->fetch();
            if (isset($row->{$field})) {
                return $row->{$field};
            } else {
                return false;
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    public static function setTitle($filename, $text = '')
    {
        self::setField($filename, $text, 'title');
    }

    public static function setAlt($filename, $text = '')
    {
        self::setField($filename, $text, 'alt');
    }

    public static function setField($filename, $text = '', $field = '')
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $fields = array('alt', 'title');

        if (!in_array($field, $fields)) {
            return '';
        }

        if (empty($filename)) {
            return false;
        }

        $query = "UPDATE media SET `{$field}` = ? WHERE `filename` = ?";

        if (self::exists($filename) === false) {
            $query = "INSERT INTO media (`{$field}`, `filename`) VALUES (?, ?) ";
        }

        $stmt = $db->prepare($query);
        $stmt->set($text);
        $stmt->set($filename);

        try {
            $stmt->execute();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    private static function exists($filename)
    {
        $db = \Webvaloa\Webvaloa::DBConnection();

        $query = 'SELECT id FROM media WHERE `filename` = ? LIMIT 1';
        $stmt = $db->prepare($query);
        $stmt->set($filename);
        try {
            $stmt->execute();

            $row = $stmt->fetch();
            if (isset($row->id)) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }
}
