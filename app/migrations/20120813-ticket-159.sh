#!/bin/sh

# Ticket #159
# https://sr.codebasehq.com/projects/sparkrebelcom/tickets/159

app/console --env=prod --no-debug category:rename "Outfits" "Outfits & Total Looks"
app/console --env=prod --no-debug category:rename "Runway & Designers" "Runway"
app/console --env=prod --no-debug category:rename "Gifts & Wish Lists" "Gifts"
app/console --env=prod --no-debug category:rename "Formal" "Formal & Eveningwear"

app/console --env=prod --no-debug category:add "Plus Size" "user" 0 145
app/console --env=prod --no-debug category:add "Latest Trends" "user" 0 115
app/console --env=prod --no-debug category:add "Hairstyle" "user" 0 95
