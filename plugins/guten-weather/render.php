<?php

namespace P20\GutenWeather;

defined('ABSPATH') || exit;

function condition_icon($symbol) {
    $condition = explode('_', $symbol)[0];
    if (strpos($condition, 'thunder') !== false) return '⛈️';
    if (strpos($condition, 'sleet') !== false) return '🌨️';
    if (strpos($condition, 'snow') !== false) return '❄️';
    if (strpos($condition, 'rain') !== false) return '🌧️';
    if ($condition === 'fog') return '🌫️';
    if ($condition === 'cloudy') return '☁️';
    if ($condition === 'partlycloudy') return '⛅';
    if (in_array($condition, array('clearsky', 'fair'), true)) {
        return strpos($symbol, '_night') !== false ? '🌙' : '☀️';
    }
    return '';
}

function forecast_cells($entry, $fahrenheit, $columns, $show_icons) {
    $details = $entry['data']['instant']['details'] ?? array();
    if (!isset($details['air_temperature'])) {
        return array();
    }

    $next_hour = $entry['data']['next_1_hours'] ?? array();
    $temperature = $details['air_temperature'];
    $temperature = $fahrenheit ? $temperature * 9 / 5 + 32 : $temperature;
    $icon = $show_icons ? condition_icon($next_hour['summary']['symbol_code'] ?? $entry['data']['next_6_hours']['summary']['symbol_code'] ?? '') : '';
    $values = array(
        'temperature' => ($icon ? '<span aria-hidden="true">' . esc_html($icon) . '</span> ' : '') .
            esc_html(number_format_i18n($temperature, 1) . ($fahrenheit ? ' °F' : ' °C')),
        'wind' => isset($details['wind_speed']) ? esc_html(number_format_i18n($details['wind_speed'], 1) . ' m/s') : 'Unavailable',
        'precipitation' => isset($next_hour['details']['precipitation_amount'])
            ? esc_html(number_format_i18n($next_hour['details']['precipitation_amount'], 1) . ' mm')
            : 'Unavailable',
        'time' => '<time datetime="' . esc_attr($entry['time']) . '">' .
            esc_html(wp_date('Y-m-d H:i', strtotime($entry['time']))) . '</time>',
    );
    return array_intersect_key($values, $columns);
}

function render($attributes) {
    $settings = settings();
    $cities = cities($settings['cities']);
    if (!$cities) {
        return '<p ' . get_block_wrapper_attributes() . '>Add cities under P20 Plugins → Guten Weather.</p>';
    }

    $layout = $attributes['layout'] ?? 'list';
    $columns = array('temperature' => 'Temperature');
    if ($attributes['showWind'] ?? true) {
        $columns['wind'] = 'Wind';
    }
    if ($attributes['showPrecipitation'] ?? false) {
        $columns['precipitation'] = 'Precipitation';
    }
    if ($attributes['showTime'] ?? true) {
        $columns['time'] = 'Forecast time';
    }

    $rows = array();
    foreach ($cities as list($name, $lat, $lon)) {
        $rows[] = array($name, forecast_cells(forecast($lat, $lon), $settings['unit'] === 'f', $columns, $attributes['showIcons'] ?? false));
    }
    $unavailable = 'The forecast is temporarily unavailable.';

    ob_start();
    ?>
    <div <?php echo get_block_wrapper_attributes(); ?>>
        <?php if ($layout === 'table') : ?>
            <div class="met-vejr-table-wrap">
                <table class="met-vejr-table">
                    <thead>
                        <tr>
                            <th scope="col">City</th>
                            <?php foreach ($columns as $label) : ?>
                                <th scope="col"><?php echo esc_html($label); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as list($name, $values)) : ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($name); ?></th>
                                <?php if ($values) : ?>
                                    <?php foreach ($values as $value) : ?>
                                        <td><?php echo $value; ?></td>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <td colspan="<?php echo esc_attr(count($columns)); ?>"><?php echo esc_html($unavailable); ?></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <ul<?php echo $layout === 'pill' ? ' class="met-vejr-pills" role="list"' : ''; ?>>
                <?php foreach ($rows as list($name, $values)) : ?>
                    <li>
                        <strong><?php echo esc_html($name); ?></strong>
                        <?php foreach ($values as $key => $value) : ?>
                            <span> · <?php echo $key === 'temperature' ? '' : esc_html($columns[$key] . ' '); ?><?php echo $value; ?></span>
                        <?php endforeach; ?>
                        <?php if (!$values) : ?>
                            <span> · <?php echo esc_html($unavailable); ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <small class="met-vejr-source">Data from <a href="https://www.met.no/">MET Norway</a></small>
    </div>
    <?php
    return ob_get_clean();
}
