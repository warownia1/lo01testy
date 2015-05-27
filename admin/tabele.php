<?php


/*
 * funkcja zwrana nazwy wszystkich tabel z bazie
 */
function pobierzTabele( $link )
{
	$query = "
		SHOW TABLES
		FROM lo01_testy
		";
		
	$result = mysqli_query( $link, $query );

	$tabele = array();

	while( $row = mysqli_fetch_row( $result ) )
	{
		$tabele[] = $row[0];
	}
	return $tabele;
}


/*
 * funkcja zwraca nazwę, typ i klucz każdej kolumny tabeli
 */
function pobierzKolumny( $link, $tabela )
{
	$query = "
		SHOW COLUMNS
		FROM {$tabela}
		";
	
	$result = mysqli_query( $link, $query );
	
	$nazwa = array();
	$typ = array();
	$klucz = array();
	
	while( $row = mysqli_fetch_assoc( $result ) )
	{
		$nazwa[] = $row['Field'];
		$typ[] = $row['Type'];
		$klucz[] = $row['Key'];
	}
	
	return array( $nazwa, $typ, $klucz );
}



function pobierzWyniki( $link, $tabela )
{
	$query = "
		SELECT *
		FROM {$tabela}
		";
	
	$result = mysqli_query( $link, $query );
	
	$rows = array();
	
	while( $row = mysqli_fetch_row( $result ) )
	{
		$rows[] = $row;
	}
	
	return $rows;
}



require 'BazaDanych.php';

$link = BazaDanych::polacz();


$tabele = pobierzTabele( $link );

$list = "<ul>\nDostępne tabele:";
foreach( $tabele as $nazwa )
	$list .= "<li><a href='#{$nazwa}'>$nazwa</a></li>\n";
$list .= "</ul>\n";


//$tabela = array();


foreach( $tabele as $nazwa_tabeli )
{
	list( $nazwa_kol, $typ_kol, $klucz_kol ) = pobierzKolumny( $link, $nazwa_tabeli );
	
	$head_row = "<tr>\n";
	
	for( $i = 0; $i < count( $nazwa_kol ); ++$i )
	{
		$head_row .= "<th>{$nazwa_kol[$i]}<br/>{$typ_kol[$i]}<br/>{$klucz_kol[$i]}</th>";
	}
	
	$head_row .= "</tr>";
	
	$rzedy = pobierzWyniki( $link, $nazwa_tabeli );
	
	$tresc_tabeli = "";
	
	foreach( $rzedy as $row )
	{
		$tr = "<tr>\n";
		foreach( $row as $pole )
		{
			$tr .= "<td>{$pole}</td>";
		}
		$tr .= "</tr>\n";
		
		$tresc_tabeli .= $tr;
	}
	
	$tabela[] = "
		<a name='{$nazwa_tabeli}'></a>
		<table>
		<caption>Tabela: {$nazwa_tabeli}</caption>
		{$head_row}
		{$tresc_tabeli}
		</table>
		";
}

$wylistowanie = implode( '<br/><br/>', $tabela );





$page = <<< PAGE

<!DOCTYPE html>

<html>

<head>
<meta charset='utf-8' />
<link rel='stylesheet' type='text/css' href='style.css'>
</head>

<body id='podglad_tabel'>
	<a name='top'> </a>
	{$list}
	
	{$wylistowanie}
	
	<div class='navi'>
	<a class='block' href='index.html'>wstecz</a>
	<a class='block' href='#top'>na góre</a>
	</div>
</body>

</html>

PAGE;

	echo $page;
?>