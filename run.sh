#!/bin/bash
# mkdir oak
# rm -rf ./oak
HOST=develop@gbdev.xyz
PORT=9022
PROJECT_PATH=www/pravo.gbdev.xyz/
PROJECT_NAME=pravo-rosta
DUMP_NAME=db.sql

ssh $HOST -p $PORT  'bash -s' < mysqldump.sh -- $PROJECT_PATH $DUMP_NAME
# scp -P 9022 develop@gbdev.xyz:~/www/oak2.gbdev.xyz/db.sql ./oak
rsync -avz --delete --exclude-from 'exclude.txt' -e "ssh -p $PORT" $HOST:$PROJECT_PATH ~/backup-gb/$PROJECT_NAME
# rsync -avz --delete --exclude-from 'exclude.txt' -e "ssh -p $PORT" $HOST: ./$PROJECT_NAME
ssh $HOST -p $PORT  'bash -s' < delete.sh -- $PROJECT_PATH$DUMP_NAME
