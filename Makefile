include .env
export

install:
	docker exec -it ${APP_NAME}_php bash /www/docker/php/sh/install.sh -p ${JWT_PASSPHRASE} -sau ${SUPER_ADMIN_USERNAME} -sae ${SUPER_ADMIN_EMAIL} -sap ${SUPER_ADMIN_PASSWORD}

start: stop
	docker-compose -f docker-compose.yml up -d

watch:
	docker-compose -f docker-compose.yml up

stop:
	docker-compose -f docker-compose.yml stop

bash:
	docker exec -it -u dev ${APP_NAME}_php bash

bash-root:
	docker exec -it ${APP_NAME}_php bash

bash-nginx:
	docker exec -it ${APP_NAME}_nginx bash

mysql:
	docker exec -it ${APP_NAME}_mysql mysql -u${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE}

dump-import:
	docker exec ${APP_NAME}_mysql mysql -u${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE}  < ${FILE}

dump-export:
	docker exec ${APP_NAME}_mysql mysqldump -u${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE} > var/dumps/vgc-dump-`date + '%F'`.sql

dump-ftp: dump-export
	docker exec ${APP_NAME}_php /www/docker/php/sh/dump-ftp.sh

admin:
	xdg-open http://${ADMIN_HOST}

update:
	git stash
	git pull
	git stash pop
	docker exec ${APP_NAME}_php bash /sh/update.sh
	make start
