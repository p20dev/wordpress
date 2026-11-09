# P20 Plugins

P20 Plugins by [P20.dev](https://wordpress.p20.dev) owns the shared WordPress admin menu and Overview page for integrated P20 plugins. It requires WordPress 6.5 or newer and PHP 7.4 or newer.

Install and activate the ZIP, then open **P20 Plugins → Overview**. The overview links to the settings pages registered by active integrations.

## Integration

Declare `Requires Plugins: p20-plugins` in the dependent plugin header. WordPress enforces activation requirements. A GitHub-hosted dependency needs a separate installation flow, such as an administrator-approved setup action in the dependent plugin.

Register settings on `admin_menu` at the default priority or later. Check that the companion API is available before calling it. The helper returns the normal WordPress settings screen hook suffix, which can be used to scope admin assets.

```php
add_action('admin_menu', function () {
    if (function_exists('P20\\Plugins\\add_settings_page')) {
        \P20\Plugins\add_settings_page('p20-example', 'Example', 'example_render_settings');
    }
});
```

P20 Plugins checks the [P20.dev GitHub releases](https://github.com/p20dev/wordpress/releases) during native WordPress plugin update checks. It supplies its own View details dialog. Integrations remain responsible for their own updates and details.

Licensed under GPL-2.0-or-later. See [LICENSE](LICENSE).
