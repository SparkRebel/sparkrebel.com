
var now = new Date();



db.areas.update(
	{
		name : {
			$in: [
				'Sporty',
				'Trendy',			
			]
		}
	},
    {$set: {isActive: false}},
	null,
	true
);

db.areas.findAndModify({
     query: {name : 'Romantic & Sweet'},
     update: {$set: {
         isActive: true,
         name: 'Romantic',
         slug: 'romantic',
         created: now,
         modified: now
        }
     },
     upsert: true
});

db.areas.findAndModify({
     query: {name : 'Vintage Lover'},
     update: {$set: {
         isActive: true,
         name: 'Vintage & Retro',
         slug: 'vintage-and-retro',
         created: now,
         modified: now
        }
     },
     upsert: true
});

db.areas.insert(
	{
        isActive: true,
        name: 'Classic & Sophisticated',
        slug: 'classic-and-sophisticated',
        created: now,
        modified: now
    }		
);

db.areas.insert(
	{
        isActive: true,
        name: 'Indie & Edgy',
        slug: 'indie-and-edgy',
        created: now,
        modified: now
    }		
);

db.areas.insert(
	{
        isActive: true,
        name: 'Basic & Comfort',
        slug: 'basic-and-comfort',
        created: now,
        modified: now
    }		
);

db.areas.insert(
	{
        isActive: true,
        name: 'Rocker Chic',
        slug: 'rocker-chick',
        created: now,
        modified: now
    }		
);

 
