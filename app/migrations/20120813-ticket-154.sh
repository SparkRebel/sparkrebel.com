#!/bin/sh

# Ticket #154
# https://sr.codebasehq.com/projects/sparkrebelcom/tickets/154

app/console --env=prod --no-debug area:rename --name="Romantic & Sweet" --new_name="Romantic"
app/console --env=prod --no-debug area:rename --name="Vintage Lover" --new_name="Vintage & Retro"

app/console --env=prod --no-debug area:create --name="Classic & Sophisticated"
app/console --env=prod --no-debug area:create --name="Basic & Comfort"
app/console --env=prod --no-debug area:create --name="Rocker Chic"

app/console --env=prod --no-debug area:delete --name="Obsessed with Dresses"
app/console --env=prod --no-debug area:delete --name="T-shirt & Jeans Girl"
app/console --env=prod --no-debug area:delete --name="Beauty Bombshell"
app/console --env=prod --no-debug area:delete --name="DIY Dame"
app/console --env=prod --no-debug area:delete --name="Accessory Queen"
app/console --env=prod --no-debug area:delete --name="Shoe Girl"
app/console --env=prod --no-debug area:delete --name="Bargain Huntress"
app/console --env=prod --no-debug area:delete --name="Jewlery Junkie"
app/console --env=prod --no-debug area:delete --name="Sporty"
app/console --env=prod --no-debug area:delete --name="Trendy"
