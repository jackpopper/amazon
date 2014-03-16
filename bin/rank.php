#!/usr/bin/php
<?php
require_once 'AmazonRankClass.php';
require_once $_SERVER['HOME'].'/lib/common.php';
require_once $_SERVER['HOME'].'/lib/db.php';

if (date('H') == '04') exit; // 4時は休止
echoLogTime('s', $argv[0]);

$min = (int)date('i');
$cnt = floor($min/2);
$category = parse_ini_file(CATEGORY_INI);
$db = new DB();
$db->selectDb(DB_NAME);
$db->setTable('category');

$i = 0;
$amz = new AmazonRankClass();
foreach ($category as $categoryId => $categoryName) {
    if ($cnt != $i) {
        $i++;
        continue;
    }
    $db->selectQuery(array('where' => "category='$categoryId'"));
    $nodeAry = array();
    while ($row = $db->fetchAssoc()) {
        $nodeAry[] = $row['node'];
    }

    // カテゴリ
    $typeAry = array('bestsellers', 'new-releases', 'movers-and-shakers');
    foreach ($typeAry as $type) {
        echo "$categoryId : all : $type\n";
        $rankAry = $amz->getRank(array('type' => $type, 'category' => $categoryId));
        if (empty($rankAry)) echo date('Y/m/d H:i:s')." [ERROR] failed get rank $categoryId all $type\n";
        else                 insertRank($categoryId, 'all', $type, $rankAry, $db);
    }
    // 第2カテゴリ
    array_pop($typeAry);
    foreach ($nodeAry as $node) {
        foreach ($typeAry as $type) {
            echo "$categoryId : ".$node." : $type\n";
            $rankAry = $amz->getRank(array('type' => $type, 'category' => $categoryId, 'node' => $node));
            if (empty($rankAry)) echo date('Y/m/d H:i:s')." [WARN] failed get rank $categoryId $node $type\n";
            else                 insertRank($categoryId, $node, $type, $rankAry, $db);
        }
    }

    $i++;
}
echoLogTime('e', $argv[0]);

//-------------------------------------------------------------------------------------------------
function insertRank($category, $node, $type, $rankAry, $db) {
    // テーブル名にハイフンを含む場合はバッククォートで囲む
    $db->deleteQuery(array('table' => "`$category`", 'where' => "node='$node' AND type='$type'"));
    foreach ($rankAry as $rank => $rankData) {
        $value = array(
            'node'  => $db->getVarcharQueryStr($node),
            'type'  => $db->getVarcharQueryStr($type),
            'rank'  => $rank,
            'title' => $db->getVarcharQueryStr($rankData['title']),
            'link'  => $db->getVarcharQueryStr($rankData['link']),
            'image' => $db->getVarcharQueryStr($rankData['image']),
            'price' => $db->getVarcharQueryStr($rankData['price']),
            'updated' => 'NOW()',
        );
        $db->insertQuery(array('table' => "`$category`", 'value' => $value));
    }
}
