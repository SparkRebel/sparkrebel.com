#!/bin/bash

echo "Dumping each collection individually..."

mongodump -h db1 -d plum -o plum -c activities
mongodump -h db1 -d plum -o plum -c activity
mongodump -h db1 -d plum -o plum -c aliases
mongodump -h db1 -d plum -o plum -c areas
mongodump -h db1 -d plum -o plum -c assets
mongodump -h db1 -d plum -o plum -c boards
mongodump -h db1 -d plum -o plum -c brand_merchant_whitelist
mongodump -h db1 -d plum -o plum -c categories
mongodump -h db1 -d plum -o plum -c cms_pages
mongodump -h db1 -d plum -o plum -c doctrine_increment_ids
mongodump -h db1 -d plum -o plum -c emails
echo " - Skipping: eventlog"
# mongodump -h db1 -d plum -o plum -c eventlog
mongodump -h db1 -d plum -o plum -c features
echo " - Skipping: feed_items"
# mongodump -h db1 -d plum -o plum -c feed_items
mongodump -h db1 -d plum -o plum -c feedback
mongodump -h db1 -d plum -o plum -c flags
mongodump -h db1 -d plum -o plum -c follows
mongodump -h db1 -d plum -o plum -c invite_codes
mongodump -h db1 -d plum -o plum -c invite_requests
mongodump -h db1 -d plum -o plum -c items
mongodump -h db1 -d plum -o plum -c notifications
mongodump -h db1 -d plum -o plum -c outfits
mongodump -h db1 -d plum -o plum -c partner_requests
mongodump -h db1 -d plum -o plum -c posts
mongodump -h db1 -d plum -o plum -c posts_activity
mongodump -h db1 -d plum -o plum -c sources
mongodump -h db1 -d plum -o plum -c system.indexes
mongodump -h db1 -d plum -o plum -c system.profile
mongodump -h db1 -d plum -o plum -c tags
mongodump -h db1 -d plum -o plum -c users

echo "Done"
