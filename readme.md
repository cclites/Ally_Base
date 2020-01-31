# ALLY

Application environment utilizing the following libraries or frameworks:

* Laravel 5.5
* Vuejs 2
* Bootstrap 4

A development environment is provided in the form of Laravel Homestead for vagrant.

## Server Requirements

* PHP 7.2+
* MySQL 5.6+ or MariaDB 10+

## Front-end Development

Sass and Javascript resources are contained in resources/assets.  Public files are contained in public.

Front end resources are compiled by [Laravel Mix](https://laravel.com/docs/5.5/mix).  To install Laravel Mix and the dependencies, run `npm install`.  Once installed, you may run the following commands:

* `npm run watch` will watch for changes and compile a dev version of assets.
* `npm run dev` will compile dev version of all assets.
* `npm run prod` will compile a production version of all assets (stripped and minified)
* `npm run hot` will compile dev version of all assets and enable the hot reloading feature.

## Dependencies

### PHP
* [laravel-snappy](https://packagist.org/packages/barryvdh/laravel-snappy)
* [h4cc/wkhtmltopdf-amd64](https://packagist.org/packages/h4cc/wkhtmltopdf-amd64)
* [h4cc/wkhtmltoimage-amd64](https://packagist.org/packages/h4cc/wkhtmltoimage-amd64)
* [bizhub/impersonate](https://packagist.org/packages/bizhub/impersonate)
* [giggsey/libphonenumber-for-php](https://packagist.org/packages/giggsey/libphonenumber-for-php)
* [inacho/php-credit-card-validator](https://packagist.org/packages/inacho/php-credit-card-validator)
* [maatwebsite/excel](https://packagist.org/packages/maatwebsite/excel)
* [mikehaertl/php-pdftk](https://packagist.org/packages/mikehaertl/php-pdftk)
* [myclabs/php-enum](https://packagist.org/packages/myclabs/php-enum)
* [nacha/file-generator](https://packagist.org/packages/nacha/file-generator)
* [owen-it/laravel-auditing](https://packagist.org/packages/owen-it/laravel-auditing)
* [phpseclib/phpseclib](https://packagist.org/packages/phpseclib/phpseclib)
* [quickbooks/v3-php-sdk](https://packagist.org/packages/quickbooks/v3-php-sdk)
* [rlanvin/php-rrule](https://packagist.org/packages/rlanvin/php-rrule)
* [s-ichikawa/laravel-sendgrid-driver](https://packagist.org/packages/s-ichikawa/laravel-sendgrid-driver)
* [sentry/sentry](https://packagist.org/packages/sentry/sentry)
* [sentry/sentry-laravel](https://packagist.org/packages/sentry/sentry-laravel)
* [tplaner/when](https://packagist.org/packages/tplaner/when)
* [twilio/sdk](https://packagist.org/packages/twilio/sdk)
* [ua-parser/uap-php](https://packagist.org/packages/ua-parser/uap-php)
