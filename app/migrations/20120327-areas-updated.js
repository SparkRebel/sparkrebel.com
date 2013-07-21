// Bohemian Chic has a new name
// Delete all the obsolete areas
// Create some more areas

var now = new Date();

db.areas.update(
	{name : 'Bohemian Chic'},
	{$set: {name: 'Boho Chic'}}
);

db.areas.update(
	{
		name : {
			$in: [
				'Accessory Queen',
				'Bargain Huntress',
				'Beauty Bombshell',
				'DIY Dame',
				'Jewlery Junkie',
				'Obsessed with Dresses',
				'Romantic & Sweet',
				'Shoe Girl',
				'T-shirt & Jeans Girl',
			]
		}
	},
    {$set: {isActive: false}},
	null,
	true
);

result = db.areas.findAndModify({
     query: {name : 'Romantic & Sweet'},
     update: {$set: {
         isActive: true,
         slug: 'romantic-and-sweet',
         created: now,
         modified: now
        }
     },
     upsert: true
});

db.areas.findAndModify({
     query: {name : 'Sporty'},
     update: {$set: {
         isActive: true,
         slug: 'sporty',
         created: now,
         modified: now
        }
     },
     upsert: true
});

db.areas.findAndModify({
     query: {name : 'Trendy'},
     update: {$set: {
         isActive: true,
         slug: 'trendy',
         created: now,
         modified: now
        }
     },
     upsert: true
});
