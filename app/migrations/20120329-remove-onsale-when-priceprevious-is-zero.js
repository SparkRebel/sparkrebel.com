db.items.update(
    {isOnSale : true, pricePrevious : 0 },
    {$set : {isOnSale : false } },
    false,
    true
);