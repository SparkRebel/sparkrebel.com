db.posts.findAndModify({query: {isCeleb : true}, update: {$set: {deleted: new Date, isActive: false}}});
