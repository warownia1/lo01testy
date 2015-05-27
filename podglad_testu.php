<?php
	session_start();
	
	require_once __DIR__ ."/class/BazaDanych.php";
	require_once __DIR__ ."/class/Test.php";
	require_once __DIR__ ."/class/utils.php";
	
	
	$link = BazaDanych::polacz();
	

	$page = NULL;
	
	
	/*
	 * sprawdzeie aktualnego zalogowania w danej sesji
	 * i wyczyszczenie zmiennych sesji jeśli użytkownik nie jest zalogowany
	 */
	if( !isset( $_SESSION['USER_ID'] ) || $_SESSION['USER_ID'] <= 0 )
	{
		$_SESSION['LOGGED_IN'] = false;
		$_SESSION['USER_ID'] = 0;
	}
	
	if( $_SESSION['LOGGED_IN'] == true )
	{
		// sprawdzenie czy został przekazany w formularzu numer testu
		if( isset( $_POST['wybrany_test'] ) || isset( $_GET['test_id'] ) )
		{
			if( isset( $_POST['wybrany_test'] ) )
				$test_id = (int) $_POST['wybrany_test'];
			if( isset( $_GET['test_id'] ) )
				$test_id = (int) $_GET['test_id'];
			
			$test = new Test( $link, $test_id );
			
			if( $test->moznaPisac()  )
			{
				if( empty( $_SESSION['AUTORYZACJA'] ) )
					$_SESSION['AUTORYZACJA'] = 0;
				
				if( $_SESSION['AUTORYZACJA'] != $test_id )
				{
					if( empty( $_SESSION['RANDOM_PASSWORD'] ) )
						$_SESSION['RANDOM_PASSWORD'] = (int) rand( 1, 5 );
						
					if( isset( $_POST['naucz_haslo'] ) )
					{
						if( sprawdzHasloNaucz( $link, $_SESSION['RANDOM_PASSWORD'], $_POST['naucz_haslo'] ) )
						{
							// weryfikacja powiodła się, test może być zdawany
							unset( $_SESSION['RANDOM_PASSWORD'] );
							$_SESSION['AUTORYZACJA'] = $test_id;
						}
					}
				}
				if( $_SESSION['AUTORYZACJA'] == $test_id )
				{
					$li_test ="<li class='aktywny test'><a href='test.php?test_id={$test_id}&jest_oceniany=1'>Test</a>";
				}
				else
				{
					$li_test = <<< LI
<li class='blokada test'>
	<span class='haslo'>
		<div>haslo nr {$_SESSION['RANDOM_PASSWORD']}</div>
		<form action="podglad_testu.php?test_id={$test_id}" method='post'>
			<input type='password' name='naucz_haslo'></input>
			<input type='hidden' name='test_id' value='{$test_id}'></input>
			<input class='nodisplay' type='submit'></input>
		</form>
	</span>
	<span id='zakrycie' class='zakrycie'>
		Test
	</span>
</li>
LI;
				}
				
				$li_trening = "<li class='aktywny trening'><a href='test.php?test_id={$test_id}&jest_oceniany=0'>Trening</a></li>";
			}
			else
			{
				$li_trening = "<li class='nieaktywny trening'><a>Trening</a></li>";
				$li_test = "<li class='nieaktywny test'><a>Test</a></li>";
			}
			
		$stringDost = Stan::doString($test->wezDostepny());
		
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

<div id='podglad_testu_pojemnik' class='tresc_strony'>
	<table id='podglad_testu'>
	<tr>
	<td>Nazwa Testu:</td><td>#{$test->wezID()} {$test->wezNazwa()}</td>
	</tr>
	<tr>
	<td>Ilość Pytań:</td><td>{$test->wezIlPytan()}</td>
	</tr>
	<tr>
	<td></td><td class='dostepnosc dostepny'>{$stringDost}</td>
	</tr>
	</table>
	
	<div id='prz_rozp'>
		<ul>
		{$li_trening}
		{$li_test}
		</ul>
	</div>
</div>

</div>
</body>

</html>
PAGE;
		}
		else
		{
		// wywołane gdy użytkownik jakimś cudem wszedł i nie wybrał testu
			header('Location: lista_testow.php');
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