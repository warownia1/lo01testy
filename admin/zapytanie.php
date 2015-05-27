<?php

	require 'BazaDanych.php';

	$link = BazaDanych::polacz();
	
	
	$tresc = "";
	
	if( !empty( $_POST['query'] ) )
	{
		$query = str_replace( '%', '', $_POST['query'] );
		$query = str_replace( '\\"', '"', $query );
		
		mysqli_query( $link, $query );
		
		$tresc = nl2br( $query );
		
	}
	
	$page = <<< PAGE

<!DOCTYPE html>

<html>

<head>
<meta charset='utf-8' />
<link rel='stylesheet' type='text/css' href='style.css'>
</head>

<body id='zapytanie'>

<div>
<form action="" method='post'>
<textarea rows=15 cols=70 name='query'>
</textarea>
<input type='submit'>
</form>
</div>


<div id='query'>
<p>
{$tresc}
</p>
</div>

<div class='navi'>
<a class='block' href='index.html'>wstecz</a>
</div>
</body>

</html>

PAGE;
	
	echo $page

?>
