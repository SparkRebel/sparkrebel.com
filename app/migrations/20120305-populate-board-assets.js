/**
 * Populate boards with the references for the last 4 post-assets
 */
db.boards.update(
	{},
	{$set: {images: []}},
	null,
	true
);
db.posts.ensureIndex({created: -1});

i = 0;
conditions = {};

count = db.posts.count(conditions);
print('Processing ' + count + ' posts');
db.posts.find(conditions, {board: true, image:true}).sort({created: -1}).forEach(function( row ) {

	if (row.deleted) {
		return;
	}
	if (row.image) {
		db.boards.update(
			{_id: row.board.$id},
			{$push: {images: row.image}}
		);
	}

	if (!(i % 1000)) {
		print('processed ' + i + '/' + count + ' posts');
	}
	i++;
} );

i = 0;
conditions = {};

count = db.boards.count(conditions);
print('Processing ' + count + ' boards');
db.boards.find(conditions, {images: true}).forEach(function( row ) {
	db.boards.update(
		{_id: row._id},
		{$set: {images: row.images.slice(-4)}}
	);

	if (!(i % 1000)) {
		print('processed ' + i + '/' + count + ' boards');
	}
	i++;
});
print('done');
