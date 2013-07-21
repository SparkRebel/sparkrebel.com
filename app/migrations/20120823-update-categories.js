
var now = new Date();




db.categories.findAndModify({
     query: {name : 'Outfits'},
     update: {$set: {name: 'Outfits & Total Looks', slug : 'outfits-and-total-looks'}},
     new: false
});

db.categories.findAndModify({
     query: {name : 'Runway & Designers'},
     update: {$set: {name: 'Runaway', slug : 'runaway'}},
     new: false
});

db.categories.findAndModify({
     query: {name : 'Runway & Designers'},
     update: {$set: {name: 'Runaway', slug : 'runaway'}},
     new: false
});

db.categories.findAndModify({
     query: {name : 'Formal'},
     update: {$set: {name: 'Formal & Evening Wear', slug : 'formal-and-evening-wear'}},
     new: false
});

db.categories.insert( 
	{
	    isActive: true,
	    name: 'Hairstyles',
	    slug: 'hairstyles',
	    type: 'user',
	    created: now,
	    modified: now,
	    weight: 91
	}
    
);

db.categories.insert(
	{
        isActive: true,
        name: 'Plus size',
        slug: 'plus-size',
        type: 'user',
        created: now,
        modified: now,
        weight : 141

    }
);