db.categories.findAndModify({
     query: {name : 'Promos'},
     update: {$set: {name: 'Sales & Promos', slug : 'sales-promos', isSeparated: true}},
     new: false
});