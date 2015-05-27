<?php

	function zmienHaslo( $link, $uzytk_id, $nowe_haslo, $stare_haslo )
	{
		$sha1 = hash ( 'md5', $nowe_haslo );
		$sha2 = hash ( 'md5', $stare_haslo );
		$query = <<< QUERY
UPDATE uzytkownicy
SET haslo='{$sha1}'
WHERE uzytk_id='{$uzytk_id}'
AND haslo='{$sha2}'
QUERY;

		mysqli_query( $link, $query );
	}

	session_start();
	
	require_once __DIR__ ."/class/uzytk.php";
	require_once __DIR__ ."/class/BazaDanych.php";
	
	$link = BazaDanych::polacz();
	

	$page = NULL;
	
	$tekst = "";
	
	if( !isset( $_SESSION['USER_ID'] ) || $_SESSION['USER_ID'] <= 0 )
	{
		$_SESSION['LOGGED_IN'] = false;
		$_SESSION['USER_ID'] = 0;
	}
	
	if( $_SESSION['LOGGED_IN'] == true )
	{
		if(  isset( $_POST['stare_haslo'] ) )
		{
			if( !empty( $_POST['stare_haslo'] ) )
			{
				if( sprawdzHaslo( $link, pobierzLogin( $link, $_SESSION['USER_ID'] ), $_POST['stare_haslo'] ) )
				{
					if( $_POST['nowe_haslo'] == $_POST['nowe_haslo2'] )
					{
						if( empty( $_POST['nowe_haslo'] ) )
						{
							$tekst = "<span class='blad'>Nowe hasło nie może być puste</span>";
						}
						else
						{
							zmienHaslo( $link, $_SESSION['USER_ID'], $_POST['nowe_haslo'], $_POST['stare_haslo'] );
							$tekst = "<span class='info'>Hasło zastało zmienione.</span>";
						}
					}
					else
					{
						$tekst = "<span class='blad'>Podane hasła są różne.</span>";
					}
				}
				else
				{
					$tekst = "<span class='blad'>Podane hasło jest nieprawidłowe.</span>";
				}
			}
			else
			{
				$tekst = "<span class='blad'>Hasło nie zostało podane.</span>";
			}
		}
		
		$page = <<< PAGE
		
		<!DOCTYPE html>
<html>

<head>
<meta charset='utf-8' />
<link rel='stylesheet' type='text/css' href='./utils/style.css' />
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
				<li><a href="#">Zmiana hasła</a></li>
				<li><a href='zmiana_loginu.php'>Zmiana loginu</a></li>
				<li><a href='raport_bledu.php'>Zgłoś błąd</a></li>
			</ul>
		</li>
		<li><a href="index.php?logout">Wyloguj</a></li>
	</ul>
</div>

<div id="zmiana_hasla" class='tresc_strony'>

	{$tekst}
	
	<form action="zmiana_hasla.php" method="post">
	<table>
		<tr>
			<td>Stare hasło:</td>
			<td><input type='password' name='stare_haslo'></td>
		</tr>
		<tr>
			<td>Nowe hasło:</td>
			<td><input type='password' name='nowe_haslo'></td>
		</tr>
		<tr>
			<td>Powtórz hasło:</td>
			<td><input type='password' name='nowe_haslo2'></td>
		</tr>
		<tr>
			<td></td>
			<td><input type='submit' value='zmień hasło'></td>
		</tr>
	</table>
	</form>
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