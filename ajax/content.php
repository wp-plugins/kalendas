<?php require_once( '../../../../wp-config.php' ); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title></title>
	</head>
<body><?php
if(wp_verify_nonce($_POST['nonce'], 'kalendas')) {
	$source =  $_POST['source'];
	if($_POST['update']) {
		kalendas_create($source);
		_e('Events list updated', 'kalendas');	
	} else {
		echo kalendas_htmlCode($source);
	}
} else {
	_e('Only Kalendas can use this link.', 'kalendas');
}

?>
</body>
</html>
