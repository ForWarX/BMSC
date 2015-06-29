<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');


function language(){
	showr($_REQUEST);
	
if (isset($_REQUEST['language']) === false || $_REQUEST['language'] == ''){$_REQUEST['language'] = '';}
else {$_REQUEST['language'] = 'en';}


language_handler($_REQUEST['language'],true);

ecs_header("Location: goods.php?id=2154");


}


language();































?>