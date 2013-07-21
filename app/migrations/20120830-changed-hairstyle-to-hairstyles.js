db.categories.findAndModify({
     query: {name : 'Hairstyle'},
     update: {$set: {name: 'Hairstyles', slug : 'hairstyles'}},
     new: false
});