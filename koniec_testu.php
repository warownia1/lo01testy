<?php
	
	session_start();
	
	require_once __DIR__ .'/class/Test.php';
	require_once __DIR__ .'/class/utils.php';
	require_once __DIR__ .'/class/uzytk.php';
	require_once __DIR__ .'/class/BazaDanych.php';
	
	
	function ilePodejsc( $link, $uzytk_id, $test_id )
	{
		$query = <<< QUERY
SELECT COUNT(uzytk_id)
FROM wyniki
WHERE uzytk_id='{$uzytk_id}'
AND test_id='{$test_id}'
QUERY;
		$result = mysqli_query( $link, $query );
		$row = mysqli_fetch_row( $result );
		return $row[0];
	}
	
	
	function wstawWynik( $link, $uzytk_id, $test_id, $wynik, $maks_wynik )
	{
		$poziom = pobierzPoziom( $link, $uzytk_id );
		$podejscie = ilePodejsc( $link, $uzytk_id, $test_id ) + 1;
		
		$query = <<< QUERY
INSERT
INTO wyniki (uzytk_id, test_id, uzytk_poziom, wynik, maks_wynik, podejscie)
VALUES ( {$uzytk_id}, {$test_id}, {$poziom}, {$wynik}, {$maks_wynik}, {$podejscie} )
QUERY;
	
		mysqli_query( $link, $query );
	}
	
	
	$link = BazaDanych::polacz();

	$page = NULL;
	
	if( !isset( $_SESSION['USER_ID'] ) || $_SESSION['USER_ID'] <= 0 )
	{
		$_SESSION['LOGGEN_IN'] = false;
		$_SESSION['USER_ID'] = 0;
	}
	
	if( $_SESSION['LOGGED_IN'] )
	{
		if( isset( $_SESSION['TEST_IN_PROGRESS'] ) )
		{
			$test = unserialize( $_SESSION['TEST_IN_PROGRESS'] );
			
			if( $test->jestZakonczony() )
			{
				unset( $_SESSION['TEST_IN_PROGRESS'] );
				
				$wynik = sumaPunktow( $link, $_SESSION['ODPOWIEDZ'] );
				$maks_wynik = maksPunktow( $link, $_SESSION['ODPOWIEDZ'] );
				$procent = round( 100 * $wynik / $maks_wynik, 1 );
				
				if( $_SESSION['JEST_OCENIANY'] && $_SESSION['AUTORYZACJA'] == $test->wezID() )
				{
					// napisany test był oceniany więc wyniki powinny być dodane do bazy danych
					wstawWynik( $link, $_SESSION['USER_ID'], $test->wezID(), $wynik, $maks_wynik );
					$string = "Moduł testowy - Twój wynik został zapisany";
					unset( $_SESSION['AUTORYZACJA'] );
				}
				else
				{
					$string = "Trening - Twój wynik nie został zapisany";
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

<div id='wynik_pojemnik' class='tresc_strony'>
	<p>
		Uzyskałeś {$wynik} na {$maks_wynik} ({$procent}%)
	</p>
	<p>
		{$string}
	</p>
</div>

</div>
</body>

</html>
PAGE;
			}
			else
			{
				header("Location: test.php");
			}
		}
		else
		{
			header("Location: lista_testow.php");
		}
	}
	else
	{
		header("Location: index.php");
	}
	
	echo $page;
?>