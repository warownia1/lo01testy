<?php
	
	/*
	 * funkcja pobiera wyniki testów i nazwy testów i zwraca wszystki w tablicy dwuwymiarowej
	 */
	function pobierzWyniki( $link, $uzytk_id )
	{
		$query = <<< QUERY
SELECT testy.test_id, testy.nazwa, wyniki.wynik, wyniki.maks_wynik, wyniki.podejscie
FROM wyniki
INNER JOIN testy
ON testy.test_id = wyniki.test_id
WHERE wyniki.uzytk_id = {$uzytk_id}
ORDER BY wyniki.test_id ASC, wyniki.podejscie ASC
QUERY;

		$result = mysqli_query( $link, $query );
		
		$id = array();
		$nazwa = array();
		$wynik = array();
		$maks = array();
		$podejscie = array();
		
		while( $row = mysqli_fetch_row( $result ) )
		{
			$id[] = $row[0];
			$nazwa[] = $row[1];
			$wynik[] = $row[2];
			$maks[] = $row[3];
			$podejscie[] = $row[4];
		}
		
		return array( $id, $nazwa, $wynik, $maks, $podejscie );
	}
	
	
	function wynikowNaTest( $link, $uzytk_id )
	{
		$query = <<< QUERY
SELECT COUNT( wyniki.test_id )
FROM wyniki
INNER JOIN testy
ON testy.test_id = wyniki.test_id
WHERE wyniki.uzytk_id = {$uzytk_id}
GROUP BY wyniki.test_id
ORDER BY wyniki.test_id ASC
QUERY;

	//echo var_dump( $query );

		$result = mysqli_query( $link, $query );
		
		$ile_wynikow = array();
		
		while( $row = mysqli_fetch_row( $result ) )
		{
			$ile_wynikow[] = $row[0];
		}
		
		return $ile_wynikow;
	}

	session_start();
	
	require_once __DIR__ ."/class/BazaDanych.php";
	
	$link = BazaDanych::polacz();
	

	$page = NULL;
	
	/*
	 * sprawdzeie aktualnego zalogowania w danej sesji
	 * i wyczyszczenie zmiennych sesji jeśli użytkownik nie jest zalogowany
	 */
	if( !isset( $_SESSION['USER_ID']) || $_SESSION['USER_ID'] <= 0 )
	{
		$_SESSION['LOGGED_IN'] = false;
		$_SESSION['USER_ID'] = 0;
	}
	
	if( $_SESSION['LOGGED_IN'] == true )
	{
		list( $id, $nazwa, $wynik, $maks, $podejscie ) = pobierzWyniki( $link, $_SESSION['USER_ID'] );
		
		
		$table = "";
		
		$id[] = 0;
		$i = 0;
		while( $i < count( $wynik ) )
		{
			$table .= "
				<tr>\r\n
				<td class='nazwa'>#{$id[ $i ]} {$nazwa[ $i ]}</td>\r\n
				<td>\r\n
				<table class='liczby'>
				<tbody>
				<tr>
				<th>podejscie</th>
				<th>wynik</th>
				</tr>
				";
			do
			{
				$procent = round( 100 * $wynik[ $i ] / $maks[ $i ], 1 );
				$table .= "
					<tr>\r\n
					<td class='podejscie'>{$podejscie[ $i ]}</td>\r\n
					<td class='wynik'>{$wynik[ $i ]} / {$maks[ $i ]} ({$procent}%)</td>\r\n
					</tr>\r\n
					";
				//$i++;
			}
			while( $id[$i] == $id[ ++$i] );
			$table .= "
				</tbody>
				</table>\r\n
				</td>\r\n
				</tr>\r\n
				";
		}
		
		
		
		$page = <<< PAGE

<!DOCTYPE html>
<html>

<head>
<meta charset='utf-8' />
<link rel='stylesheet' type='text/css' href='./utils/style.css' />
<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'></script>
<script type='text/javascript' src='./utils/jquery.js'></script>
</head>

<body>

<div id='global_container'>

<div id='top_menu'>
	<ul>
		<li><a href='strona_glowna.php'>Strona Główna</a></li>
		<li class='has_sub'><span>Testy</span>
			<ul>
				<li><a href='lista_testow.php'>Lista Testów</a></li>
				<li><a href='test.php?kontynuuj'>Kontynuuj Test</a></li>
				<li><a href='wyniki.php'>Wyniki</a></li>
			</ul>
		</li>
		<li class='has_sub'><span>Ustawienia</span>
			<ul>
				<li><a href="zmiana_hasla.php">Zmiana hasła</a></li>
				<li><a href='zmiana_loginu.php'>Zmiana loginu</a></li>
				<li><a href='raport_bledu.php'>Zgłoś błąd</a></li>
			</ul>
		</li>
		<li><a href="index.php?logout">Wyloguj</a></li>
	</ul>
</div>

<div id="lista_wynikow" class='tresc_strony'>
	<table>
	<tbody>
		<tr>
			<th class="nazwa">Nazwa testu</th>
		</tr>
		{$table}
	</tbody>
	</table>
</div>

</div>
</body>

</html>

PAGE;
	}
	
	echo $page;

?>

