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
