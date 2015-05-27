<?php

function pobierzWyniki( $link )
{
	$query = <<< QUERY
	
SELECT 
	klasy.klasa_id AS klasa_id,
	klasy.nazwa AS klasa_nazwa,
	uzytkownicy.uzytk_id AS uzytk_id,
	uzytkownicy.nazwisko AS nazwisko,
	uzytkownicy.imie AS imie,
	uzytkownicy.poziom AS poziom,
	testy.test_id AS test_id,
	testy.nazwa AS test_nazwa,
	wyniki.wynik AS wynik,
	wyniki.maks_wynik AS maks,
	wyniki.podejscie AS podejscie
FROM 
	(uzytkownicy
	LEFT JOIN (
		uzytk_klasa
		INNER JOIN klasy
		ON uzytk_klasa.klasa_id=klasy.klasa_id
		)
	ON uzytkownicy.uzytk_id=uzytk_klasa.uzytk_id
	)
	LEFT JOIN (
		wyniki
		INNER JOIN testy
		ON testy.test_id=wyniki.test_id
		)
	ON wyniki.uzytk_id=uzytkownicy.uzytk_id

	ORDER BY
		klasy.nazwa ASC,
		uzytkownicy.nazwisko ASC,
		uzytkownicy.imie ASC,
		wyniki.test_id ASC,
		wyniki.podejscie ASC
		
QUERY;

	$klasa_id = array();
	$klasa_nazwa = array();
	$uzytk_id = array();
	$nazwisko = array();
	$imie = array();
	$poziom = array();
	$test_id = array();
	$test_nazwa = array();
	$wynik = array();
	$maks = array();
	$podejscie = array();

	$result = mysqli_query( $link, $query );
	
	while( $row = mysqli_fetch_assoc( $result ) )
	{
		$klasa_id[] = $row['klasa_id'];
		$klasa_nazwa[] = $row['klasa_nazwa'];
		$uzytk_id[] = $row['uzytk_id'];
		$nazwisko[] = $row['nazwisko'];
		$imie[] = $row['imie'];
		$poziom[] = $row['poziom'];
		$test_id[] = $row['test_id'];
		$test_nazwa[] = $row['test_nazwa'];
		$wynik[] = $row['wynik'];
		$maks[] = $row['maks'];
		$podejscie[] = $row['podejscie'];
	}
	
	return array( $klasa_id, $klasa_nazwa, $uzytk_id, $nazwisko, $imie, $poziom, $test_id, $test_nazwa, $wynik, $maks, $podejscie );
}


require "BazaDanych.php";

$link = BazaDanych::polacz();


list( $klasa_id, $klasa_nazwa, $uzytk_id, $nazwisko, $imie, $poziom, $test_id, $test_nazwa, $wynik, $maks, $podejscie ) = pobierzWyniki( $link );

$test_id[] = -1;
$uzytk_id[] = -1;
$klasa_id[] = -1;


$table = "";

$i = 0;
while( $i < count( $wynik ) )
{
	$table .= "
	<table>
	<caption>
	{$klasa_nazwa[ $i ]}
	</caption>
	";
	
	// przewijanie uczniów powtarzane dopóki klasa jest taka sama
	do
	{
		$table .= "
		<tr>
			<td class='imie'>
				{$nazwisko[ $i ]} {$imie[ $i ]}
			</td>
			<td class='test_table_container'>
				<table>
		";
				if( !empty( $test_id[ $i ] ) )
				{	
					// przewijanie testów powtarzane dopóki uczeń jest ten sam
					do
					{
						$table .= "
						<tr class='test_row'>
							<td>
								{$test_nazwa[ $i ]}
							</td>
							
							<td>
								<table>
						";
						
								//przewijanie podejść powtarzane dopóki test jest ten sam
								do
								{
									$procenty = round( 100 * $wynik[ $i ] / $maks[ $i ], 1 );
									$table .= "
									<tr class='wynik_row'>
										<td>{$wynik[ $i ]} / {$maks[ $i ]} ({$procenty}%)</td>
									</tr>
									";
								}
								while( $test_id[ $i ] == $test_id[ ++$i ] );
								
								$table .= "
								</table>
							</td>
						</tr>
						";
					}
					while( $uzytk_id[ $i-1 ] == $uzytk_id[ $i ] );
				}
				else
					$i++;
				
				$table .= "
				</table>
			</td>
		</tr>
		";
	}
	while( $klasa_id[ $i-1 ] == $klasa_id[ $i ] );
	
	$table .="</table>";
}

$page = <<< PAGE

<!DOCTYPE html>

<html>

<head>
<meta charset='utf-8' />
<link rel='stylesheet' type='text/css' href='style.css'>
</head>

<body id='wyniki'>
<a name='top'></a>

{$table}

<div class='navi'>
<a class='block' href='index.html'>wstecz</a>
<a class='block' href='#top'>na górę</a>
</div>

</body>

</html>

PAGE;

echo $page;

?>