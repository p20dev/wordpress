<?php

namespace P20\Plugins;

defined('ABSPATH') || exit;

add_filter('plugins_api', function ($result, $action, $args) {
    if ($action !== 'plugin_information' || ($args->slug ?? '') !== 'p20-plugins') {
        return $result;
    }

    $file = __DIR__ . '/p20-plugins.php';
    $plugin = get_plugin_data($file, false, false);
    $update = apply_filters('update_plugins_github.com', false, $plugin, plugin_basename($file), array());
    $version = $plugin['Version'];
    $changelog = '<h4>1.0.0</h4><p>First release. Adds the shared P20 Plugins menu, overview, and settings registration API.</p>';
    if (isset($update['version']) && version_compare($update['version'], $version, '>')) {
        $version = $update['version'];
        $notes = trim(preg_replace('/^Source commit: [a-f0-9]+\s*$/mi', '', $update['release_notes'] ?? ''));
        $changelog = '<h4>' . esc_html($version) . '</h4>' .
            ($notes !== '' ? wpautop(esc_html($notes)) : '<p>See GitHub for this release’s changes.</p>') .
            '<p><a href="' . esc_url($update['url']) . '">View this release on GitHub</a></p>' . $changelog;
    }

    return (object) array(
        'name' => $plugin['Name'],
        'slug' => 'p20-plugins',
        'version' => $version,
        'author' => '<a href="' . esc_url($plugin['AuthorURI']) . '">' . esc_html($plugin['Author']) . '</a>',
        'homepage' => 'https://wordpress.p20.dev/',
        'requires' => $plugin['RequiresWP'],
        'requires_php' => $plugin['RequiresPHP'],
        'sections' => array(
            'description' => '<p>P20 Plugins provides the shared WordPress admin menu and links to the settings of integrated P20 plugins.</p>',
            'installation' => '<p>Install and activate P20 Plugins, then open P20 Plugins → Overview in the WordPress admin.</p>',
            'changelog' => $changelog,
        ),
    );
}, 10, 3);

add_filter('plugin_row_meta', function ($links, $file, $data) {
    if ($file !== plugin_basename(__DIR__ . '/p20-plugins.php') || isset($data['slug']) || !current_user_can('install_plugins')) {
        return $links;
    }

    $url = network_admin_url('plugin-install.php?tab=plugin-information&plugin=p20-plugins&TB_iframe=true&width=600&height=550');
    $links[] = '<a href="' . esc_url($url) . '" class="thickbox open-plugin-details-modal" data-title="P20 Plugins">' . esc_html__('View details') . '</a>';
    return $links;
}, 10, 3);
