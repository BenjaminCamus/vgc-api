#!/bin/sh

export $(egrep -v '^#' /www/.env | xargs)

cd /www/dumps

# DUMP
DUMPFILE=vgc-dump-$(date +%Y-%m-%d).sql

ftp -pnv ${FTP_HOST} ${FTP_PORT} << END_SCRIPT
quote USER ${FTP_LOGIN}
quote PASS ${FTP_PASSWORD}
cd ${FTP_DUMP_FOLDER}
binary
put ${DUMPFILE}
close
quit
END_SCRIPT
