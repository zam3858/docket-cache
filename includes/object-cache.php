<?php
/**
 * @wordpress-plugin
 * Plugin Name:         Docket Cache Drop-in
 * Plugin URI:          http://wordpress.org/plugins/docket-cache/
 * Version:             20.07.17
 * Description:         A file-based persistent WordPress Object Cache stored as a plain PHP code.
 * Author:              Nawawi Jamili
 * Author URI:          https://rutweb.com
 * Requires at least:   5.4
 * Requires PHP:        7.2.5
 * License:             MIT
 * License URI:         https://opensource.org/licenses/MIT
 */

/**
 * Based on:
 *  wp-includes/cache.php
 *  wp-includes/class-wp-object-cache.php.
 */

/**
 * Check if caching is not disabled.
 * If false, prevent functions and classes from being defined.
 * See wp_start_object_cache() -> wp-includes/load.php.
 */
if (!\defined('DOCKET_CACHE_HALT') && \defined('DOCKET_CACHE_DISABLED') && DOCKET_CACHE_DISABLED) {
    \define('DOCKET_CACHE_HALT', true);
}

/*
 * Check for minimum php version.
 * If not match, prevent functions and classes from being defined.
 * See wp_start_object_cache() -> wp-includes/load.php.
 */
if (!\defined('DOCKET_CACHE_HALT') && version_compare(PHP_VERSION, '7.2.5', '<')) {
    \define('DOCKET_CACHE_HALT', true);
}

/*
 * Determine if we can load Docket Cache Library.
 * If failed, prevent functions and classes from being defined.
 * See wp_start_object_cache() -> wp-includes/load.php.
 */
if (!\defined('DOCKET_CACHE_HALT')) {
    if (!\defined('WP_CONTENT_DIR')) {
        \define('WP_CONTENT_DIR', ABSPATH.'wp-content');
    }

    if (!\defined('WP_PLUGIN_DIR')) {
        \define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
    }

    if (!\defined('DOCKET_CACHE_AUTOLOAD')) {
        \define('DOCKET_CACHE_AUTOLOAD', WP_PLUGIN_DIR.'/docket-cache/includes/load.php');
    }

    if (is_file(DOCKET_CACHE_AUTOLOAD) && is_readable(DOCKET_CACHE_AUTOLOAD)) {
        include_once DOCKET_CACHE_AUTOLOAD;
    }

    if (!class_exists('Nawawi\\DocketCache\\Plugin') || !class_exists('Nawawi\\DocketCache\\Constans')) {
        \define('DOCKET_CACHE_HALT', true);
    }
}

