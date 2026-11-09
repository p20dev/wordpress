<?php
/**
 * Plugin Name: Guten Weather
 * Description: Displays local weather forecasts from MET Norway in a Gutenberg block.
 * Version: 1.1.10
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Author: P20.dev
 * Author URI: https://wordpress.p20.dev
 * Update URI: https://github.com/p20dev/wordpress/releases#guten-weather
 * Requires at least: 6.5
 * Requires PHP: 7.4
 */

namespace P20\GutenWeather;

defined('ABSPATH') || exit;

require_once __DIR__ . '/p20-admin-menu.php';
require_once __DIR__ . '/city-search.php';
require_once __DIR__ . '/render.php';
require_once __DIR__ . '/github-updates.php';

\P20\WordPressPlugins\register_github_updates(__FILE__, 'guten-weather');

function settings() {
    return get_option('met_vejr_settings', array()) + array('cities' => '', 'unit' => 'c');
}

function cities($value) {
    $cities = array();
    foreach (preg_split('/\R/u', $value) as $line) {
        if (trim($line) === '') {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) !== 3) {
            return null;
        }
        list($name, $lat, $lon) = $parts;
        $name = sanitize_text_field($name);
        if ($name === '' || !is_numeric($lat) || !is_numeric($lon) || abs((float) $lat) > 90 || abs((float) $lon) > 180) {
            return null;
        }
        $cities[] = array($name, round((float) $lat, 4), round((float) $lon, 4));
    }
    return $cities;
}

add_action('admin_init', function () {
    register_setting('met_vejr', 'met_vejr_settings', array(
        'type' => 'array',
        'sanitize_callback' => function ($input) {
            $value = sanitize_textarea_field($input['cities'] ?? '');
            $cities = cities($value);
            if ($cities === null || count($cities) > 10) {
                add_settings_error('met_vejr_settings', 'cities', 'Enter up to 10 cities with valid coordinates, one city per line.');
                return settings();
            }
            return array('cities' => $value, 'unit' => ($input['unit'] ?? '') === 'f' ? 'f' : 'c');
        },
    ));
});

add_action('admin_menu', function () {
    $page = \P20\WordPressPlugins\add_settings_page('p20-guten-weather', 'Guten Weather', function () {
        if (!current_user_can('manage_options')) {
            return;
        }
        $settings = settings();
        ?>
        <div class="wrap">
            <h1>Guten Weather</h1>
            <?php settings_errors('met_vejr_settings'); ?>
            <p>
                <label for="met-vejr-search"><strong>Find a city</strong></label>
                <input type="search" id="met-vejr-search" maxlength="100" placeholder="e.g. Copenhagen">
                <button type="button" class="button" id="met-vejr-search-button" data-nonce="<?php echo esc_attr(wp_create_nonce('met_vejr_search_cities')); ?>">Search</button>
            </p>
            <p class="description">City search by <a href="https://open-meteo.com/en/docs/geocoding-api">Open-Meteo</a>, using <a href="https://www.geonames.org/">GeoNames</a> data.</p>
            <p id="met-vejr-search-status" role="status"></p>
            <ul id="met-vejr-search-results"></ul>
            <form method="post" action="options.php">
                <?php settings_fields('met_vejr'); ?>
                <p><label for="met-vejr-cities"><strong>Cities</strong></label></p>
                <textarea class="large-text code" id="met-vejr-cities" name="met_vejr_settings[cities]" rows="6" aria-describedby="met-vejr-format"><?php echo esc_textarea($settings['cities']); ?></textarea>
                <p class="description" id="met-vejr-format">Enter up to 10 cities, one per line, using a decimal point. Example <code>Copenhagen | 55.6761 | 12.5683</code></p>
                <p>
                    <label for="met-vejr-unit"><strong>Temperature unit</strong></label>
                    <select id="met-vejr-unit" name="met_vejr_settings[unit]">
                        <option value="c" <?php selected($settings['unit'], 'c'); ?>>Celsius (°C)</option>
                        <option value="f" <?php selected($settings['unit'], 'f'); ?>>Fahrenheit (°F)</option>
                    </select>
                </p>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    });
    add_action('load-' . $page, function () {
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_script('met-vejr-admin', plugins_url('admin.js', __FILE__), array(), '1.1.10', true);
        });
    });
});

function forecast($lat, $lon) {
    $coordinates = sprintf('%.4F,%.4F', $lat, $lon);
    $key = 'met_vejr_' . md5($coordinates);
    $cached = get_transient($key);
    if (($cached === false || $cached['refresh'] <= time()) && !get_transient($key . '_retry')) {
        set_transient($key . '_retry', 1, 30 * MINUTE_IN_SECONDS);
        $headers = array('Accept' => 'application/json');
        if (!empty($cached['modified'])) {
            $headers['If-Modified-Since'] = $cached['modified'];
        }
        $url = add_query_arg(
            array('lat' => sprintf('%.4F', $lat), 'lon' => sprintf('%.4F', $lon)),
            'https://api.met.no/weatherapi/locationforecast/2.0/compact'
        );
        $response = wp_remote_get($url, array(
            'timeout' => 3,
            'redirection' => 0,
            'user-agent' => 'Guten-Weather/1.1.10 (' . home_url('/') . ')',
            'headers' => $headers,
        ));
        if (!is_wp_error($response)) {
            $status = wp_remote_retrieve_response_code($response);
            if ($status === 200) {
                $data = json_decode(wp_remote_retrieve_body($response), true);
                $timeseries = $data['properties']['timeseries'] ?? null;
                $series = is_array($timeseries) ? array_slice($timeseries, 0, 48) : array();
                if ($series) {
                    $cached = array(
                        'series' => $series,
                        'modified' => wp_remote_retrieve_header($response, 'last-modified'),
                    );
                }
            }
            if (($status === 200 && !empty($series)) || ($status === 304 && is_array($cached))) {
                $expires = strtotime(wp_remote_retrieve_header($response, 'expires'));
                $cached['refresh'] = max(time() + 3 * HOUR_IN_SECONDS, (int) $expires);
                set_transient($key, $cached, $cached['refresh'] - time() + DAY_IN_SECONDS);
            }
        }
    }

    $hour = intdiv(time(), HOUR_IN_SECONDS) * HOUR_IN_SECONDS;
    foreach ($cached['series'] ?? array() as $entry) {
        if (strtotime($entry['time'] ?? '') >= $hour) {
            return $entry;
        }
    }
    return null;
}

add_action('init', function () {
    wp_register_script(
        'met-vejr-editor',
        plugins_url('editor.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render'),
        '1.1.10',
        true
    );
    register_block_type(__DIR__, array('render_callback' => __NAMESPACE__ . '\render'));
});
