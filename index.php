<?php
	session_start();
	
	require __DIR__ ."/class/BazaDanych.php";
	require __DIR__ ."/class/uzytk.php";
	
	$link = BazaDanych::polacz();
	
	/*
	 * zmienna zawiera kod HTML całej strony
	 * w trakcie wykonywania kodu php jest modyfikowana i na końcu jest wyświetlona
	 */
	$page = "";
	
	/*
	 * zawiera kod HTML komórki tabeli wyświetlanej ponad formularzem logowania
	 * zależnie od poniższych sytuacji będzie wyświetlany inny tekst lub wcale
	 * - gdy użytkownik się wylogował
	 * - gdy wprowadził błędne hasło
	 * - nie podał loginu
	 */
	$login_comment = "";
	
	/*
	 * sprawdzenie, czy nie nastąpiło naciśnięcie przycisku wyloguj,
	 * który przekierowuje na stronę główną z ustawionym parametrem logout
	 * jeśli tak, to niszczy dane sesji i ustawia napis nad formularzem
	 */
	if ( isset( $_GET['logout'] ) )
	{
		$_SESSION = array();
		session_destroy();
		$login_comment = <<< COMMENT
<div id="komentarz_logowania">
<span class='wylogowano'>Wylogowano pomyślnie</span>
</div>
COMMENT;
	}
	
	/*
	 * zbadanie czy aktualnie nie trwa sesja
	 * jeśli nie trwa zmienne przechowujące stan sesji są zerowane
	 */
	if( !isset( $_SESSION['USER_ID'] ) || $_SESSION['USER_ID'] <= 0 )
	{
		$_SESSION['LOGGED_IN'] = false;
		$_SESSION['USER_ID'] = 0;
	}
	
	/*
	 * sprawdzenie czy nie trwa aktualnie sesja
	 * jeśli nie, sprawdzane są dane logowania przesłane z formularza i następuje zalogowanie lub wyświetlenie formularza logowania
	 */
	if( $_SESSION['LOGGED_IN'] == false )
	{
		if( isset( $_POST['login'] ) )
		{
			if( $_POST['login'] != "" )
			{
				if( $id = sprawdzHaslo( $link, $_POST['login'], $_POST['haslo'] ) )
				{
					$_SESSION['USER_ID'] = $id;
					$_SESSION['LOGGED_IN'] = true;
					$login_comment = "";
				}
				else
				{
					$login_comment = <<< COMMENT
<div id="komentarz_logowania">
<span class='blad'>Błędne hasło</span>
</div>
COMMENT;
				}
			}
			else
			{
				/*
				 * wywołane gdy w wysłanym formularzu pole loginu było puste
				 * nad logowaniem wyswietli się napis "Nie podano loginu"
				 */
				$login_comment = <<< COMMENT
<div id="komentarz_logowania">
<span class='blad'>Nie podano loginu</span>
</div>
COMMENT;
			}
		}
		
		/*
		 * wygenerowanie tabeli z formularzem logowania jeśli użytkownik nie był zalogowany wchodząc na stronę
		 * jeśli logowanie nastąpiło teraz to treść zostanie podmieniona
		 */
	$page = <<< PAGE
<!DOCTYPE html>

<html>
<head>
<meta charset='utf-8' />
<link rel='stylesheet' type='text/css' href='./utils/style.css' />
<script src='http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js'></script>
<script type='text/javascript' src='./utils/index_jquery.js'></script>
</head>

<body>

<div id="global_container" >
	<div id="pojemnik_logowania">
		{$login_comment}
		<div id="menu_login_container">

			<div id='portrait_container'>
			<img src='./img/portrait.png'></img>
			</div>

			<div id='menu_login_right_container'>
				<form action="index.php?login" method="post">
					<div class='login_input_title'>login</div>
					<input type='login' name='login'></input>
					<div class='login_input_title'>hasło</div>
					<input type='password' name='haslo'></input>
					<input type='submit' class='hidden' ></input>
					<div id='login_button' class='pointercursor'><span>zaloguj</span></div>
				</form>
			</div>
			
		</div>
	</div>
</div>

</body>

</html>
PAGE;

	}
	
	/*
	 * jesli sesja trwa i uzytkownik jest zalogowany
	 * generowania jest strona przekierowująca na stronę główną
	 */
	if( $_SESSION['LOGGED_IN'] == true )
	{
		header('Location: strona_glowna.php');
	}

echo $page;

?>