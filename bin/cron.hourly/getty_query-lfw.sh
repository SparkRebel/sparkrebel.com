#!/bin/bash

set -e

cd /var/www/sparkrebel.com/current
app/console getty:query "london fashion week" "London Fashion Week" --env=prod
