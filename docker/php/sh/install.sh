#!/bin/bash

# args
POSITIONAL=()
while [[ $# -gt 0 ]]; do
  key="$1"

  case $key in
  -p)
    PASSPHRASE="$2"
    shift # past argument
    shift # past value
    ;;
  -sau)
    SUPER_ADMIN_USERNAME="$2"
    shift # past argument
    shift # past value
    ;;
  -sae)
    SUPER_ADMIN_EMAIL="$2"
    shift # past argument
    shift # past value
    ;;
  -sap)
    SUPER_ADMIN_PASSWORD="$2"
    shift # past argument
    shift # past value
    ;;
  --default)
    DEFAULT=YES
    shift # past argument
    ;;
  *) # unknown option
    POSITIONAL+=("$1") # save it in an array for later
    shift              # past argument
    ;;
  esac
done
set -- "${POSITIONAL[@]}" # restore positional parameters

sh_dir="$(dirname "$0")"

source $sh_dir/colors.sh
source $sh_dir/bashrc.sh

cd /www

echo_step "apt-get update / install"
apt-get update
apt-get install -y wget git zip unzip
pecl install -o -f zip &&
  echo "extension=zip.so" >>/usr/local/etc/php/conf.d/php.ini

echo_step "openssl"
mkdir -p var/jwt
apt-get install openssl
openssl genrsa -aes128 -passout pass:${PASSPHRASE} -out var/jwt/private.pem 4096
openssl rsa -in var/jwt/private.pem -passin pass:${PASSPHRASE} -pubout -out var/jwt/public.pem

echo_step "composer install"
source $sh_dir/install-composer.sh
composer install

echo_step "database"

bin/console doctrine:schema:drop --force
bin/console doctrine:schema:update --force
bin/console fos:user:create ${SUPER_ADMIN_USERNAME} ${SUPER_ADMIN_EMAIL} ${SUPER_ADMIN_PASSWORD} --super-admin

echo_step "cache clear"
bin/console cache:clear --env=dev
bin/console cache:clear --env=prod

echo_step "chown www-data /www/var"
chown -R www-data:www-data /www/var
chmod -R g+w /www/var
chmod +x bin/console
