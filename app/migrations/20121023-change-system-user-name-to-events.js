user = db.users.findOne( {	"name": /^SparkRebel.com/i	} );
user.name = 'Events';
db.users.save(user);
