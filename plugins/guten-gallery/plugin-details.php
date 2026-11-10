<?php

namespace P20\GutenGallery;

defined('ABSPATH') || exit;

add_filter('plugins_api', function ($result, $action, $args) {
    if ($action !== 'plugin_information' || ($args->slug ?? '') !== 'guten-gallery') {
        return $result;
    }

    $plugin = get_plugin_data(__DIR__ . '/guten-gallery.php', false, false);
    $update = apply_filters('update_plugins_github.com', false, $plugin, plugin_basename(__DIR__ . '/guten-gallery.php'), array());
    $version = $plugin['Version'];
    $changelog = '<h4>1.0.9</h4><p>Made the native auto-updates control available before the first GitHub release.</p><h4>1.0.8</h4><p>Paused carousel autoplay while hovering over either arrow.</p><h4>1.0.7</h4><p>Centered the carousel arrow icons.</p><h4>1.0.6</h4><p>Added caption alignment for all layouts.</p><h4>1.0.5</h4><p>Enabled carousel autoplay by default and pause it over pagination.</p><h4>1.0.4</h4><p>Added carousel arrows, pagination, height settings, autoplay, and looping.</p><h4>1.0.3</h4><p>Simplified the full-size image viewer and added backdrop dismissal.</p><h4>1.0.2</h4><p>Aligned image heights in mosaic galleries.</p><h4>1.0.1</h4><p>Filled mosaic rows while preserving image order.</p><h4>1.0.0</h4><p>First public release with four layouts and two image sources.</p>';
    if (isset($update['version']) && version_compare($update['version'], $version, '>')) {
        $version = $update['version'];
        $notes = trim(preg_replace('/^Source commit: [a-f0-9]+\s*$/mi', '', $update['release_notes'] ?? ''));
        $changelog = '<h4>' . esc_html($version) . '</h4>' .
            ($notes !== '' ? wpautop(esc_html($notes)) : '<p>See the GitHub release for details about this update.</p>') .
            '<p><a href="' . esc_url($update['url']) . '">View this release on GitHub</a></p>' . $changelog;
    }
    return (object) array(
        'name' => $plugin['Name'],
        'slug' => 'guten-gallery',
        'version' => $version,
        'author' => '<a href="' . esc_url($plugin['AuthorURI']) . '">' . esc_html($plugin['Author']) . '</a>',
        'homepage' => 'https://wordpress.p20.dev/guten-gallery/',
        'requires' => $plugin['RequiresWP'],
        'requires_php' => $plugin['RequiresPHP'],
        'sections' => array(
            'description' => '<p>Build galleries with grid, masonry, carousel, and mosaic layouts using selected images or images attached to the current post.</p><p>Licensed under GPL-2.0-or-later.</p>',
            'installation' => '<p>Activate Guten Gallery, then insert the Guten Gallery block. Complete P20 Plugins setup if prompted to access the administration page.</p>',
            'changelog' => $changelog,
        ),
    );
}, 10, 3);

add_filter('plugin_row_meta', function ($links, $file, $data) {
    if ($file !== plugin_basename(__DIR__ . '/guten-gallery.php') || isset($data['slug']) || !current_user_can('install_plugins')) {
        return $links;
    }

    // Core adds this link itself when GitHub update metadata supplies a slug.
    $url = network_admin_url('plugin-install.php?tab=plugin-information&plugin=guten-gallery&TB_iframe=true&width=600&height=550');
    $links[] = sprintf(
        '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="P20 Guten Gallery">%s</a>',
        esc_url($url),
        esc_attr__('More information about Guten Gallery'),
        esc_html__('View details')
    );
    return $links;
}, 10, 3);
