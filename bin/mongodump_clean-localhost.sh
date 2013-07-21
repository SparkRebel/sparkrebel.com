#!/bin/bash

echo "Dumping each collection individually..."

mongodump -d plum -o plum -c activities
mongodump -d plum -o plum -c activity
mongodump -d plum -o plum -c aliases
mongodump -d plum -o plum -c areas
mongodump -d plum -o plum -c assets
mongodump -d plum -o plum -c boards
mongodump -d plum -o plum -c brand_merchant_whitelist
mongodump -d plum -o plum -c categories
mongodump -d plum -o plum -c cms_pages
mongodump -d plum -o plum -c doctrine_increment_ids
mongodump -d plum -o plum -c emails
echo " - Skipping: eventlog"
# mongodump -d plum -o plum -c eventlog
mongodump -d plum -o plum -c features
echo " - Skipping: feed_items"
# mongodump -d plum -o plum -c feed_items
mongodump -d plum -o plum -c feedback
mongodump -d plum -o plum -c flags
mongodump -d plum -o plum -c follows
mongodump -d plum -o plum -c invite_codes
mongodump -d plum -o plum -c invite_requests
mongodump -d plum -o plum -c items
mongodump -d plum -o plum -c notifications
mongodump -d plum -o plum -c outfits
mongodump -d plum -o plum -c partner_requests
mongodump -d plum -o plum -c posts
mongodump -d plum -o plum -c posts_activity
mongodump -d plum -o plum -c sources
mongodump -d plum -o plum -c system.indexes
mongodump -d plum -o plum -c system.profile
mongodump -d plum -o plum -c tags
mongodump -d plum -o plum -c users

echo "Done"
