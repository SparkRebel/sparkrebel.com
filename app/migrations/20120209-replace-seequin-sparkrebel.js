
db.cms_pages.find({$or: [{title: /seequin/i}, {slug: /seequin/i}, {content: /seequin/i}]})
	.forEach(function(x) {
		db.cms_pages.update(
			{_id: x._id},
			{$set: {
				title: x.title.replace(/Seequin/g, 'SparkRebel').replace(/seequin/g, 'sparkrebel'),
				slug: x.slug.replace(/Seequin/g, 'SparkRebel').replace(/seequin/g, 'sparkrebel'),
				content: x.content.replace(/Seequin/g, 'SparkRebel').replace(/seequin/g, 'sparkrebel').replace(/SEEQUIN/g, 'SPARKREBEL')
			}}
		);
		print('Replaced all occurances of Seequin with SparkRebel in ' + x.title);
	});
