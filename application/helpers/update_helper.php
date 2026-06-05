<?php
function is_phppos_update_available()
{
	$host = $_SERVER['HTTP_HOST'] . '/current_version.php?build_timestamp=1';
	$url = (!defined("ENVIRONMENT") or ENVIRONMENT == 'development') ? $host : $host;
   	$ch = curl_init($url);
  	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  	$current_build = curl_exec($ch);
  	curl_close($ch);

	return ($current_build != '' && (BUILD_TIMESTAMP != $current_build));
}
?>