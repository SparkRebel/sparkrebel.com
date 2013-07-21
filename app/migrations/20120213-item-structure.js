db.items.find({merchant : {$exists : true}})
	.forEach(function(x) {
	    var m,b = null;
	    
	    if (x.merchant) {
	        m = x.merchant['$id'];
	        m = db.users.find({_id : x.merchant['$id']});
	        
	        db.items.update(
    			{_id: x._id},
    			{$set: {
    			    merchantName : m.username
    			    },
    			 $rename: {
    			     merchant : 'merchantUser'
    			 }
    			}
    		);
    		print('updated merchant for item ' + x._id);
	    }
	    
	    if (x.brand) {
	        b = x.brand['$id'];
	        b = db.users.find({_id : x.brand['$id']});
	        db.items.update(
    			{_id: x._id},
    			{$set: {
    			    brandName : b.username
    			    },
    			 $rename: {
    			     brand : 'brandUser'
    			 }
    			}
    		);
    		print('updated brand for item ' + x._id);	
	    }

	});