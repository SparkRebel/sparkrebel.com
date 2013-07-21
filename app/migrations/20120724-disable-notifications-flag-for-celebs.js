db.users.findAndModify({
     query: {name : 'Celebs'},
     update: {$set: {disabledNotifications: true}},
     new: false
});