/**
 * loop on all collections and set isActive to either true or false IF MISSING
 * based on the value of deleted. Ignore a few collections that are irrelevant
 *
 * Prints in a tabular format.
 *
 * Call as:
 *     $ mongo <databasename> <path to this script>
 */

collections = db.getCollectionNames();
exclude = [
    'doctrine_increment_ids',
    'system.indexes',
    'eventlog'
];

for(i in collections) {
    if (exclude.indexOf(collections[i]) > -1) {
        continue;
    }

    print("Processing " + collections[i]);
    cursor = db[collections[i]].find({isActive: {$exists: false}}, {deleted: true});

    while (cursor.hasNext()) {
      row = cursor.next();
      if (row.deleted) {
        db[collections[i]].update({_id: row._id}, {$set: {isActive: false}});
      } else {
        db[collections[i]].update({_id: row._id}, {$set: {isActive: true}});
      }
    }
}
