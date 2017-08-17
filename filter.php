<?php
/**
* filtering.php filtering $_GET and $_POST
*
* @package Secure
* @copyright Copyright 2003-2016 Zen Cart Development Team
* @copyright Portions Copyright 2004 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
*/
$filter_wave1 = Array('select ', 'update ', 'insert', 'delete ');
$filter_wave2 = Array('"', "'");
function replace_injection($str, $filter)
{
foreach($filter as $key => $value)
$str = str_replace($filter[$key], "", $str);
return $str;
}

foreach($_POST as $key => $value)
{
	$_POST[$key] = replace_injection(($_POST[$key]), $filter_wave1);
	$_POST[$key] = replace_injection(($_POST[$key]), $filter_wave2);


}

foreach($_GET as $key => $value)
{
	$_GET[$key] = replace_injection(($_GET[$key]), $filter_wave2);
	$_GET[$key] = replace_injection(($_GET[$key]), $filter_wave1);
}
//echo "<pre>"; print_r($_GET); echo "</pre>"; die();

?>