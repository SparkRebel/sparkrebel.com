db.posts.find({isVideoPost: true}).sort({created: -1}).forEach(function( row ) {

	if (row.deleted) {
		return;
	}
	
	asset = db.assets.findOne({'_id': ObjectId(row.image.$id)});
	db.posts.update({_id: row._id}, {$set: {link: asset.sourceUrl}});
	
} );