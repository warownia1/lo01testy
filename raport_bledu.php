<?php
	session_start();
	
	

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
		if( empty( $_POST['raport'] ) )
		{
		
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

<div id='raport_bledu' class='tresc_strony'>
	<form action='' method="post">
	<table>
	<tr>
	<td>kategoria:</td>
	<td><input type='text' name='kategoria'></input></td>
	</tr>
	</table>
	
	<table>
	<tr>
	<td>Opis błędu. (powinien zawierać opis błędu oraz czynności, które zostały wykonane tuż przed jego wystąpieniem)</td>
	</tr>
	
	<tr>
	<td><textarea name='raport' cols='40' rows='12' ></textarea></td>
	</tr>
	
	<tr>
	<td><input type='submit' value='Przeslij raport'></input>
	</td>
	</tr>
	</table>
	<form>
	
</div>

</div>
</body>

</html>
PAGE;

		}
		else
		{
			$kategoria = "NULL";
			if( !empty( $_POST['kategoria'] ) )
			{
				$kategoria = $_POST['kategoria'];
			}
			
			$raport = $_POST['raport'];
			$date = date( 'H:i:s d.m.y' );
			$id_uzytk = $_SESSION['USER_ID'];
			
			$file = fopen( 'admin/bug_report.txt', 'a' );
			
			$report = <<<REPORT
{$date} użytkownik {$id_uzytk} zgłosił:
kategotia: {$kategoria}\n
{$raport}\n
Koniec Raportu
\n\n
REPORT;
			fwrite( $file, $report );
			fclose( $file );
			
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

<div id='raport_bledu' class='tresc_strony'>
	<p>
	Dziękuję za przesłanie błędu. Wkrótce zostanie on poprawiony.
	</p>
</div>

</div>
</body>

</html>
PAGE;

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