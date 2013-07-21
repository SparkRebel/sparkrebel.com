<?php

$conn = new Mongo;
$db   = $conn->selectDB('plum-staging');
$col  = $db->Usage;

$col->ensureIndex(array('gettyId' => 1));

$ids = $col->distinct('gettyId', array('sent' => 0));

$table = array();
foreach ($ids as $id) {
    $reports = $col->find(array('gettyId' => $id));
    $dates   = array();
    $row     = array('gettyId' => $id, 'total' => 0);
    foreach ($reports as $report) {
        if (empty($report['month'])) continue;
        $row['total'] += $report['quantity'];
        $dates[] = $report['month'] . ':view:' . $report['asset']['$id'];
        $dates[] = $report['month'] . ':comment:' . $report['asset']['$id'];
        $dates[] = $report['month'] . ':repost:' . $report['asset']['$id'];
    }
    $details = $db->selectCollection('Stats.summary')
        ->find(array('_id' => array('$in' => $dates)));
    foreach ($details as $detail) {
        $type = explode(":", $detail['_id'], 3);
        $type = $type[1];
        if (empty($row[$type])) {
            $row[$type] = '';
        }
        $row[$type] .= '<b>'. $detail['date'] . '</b>:' . $detail['total'] . '<br/>';
    }

    $img = $db->getDBRef($report['asset']);
    $row['image'] = $img['url'];

    $table[] = $row;
}

usort($table, function($a, $b) {
    return $b['total'] - $a['total'];
});

$columns = array('month', 'image', 'gettyId', 'total', 'view', 'comment', 'repost');

echo "<table>";
echo "<tr>";
foreach ($columns as $column) {
    echo "<th>{$column}</th>";
}
echo "</tr>";

$colors = array('white','gray');
foreach ($table as $id=>$row) {
    echo "<tr bgcolor='" . $colors[$id%count($colors)] . "'>";
    foreach ($columns as $column) {
        echo "<td align=center valign=center>";
        if (!empty($row[$column])){ 
            if ($column == 'image') {
                echo "<img src='{$row[$column]}' width=100>";
            } else {
                echo $row[$column];
            }
        } else {
            echo "0";
        }
        echo "</td>";
    }
    echo "</tr>";
}
echo "</table>";


