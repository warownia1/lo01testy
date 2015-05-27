<?php

/*
 * funckja sprawdza, czy dana odpowiedz i pytanie sa ze sobą powiązane
 * zwraca wartość 1 jeśli tak, 0 jeśli nie lub w przypadku błędu.
 */
function pasujePytOdp( $link, $pyt_id, $odp_id )
{
	if( $pyt_id <= 0 ) return 0;

	$query = <<< QUERY
SELECT COUNT(*)
FROM odpowiedzi
WHERE pytanie_id = {$pyt_id}
AND odpowiedz_id = {$odp_id}
QUERY;
	$result = mysqli_query( $link, $query );
	$row = mysqli_fetch_row( $result );
	if( $row[0] == 1 )
		return 1;
	else if( $row[0] == 0 )
		return 0;
	else
	{
		trigger_error( "Do odpowiedzi pasuje więcej niż jedno pytanie.", E_USER_ERROR );
		return 0;
	}
}


/*
 * pobiera treść pytania o podanym id z bazy danych
 */
function pobierzTrescPyt( $link, $pyt_id )
{
	$query = <<< QUERY
SELECT tresc
FROM pytania
WHERE pytanie_id = {$pyt_id}
QUERY;
	$result = mysqli_query( $link, $query );
	$row = mysqli_fetch_row( $result );
	return $row[0];
}


/*
 * funkcja przekazuje tablice z id odpowiedzi i ich treściami
 * uwaga, poptrzednie wartości zostana usunięte
 */
function pobierzOdp( $link, $pyt_id, &$odp_id, &$odp_tresc )
{
	$query = <<< QUERY
SELECT odpowiedz_id, tresc
FROM odpowiedzi
WHERE pytanie_id = {$pyt_id}
ORDER BY RAND();
QUERY;
	$result = mysqli_query( $link, $query );

	$odp_id = array();
	$odp_tresc = array();
	while( $row = mysqli_fetch_row( $result ) )
	{
		$odp_id[] = $row[0];
		$odp_tresc[] = $row[1];
	}
}


/*
 * funkcja sumująca punkty uzyskane z udzielonych odpowiedzi
 * jako argument otrzymuje tablicę z id odpowiedzi i link do bazy
 * zwraca suę otrzymanych punktów
 */
function sumaPunktow( $link, $odp )
{
	$matches = implode( ', ', $odp );

	$query = <<< QUERY
SELECT SUM( punkty )
FROM odpowiedzi
WHERE odpowiedz_id IN ( {$matches} )
QUERY;

	$result = mysqli_query( $link, $query );
	$row = mysqli_fetch_row( $result );
	return $row[0];
}


 
function maksPunktow( $link, $odp )
{
	$matches = implode( ', ', $odp );
	$query = <<< QUERY
SELECT SUM( maks )
FROM
    (SELECT MAX( punkty ) AS maks
    FROM odpowiedzi
    JOIN (SELECT pytanie_id
          FROM odpowiedzi
          WHERE odpowiedz_id IN ( {$matches} ))
          AS T
    ON odpowiedzi.pytanie_id=T.pytanie_id
    GROUP BY odpowiedzi.pytanie_id)
    AS M
QUERY;
	$result = mysqli_query( $link, $query );
	$row = mysqli_fetch_row( $result );
	return $row[0];
}



/*
 * funkcja sprawdza zgodność hasła nauczyciela z id hasła
 * zwraca wartosć true jesli się zgadzają
 */
function sprawdzHasloNaucz( $link, $id, $haslo )
{
	//$sha = hash ( 'sha256', $haslo );
	$query = <<< QUERY
SELECT COUNT(haslo_id)
FROM weryf_hasla
WHERE haslo_id='{$id}'
AND haslo='{$haslo}'
QUERY;
	$result = mysqli_query( $link, $query );
	$row = mysqli_fetch_row( $result );
	
	if( $row[0] == 1 )
		return true;
	else
		return false;
}

?>