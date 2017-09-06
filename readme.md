# KriosCare

Application environment utilizing the following libraries or frameworks:

* Laravel 5.5
* Vuejs 2
* Bootstrap 4

A development environment is provided in the form of Laravel Homestead for vagrant.

## Server Requirements

* PHP 7.0+
* MySQL 5.6+ or MariaDB 10+

## Front-end Development

Sass and Javascript resources are contained in resources/assets.  Public files are contained in public.

Front end resources are compiled by [Laravel Mix](https://laravel.com/docs/5.5/mix).  To install Laravel Mix and the dependencies, run `npm install`.  Once installed, you may run the following commands:

* `npm run watch` will watch for changes and compile a dev version of assets.
* `npm run dev` will compile dev version of all assets.
* `npm run prod` will compile a production version of all assets (stripped and minified)

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, yet powerful, providing tools needed for large, robust applications. A superb combination of simplicity, elegance, and innovation give you tools you need to build any application with which you are tasked.

## Learning Laravel

Laravel has the most extensive and thorough documentation and video tutorial library of any modern web application framework. The [Laravel documentation](https://laravel.com/docs) is thorough, complete, and makes it a breeze to get started learning the framework.

If you're not in the mood to read, [Laracasts](https://laracasts.com) contains over 900 video tutorials on a range of topics including Laravel, modern PHP, unit testing, JavaScript, and more. Boost the skill level of yourself and your entire team by digging into our comprehensive video library.