if (!\defined('DOCKET_CACHE_HALT') || !DOCKET_CACHE_HALT) :
    /**
     * Adds data to the cache, if the cache key doesn't already exist.
     *
     * @see WP_Object_Cache::add()
     *
     * @global WP_Object_Cache $wp_object_cache Object cache global instance.
     *
     * @param int|string $key    the cache key to use for retrieval later
     * @param mixed      $data   the data to add to the cache
     * @param string     $group  Optional. The group to add the cache to. Enables the same key
     *                           to be used across groups. Default empty.
     * @param int        $expire Optional. When the cache data should expire, in seconds.
     *                           Default 0 (no expiration).
     *
     * @return bool true on success, false if cache key and group already exist
     */
    function wp_cache_add($key, $data, $group = '', $expire = 0)
    {
        global $wp_object_cache;

        return $wp_object_cache->add($key, $data, $group, (int) $expire);
    }

    /**
     * Closes the cache.
     *
     * This function has ceased to do anything since WordPress 2.5. The
     * functionality was removed along with the rest of the persistent cache. This
     * does not mean that plugins can't implement this function when they need to
     * make sure that the cache is cleaned up after WordPress no longer needs it.
     *
     * @return bool Always returns True
     */
    function wp_cache_close()
    {
        return true;
    }

    /**
     * Decrements numeric cache item's value.
     *
     * @see WP_Object_Cache::decr()
     *
     * @global WP_Object_Cache $wp_object_cache Object cache global instance.
     *
     * @param int|string $key    the cache key to decrement
     * @param int        $offset Optional. The amount by which to decrement the item's value. Default 1.
     * @param string     $group  Optional. The group the key is in. Default empty.
     *
     * @return int|false the item's new value on success, false on failure
     */
    function wp_cache_decr($key, $offset = 1, $group = '')
    {
        global $wp_object_cache;

        return $wp_object_cache->decr($key, $offset, $group);
    }

    /**
     * Removes the cache contents matching key and group.
     *
     * @see WP_Object_Cache::delete()
     *
     * @global WP_Object_Cache $wp_object_cache Object cache global instance.
     *
     * @param int|string $key   what the contents in the cache are called
     * @param string     $group Optional. Where the cache contents are grouped. Default empty.
     *
     * @return bool true on successful removal, false on failure
     */
    function wp_cache_delete($key, $group = '')
    {
        global $wp_object_cache;

        return $wp_object_cache->delete($key, $group);
    }

    /**
     * Removes all cache items.
     *
     * @see WP_Object_Cache::flush()
     *
     * @global WP_Object_Cache $wp_object_cache Object cache global instance.
     *
     * @return bool true on success, false on failure
     */
    function wp_cache_flush()
    {
        global $wp_object_cache;

        return $wp_object_cache->flush();
    }

    /**
     * Retrieves the cache contents from the cache by key and group.
     *
     * @see WP_Object_Cache::get()
     *
     * @global WP_Object_Cache $wp_object_cache Object cache global instance.
     *
     * @param int|string $key   the key under which the cache contents are stored
     * @param string     $group Optional. Where the cache contents are grouped. Default empty.
     * @param bool       $force Optional. Whether to force an update of the local cache from the persistent
     *                          cache. Default false.
     * @param bool       $found Optional. Whether the key was found in the cache (passed by reference).
     *                          Disambiguates a return of false, a storable value. Default null.
     *
     * @return bool|mixed False on failure to retrieve contents or the cache
     *                    contents on success
     */
    function wp_cache_get($key, $group = '', $force = false, &$found = null)
    {
        global $wp_object_cache;

        return $wp_object_cache->get($key, $group, $force, $found);
    }

    /**
     * Retrieves multiple values from the cache in one call.
     *
     * @see WP_Object_Cache::get_multiple()
     *
     * @global WP_Object_Cache $wp_object_cache Object cache global instance.
     *
     * @param array  $keys  array of keys under which the cache contents are stored
     * @param string $group Optional. Where the cache contents are grouped. Default empty.
     * @param bool   $force Optional. Whether to force an update of the local cache
     *                      from the persistent cache. Default false.
     *
     * @return array array of values organized into groups
     */
    function wp_cache_get_multiple($keys, $group = '', $force = false)
    {
        global $wp_object_cache;

        return $wp_object_cache->get_multiple($keys, $group, $force);
    }

    /**
     * Increment numeric cache item's value.
     *
     * @see WP_Object_Cache::incr()
     *
     * @global WP_Object_Cache $wp_object_cache Object cache global instance.
     *
     * @param int|string $key    the key for the cache contents that should be incremented
     * @param int        $offset Optional. The amount by which to increment the item's value. Default 1.
     * @param string     $group  Optional. The group the key is in. Default empty.
     *
     * @return int|false the item's new value on success, false on failure
     */
    function wp_cache_incr($key, $offset = 1, $group = '')
    {
        global $wp_object_cache;

        return $wp_object_cache->incr($key, $offset, $group);
    }

    /**
     * Sets up Object Cache Global and assigns it.
     *
     * @global WP_Object_Cache $wp_object_cache
     */
    function wp_cache_init()
    {
        global $wp_object_cache;
        if (!($wp_object_cache instanceof WP_Object_Cache)) {
            $wp_object_cache = new WP_Object_Cache();
        }
    }

    /**
     * Replaces the contents of the cache with new data.
     *
     * @see WP_Object_Cache::replace()
     *
     * @global WP_Object_Cache $wp_object_cache Object cache global instance.
     *
     * @param int|string $key    the key for the cache data that should be replaced
     * @param mixed      $data   the new data to store in the cache
     * @param string     $group  Optional. The group for the cache data that should be replaced.
     *                           Default empty.
     * @param int        $expire Optional. When to expire the cache contents, in seconds.
     *                           Default 0 (no expiration).
     *
     * @return bool False if original value does not exist, true if contents were replaced
     */
    function wp_cache_replace($key, $data, $group = '', $expire = 0)
    {
        global $wp_object_cache;

        return $wp_object_cache->replace($key, $data, $group, (int) $expire);
    }

    /**
     * Saves the data to the cache.
     *
     * Differs from wp_cache_add() and wp_cache_replace() in that it will always write data.
     *
     * @see WP_Object_Cache::set()
     *
     * @global WP_Object_Cache $wp_object_cache Object cache global instance.
     *
     * @param int|string $key    the cache key to use for retrieval later
     * @param mixed      $data   the contents to store in the cache
     * @param string     $group  Optional. Where to group the cache contents. Enables the same key
     *                           to be used across groups. Default empty.
     * @param int        $expire Optional. When to expire the cache contents, in seconds.
     *                           Default 0 (no expiration).
     *
     * @return bool true on success, false on failure
     */
    function wp_cache_set($key, $data, $group = '', $expire = 0)
    {
        global $wp_object_cache;

        return $wp_object_cache->set($key, $data, $group, (int) $expire);
    }

    /**
     * Switches the internal blog ID.
     *
     * This changes the blog id used to create keys in blog specific groups.
     *
     * @see WP_Object_Cache::switch_to_blog()
     *
     * @global WP_Object_Cache $wp_object_cache Object cache global instance.
     *
     * @param int $blog_id site ID
     */
    function wp_cache_switch_to_blog($blog_id)
    {
        global $wp_object_cache;

        $wp_object_cache->switch_to_blog($blog_id);
    }

    /**
     * Adds a group or set of groups to the list of global groups.
     *
     * @see WP_Object_Cache::add_global_groups()
     *
     * @global WP_Object_Cache $wp_object_cache Object cache global instance.
     *
     * @param string|array $groups a group or an array of groups to add
     */
    function wp_cache_add_global_groups($groups)
    {
        global $wp_object_cache;

        $wp_object_cache->add_global_groups($groups);
    }

    /**
     * Adds a group or set of groups to the list of non-persistent groups.
     *
     * @param string|array $groups a group or an array of groups to add
     */
    function wp_cache_add_non_persistent_groups($groups)
    {
        global $wp_object_cache;
        $wp_object_cache->add_non_persistent_groups($groups);
    }

    function wp_cache_stats()
    {
        global $wp_object_cache;
        $wp_object_cache->stats();
    }

    /**
     * Core class that implements an object cache.
     */
    class WP_Object_Cache
    {
        /**
         * Holds the cached objects.
         *
         * @var array
         */
        private $cache = [];

        /**
         * The amount of times the cache data was already stored in the cache.
         *
         * @var int
         */
        public $cache_hits = 0;

        /**
         * Amount of times the cache did not have the request in cache.
         *
         * @var int
         */
        public $cache_misses = 0;

        /**
         * List of global cache groups.
         *
         * @var array
         */
        protected $global_groups = [];

        /**
         * List of non persistent groups.
         *
         * @var array
         */
        protected $non_persistent_groups = [];

        /**
         * List of non persistent keys.
         *
         * @var array
         */
        protected $non_persistent_keys = [];

        /**
         * The blog prefix to prepend to keys in non-global groups.
         *
         * @var string
         */
        private $blog_prefix;

        /**
         * Holds the value of is_multisite().
         *
         * @var bool
         */
        private $multisite;

        /**
         * The cache path.
         *
         * @var string
         */
        private $cache_path;

        /**
         * The cache maximum time-to-live.
         *
         * @var int
         */
        private $cache_maxttl = 0;

        /**
         * The cache maximum size of cache file.
         *
         * @var int
         */
        private $cache_maxsize = 5000000;

        /**
         * List of filtered groups.
         *
         * @var array
         */
        private $filtered_groups = false;

        /**
         * Filesystem() instance.
         *
         * @var object
         */
        private $filesystem;

        /**
         * Sets up object properties.
         */
        public function __construct()
        {
            $this->multisite = is_multisite();
            $this->blog_prefix = $this->switch_to_blog(get_current_blog_id());
            $this->docket_init();
        }

        /**
         * Adds data to the cache if it doesn't already exist.
         *
         * @uses WP_Object_Cache::_exists() Checks to see if the cache already has data.
         * @uses WP_Object_Cache::set()     Sets the data after the checking the cache
         *                                  contents existence.
         *
         * @param int|string $key    what to call the contents in the cache
         * @param mixed      $data   the contents to store in the cache
         * @param string     $group  Optional. Where to group the cache contents. Default 'default'.
         * @param int        $expire Optional. When to expire the cache contents. Default 0 (no expiration).
         *
         * @return bool true on success, false if cache key and group already exist
         */
        public function add($key, $data, $group = 'default', $expire = 0)
        {
            if (wp_suspend_cache_addition()) {
                return false;
            }

            if (empty($group)) {
                $group = 'default';
            }

            $id = $this->define_key($key, $group);
            if ($this->_exists($id, $group)) {
                return false;
            }

            return $this->set($key, $data, $group, (int) $expire);
        }

        /**
         * Sets the list of global cache groups.
         *
         * @param array $groups list of groups that are global
         */
        public function add_global_groups($groups)
        {
            $groups = (array) $groups;

            $groups = array_fill_keys($groups, true);
            $groups['docketcache-self'] = true;
            $this->global_groups = array_merge($this->global_groups, $groups);
        }

        /**
         * Decrements numeric cache item's value.
         *
         * @param int|string $key    the cache key to decrement
         * @param int        $offset Optional. The amount by which to decrement the item's value. Default 1.
         * @param string     $group  Optional. The group the key is in. Default 'default'.
         *
         * @return int|false the item's new value on success, false on failure
         */
        public function decr($key, $offset = 1, $group = 'default')
        {
            if (empty($group)) {
                $group = 'default';
            }

            $key = $this->define_key($key, $group);

            if (!$this->_exists($key, $group)) {
                return false;
            }

            if (!is_numeric($this->cache[$group][$key])) {
                $this->cache[$group][$key] = 0;
            }

            $offset = (int) $offset;

            $this->cache[$group][$key] -= $offset;

            if ($this->cache[$group][$key] < 0) {
                $this->cache[$group][$key] = 0;
            }

            $this->docket_update($key, $this->cache[$group][$key], $group);

            return $this->cache[$group][$key];
        }

        /**
         * Removes the contents of the cache key in the group.
         *
         * If the cache key does not exist in the group, then nothing will happen.
         *
         * @param int|string $key        what the contents in the cache are called
         * @param string     $group      Optional. Where the cache contents are grouped. Default 'default'.
         * @param bool       $deprecated Optional. Unused. Default false.
         *
         * @return bool false if the contents weren't deleted and true on success
         */
        public function delete($key, $group = 'default', $deprecated = false)
        {
            if (empty($group)) {
                $group = 'default';
            }

            $key = $this->define_key($key, $group);

            if (!$this->_exists($key, $group)) {
                return false;
            }

            unset($this->cache[$group][$key]);
            $this->docket_remove($key, $group);

            return true;
        }

        /**
         * Clears the object cache of all data.
         *
         * @return true always returns true
         */
        public function flush()
        {
            $this->cache = [];

            return $this->docket_flush();
        }

        /**
         * Retrieves the cache contents, if it exists.
         *
         * The contents will be first attempted to be retrieved by searching by the
         * key in the cache group. If the cache is hit (success) then the contents
         * are returned.
         *
         * On failure, the number of cache misses will be incremented.
         *
         * @param int|string $key   what the contents in the cache are called
         * @param string     $group Optional. Where the cache contents are grouped. Default 'default'.
         * @param bool       $force Optional. Unused. Whether to force a refetch rather than relying on the local
         *                          cache. Default false.
         * @param bool       $found Optional. Whether the key was found in the cache (passed by reference).
         *                          Disambiguates a return of false, a storable value. Default null.
         *
         * @return mixed|false the cache contents on success, false on failure to retrieve contents
         */
        public function get($key, $group = 'default', $force = false, &$found = null)
        {
            if (empty($group)) {
                $group = 'default';
            }

            $key = $this->define_key($key, $group);

            if ($this->_exists($key, $group)) {
                $found = true;
                ++$this->cache_hits;
                if (\is_object($this->cache[$group][$key])) {
                    return clone $this->cache[$group][$key];
                }

                return $this->cache[$group][$key];
            }

            $found = false;
            ++$this->cache_misses;

            return false;
        }

        /**
         * Retrieves multiple values from the cache in one call.
         *
         * @param array  $keys  array of keys under which the cache contents are stored
         * @param string $group Optional. Where the cache contents are grouped. Default 'default'.
         * @param bool   $force Optional. Whether to force an update of the local cache
         *                      from the persistent cache. Default false.
         *
         * @return array array of values organized into groups
         */
        public function get_multiple($keys, $group = 'default', $force = false)
        {
            $values = [];

            foreach ($keys as $key) {
                $values[$key] = $this->get($key, $group, $force);
            }

            return $values;
        }

        /**
         * Increments numeric cache item's value.
         *
         * @param int|string $key    The cache key to increment
         * @param int        $offset Optional. The amount by which to increment the item's value. Default 1.
         * @param string     $group  Optional. The group the key is in. Default 'default'.
         *
         * @return int|false the item's new value on success, false on failure
         */
        public function incr($key, $offset = 1, $group = 'default')
        {
            if (empty($group)) {
                $group = 'default';
            }

            $key = $this->define_key($key, $group);

            if (!$this->_exists($key, $group)) {
                return false;
            }

            if (!is_numeric($this->cache[$group][$key])) {
                $this->cache[$group][$key] = 0;
            }

            $offset = (int) $offset;

            $this->cache[$group][$key] += $offset;

            if ($this->cache[$group][$key] < 0) {
                $this->cache[$group][$key] = 0;
            }

            $this->docket_update($key, $this->cache[$group][$key], $group);

            return $this->cache[$group][$key];
        }

        /**
         * Replaces the contents in the cache, if contents already exist.
         *
         * @see WP_Object_Cache::set()
         *
         * @param int|string $key    what to call the contents in the cache
         * @param mixed      $data   the contents to store in the cache
         * @param string     $group  Optional. Where to group the cache contents. Default 'default'.
         * @param int        $expire Optional. When to expire the cache contents. Default 0 (no expiration).
         *
         * @return bool false if not exists, true if contents were replaced
         */
        public function replace($key, $data, $group = 'default', $expire = 0)
        {
            if (empty($group)) {
                $group = 'default';
            }

            $id = $this->define_key($key, $group);

            if (!$this->_exists($id, $group)) {
                return false;
            }

            return $this->set($key, $data, $group, (int) $expire);
        }

        /**
         * Sets the data contents into the cache.
         *
         * The cache contents are grouped by the $group parameter followed by the
         * $key. This allows for duplicate ids in unique groups. Therefore, naming of
         * the group should be used with care and should follow normal function
         * naming guidelines outside of core WordPress usage.
         *
         * @param int|string $key    what to call the contents in the cache
         * @param mixed      $data   the contents to store in the cache
         * @param string     $group  Optional. Where to group the cache contents. Default 'default'.
         * @param int        $expire the expiration time, defaults to 0
         *
         * @return true always returns true
         */
        public function set($key, $data, $group = 'default', $expire = 0)
        {
            if (empty($group)) {
                $group = 'default';
            }

            $key = $this->define_key($key, $group);

            if (\is_object($data)) {
                $data = clone $data;
            }

            $this->cache[$group][$key] = $data;

            if (!$this->is_non_persistent_groups($group) && !$this->is_non_persistent_keys($key) || $this->is_filtered_groups($group, $key)) {
                $expire = $this->maybe_expire($expire);
                $this->docket_save($key, $this->cache[$group][$key], $group, $expire);
            }

            return true;
        }

        /**
         * Echoes the stats of the caching.
         *
         * Gives the cache hits, and cache misses. Also prints every cached group,
         * key and the data.
         */
        public function stats()
        {
            $ret = '';
            $ret .= '<p>';
            $ret .= "<strong>Cache Hits:</strong> {$this->cache_hits}<br />";
            $ret .= "<strong>Cache Misses:</strong> {$this->cache_misses}<br />";
            $ret .= '</p>';
            $ret .= '<ul>';
            foreach ($this->cache as $group => $cache) {
                $ret .= '<li><strong>Group:</strong> '.esc_html($group).' - ( '.number_format(\strlen(serialize($cache)) / KB_IN_BYTES, 2).'K )</li>';
            }
            $ret .= '</ul>';
            echo $ret;
        }

        /**
         * Switches the internal blog ID.
         *
         * This changes the blog ID used to create keys in blog specific groups.
         *
         * @param int $blog_id blog ID
         */
        public function switch_to_blog($blog_id)
        {
            $blog_id = (int) $blog_id;
            $this->blog_prefix = $this->multisite ? $blog_id.':' : '';
        }

        /**
         * Serves as a utility function to determine whether a key exists in the cache.
         *
         * @since 3.4.0
         *
         * @param int|string $key   cache key to check for existence
         * @param string     $group cache group for the key existence check
         *
         * @return bool whether the key exists in the cache for the given group
         */
        protected function _exists($key, $group)
        {
            $is_exists = isset($this->cache[$group]) && (isset($this->cache[$group][$key]) || \array_key_exists($key, $this->cache[$group]));
            if (!$is_exists) {
                $data = $this->docket_get($key, $group, false);
                if (false !== $data) {
                    $this->cache[$group][$key] = $data;
                    $is_exists = true;
                    $hash = $this->item_key($key, $group);
                    $this->debug('hit', $hash, $group.':'.$key);
                }
            }

            return $is_exists;
        }

        /**
         * Sets the list of non persistent groups.
         *
         * @param array $groups list of groups that are to be ignored
         */
        public function add_non_persistent_groups($groups)
        {
            $groups = (array) $groups;
            $this->non_persistent_groups = array_unique(array_merge($this->non_persistent_groups, $groups));
        }

        /**
         * Check if group in non persistent groups.
         *
         * @param bool $group cache group
         */
        protected function is_non_persistent_groups($group)
        {
            return !empty($this->non_persistent_groups) && \in_array($group, $this->non_persistent_groups);
        }

        /**
         * Sets the list of non persistent keys.
         *
         * @param array $keys list of keys that are to be ignored
         */
        public function add_non_persistent_keys($keys)
        {
            $keys = (array) $keys;

            $this->non_persistent_keys = array_unique(array_merge($this->non_persistent_keys, $keys));
        }

        /**
         * Check if key in non persistent keys.
         *
         * @param bool $key cache key
         */
        protected function is_non_persistent_keys($key)
        {
            $key = $this->remove_blog_prefix($key);

            return !empty($this->non_persistent_keys) && \in_array($key, $this->non_persistent_keys);
        }

        private function define_key($key, $group)
        {
            if ($this->multisite && !\array_key_exists($group, $this->global_groups)) {
                $key = $this->blog_prefix.$key;
            }

            return $key;
        }

        private function is_filtered_groups($group, $key)
        {
            if (!\is_array($this->filtered_groups) || !isset($this->filtered_groups[$group])) {
                return false;
            }

            $key = $this->remove_blog_prefix($key);

            if (false === $this->filtered_groups[$group]) {
                $this->filtered_groups[$group][] = $key;
                $this->filtered_groups[$group] = array_unique($this->filtered_groups[$group]);

                return true;
            }

            if (\in_array($key, $this->filtered_groups[$group])) {
                return true;
            }

            return false;
        }

        private function flush_filtered_groups($hook, $args)
        {
            if (!\is_array($this->filtered_groups)) {
                return false;
            }

            foreach ($this->filtered_groups as $group => $keys) {
                if (empty($keys) || !\is_array($keys)) {
                    //$this->docket_remove_group($group);
                    //$this->debug('run', __FUNCTION__.'-'.$hook, $group.':*');
                    continue;
                }

                $keys = array_unique($keys);
                foreach ($keys as $key) {
                    $this->delete($key, $group);
                    $this->debug('run', __FUNCTION__.'-'.$hook, $group.':'.$key);
                }
            }

            return true;
        }

        private function remove_blog_prefix($key)
        {
            return str_replace($this->blog_prefix, '', $key);
        }

        private function sanitize_second($time)
        {
            $time = (int) $time;
            if ($time < 0) {
                $time = 0;
            } else {
                $max = ceil(log10($time));
                if ($max > 10 || 'NaN' === $max) {
                    $time = 0;
                }
            }

            return $time;
        }

        private function maybe_expire($expire = 0)
        {
            $expire = $this->sanitize_second($expire);
            if (0 !== $this->cache_maxttl && 0 === $expire) {
                $expire = $this->cache_maxttl;
            }

            return $expire;
        }

        private function get_item_hash($file)
        {
            return basename($file, '.php');
        }

        private function item_hash($str, $length = 12)
        {
            return substr(md5($str), 0, $length);
        }

        private function item_key($key, $group)
        {
            return $this->item_hash($group).'-'.$this->item_hash($key);
        }

        private function get_file_path($key, $group)
        {
            return $this->cache_path.$this->item_key($key, $group).'.php';
        }

        private function code_stub($data = '')
        {
            $code = '<?php ';
            $code .= "defined('ABSPATH') || exit;".PHP_EOL;
            if (!empty($data)) {
                $code .= 'return '.$data.';'.PHP_EOL;
            }

            return $code;
        }

        private function debug($tag, $id, $data)
        {
            if (!DOCKET_CACHE_LOG) {
                return false;
            }

            $caller = '';
            if (!empty($_SERVER['REQUEST_URI'])) {
                $caller = $_SERVER['REQUEST_URI'];
            } elseif (DOCKET_CACHE_WPCLI) {
                $caller = 'wp-cli';
            }

            return $this->filesystem->log($tag, $id, $data, $caller);
        }

        private function docket_flush()
        {
            $dir = $this->cache_path;
            $cnt = $this->filesystem->cachedir_flush($dir);
            if ($cnt > 0) {
                $this->debug('run', __FUNCTION__, 'Files: '.$cnt);

                if (DOCKET_CACHE_WPCLI) {
                    do_action('docket_preload');
                }

                return true;
            }

            $this->debug('err', __FUNCTION__, 'Object cache could not be flushed');

            return false;
        }

        private function docket_remove($key, $group)
        {
            $file = $this->get_file_path($key, $group);
            $this->filesystem->unlink($file, false);
            $this->debug('del', $this->get_item_hash($file), $group.':'.$key);
        }

        private function docket_remove_group($group)
        {
            $match = $this->item_hash($group).'-';
            if ($this->filesystem->is_docketcachedir($this->cache_path)) {
                foreach ($this->filesystem->scanfiles($this->cache_path) as $object) {
                    $fn = $object->getFileName();
                    $fx = $object->getPathName();
                    if ($match === substr($fn, 0, \strlen($match))) {
                        $this->filesystem->unlink($fx, false);
                        $this->debug('del', $this->get_item_hash($fx), $group.':*');
                        unset($this->cache[$group]);
                    }
                }
            }
        }

        private function docket_get($key, $group, $is_raw = false)
        {
            $file = $this->get_file_path($key, $group);

            if (!file_exists($file) || empty($this->filesystem->filesize($file))) {
                return false;
            }

            if (!$handle = @fopen($file, 'rb')) {
                return false;
            }

            $data = [];

            // include when we can read, try to avoid fatal error.
            if (flock($handle, LOCK_SH)) {
                $data = @include $file;
                @flock($handle, LOCK_UN);
            }
            @fclose($handle);

            if (empty($data) || !isset($data['data'])) {
                return false;
            }

            if (!isset($data['timeout'])) {
                $data['timeout'] = $this->maybe_expire(0);
            } elseif ($data['timeout'] > 0 && time() >= $data['timeout']) {
                $this->debug('exp', $this->get_item_hash($file), $group.':'.$key);
                $this->filesystem->unlink($file, false);

                return false;
            }

            clearstatcache();

            return  $is_raw ? $data : $data['data'];
        }

        private function docket_code($file, $arr)
        {
            $fname = $this->get_item_hash($file);

            $data = $this->filesystem->export_var($arr, $error);
            if (false === $data) {
                $this->debug('err', $fname, 'Failed to export var: '.$error);

                return false;
            }

            $len = \strlen($data);
            if ($len >= $this->cache_maxsize) {
                $this->debug('err', $fname, 'Data too large: '.$len.'/'.$this->cache_maxsize);

                return false;
            }

            $code = $this->code_stub($data);
            $stat = $this->filesystem->dump($file, $code);
            if (-1 === $stat) {
                $this->debug('err', $fname, 'Failed to write');

                return false;
            }

            return $stat;
        }

        private function docket_save($key, $data, $group = 'default', $expire = 0)
        {
            if (!@wp_mkdir_p($this->cache_path)) {
                return false;
            }

            @$this->filesystem->put($this->cache_path.'index.php', $this->code_stub(time()));

            $file = $this->get_file_path($key, $group);

            $timeout = ($expire > 0 ? time() + $expire : 0);

            $type = \gettype($data);
            if ('NULL' === $type && null === $data) {
                $data = '';
            }

            $meta = [
                'blog_id' => get_current_blog_id(),
                'group' => $group,
                'key' => $key,
                'type' => $type,
                'timeout' => $timeout,
                'data' => $data,
            ];

            if (true === $this->docket_code($file, $meta)) {
                $this->debug('set', $this->get_item_hash($file), $group.':'.$key);
            }

            return false;
        }

        private function docket_update($key, $data, $group)
        {
            $meta = $this->docket_get($key, $group, true);
            if (false === $meta || !\is_array($meta) || !isset($meta['data'])) {
                return false;
            }

            $file = $this->get_file_path($key, $group);
            $meta['timeout'] = $this->maybe_expire($meta['timeout']);
            $meta['data'] = $data;

            if (true === $this->docket_code($file, $meta)) {
                $this->debug('set', $this->get_item_hash($file), $group.':'.$key);
            }

            return false;
        }

        private function docket_init()
        {
            Nawawi\DocketCache\Constans::init();
            $this->filesystem = new Nawawi\DocketCache\Filesystem();

            if (\is_int(DOCKET_CACHE_MAXSIZE) && DOCKET_CACHE_MAXSIZE > 1000000) {
                $this->cache_maxsize = DOCKET_CACHE_MAXSIZE;
            }

            if (\is_int(DOCKET_CACHE_MAXTTL)) {
                $this->cache_maxttl = $this->sanitize_second(DOCKET_CACHE_MAXTTL);
            }

            if (\is_array(DOCKET_CACHE_GLOBAL_GROUPS)) {
                $this->add_global_groups(DOCKET_CACHE_GLOBAL_GROUPS);
            }

            if (\is_array(DOCKET_CACHE_IGNORED_GROUPS)) {
                $this->non_persistent_groups = DOCKET_CACHE_IGNORED_GROUPS;
            }

            if (\is_array(DOCKET_CACHE_IGNORED_KEYS)) {
                $this->non_persistent_keys = DOCKET_CACHE_IGNORED_KEYS;
            }

            if (\is_array(DOCKET_CACHE_FILTERED_GROUPS)) {
                $this->filtered_groups = DOCKET_CACHE_FILTERED_GROUPS;
            }

            $this->cache_path = is_dir(DOCKET_CACHE_PATH) && '/' !== DOCKET_CACHE_PATH ? rtrim(DOCKET_CACHE_PATH, '/\\').'/' : WP_CONTENT_DIR.'/cache/docket-cache/';
            if (!$this->filesystem->is_docketcachedir($this->cache_path)) {
                $this->cache_path = rtim($this->cache_path, '/').'docket-cache/';
            }

            foreach (['added', 'updated', 'deleted'] as $prefix) {
                add_action(
                    $prefix.'_option',
                    function ($option) use ($prefix) {
                        if (!wp_installing()) {
                            $alloptions = wp_load_alloptions();
                            if (isset($alloptions[$option])) {
                                add_action(
                                    'shutdown',
                                    function () {
                                        $this->delete('alloptions', 'options');
                                    },
                                    PHP_INT_MAX
                                );
                            }
                        }
                    },
                    PHP_INT_MAX
                );
            }

            foreach (['activate', 'deactivate'] as $prefix) {
                add_action(
                    $prefix.'_plugin',
                    function ($plugin, $network) use ($prefix) {
                        if ($this->multisite) {
                            add_action(
                                'shutdown',
                                function () {
                                    $this->delete(get_network()->site_id.':active_sitewide_plugins', 'site-options');
                                },
                                PHP_INT_MAX
                            );
                        }
                        add_action(
                            'shutdown',
                            function () {
                                $this->delete('uninstall_plugins', 'options');
                            },
                            PHP_INT_MAX
                        );
                    },
                    PHP_INT_MAX,
                    2
                );
            }

            // filtered groups hooks
            if (\is_array($this->filtered_groups)) {
                add_action(
                    'save_post',
                    function ($post_id, $post, $update) {
                        $this->flush_filtered_groups('save_post', \func_get_args());
                    },
                    -PHP_INT_MAX,
                    3
                );

                add_action(
                    'edit_post',
                    function ($post_id, $post) {
                        $this->flush_filtered_groups('edit_post', \func_get_args());
                    },
                    -PHP_INT_MAX,
                    2
                );

                add_action(
                    'delete_post',
                    function ($post_id) {
                        $this->flush_filtered_groups('delete_post', \func_get_args());
                    },
                    -PHP_INT_MAX
                );

                /*add_action(
                    'wp_login',
                    function ($login) {
                        $this->flush_filtered_groups('wp_login', \func_get_args());
                    },
                    PHP_INT_MAX
                );

                add_action(
                    'wp_logout',
                    function () {
                        $this->flush_filtered_groups('wp_logout', \func_get_args());
                    },
                    PHP_INT_MAX
                );

                add_action(
                    'clear_auth_cookie',
                    function () {
                        $this->flush_filtered_groups('clear_auth_cookie', \func_get_args());
                    },
                    PHP_INT_MAX
                );

                add_action(
                    'profile_update',
                    function ($user_id, $old_user_data) {
                        $this->flush_filtered_groups('profile_update', \func_get_args());
                    },
                    -PHP_INT_MAX,
                    2
                );

                add_filter(
                    'insert_user_meta',
                    function ($meta, $user, $update) {
                        if ($update) {
                            $this->flush_filtered_groups('insert_user_meta', \func_get_args());
                        }

                        return $meta;
                    },
                    -PHP_INT_MAX,
                    3
                );*/
            }
        }
    }
endif;
