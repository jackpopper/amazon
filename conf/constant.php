<?php
define('USER', 'jackpopper');

// amazon
define('AMAZON_WEB', 'http://www.amazon.co.jp/gp/bestsellers/');
define('AMAZON_RSS', 'http://www.amazon.co.jp/gp/rss/');
define('DEFAULT_TYPE', 'bestsellers');
define('DEFAULT_CATEGORY', 'books');
define('CONF_DIR', $_SERVER['HOME'].'/conf/amazon');
define('VAR_DIR', $_SERVER['HOME'].'/var/amazon');
define('CATEGORY_INI', CONF_DIR.'/category.ini');
define('KILL_SWITCH', VAR_DIR.'/kill');
define('DONE_CRAWLER', VAR_DIR.'/donec');

// DB
define('DB_HOST', '');
define('DB_USER', USER);
define('DB_PASS', 'taka1206');
define('DB_NAME', 'amazon');
