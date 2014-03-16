<?php
require_once $_SERVER['HOME'].'/conf/amazon/constant.php';
require_once $_SERVER['HOME'].'/lib/curl.php';

class AmazonRankClass {
    /**
     * ランキング取得
     **/
    public static function getRank($params) {
        $type = (!empty($params['type'])) ? $params['type'] : DEFAULT_TYPE;
        $category = (!empty($params['category'])) ? $params['category'] : DEFAULT_CATEGORY;
        $node = (!empty($params['node'])) ? $params['node'] : NULL;

        // RSS取得
        $url_node = (empty($node)) ? '' : '/'.$node;
        $url = AMAZON_RSS.'/'.$type.'/'.$category.$url_node.'/';
        $rss = getWeb($url);
        if (empty($rss)) {
            return false;
        }
        $xml = simplexml_load_string($rss);
/*
        // RSS情報
        $categoryNodeTitle = str_replace('Amazon.co.jp: ', '' , (string)$xml->channel->title);
        $categoryNodeTitle = preg_replace('|.+\s>\s|', '' , $categoryNodeTitle);
        $categoryLink = (string)$xml->channel->link;
        $pubDate      = (string)$xml->channel->pubDate;
        $lastBuildDate = (string)$xml->channel->lastBuildDate;
*/
        // 商品情報
        $ret = array();
        $i = 1;
        foreach ($xml->channel->item as $item) {
            $title = str_replace("#$i: ", '', (string)$item->title);
            if (empty($title)) continue;
            $description = (string)$item->description;
            preg_match('|<img src="(?P<image>http://[\w\.\-,%/=]+)" |', $description, $matches);
            $image = $matches['image'];
            preg_match('|<font color="#990000"><b>￥ (?P<price>[\w,]+)</b></font>|', $description, $matches);
            $price = (!empty($matches['price'])) ? $matches['price'] : '';
            $ret[$i] = array(
                'title' => $title, // 商品タイトル
                'link'  => trim((string)$item->link), // 商品リンク
//                'description' => (string)$item->description, // 商品HTML
                'image' => $image, // 商品画像
                'price' => $price, // 商品価格
            );
            $i++;
        }

        return $ret;
    }

    /**
     * node取得
     **/
    public static function getNode($params) {
        $category = (!empty($params['category'])) ? $params['category'] : DEFAULT_CATEGORY;
        $node = (!empty($params['node'])) ? $params['node'] : NULL;

        $url_node = (empty($node)) ? '' : '/'.$node;
        $url = AMAZON_WEB.'/'.$category.$url_node.'/';
        $html = getWeb($url);
        if (empty($html)) {
            return false;
        }

        preg_match_all("|<li><a href='(?P<tmp>http://[\w\.\-,%/=]+'>.+)</a></li>|", $html, $matches);
        $ret = array();
        foreach ($matches['tmp'] as $tmp) {
            $tmp = mb_convert_encoding($tmp, 'UTF-8', 'SJIS');
            preg_match("|/gp/bestsellers/$category/(?P<node>[\w\-]+)/ref|", $tmp, $matches);
            $node = $matches['node'];
            preg_match("|'>(?P<name>.+)$|", $tmp, $matches);
            $name = str_replace('&', '＆', $matches['name']);
            $ret[] = array('node' => $node, 'name' => $name);
        }

        return $ret;
    }

}
