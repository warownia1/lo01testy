<?php

	session_start();
	
	require __DIR__ ."/class/uzytk.php";
	require __DIR__ ."/class/BazaDanych.php";
	
	$link = BazaDanych::polacz();
	

	$page = NULL;
	
	if( !isset( $_SESSION['USER_ID'] ) || $_SESSION['USER_ID'] <= 0 )
	{
		$_SESSION['LOGGED_IN'] = false;
		$_SESSION['USER_ID'] = 0;
	}
	
	if( $_SESSION['LOGGED_IN'] == true )
	{
		$imie = pobierzImie( $link, $_SESSION['USER_ID'] );
		$nazwisko = pobierzNazwisko( $link, $_SESSION['USER_ID'] );
		$login = pobierzLogin( $link, $_SESSION['USER_ID'] );
		$poziom = pobierzPoziom( $link, $_SESSION['USER_ID'] );
		
		$page = <<< PAGE
<!DOCTYPE html>

<html>

<head>
<meta charset='utf-8' />
<link rel='stylesheet' type='text/css' href='./utils/style.css' />
</head>

<body>

<div id="global_container">

<div id='top_menu'>
	<ul>
		<li><a href='#'>Strona Główna</a></li>
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

<div id="strona_glowna" class='tresc_strony'>
	<table id='dane_ucznia'>
	<tr>
	<td>Imię:</td> <td>{$imie}</td>
	</tr>
	<tr>
	<td>Nazwisko:</td> <td>{$nazwisko}</td>
	</tr>
	<tr>
	<td>Login:</td> <td>{$login}</td>
	</tr>
	<tr>
	<td>Poziom:</td> <td class='ocena ocena{$poziom}'>{$poziom}</td>
	</tr>
	</table>
</div>

</div>

</body>

</html>		
PAGE;
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










