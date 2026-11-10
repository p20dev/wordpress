<?php

namespace P20\GutenGallery;

defined('ABSPATH') || exit;

const COMPANION_PLUGIN = 'p20-plugins/p20-plugins.php';
const COMPANION_PACKAGE = 'https://github.com/p20dev/wordpress/releases/download/p20-plugins-v1.0.0/p20-plugins.zip';

function companion_ready() {
    return function_exists('P20\\Plugins\\add_settings_page');
}

function install_companion() {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    if (is_plugin_active(COMPANION_PLUGIN)) {
        return true;
    }

    if (!is_file(WP_PLUGIN_DIR . '/' . COMPANION_PLUGIN)) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $skin = new \Automatic_Upgrader_Skin();
        $upgrader = new \Plugin_Upgrader($skin);
        $result = $upgrader->install(COMPANION_PACKAGE);
        if ($result !== true) {
            return is_wp_error($result) ? $result : new \WP_Error('p20_install_failed', 'Could not install P20 Plugins.');
        }
    }

    $result = activate_plugin(COMPANION_PLUGIN);
    return is_wp_error($result) ? $result : true;
}

add_action('admin_post_p20_guten_gallery_setup', function () {
    if (!current_user_can('manage_options') || !current_user_can('activate_plugins') || (!is_file(WP_PLUGIN_DIR . '/' . COMPANION_PLUGIN) && !current_user_can('install_plugins'))) {
        wp_die('You do not have permission to set up P20 Plugins.', '', array('response' => 403));
    }
    check_admin_referer('p20_guten_gallery_setup');

    $result = install_companion();
    $url = is_wp_error($result)
        ? add_query_arg('p20-guten-gallery-setup', 'failed', admin_url('plugins.php'))
        : admin_url('admin.php?page=p20-guten-gallery');
    wp_safe_redirect($url);
    exit;
});

function companion_setup_notice() {
    if (companion_ready() || !current_user_can('manage_options')) {
        return;
    }

    $installed = is_file(WP_PLUGIN_DIR . '/' . COMPANION_PLUGIN);
    $can_setup = current_user_can('activate_plugins') && ($installed || current_user_can('install_plugins'));
    $failed = isset($_GET['p20-guten-gallery-setup']) && $_GET['p20-guten-gallery-setup'] === 'failed';
    ?>
    <div class="notice notice-warning">
        <p><strong>Guten Gallery requires P20 Plugins</strong> to provide its settings page.</p>
        <?php if ($failed) : ?>
            <p>Automatic setup failed. You can download P20 Plugins and install it using the WordPress plugin uploader.</p>
        <?php endif; ?>
        <?php if ($can_setup) : ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="p20_guten_gallery_setup">
                <?php wp_nonce_field('p20_guten_gallery_setup'); ?>
                <p><button type="submit" class="button button-primary"><?php echo $installed ? 'Activate P20 Plugins' : 'Install and activate P20 Plugins'; ?></button></p>
            </form>
        <?php else : ?>
            <p>Ask a site administrator with plugin installation access to complete setup.</p>
        <?php endif; ?>
        <p><a href="<?php echo esc_url(COMPANION_PACKAGE); ?>">Download P20 Plugins</a></p>
    </div>
    <?php
}
add_action('admin_notices', __NAMESPACE__ . '\companion_setup_notice');
add_action('network_admin_notices', __NAMESPACE__ . '\companion_setup_notice');
