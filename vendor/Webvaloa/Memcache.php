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

use Webvaloa\MemcachedCache as Memcached;

/**
 * Webvaloa caching class.
 *
 * Note:
 * set() and get() (and their respective magic versions) set stuff to global cache
 *
 * To use session/user specific caching, use _set() and _get() instead.
 */
class Memcache
{
    /**
     * @var
     */
    private $cache;

    /**
     * Memcache constructor.
     */
    public function __construct()
    {
        $config = new Configuration();

        // Settings
        if (!empty($config->memcached_host)) {
            // Memcached driver from libvaloa
            $this->cache = new Memcached();
            $this->cache->properties['host'] = $config->memcached_host;
            $this->cache->properties['port'] = $config->memcached_port;
        } else {
            $this->cache = &$_SESSION['__CACHE__'];
        }

        if (!empty($config->memcached_expires)) {
            $this->cache->properties['expires'] = $config->memcached_expires;
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
        if (method_exists($this->cache, 'set')) {
            return $this->cache->set($key, $value);
        }

        return false;
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
        if (method_exists($this->cache, 'get')) {
            return $this->cache->get($key);
        }

        return false;
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
        if (method_exists($this->cache, 'set')) {
            return $this->cache->set($key, $value);
        }

        return false;
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
        if (method_exists($this->cache, 'get')) {
            return $this->cache->get($key);
        }

        return false;
    }

    /**
     * Set session-specific key/value pair to memcached.
     *
     * @param type $key
     * @param type $value
     *
     * @return bool
     */
    public function _set($key, $value)
    {
        if (method_exists($this->cache, '_set')) {
            return $this->cache->_set($key, $value);
        }

        return false;
    }

    /**
     * Get session-specific cache value by key.
     *
     * @param type $key
     *
     * @return mixed
     */
    public function _get($key)
    {
        if (method_exists($this->cache, '_get')) {
            return $this->cache->_get($key);
        }

        return false;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function delete($key)
    {
        return $this->cache->delete($key);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function _delete($key)
    {
        return $this->cache->_delete($key);
    }
}
