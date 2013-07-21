//Category Migrations:
// rename "Celeb Style (& Red Carpet)" to "Celeb Style & Red Carpet"
// rename "Shoes & Accessories" to "Accessories"
// rename "Style Articles" to "Style Articles & Tips"
// create new category "Shoes" 
// create new category "Fashion Disasters" 

var now = new Date();

db.categories.findAndModify({
     query: {name : 'Celeb Style (& Red Carpet)'},
     update: {$set: {name: 'Celeb Style & Red Carpet'}},
     new: false
});

db.categories.findAndModify({
     query: {name : 'Shoes & Accessories'},
     update: {$set: {name: 'Accessories', slug : 'accessories'}},
     new: false
});

db.categories.findAndModify({
     query: {name : 'Style Articles'},
     update: {$set: {name: 'Style Articles & Tips', slug : 'style-articles-tips'}},
     new: false
});

db.categories.findAndModify({
     query: {name : 'Shoes'},
     update: {$set: {
         isActive: true,
         name: 'Shoes',
         slug: 'shoes',
         type: 'user',
         created: now,
         modified: now
        }
     },
     new: true
});

db.categories.findAndModify({
     query: {name : 'Fashion Disasters'},
     update: {$set: {
         isActive: true,
         name: 'Fashion Disasters',
         slug: 'fashion-disasters',
         type: 'user',
         created: now,
         modified: now
        }
     },
     new: true
});