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

/**
 * Class SessionCache.
 */
class SessionCache
{
    /**
     * @var int
     */
    private $time;

    /**
     * 10 minute session cache.
     *
     * @var int
     */
    private $expires = 600;

    /**
     * SessionCache constructor.
     */
    public function __construct()
    {
        $config = new Configuration();

        $this->time = time();

        if (!isset($_SESSION['cache'])) {
            $_SESSION['cache'] = new stdClass();
        }

        if (!empty($config->cache_time)) {
            $this->expires = $config->cache_time;
        }
    }

    /**
     * Set key/value pair to global memcached caching scope.
     * Alias for set().
     *
     * @param type $key
     * @param type $value
     *
     * @return bool
     */
    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Get global cache value by key.
     * Alias for get().
     *
     * @param type $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Set key/value pair to global memcached caching scope.
     *
     * @param type $key
     * @param type $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        if (!isset($_SESSION['cache'])) {
            $_SESSION['cache'] = new stdClass();
        }

        $_SESSION['cache']->{$key} = new stdClass();
        $_SESSION['cache']->{$key}->key = $key;
        $_SESSION['cache']->{$key}->value = $value;
        $_SESSION['cache']->{$key}->expires = $this->time + $this->expires;

        return $value;
    }

    /**
     * Get global cache value by key.
     *
     * @param type $key
     *
     * @return mixed
     */
    public function get($key)
    {
        if (isset($_SESSION['cache']->{$key})) {
            if ($_SESSION['cache']->{$key}->expires > $this->time) {
                return $_SESSION['cache']->{$key}->value;
            }

            unset($_SESSION['cache']->{$key});
        }

        return false;
    }

    /**
     * Session cache is always local, so just alias to set().
     */
    public function _set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Session cache is always local, so just alias to get().
     */
    public function _get($key)
    {
        return $this->get($key);
    }

    /**
     * @param $key
     */
    public function delete($key)
    {
        // TODO
    }

    /**
     * @param $key
     */
    public function _delete($key)
    {
        // TODO
    }
}
