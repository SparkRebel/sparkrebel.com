#!/bin/bash

set -e

cd /var/www/sparkrebel.com/current
app/console getty:query "new york fashion week" "New York Fashion Week" --env=prod
