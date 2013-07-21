boards = db.boards.find();

count = boards.count();

print('Processing '  + count + ' posts');

i = 0;
boards.forEach(function(row) {
    cnt = db.posts.find({'board.$id': row._id}).count();
    
    if (cnt != row.postCount) {
        db.boards.update({_id: row._id}, {$set: {postCount: cnt}});
    }
    
    if (!(i % 100)) {
        print('processed ' + i + '/' + count + ' posts');
    }
    i++;
});
