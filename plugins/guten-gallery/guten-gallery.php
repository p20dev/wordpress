<?php
/**
 * Plugin Name: P20 Guten Gallery
 * Description: Create galleries with grid, masonry, carousel, and mosaic layouts.
 * Version: 1.0.9
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: P20.dev
 * Author URI: https://wordpress.p20.dev
 * License: GPL-2.0-or-later
 * Update URI: https://github.com/p20dev/wordpress/releases#guten-gallery
 * Text Domain: guten-gallery
 */

namespace P20\GutenGallery;

defined('ABSPATH') || exit;

require_once __DIR__ . '/github-updates.php';
require_once __DIR__ . '/plugin-details.php';
require_once __DIR__ . '/companion.php';
\P20\WordPressPlugins\register_github_updates(__FILE__, 'guten-gallery');

add_filter('update_plugins_github.com', static function ($update, $data, $plugin_file) {
    if ($update || $plugin_file !== plugin_basename(__FILE__)) {
        return $update;
    }

    // Keep the native toggle available before the first GitHub release.
    return array(
        'slug' => 'guten-gallery',
        'version' => $data['Version'],
        'url' => 'https://wordpress.p20.dev/plugins/guten-gallery/',
    );
}, 20, 3);

add_action('init', function () {
    register_block_type(__DIR__);
});

add_action('admin_menu', function () {
    if (companion_ready()) {
        \P20\Plugins\add_settings_page('p20-guten-gallery', 'Guten Gallery', __NAMESPACE__ . '\render_settings');
    }
});

function render_settings() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Guten Gallery</h1>
        <p>Insert the Guten Gallery block in the editor and choose a layout and image source in the block settings.</p>
        <p>Use the Media Library to select and reorder images, or choose Attached to current post to display images uploaded as attachments to that post.</p>
    </div>
    <?php
}
