<?php
require __DIR__ . "/../GettyImage.php";


$getty = new PW\CelebBundle\GettyImage;

$conn = new Mongo("db1.int.sparkrebel.com");
$db = $conn->plum;

$ids = array();
foreach ($db->assets->find(array('source' => new MongoRegex("/^getty/")), array('_id' => 1, 'source' => 1)) as $doc) {
    $ids[substr($doc['source'], 6)] = $doc['_id'];
}

$chunks = array_chunk($ids, 100, true);
$total  = count($chunks);

$i = 0;
foreach ($chunks as $chunk) {
    $i++;
    foreach ($getty->getImageDetails(array_keys($chunk))->Images as $doc) {
        if (empty($chunk[$doc->ImageId])) continue;
        $meta = $doc;
        unset($meta->Keywords);
        unset($meta->SizesDownloadableImages);
        $meta->copyright = $meta->Artist . '/' . $meta->CollectionName;
        $db->assets->update(array('_id' => $chunk[$doc->ImageId]), array('$set' => array('description' => $doc->Caption, 'meta' => $meta)));
        $db->posts->update(array('image.$id' => $chunk[$doc->ImageId]), array('$set' => array('description' => $doc->Caption)), array('multiple' => true, 'safe'=>true));
    }
    echo "chunk $i/$total\n";
}
