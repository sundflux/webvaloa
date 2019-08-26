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

use Libvaloa\Debug\Debug;

/**
 * Interface ICache.
 */
interface ICache
{
    public function set($key, $value);
    public function get($key);
    public function _set($key, $value);
    public function _get($key);
    public function delete($key);
    public function _delete($key);
}

/**
 * Class Cache.
 */
class Cache implements ICache
{
    /**
     * @var
     */
    private $cache;

    /**
     * Cache constructor.
     */
    public function __construct()
    {
        $config = new Configuration();

        // Default to file cache for global cache
        $backend = '\Webvaloa\FileCache';

        // Default to session cache for local cache
        $backendLocal = '\Webvaloa\SessionCache';

        // Set global cache driver
        if (!empty($config->cache_driver)) {
            $backend = $config->cache_driver;
        }

        // Set local cache driver
        if (!empty($config->local_cache_driver)) {
            $backendLocal = $config->local_cache_driver;
        }

        $this->cache = new $backend();
        $this->cacheLocal = new $backendLocal();

        Debug::__print('Using '.$backend.' cache backend');
        Debug::__print('Using '.$backendLocal.' local cache backend');
    }

    /**
     * Set key/value pair to global caching scope.
     * Alias for set().
     *
     * @param type $key
     * @param type $value
     *
     * @return bool
     */
    public function __set($key, $value)
    {
        $this->cache->set($key, $value);
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
        return $this->cache->get($key);
    }

    /**
     * Set key/value pair to global caching scope.
     *
     * @param type $key
     * @param type $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        $this->cache->set($key, $value);

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
        return $this->cache->get($key);
    }

    /**
     * Set key/value pair to local caching scope.
     *
     * @param type $key
     * @param type $value
     *
     * @return bool
     */
    public function _set($key, $value)
    {
        $this->cacheLocal->_set($key, $value);

        return $value;
    }

    /**
     * Get local cache value by key.
     *
     * @param type $key
     *
     * @return mixed
     */
    public function _get($key)
    {
        return $this->cacheLocal->_get($key);
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
