db.categories.findAndModify({
     query: {name : 'Prom & Formal'},
     update: {$set: {name: 'Formal', slug : 'formal'}},
     new: false
});