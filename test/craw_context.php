<?php

use LFPhp\Craw\Context;

require_once "../autoload.php";
require_once "../vendor/autoload.php";

$url = 'http://www.baidu.com';
echo "Fetching $url", PHP_EOL;
$result = Context::create()->get('http://www.baidu.com');;
echo "Content got, content size:", strlen($result);