/**
 * Convert asset urls to asset references
 */
reHash = /\/([0-9a-f]{40})\./;
db.assets.ensureIndex({
	hash: 1,
	sourceUrl: 1,
	url: 1
});

i = 0;
conditions = {icon: {$type: 2}};

count = db.users.count(conditions);
print('Converting user icons (' + count + ')');
db.users.find(conditions).forEach(function( row ) {

	hash = 'missing';
	match = row.icon.match(reHash);
	if (match) {
		hash = match[1];
	}

	asset = db.assets.findOne({
		'$or': [
			{hash: hash},
			{sourceUrl: row.icon},
			{url: row.icon}
		]
	});
	if (asset) {
		db.users.update({_id: row._id}, {$set: {icon: {"$ref": 'assets', "$id": asset._id}}});
	} else {
		print('user icon ' + row.icon + ' doesn\'t exist in the assets collection - removing');
		db.users.update({_id: row._id}, {$set: {icon: null}});
	}

	if (!(i % 1000)) {
		print('processed ' + i + '/' + count + ' users');
	}
	i++;
} );
print('done');

i = 0;
conditions = {image: {$type: 2}};

count = db.posts.count(conditions);
print('Converting post images (' + count + ')');
db.posts.find(conditions).forEach(function( row ) {

	hash = 'missing';
	match = row.image.match(reHash);
	if (match) {
		hash = match[1];
	}

	asset = db.assets.findOne({
		'$or': [
			{hash: hash},
			{sourceUrl: row.image},
			{url: row.image}
		]
	});
	if (asset) {
		db.posts.update({_id: row._id}, {$set: {image: {"$ref": 'assets', "$id": asset._id}}});
	} else {
		print('user image ' + row.image + ' doesn\'t exist in the assets collection - removing');
		db.posts.update({_id: row._id}, {$set: {image: null}});
	}

	if (!(i % 1000)) {
		print('processed ' + i + '/' + count + ' posts');
	}
	i++;
} );
print('done');

i = 0;
conditions = {imagePrimary: {$type: 2}};

count = db.items.count(conditions);
print('Converting post images (' + count + ')');
db.items.find(conditions).forEach(function( row ) {

	hash = 'missing';
	match = row.imagePrimary.match(reHash);
	if (match) {
		hash = match[1];
	}

	asset = db.assets.findOne({
		'$or': [
			{hash: hash},
			{sourceUrl: row.imagePrimary},
			{url: row.imagePrimary}
		]
	});
	if (asset) {
		db.items.update({_id: row._id}, {$set: {
			imagePrimary: {"$ref": 'assets', "$id": asset._id},
			images: [{"$ref": 'assets', "$id": asset._id}]
		}});
	} else {
		print('user imagePrimary ' + row.imagePrimary + ' doesn\'t exist in the assets collection - removing');
		db.items.update({_id: row._id}, {$set: {imagePrimary: null, images: []}});
	}

	if (!(i % 1000)) {
		print('processed ' + i + '/' + count + ' items');
	}
	i++;
} );
print('done');

db.assets.dropIndex({
	hash: 1,
	sourceUrl: 1,
	url: 1
});

