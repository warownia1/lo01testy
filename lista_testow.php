<?php
	session_start();
	
	require __DIR__ ."/class/Test.php";
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
	
		$query= <<< QUERY
SELECT testy.test_id, MAX(wartosc)
FROM testy
LEFT JOIN (SELECT test_id, wartosc
           FROM grupa_test
		   INNER JOIN uzytk_grupa
           ON grupa_test.grupa_id=uzytk_grupa.grupa_id
           WHERE uzytk_id={$_SESSION['USER_ID']})
           AS T
ON T.test_id=testy.test_id
GROUP BY testy.test_id
QUERY;
		
		// echo var_dump($query);
		$result = mysqli_query( $link, $query );
		$tests_list = array( array(), array(), array(), array() );
		
		while( $row = mysqli_fetch_row( $result ) )
		{
			switch( $row[1] )
			{
				case Stan::WAZNY :
				case Stan::AKTUALNY :
					$tests_list[0][] = new Test( $link, $row[0] );
				break;
				
				case Stan::ZABLOKOWANY :
					$tests_list[1][] = new Test( $link, $row[0] );
				break;
				
				case Stan::WYGASL :
					$tests_list[2][] = new Test( $link, $row[0] );
				break;
				
				case Stan::NIEAKTYWNY :
				default:
					$tests_list[3][] = new Test( $link, $row[0] );
				break;
			}
		}
		
		$li_testow = "";
		

		foreach( $tests_list as $tests_group )
		{
			foreach( $tests_group as $test )
			{
				switch( $test->wezDostepny() )
				{
					case Stan::WAZNY :
						$klasy="aktywny wazny";
						break;
					case Stan::AKTUALNY :
						$klasy = "aktywny dostepny";
						break;
					case Stan::NIEAKTYWNY :
						$klasy = "nieaktywny niedostepny";
						break;
					case Stan::ZABLOKOWANY :
						$klasy = "nieaktywny zablokowany";
						break;
					default:
						$klasy = "nieaktywny niedostepny";
				}
				$segment = <<< SEGMENT
<li class='{$klasy}'>
<a href="podglad_testu.php?test_id={$test->wezID()}">{$test->wezNazwa()}</a>
</li>
SEGMENT;
				$li_testow .= "\n". $segment;
			}
		}
		
		$li_testow .= "\n";
		
		// wygenerowanie strony głównej
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

<div id="global_container">

<div id='top_menu'>
	<ul>
		<li><a href='strona_glowna.php'>Strona Główna</a></li>
		<li class='has_sub'><span>Testy</span>
			<ul>
				<li><a href='#'>Lista Testów</a></li>
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

<div id="lista_testow_pojemnik" class='tresc_strony'>
	<ul id='lista_testow'>
		{$li_testow}
	</ul>
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
	