/**
 * Copy from the old items collection to the new
 *
 */

/**
 * Where are we importing from - where is the old items collection
 */
hostSource="localhost";
dbSource="plum-v1";

/**
 * Where are we importing to - where is the feed_items collection
 */
hostTarget="localhost";
dbTarget="plum";

/**
 * End configuration - don't edit below this line
 */

source = connect(hostSource + '/' + dbSource);
target = connect(hostTarget + '/' + dbTarget);

print('Creating Feed Item entries');
target.feed_items.drop();
target.feed_items.ensureIndex({fid: 1}, {unique:true, dropDups: true});
target.items.ensureIndex({feedId: 1}, {unique:true, dropDups: true});
target.boards.ensureIndex({'createdBy.$id': 1, name: 1}, {unique:true, dropDups: true});

conditions = {
	$nor: [
		{unsearchable:true},
		{hidden:true},
		{discontinued:{$gt: 0}},
		{categories:{$size:0}},
		{categories:null},
		{categories:{$exists: false}}
	]
};
i = 0;
count = source.items.count(conditions);
source.items.find(conditions).forEach(function( row ) {

	score = 0;
	category = null;
	for (cat in row.categories) {
		if (row.categories[cat] > score) {
			score = row.categories[cat];
			category = cat;
		}
	}
	categories = [category];

	price = null;
	for (j in row.price) {
		price = row.price[j];
	}

	target.feed_items.insert(
		{
			brand: row.brand,
			categories: categories,
			categoriesMeta: row.categoriesMeta,
			description: row.description,
			fid: row.fid,
			name: row.name,
			main_image: row.image,
			images: [row.image],
			link: row.link,
			merchant: row.merchant,
			modified: new Date(row.modified * 1000),
			price: price,
			priceHistory: row.price,
			status: 'pending',
			action: 'created'
		},
		{
			fsync: true,
			safe: true
		}
	);

	target.items.insert(
		{
			_id: row._id,
			feedId: row.fid
		}
	);

	if (!(i % 1000)) {
		print('processed ' + i + '/' + count + ' items');
	}
	i++;
} );
print('done');

print('Creating Brands');
target.brands.ensureIndex({name: 1}, {unique:true, dropDupes: true});
brands = target.feed_items.distinct('brand');
for(i in brands) {
	brand = brands[i];
	target.brands.insert({name: brand});
}
print(i + ' found');
print('done');

print('Creating Merchants');
target.merchants.ensureIndex({name: 1}, {unique:true, dropDupes: true});
merchants = target.feed_items.distinct('merchant');
for(i in merchants) {
	merchant = merchants[i];
	target.merchants.insert({name: merchant});
}
print(i + ' found');
print('done');

print('Creating Categories');
target.categories.ensureIndex({name: 1}, {unique:true, dropDupes: true});
categories = target.feed_items.distinct('categories');
for(i in categories) {
	category = categories[i];
	target.categories.insert({name: category, type: 'item'});
}
print(i + ' found');
print('done');
