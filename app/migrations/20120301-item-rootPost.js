count = db.items.count();
num = 0;
print("Adding item.rootPost (" + count + ")");

db.items.find({}, {_id:1}).forEach(function(row) {
    post = db.posts.findOne({
        parent: null,
        'target.$ref': 'items',
        'target.$id': row._id,
    }, {_id: 1});
    
    if(post) {
        db.items.update({_id: row._id}, {$set: {rootPost: {'$ref':'posts', '$id':post._id}}});
    } else {
        print(row._id + ' is missing a post');
    }
    
    if (!(++num % 1000)) {
        print('processed ' + num + '/' + count + ' items');
    }
});

print('done');
