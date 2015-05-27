<?php
	session_start();
	
	require __DIR__ ."/class/BazaDanych.php";
	require __DIR__ ."/class/Test.php";
	require __DIR__ ."/class/utils.php";
	require __DIR__ ."/class/uzytk.php";
	
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
		$test = NULL;
		
		/*
		 * gdy dane branego testu nie są przekazane w zmiennej $_POST moze to oznaczać jedną z dwóch rzeczy:
		 * a) skierowano ze strony testu (poprzedniego pytania)
		 * b) nieautoryzowany dostęp
		 * Jeśli zaś są przekazane, to znaczy, że użytkownik wziął nowy test i chce go rozpocząć
		 
		 * get'a musisz potem wywalić i zamienić na post
		 */
		//echo var_dump(isset( $_GET['test_id'] ));
		 
		if( isset( $_GET['test_id'] ) && isset( $_GET['jest_oceniany'] ) )
		{
			$_POST['test_id'] = $_GET['test_id'];
			$_POST['jest_oceniany'] = $_GET['jest_oceniany'];
		}
		 
		/*ostatni parametr empty( $_SESSION['TEST_IN_PROGRESS'] ) sprawia,
		 * że nie można zaćżąć nowego testu, dopóki trwa stary */
		
		if( isset( $_POST['test_id'] ) && isset( $_POST['jest_oceniany'] ) && empty( $_SESSION['TEST_IN_PROGRESS'] ) )
		{
			$test_id = (int) $_POST['test_id'];
			
			// sprawdzenie dostępności testu
			if( Test::jestDostepny( $link, $test_id, $_SESSION['USER_ID'] ) )
			{
				$jest_oceniany = 0;
				if( $_POST['jest_oceniany'] == 1 and $_POST['test_id'] == $_SESSION['AUTORYZACJA'] )
				{
					$jest_oceniany = 1;
				}				
				$_SESSION['JEST_OCENIANY'] = $jest_oceniany;
				
				$test = new Test( $link, $test_id );				
				$poziom = pobierzPoziom( $link, $_SESSION['USER_ID'] );				
				$test->losujPytania( $link, $poziom );				
				$_SESSION['ODPOWIEDZ'] = array();
			}
			else
			{
				header( "Location: podglad_testu.php?test_id={$test_id}" );
			}
			
		}
		else
		{
			if( isset( $_SESSION['TEST_IN_PROGRESS'] ) )
				$test = unserialize( $_SESSION['TEST_IN_PROGRESS'] );
			else
			{
				//header( "Location: lista_testow.php" );
			}
		}
		
		
		if( !empty( $test ) )
		{
			if( isset( $_POST['odpowiedz'] ) )
			{
				$odpowiedz_id = (int) $_POST['odpowiedz'];
				$pytanie_id = $test->wezPytanie();
				
				if( pasujePytOdp( $link, $pytanie_id, $odpowiedz_id ) )
				{
					$_SESSION['ODPOWIEDZ'][] = $odpowiedz_id;
					$test->nastepny();
				}
				unset( $_POST['odpowiedz'] );
			}
			
			$_SESSION['TEST_IN_PROGRESS'] = serialize( $test );
			
			if( !$test->jestZakonczony() )
			{
				$pytanie_id = $test->wezPytanie();
				$pytanie_tresc = pobierzTrescPyt( $link, $pytanie_id );
				
				$odpowiedz_id;
				$odpowiedz_tresc;
				
				pobierzOdp( $link, $pytanie_id, $odpowiedz_id, $odpowiedz_tresc );
				
				$li_odp = "";
				for( $i = 0; $i < count( $odpowiedz_id ); $i++ )
				{
					$li_odp .= <<< LI
\n
<label>
<li>
<input type='radio' name='odpowiedz' value='{$odpowiedz_id[$i]}'>
{$odpowiedz_tresc[$i]}
</li>
</label>
LI;
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

<div id='test_pojemnik'>

<span class='pytanie'>{$pytanie_tresc}</span>
<form action="test.php" method='post'>
	<ul class='odpowiedzi'>
		{$li_odp}
	</ul>
	<input type='submit'>
</form>

</div>

</div>
</body>

</html>
PAGE;
			}
			else
			{
				header('Location: koniec_testu.php');
			}
		}
		else
		{
			// strona wyświetlana, gdy żaden stet nie jest ustawiony
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

<div class='tresc_strony'>

	<p>
		Aktualnie nie trwa żaden test.
	</p>

</div>

</div>
</body>

</html>
PAGE;
			//header('Location: main_page.php');
		}
	}
	else
	{
	/*
	 * wywołane, gdy użytkownik nie jest zalogowany
	 * wyświetlona zostaje przekierowanie na stronę logowania
	 */
		header('Location: index.php');
	}
	
	echo $page;
?>