#!/bin/bash

sh_dir='docker/php/sh'

source $sh_dir/colors.sh

# logo
echo -e "${logoColor}"
cat logo.txt
echo -e "${noneColor}\n"

if [ -f "./.env" ]; then
  echo -e ${warningColor}"  WARNING: All app data (including database) will be reset!!!  "${noneColor}
  read -n 1 -p "$( echo -e ${warningColor}"  Do you wish to continue? [y/n]:  "${noneColor})  " confirmation
  echo -e "\n"

  if [[ ${confirmation} != "Y" && ${confirmation} != "y" ]]; then
    echo_warning "ABORTED!"
    exit 1
  else
    docker-compose down -v
  fi
fi

echo_step "environnement variables"
while IFS= read -r line; do

  MYLINE="$(cut -d'#' -f1 <<<"$line")"
  MYLINE=${MYLINE#"${MYLINE%%[![:space:]]*}"} # trim leading whitespace
  MYLINE=${MYLINE%"${MYLINE##*[![:space:]]}"} # trim trailing whitespace
  if [[ $MYLINE == *"="* ]]; then

    VARIABLE="$(cut -d'=' -f1 <<<"$MYLINE")"
    VARIABLE=${VARIABLE#"${VARIABLE%%[![:space:]]*}"} # trim leading whitespace
    VARIABLE=${VARIABLE%"${VARIABLE##*[![:space:]]}"} # trim trailing whitespace

    DEFAULT="$(cut -d'=' -f2 <<<"$MYLINE")"
    DEFAULT=${DEFAULT#"${DEFAULT%%[![:space:]]*}"} # trim leading whitespace
    DEFAULT=${DEFAULT%"${DEFAULT##*[![:space:]]}"} # trim trailing whitespace

    MORE=''

    if [ "${DEFAULT}" == "password" ]; then
      DEFAULT=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 12 | head -n 1)
      MORE=" (auto-generated)"
    fi

    if [ "${DEFAULT}" == "secret" ]; then
      DEFAULT=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 40 | head -n 1)
      MORE=" (auto-generated)"
    fi

    read -p "$(echo -e "    ${varColor} ${VARIABLE} ${noneColor} [Default: ${valColor} ${DEFAULT}${MORE} ${noneColor}]: ")" VALUE </dev/tty

    VALUE=${VALUE:-${DEFAULT}}
    VALUE=${VALUE#"${VALUE%%[![:space:]]*}"} # trim leading whitespace
    VALUE=${VALUE%"${VALUE##*[![:space:]]}"} # trim trailing whitespace

    DOTENV+="${VARIABLE}=${VALUE}\n"

  fi

done <.env.dist

truncate -s 0 .env
echo -e "${DOTENV}" >>.env

echo_step "docker build"
docker-compose -f docker-compose.yml build
docker-compose -f ../nginx-proxy/docker-compose.yml stop
docker-compose -f ../nginx-proxy/docker-compose.yml up -d

echo_step "docker up"
make start

make install

echo_step "docker reload"
make start

echo_success "App successfully installed!"
