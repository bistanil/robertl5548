#!/bin/bash
touch app.tar.gz
tar -zcvf app.tar.gz app bootstrap config database packages resources routes storage tests vendor public_html .htaccess artisan composer.json composer.lock package.json phpunit.xml readme.md webpack.mix.js .env .env.example --exclude="public_html/public/photos" --exclude="public_html/public/files"