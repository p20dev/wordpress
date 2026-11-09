<?php
/**
 * Plugin Name: P20 - Plugin Manager
 * Description: Provides a shared WordPress admin menu and overview for P20 plugins.
 * Version: 1.0.1
 * Author: P20.dev
 * Author URI: https://wordpress.p20.dev
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Update URI: https://github.com/p20dev/wordpress/releases#p20-plugins
 */

namespace P20\Plugins;

defined('ABSPATH') || exit;

require_once __DIR__ . '/github-updates.php';
require_once __DIR__ . '/plugin-details.php';

\P20\WordPressPlugins\register_github_updates(__FILE__, 'p20-plugins');

add_action('admin_menu', function () {
    add_menu_page('P20 Plugins', 'P20 Plugins', 'manage_options', 'p20-plugins', __NAMESPACE__ . '\render_overview', 'dashicons-admin-plugins');
    add_submenu_page('p20-plugins', 'P20 Plugins', 'Overview', 'manage_options', 'p20-plugins');
}, 9);

/**
 * Register a settings page from an admin_menu callback at priority 10 or later.
 * Returns the screen hook suffix, or false when the user lacks permission.
 */
function add_settings_page($slug, $title, $callback) {
    return add_submenu_page('p20-plugins', $title, $title, 'manage_options', $slug, $callback);
}

function render_overview() {
    if (!current_user_can('manage_options')) {
        return;
    }

    global $submenu;
    ?>
    <div class="wrap">
        <h1>P20 Plugins</h1>
        <p>Manage your P20 plugins from this menu.</p>
        <?php
        $links = array();
        foreach ($submenu['p20-plugins'] ?? array() as $item) {
            if ($item[2] !== 'p20-plugins' && current_user_can($item[1])) {
                $links[] = $item;
            }
        }
        if ($links) :
            ?>
            <ul>
                <?php foreach ($links as $item) : ?>
                    <li><a href="<?php echo esc_url(menu_page_url($item[2], false)); ?>"><?php echo esc_html($item[0]); ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <p>No P20 plugin settings pages are registered yet.</p>
        <?php endif; ?>
        <p><a href="https://wordpress.p20.dev/">Explore P20 plugins</a></p>
    </div>
    <?php
}
