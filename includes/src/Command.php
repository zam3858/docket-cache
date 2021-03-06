<?php
/**
 * Docket Cache.
 *
 * @author  Nawawi Jamili
 * @license MIT
 *
 * @see    https://github.com/nawawi/docket-cache
 */

namespace Nawawi\DocketCache;

use WP_CLI;
use WP_CLI_Command;

/**
 * Enables, disabled, updates, and checks the status of the Docket object cache.
 */
class Command extends WP_CLI_Command
{
    private $plugin;

    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    private function halt_error($error)
    {
        WP_CLI::error($error, false);
        WP_CLI::halt(1);
    }

    private function halt_success($success)
    {
        WP_CLI::success($success, false);
        WP_CLI::halt(0);
    }

    private function halt_status($text, $status = 0)
    {
        WP_CLI::line($text);
        WP_CLI::halt($status);
    }

    private function status_color($status, $text)
    {
        switch ($status) {
            case 1:
                $text = WP_CLI::colorize("%g{$text}%n");
                break;
            default:
                $text = WP_CLI::colorize("%r{$text}%n");
                break;
        }

        return $text;
    }

    /**
     * Display the Docket object cache status enable or disable.
     *
     * ## EXAMPLES
     *
     *  wp cache status
     */
    public function status()
    {
        $info = (object) $this->plugin->get_info();
        $halt = $info->status_code ? 0 : 1;

        WP_CLI::line("Status\t: ".$this->status_color($info->status_code, $info->status_text));
        WP_CLI::line("OPcache\t: ".$this->status_color($info->opcache_code, $info->opcache_text));
        WP_CLI::line("Path\t: ".$info->cache_path);
        WP_CLI::line("Size\t: ".$info->cache_size);
        WP_CLI::halt($halt);
    }

    /**
     * Enables the Docket object cache.
     *
     * Default behavior is to create the object cache drop-in,
     * unless an unknown object cache drop-in is present.
     *
     * ## EXAMPLES
     *
     *  wp cache enable
     */
    public function enable()
    {
        if ($this->plugin->has_dropin()) {
            if ($this->plugin->validate_dropin()) {
                WP_CLI::line(__('Docket object cache already enabled.', 'docket-cache'));
                WP_CLI::halt(0);
            }

            $this->halt_error(__('An unknown object cache drop-in was found. To use Docket object cache, run: wp docket-cache update-dropin.', 'docket-cache'));
        }

        if ($this->plugin->dropin_install()) {
            $this->halt_success(__('Object cache enabled.', 'docket-cache'));
        }

        $this->halt_error(__('Object cache could not be enabled.', 'docket-cache'));
    }

    /**
     * Disables the Docket object cache.
     *
     * Default behavior is to delete the object cache drop-in,
     * unless an unknown object cache drop-in is present.
     *
     * ## EXAMPLES
     *
     *  wp cache disable
     */
    public function disable()
    {
        if (!$this->plugin->has_dropin()) {
            $this->halt_error(__('No object cache drop-in found.', 'docket-cache'));
        }

        if (!$this->plugin->validate_dropin()) {
            $this->halt_error(__('An unknown object cache drop-in was found. To use Docket run: wp docket-cache update-dropin.', 'docket-cache'));
        }

        if ($this->plugin->dropin_uninstall()) {
            $this->halt_success(__('Object cache disabled.', 'docket-cache'));
        }

        $this->halt_error(__('Object cache could not be disabled.', 'docket-cache'));
    }

    /**
     * Updates the Docket object cache drop-in.
     *
     * Default behavior is to overwrite any existing object cache drop-in.
     *
     * ## EXAMPLES
     *
     *  wp cache update-dropin
     *
     * @subcommand update-dropin
     */
    public function update_dropin()
    {
        if ($this->plugin->dropin_install()) {
            $this->halt_success(__('Updated object cache drop-in and enabled Docket object cache.', 'docket-cache'));
        }
        $this->halt_error(__('Object cache drop-in could not be updated.', 'docket-cache'));
    }

    /**
     * Flushes the object cache.
     *
     * Directly execute 'wp cache flush' if drop-in file exists.
     *
     * ## EXAMPLES
     *
     *  wp docket-cache flush
     */
    public function flush()
    {
        if (!$this->plugin->has_dropin()) {
            $this->halt_error(__('No object cache drop-in found.', 'docket-cache'));
        }

        WP_CLI::runcommand('cache flush');
    }

    /**
     * Run cache preload.
     *
     * Default behavior is to overwrite any existing object cache drop-in.
     *
     * ## EXAMPLES
     *
     *  wp cache preload
     *
     * @subcommand preload
     */
    public function run_preload()
    {
        if (!DOCKET_CACHE_PRELOAD) {
            $this->halt_error(__('Cache preloading not enabled.', 'docket-cache'));
        }
        do_action('docket_preload');
        $this->halt_status(__('Preloading will happen shortly.', 'docket-cache'));
    }

    /**
     * Attempts to determine which object cache is being used.
     *
     * Note that the guesses made by this function are based on the
     * WP_Object_Cache classes that define the 3rd party object cache extension.
     * Changes to those classes could render problems with this function's
     * ability to determine which object cache is being used.
     *
     * ## EXAMPLES
     *
     *     wp cache type
     */
    public function type()
    {
        $this->halt_status($this->plugin->slug);
    }
}
