/**
 * loop on all brand/merchant users, and update the type key for all collections that reference them
 *
 * Call as:
 *     $ mongo <databasename> <path to this script>
 */

collections = db.getCollectionNames();
defaultFields = ['createdBy', 'modifiedBy', 'deletedBy'];
exclude = [
   'doctrine_increment_ids',
   'system.indexes'
];

print("Creating indexes ...");

for(i in collections) {
	if (exclude.indexOf(collections[i]) > -1) {
		continue;
	}
	collection = collections[i];

	fields = [];
	for(j in defaultFields) {
		fields.push(defaultFields[j]);
	}
	if (collection === 'features') {
		fields.push('target');
	} else if (collection === 'flags') {
		fields.push('target');
		fields.push('targetUser');
	} else if (collection === 'follows') {
		fields.push('follower');
		fields.push('target');
		fields.push('user');
	} else if (collection === 'items') {
		fields.push('merchantUser');
		fields.push('brandUser');
	}

	for(j in fields) {
		field = fields[j];
		print("\t" + collection + " " + field + " index");
		db[collection].ensureIndex({field: 1});
	}
}
print("\tDone");

users = db.users.find({
    // _id: ObjectId("4f4c843a123ae69e35000000"), // Unique Vintage
	type: {$ne: "user"}
});
while (users.hasNext()) {
    user = users.next();
    print("Processing " + user.name + " (" + user.type + ") ...");

	for(i in collections) {
		if (exclude.indexOf(collections[i]) > -1) {
			continue;
		}
		collection = collections[i];

		fields = [];
		for(j in defaultFields) {
			fields.push(defaultFields[j]);
		}
		if (collection === 'features') {
			fields.push('target');
		} else if (collection === 'flags') {
			fields.push('target');
			fields.push('targetUser');
		} else if (collection === 'follows') {
			fields.push('follower');
			fields.push('target');
			fields.push('user');
		} else if (collection === 'items') {
			fields.push('merchantUser');
			fields.push('brandUser');
		}

		for(j in fields) {
			field = fields[j];

			conditions = {};
			conditions[field + ".$id"] = user._id;
			if (field !== "target") {
				conditions[field + ".type"] = {$ne: user.type};
			}

			count = db[collection].count(conditions);
			if (count) {

				update = {$set: {}};
				update["$set"][field] = {
					$ref: 'users',
					$id: user._id,
					type: user.type
				};

				if (field === "target") {
					update["$set"][field]['_doctrine_class_name'] = "PW\\UserBundle\\Document\\" + user.type.charAt(0).toUpperCase() + user.type.slice(1);
				}

				db[collection].update(conditions, update, null, true);
				print("\tCorrected " + count + " " + collection + " records which did not have type " + user.type + " for " + field);
			}
		}
	}
	print("\tDone");
}
