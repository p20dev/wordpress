<?php

namespace P20\GutenWeather;

defined('ABSPATH') || exit;

add_filter('plugins_api', function ($result, $action, $args) {
    if ($action !== 'plugin_information' || ($args->slug ?? '') !== 'guten-weather') {
        return $result;
    }

    $plugin = get_plugin_data(__DIR__ . '/guten-weather.php', false, false);
    return (object) array(
        'name' => $plugin['Name'],
        'slug' => 'guten-weather',
        'version' => $plugin['Version'],
        'author' => '<a href="' . esc_url($plugin['AuthorURI']) . '">' . esc_html($plugin['Author']) . '</a>',
        'homepage' => 'https://wordpress.p20.dev/guten-weather/',
        'requires' => $plugin['RequiresWP'],
        'requires_php' => $plugin['RequiresPHP'],
        'sections' => array(
            'description' => '<p>Guten Weather displays local forecasts from MET Norway in pages, posts, and templates. Choose a list, table, or pills and customize the styling and weather details for each block.</p><p>Licensed under GPL-2.0-or-later.</p>',
            'installation' => '<p>Open P20 Plugins → Guten Weather, add your cities, and choose Celsius or Fahrenheit. Then insert the Guten Weather block in the editor.</p>',
            'changelog' => '<h4>1.1.11</h4><ul><li>Added this plugin details dialog.</li><li>Made the automatic updates control visible when the installed version is current.</li></ul><h4>1.1.10</h4><p>First public release.</p><p><a href="https://github.com/p20dev/wordpress/releases">View GitHub releases</a></p>',
        ),
    );
}, 10, 3);

add_filter('plugin_row_meta', function ($links, $file, $data) {
    if ($file !== plugin_basename(__DIR__ . '/guten-weather.php') || isset($data['slug']) || !current_user_can('install_plugins')) {
        return $links;
    }

    // Core adds this link itself when GitHub update metadata supplies a slug.
    $url = network_admin_url('plugin-install.php?tab=plugin-information&plugin=guten-weather&TB_iframe=true&width=600&height=550');
    $links[] = sprintf(
        '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="Guten Weather">%s</a>',
        esc_url($url),
        esc_attr__('More information about Guten Weather'),
        esc_html__('View details')
    );
    return $links;
}, 10, 3);
