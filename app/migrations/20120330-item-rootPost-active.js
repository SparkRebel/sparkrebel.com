count = db.items.find({rootPost:null, isActive: true}, {isActive:1}).count();

num = 0;
print("Setting isActive items with null rootPost to not isActive (" + count + ")");

db.items.update({rootPost:null, isActive: true}, {$set: {isActive: false}}, false, true);

print('done');
