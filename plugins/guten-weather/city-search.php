<?php

namespace P20\GutenWeather;

defined('ABSPATH') || exit;

function search_cities($query) {
    $url = add_query_arg(array('name' => $query, 'count' => 8, 'language' => 'en'), 'https://geocoding-api.open-meteo.com/v1/search');
    $response = wp_remote_get($url, array('timeout' => 5, 'redirection' => 0));
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return new \WP_Error('lookup_failed', 'City search is temporarily unavailable.');
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($data)) {
        return new \WP_Error('lookup_failed', 'City search returned an invalid response.');
    }

    $matches = array();
    foreach ($data['results'] ?? array() as $result) {
        if (!isset($result['name'], $result['latitude'], $result['longitude'])) {
            continue;
        }
        $name = str_replace('|', '', sanitize_text_field($result['name']));
        $location = implode(', ', array_unique(array_filter(array(
            $name,
            sanitize_text_field($result['admin1'] ?? ''),
            sanitize_text_field($result['country'] ?? ''),
        ))));
        $matches[] = array(
            'label' => $location,
            'value' => sprintf('%s | %.4F | %.4F', $name, $result['latitude'], $result['longitude']),
        );
    }
    return $matches;
}

add_action('wp_ajax_met_vejr_search_cities', function () {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Access denied.', 403);
    }
    check_ajax_referer('met_vejr_search_cities');
    $query = sanitize_text_field(wp_unslash($_POST['query'] ?? ''));
    if (strlen($query) < 2 || strlen($query) > 200) {
        wp_send_json_error('Enter a city name between 2 and 200 characters.', 400);
    }
    $matches = search_cities($query);
    if (is_wp_error($matches)) {
        wp_send_json_error($matches->get_error_message(), 502);
    }
    wp_send_json_success($matches);
});
