#!/bin/bash

sh_dir="$(dirname "$0")"

source $sh_dir/colors.sh
source $sh_dir/bashrc.sh

cd /www

echo_step "composer install"
composer install

echo_step "database"
bin/console doctrine:schema:update --force --dump-sql

echo_step "cache clear"
bin/console cache:clear --env=dev
bin/console cache:clear --env=prod

echo_step "chown www-data /www/var"
chown -R www-data:www-data /www/var
chmod -R g+w /www/var
