<?php
/**
 * Docket Cache.
 *
 * @author  Nawawi Jamili
 * @license MIT
 *
 * @see    https://github.com/nawawi/docket-cache
 */

namespace Nawawi\Docket_Cache;

use Symfony\Component\VarExporter\VarExporter;

class Files
{
    public static $inst;

    public function chmod($file)
    {
        static $cache = [];

        if (isset($cache[$file])) {
            return $cache[$file];
        }

        if (is_file($file) && \defined('FS_CHMOD_FILE')) {
            $perms = FS_CHMOD_FILE;
        } elseif (is_dir($file) && \defined('FS_CHMOD_DIR')) {
            $perms = FS_CHMOD_DIR;
        } else {
            $stat = @stat(\dirname($file));
            $perms = $stat['mode'] & 0007777;
            $perms = $perms & 0000666;
        }

        if (@chmod($file, $perms)) {
            $cache[$file] = $perms;
        }

        clearstatcache();
    }

    public function copy($src, $dst)
    {
        $this->opcache_flush($dst);
        if (@copy($src, $dst)) {
            $this->chmod($dst);

            return true;
        }

        return false;
    }

    public function scandir($dir)
    {
        return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
    }

    public function tail($filepath, $limit = 100)
    {
        $limit = (int) $limit;
        $file = new \SplFileObject($filepath);
        $file->seek(PHP_INT_MAX);
        $total_lines = $file->key();
        $total_lines = $total_lines > $limit ? $total_lines - $limit : $total_lines;
        if ($total_lines > 0) {
            return new \LimitIterator($file, $total_lines);
        }

        return [];
    }

    public function export_var($data, &$error = '')
    {
        try {
            $data = VarExporter::export($data);
        } catch (\Exception $e) {
            $error = $e->getMessage();
            if (false !== strpos($error, 'Cannot export value of type "stdClass"')) {
                $data = var_export($data, 1);
                $data = str_replace('stdClass::__set_state', '(object)', $data);
            } else {
                return false;
            }
        }

        return $data;
    }

    public function unlink($file, $del = false)
    {
        $ok = false;

        $handle = @fopen($file, 'cb');
        if ($handle) {
            if (@flock($handle, LOCK_EX | LOCK_NB)) {
                $ok = @ftruncate($handle, 0);
                @flock($handle, LOCK_UN);
            }
            @fclose($handle);
        }

        if ((\defined('DOCKET_CACHE_FLUSH_DELETE') && DOCKET_CACHE_FLUSH_DELETE || $del) && @unlink($file)) {
            $ok = true;
        }

        if (false === $ok) {
            // if failed, try to remove on shutdown instead of truncate
            add_action(
                'shutdown',
                function () use ($file) {
                    @unlink($file);
                }
            );
        }

        // always true
        return true;
    }

    public function put($file, $data, $flag = 'cb')
    {
        if (!$handle = @fopen($file, $flag)) {
            return false;
        }

        $ok = false;
        if (@flock($handle, LOCK_EX | LOCK_NB)) {
            $len = \strlen($data);
            $cnt = @fwrite($handle, $data);
            @fflush($handle);
            @flock($handle, LOCK_UN);
            if ($len === $cnt) {
                $ok = true;
            }
        }
        @fclose($handle);
        clearstatcache();

        if (false === $ok) {
            $this->unlink($file, true);

            return -1;
        }

        $this->opcache_flush($file);
        $this->chmod($file);

        return $ok;
    }

    public function dump($file, $data)
    {
        $dir = \dirname($file);
        $tmpfile = $dir.'/'.'dump_'.uniqid().'_'.basename($file);
        $this->log(__FUNCTION__,$file, $tmpfile);
        add_action(
            'shutdown',
            function () use ($tmpfile) {
                @unlink($tmpfile);
            }
        );

        $ok = $this->put($tmpfile, $data);
        if (true === $ok) {
            if (@rename($tmpfile, $file)) {
                $this->chmod($file);

                return true;
            }
        }

        // maybe -1, true, false
        return $ok;
    }

    public function opcache_flush($file)
    {
        static $done = [];

        if (isset($done[$file])) {
            return true;
        }

        if (\function_exists('opcache_invalidate') && 'php' === substr($file, -3) && file_exists($file)) {
            $done[$file] = $file;

            return @opcache_invalidate($file, true);
        }

        return false;
    }

    public function log($tag, $id, $data, $caller = '')
    {
        $file = DOCKET_CACHE_LOG_FILE;
        if (file_exists($file)) {
            if (DOCKET_CACHE_LOG_FLUSH && 'flush' === strtolower($id) || @filesize($file) >= (int) DOCKET_CACHE_LOG_SIZE) {
                $this->put($file, '', 'cb');
            }
        }

        $meta = [];
        $meta['timestamp'] = date('Y-m-d H:i:s e');
        if (!empty($caller)) {
            $meta['caller'] = $caller;
        }

        if (\is_array($data)) {
            $log = $this->export_var(array_merge($meta, $data));
        } else {
            $log = '['.$meta['timestamp'].'] '.$tag.': "'.$id.'" "'.trim($data).'"';
            if (isset($meta['caller'])) {
                $log .= ' "'.$meta['caller'].'"';
            }
        }

        return @$this->put($file, $log.PHP_EOL, 'ab');
    }

    public static function inst()
    {
        if (!isset(self::$inst)) {
            self::$inst = new self();
        }

        return self::$inst;
    }
}
