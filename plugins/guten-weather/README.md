# Guten Weather

Guten Weather by [P20.dev](https://wordpress.p20.dev) displays local forecasts in the WordPress block editor and on the front end. It requires WordPress 6.5 or newer and PHP 7.4 or newer.

Install and activate the ZIP. Guten Weather requires the P20 Plugins companion for its settings. If prompted, click **Install and activate P20 Plugins**. You can also install the [companion ZIP](https://github.com/p20dev/wordpress/releases/tag/p20-plugins-v1.0.0) through the WordPress plugin uploader. Then open **P20 Plugins → Guten Weather**. Add cities by name or enter coordinates manually, choose a temperature unit, and save. Insert the **Guten Weather** block into a page, post, or template. The block sidebar controls the layout, forecast details, and styling.

## External services

- [MET Norway Locationforecast](https://api.met.no/weatherapi/locationforecast/2.0/documentation) receives the configured coordinates and the site's URL for client identification. Forecasts are cached server-side. The data is licensed under [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/). Fahrenheit values are converted from the original Celsius data. Include license information and disclose conversions on an easy-to-find credits page. See [MET Norway's attribution guidance](https://docs.api.met.no/doc/FAQ.html#how-do-i-give-credit-to-met-norway-for-using-your-data).
- [Open-Meteo Geocoding](https://open-meteo.com/en/docs/geocoding-api) receives the city name when an administrator uses city search. Its hosted free service is for non-commercial use under [Open-Meteo's terms](https://open-meteo.com/en/terms). Manual coordinate entry is available.
- WordPress queries the [P20.dev GitHub releases](https://github.com/p20dev/wordpress/releases) for plugin updates.

## Code license

Guten Weather is licensed under GPL-2.0-or-later. See [LICENSE](LICENSE).
