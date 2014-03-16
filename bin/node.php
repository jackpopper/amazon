#!/usr/bin/php
<?php
//require_once $_SERVER['HOME'].'/conf/amazon/constant.php';
require_once 'AmazonRankClass.php';
require_once $_SERVER['HOME'].'/lib/common.php';
require_once $_SERVER['HOME'].'/lib/db.php';

echoLogTime('s', $argv[0]);

$category = parse_ini_file(CATEGORY_INI);
$amz = new AmazonRankClass();
$nodeAry = array();
foreach ($category as $categoryId => $categoryName) {
    echo "[$categoryId]\n";
    $nodeList = $amz->getNode(array('category' => $categoryId));
    if ($nodeList === false) {
        echo date('Y/m/d H:i:s')." [ERROR] failed get node $categoryId\n";
        exit(1);
    }
    foreach ($nodeList as $node) {
        echo $node['node']." = ".$node['name']."\n";
        $nodeAry[$categoryId][$node['node']] = $node['name'];
/*
        $subNodeList = getNodeList($categoryId, $node['node']);
        foreach ($subNodeList as $snode) {
            echo '<'.$node['node'].'> '.$snode['node']." = ".$snode['name']."\n";
        }
*/
    }
}
//print_r($nodeAry);
$db = new DB();
$db->selectDb(DB_NAME);
$db->setTable('category');
foreach ($nodeAry as $cat => $nod) {
    $db->deleteQuery(array('where' => "category='$cat'"));
    foreach($nod as $nodnode => $nodname) {
        $value = array(
            'category' => $db->getVarcharQueryStr($cat),
            'node'     => $db->getVarcharQueryStr($nodnode),
            'name'     => $db->getVarcharQueryStr($nodname),
        );
        $db->replaceQuery(array('value' => $value));
    }
}
echoLogTime('e', $argv[0]);
