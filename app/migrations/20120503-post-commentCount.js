posts = db.posts.find();

count = posts.count();

print('Processing '  + count + ' posts');

i = 0;
posts.forEach(function(row) {
    comments = db.posts_activity.find({'post.$id': row._id}).count();
    
    if (comments != row.commentCount) {
        db.posts.update({_id: row._id}, {$set: {commentCount: comments}});
    }
    
    if (!(i % 1000)) {
        print('processed ' + i + '/' + count + ' posts');
    }
    i++;
});
