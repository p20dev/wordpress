<?php

namespace P20\GutenWeather;

defined('ABSPATH') || exit;

add_filter('plugins_api', function ($result, $action, $args) {
    if ($action !== 'plugin_information' || ($args->slug ?? '') !== 'guten-weather') {
        return $result;
    }

    $plugin = get_plugin_data(__DIR__ . '/guten-weather.php', false, false);
    $update = apply_filters('update_plugins_github.com', false, $plugin, plugin_basename(__DIR__ . '/guten-weather.php'), array());
    $version = $plugin['Version'];
    $changelog = '<h4>1.1.13</h4><p>Moved settings under the shared P20 Plugins companion and added one-click setup.</p><h4>1.1.12</h4><p>The update dialog now shows the incoming GitHub release notes.</p><h4>1.1.11</h4><ul><li>Added the native plugin details dialog.</li><li>Made the automatic updates control visible when the installed version is current.</li></ul><h4>1.1.10</h4><p>First public release.</p>';
    if (isset($update['version']) && version_compare($update['version'], $version, '>')) {
        $version = $update['version'];
        $notes = trim(preg_replace('/^Source commit: [a-f0-9]+\s*$/mi', '', $update['release_notes'] ?? ''));
        $changelog = '<h4>' . esc_html($version) . '</h4>' .
            ($notes !== '' ? wpautop(esc_html($notes)) : '<p>See the GitHub release for details about this update.</p>') .
            '<p><a href="' . esc_url($update['url']) . '">View this release on GitHub</a></p>' . $changelog;
    }
    return (object) array(
        'name' => $plugin['Name'],
        'slug' => 'guten-weather',
        'version' => $version,
        'author' => '<a href="' . esc_url($plugin['AuthorURI']) . '">' . esc_html($plugin['Author']) . '</a>',
        'homepage' => 'https://wordpress.p20.dev/guten-weather/',
        'requires' => $plugin['RequiresWP'],
        'requires_php' => $plugin['RequiresPHP'],
        'sections' => array(
            'description' => '<p>Guten Weather displays local forecasts from MET Norway in pages, posts, and templates. Choose a list, table, or pills and customize the styling and weather details for each block.</p><p>Licensed under GPL-2.0-or-later.</p>',
            'installation' => '<p>Activate Guten Weather and complete the P20 Plugins setup if prompted. Open P20 Plugins → Guten Weather, add your cities, and choose Celsius or Fahrenheit. Then insert the Guten Weather block in the editor.</p>',
            'changelog' => $changelog,
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
