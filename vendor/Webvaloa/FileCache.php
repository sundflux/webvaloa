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
 * Class FileCache.
 */
class FileCache
{
    /**
     * @var int
     */
    private $time;

    /**
     * @var
     */
    private $cache;

    /**
     * @var
     */
    private $file;

    /**
     * @var
     */
    private $expires;

    /**
     * FileCache constructor.
     */
    public function __construct()
    {
        $this->time = time();
        $this->read();
    }

    /**
     *
     */
    private function read()
    {
        $this->file = LIBVALOA_PUBLICPATH.'/cache/.cache';
        $config = new Configuration();

        if (!is_writable($tmp = realpath(dirname($this->file)))) {
            return;
        }

        if (!file_exists($this->file)) {
            $this->cache = new stdClass();

            file_put_contents($this->file, serialize($this->cache));
        }

        if (file_exists($this->file) && is_readable($this->file)) {
            $this->cache = unserialize(file_get_contents($this->file));
        } else {
            // No permissions to write anywhere, write to dummy object
            $this->cache = new stdClass();
        }

        if (!empty($config->cache_time)) {
            $this->expires = $config->cache_time;
        } else {
            // 10 minute session cache
            $this->expires = 600;
        }
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
     * Set key/value pair to caching scope.
     *
     * @param type $key
     * @param type $value
     *
     * @return bool
     */
    public function set($key, $value)
    {
        // Could not write to cache path if we hit this:
        if (!isset($this->cache)) {
            return;
        }

        $this->cache->{$key} = new stdClass();
        $this->cache->{$key}->key = $key;
        $this->cache->{$key}->value = $value;
        $this->cache->{$key}->expires = $this->time + $this->expires;

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
        if (isset($this->cache->{$key})) {
            if ($this->cache->{$key}->expires > $this->time) {
                return $this->cache->{$key}->value;
            }

            unset($this->cache->{$key});
        }

        return false;
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
        $key .= session_id();

        return $this->set($key, $value);
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
        $key .= session_id();

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

    /**
     *
     */
    public function __destruct()
    {
        if (is_writable($this->file)) {
            // Get a copy of the current cache
            $newCache = $this->cache;
            // Read from file in case there were changes after load
            $this->read();
            // Update loaded cache before saving
            foreach ($newCache as $k => $item) {
                if ($item->expires > $this->time) {
                    $this->cache->{$k} = $item;
                }
            }
            file_put_contents($this->file, serialize($this->cache));
        }
    }
}
