<?php

namespace P20\WordPressPlugins;

defined('ABSPATH') || exit;

if (!function_exists(__NAMESPACE__ . '\add_settings_page')) {
    /**
     * Call on admin_menu. Bundle this helper with each P20 plugin that has settings.
     */
    function add_settings_page($slug, $title, $callback) {
        static $registered = false;
        $parent = 'p20-plugins';
        $capability = 'manage_options';
        if (!$registered) {
            add_menu_page('P20 Plugins', 'P20 Plugins', $capability, $parent, function () {
                echo '<div class="wrap"><h1>P20 Plugins</h1><p>Select a plugin from the menu to manage its settings.</p></div>';
            }, 'dashicons-admin-plugins');
            add_submenu_page($parent, 'P20 Plugins', 'Overview', $capability, $parent);
            $registered = true;
        }
        return add_submenu_page($parent, $title, $title, $capability, $slug, $callback);
    }
}
