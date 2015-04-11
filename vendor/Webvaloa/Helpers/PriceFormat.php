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

class PriceFormat
{
    /**
     * Format price to 0,00 format.
     *
     * @param mixed $v        numeric value to format
     * @param int   $decimals numbers of decimals, default 2
     * @param int   $accuracy number of decimals to use for round(), default 2
     *
     * @return string
     */
    public static function formatPrice($v, $decimals = 2, $accuracy = 2)
    {
        return str_replace('.', ',', self::formatCountablePrice($v, $decimals, $accuracy));
    }

    /**
     * Format price to (countable) 0.00 format.
     *
     * @param mixed $v        numeric value to format
     * @param int   $decimals numbers of decimals, default 2
     * @param int   $accuracy number of decimals to use for round(), default 2
     *
     * @return float
     */
    public static function formatCountablePrice($v, $decimals = 2, $accuracy = 2)
    {
        return number_format(round(str_replace(',', '.', $v), $accuracy), $decimals, '.', '');
    }

    /**
     * Format price to cents.
     *
     * @param mixed $v numeric value to format
     *
     * @return int
     */
    public static function toCents($v)
    {
        return (int) str_replace('.', '', self::formatCountablePrice($v, 2));
    }
}
