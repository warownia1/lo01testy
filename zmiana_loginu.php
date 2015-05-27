<?php

	function loginIstnieje( $link, $login )
	{
		$query = <<< QUERY
SELECT COUNT(login)
FROM uzytkownicy
WHERE login='{$login}'
QUERY;

		$result = mysqli_query( $link, $query );
		$row = mysqli_fetch_row( $result );
		return $row[0];
	}


	function zmienLogin( $link, $uzytk_id, $nowy_login, $haslo )
	{
		$query = <<< QUERY
UPDATE uzytkownicy
SET login='{$nowy_login}'
WHERE uzytk_id='{$uzytk_id}'
AND haslo='{$haslo}'
QUERY;
		mysqli_query( $link, $query );
	}
	

	function zmienHaslo( $link, $uzytk_id, $nowe_haslo, $stare_haslo )
	{
		$query = <<< QUERY
UPDATE uzytkownicy
SET haslo='{$nowe_haslo}'
WHERE uzytk_id='{$uzytk_id}'
AND haslo='{$stare_haslo}'
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
		if(  isset( $_POST['haslo'] ) )
		{
			if( !empty( $_POST['haslo'] ) )
			{
				if( sprawdzHasloID( $link, $_SESSION['USER_ID'], $_POST['haslo'] ) )
				{
					if( !empty( $_POST['nowy_login'] ) )
					{
						if( !loginIstnieje( $link, $_POST['nowy_login'] ) )
						{
							zmienLogin( $link, $_SESSION['USER_ID'], $_POST['nowy_login'], $_POST['haslo'] );
							$tekst = "<span class='info'>Login został zmieniony</span>";
						}
						else
						{
							$tekst = "<span class='blad'>Podany login jest już w użyciu.</span>";
						}
					}
					else
					{
						$tekst = "<span class='blad'>Należy podać nowy login.</span>";
					}
				}
				else
				{
					$tekst = "<span class='blad'>Podane hasło jest nieprawidłowe.</span>";
				}
			}
			else
			{
				$tekst = "<span class='blad'>Należy wprowadzić hasło.</span>";
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
				<li><a href="zmiana_hasla.php">Zmiana hasła</a></li>
				<li><a href='zmiana_loginu.php'>Zmiana loginu</a></li>
				<li><a href='raport_bledu.php'>Zgłoś błąd</a></li>
			</ul>
		</li>
		<li><a href="index.php?logout">Wyloguj</a></li>
	</ul>
</div>

<div id="zmiana_hasla" class='tresc_strony'>

	{$tekst}
	
	<form action="zmiana_loginu.php" method="post">
	<table>
		<tr>
			<td>Nowy login:</td>
			<td><input type='tekst' name='nowy_login'></td>
		</tr>
		<tr>
			<td>Hasło:</td>
			<td><input type='password' name='haslo'></td>
		</tr>
		<tr>
			<td></td>
			<td><input type='submit' value='zmień login'></td>
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