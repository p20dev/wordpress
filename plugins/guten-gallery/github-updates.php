<?php

namespace P20\WordPressPlugins;

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( __NAMESPACE__ . '\\register_github_updates' ) ) {
    function register_github_updates( $file, $slug ) {
        $basename = plugin_basename( $file );
        add_filter( 'update_plugins_github.com', static function ( $update, $data, $plugin_file ) use ( $basename, $slug ) {
            if ( $plugin_file !== $basename ) {
                return $update;
            }

            $key = 'p20_wordpress_github_releases';
            $releases = get_site_transient( $key );
            if ( false === $releases ) {
                $releases = fetch_github_releases();
                set_site_transient( $key, $releases, $releases ? 15 * MINUTE_IN_SECONDS : MINUTE_IN_SECONDS );
            }

            $best = $update;
            $pattern = '/^' . preg_quote( $slug, '/' ) . '-v((?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*)\.(?:0|[1-9][0-9]*))$/D';
            foreach ( $releases as $release ) {
                if ( ! empty( $release['draft'] ) || ! empty( $release['prerelease'] )
                    || ! preg_match( $pattern, $release['tag_name'] ?? '', $matches ) ) {
                    continue;
                }

                $version = $matches[1];
                if ( isset( $best['version'] ) && ! version_compare( $version, $best['version'], '>' ) ) {
                    continue;
                }

                $url = 'https://github.com/p20dev/wordpress/releases';
                $package = $url . '/download/' . $matches[0] . '/' . $slug . '.zip';
                foreach ( $release['assets'] ?? array() as $asset ) {
                    if ( ( $asset['name'] ?? '' ) === $slug . '.zip'
                        && ( $asset['state'] ?? '' ) === 'uploaded'
                        && ( $asset['browser_download_url'] ?? '' ) === $package ) {
                        $best = array(
                            'id'      => $data['UpdateURI'],
                            'slug'    => $slug,
                            'version' => $version,
                            'url'     => $url . '/tag/' . $matches[0],
                            'package' => $package,
                            'release_notes' => $release['body'] ?? '',
                        );
                        break;
                    }
                }
            }

            return $best;
        }, 10, 3 );
    }

    function fetch_github_releases() {
        $releases = array();
        for ( $page = 1; ; ++$page ) {
            $response = wp_remote_get(
                'https://api.github.com/repos/p20dev/wordpress/releases?per_page=100&page=' . $page,
                array( 'timeout' => 5, 'headers' => array( 'Accept' => 'application/vnd.github+json' ) )
            );
            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                return array();
            }

            $batch = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( ! is_array( $batch ) || array_values( $batch ) !== $batch ) {
                return array();
            }

            $releases = array_merge( $releases, $batch );
            if ( count( $batch ) < 100 ) {
                return $releases;
            }
        }
    }
}
