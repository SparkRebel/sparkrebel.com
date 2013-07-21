#!/bin/bash

set -e

cd /var/www/sparkrebel.com/current
app/console celeb:process:all --env=prod --no-debug >> app/logs/cron-celeb_process_all.log 2>&1
