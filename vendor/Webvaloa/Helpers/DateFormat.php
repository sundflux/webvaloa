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

/**
 * Class DateFormat
 *
 * @package Webvaloa\Helpers
 */
class DateFormat
{

    /**
     * @param  string $date
     * @return false|string
     */
    public static function toMySQL($date = '')
    {
        if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        if (empty($date)) {
            $date = time();
        }

        return date('Y-m-d H:i:s', $date);
    }

    /**
     * @param  $date
     * @param  $format
     * @return false|string
     */
    public static function format($date, $format)
    {
        if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        return date($format, $date);
    }

    /**
     * @param  $date
     * @param  $format
     * @return string
     */
    public static function localeFormat($date, $format)
    {
        if (!is_numeric($date)) {
            $date = strtotime($date);
        }

        return strftime($format, $date);
    }

    /**
     * @param  $month
     * @return string
     */
    public static function monthName($month)
    {
        return strftime('%B', mktime(null, null, null, (int) $month, 1));
    }
}
