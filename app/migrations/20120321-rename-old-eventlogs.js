/**
 * Correct for renamed events
 */

db.eventlog.update({event: "asset.new"}, {$set: {event: "asset.create"}}, null, true);
db.eventlog.update({event: "post.new"}, {$set: {event: "post.create"}}, null, true);
db.eventlog.update({event: "follow.board"}, {$set: {event: "board.follow"}}, null, true);
db.eventlog.update({event: "follow.board.new"}, {$set: {event: "board.create"}}, null, true);
db.eventlog.update({event: "follow.user"}, {$set: {event: "user.follow"}}, null, true);
db.eventlog.update({event: "unfollow.board"}, {$set: {event: "board.unfollow"}}, null, true);
db.eventlog.update({event: "unfollow.user"}, {$set: {event: "user.unfollow"}}, null, true);
