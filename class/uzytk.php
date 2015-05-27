<?php

/*
 * funkcja pobiera z bazy danych poziom użytkownika i zwraca go
 * w przypadku niepowodzenia zwraca -1
 */
function pobierzPoziom( $link, $uzytk_id )
{
	$query = <<< QUERY
SELECT poziom
FROM uzytkownicy
WHERE uzytk_id = {$uzytk_id}
QUERY;
	$result = mysqli_query( $link, $query );
	
	if( mysqli_num_rows( $result ) != 1 )
	{
		trigger_error( "poziom żadnego użytkownika nie został pobrany czy ten użytkownik istnieje?", E_USER_ERROR );
		return -1;
	}
	$row = mysqli_fetch_row( $result );
	return $row[0];
}


/*
 * funkcja pobiera z bazy danych imię użytkownika podaneg ojako jego id
 */
function pobierzImie( $link, $uzytk_id )
{
	$query = <<< QUERY
SELECT imie
FROM uzytkownicy
WHERE uzytk_id = {$uzytk_id}
QUERY;
	$result = mysqli_query( $link, $query );
	$row = mysqli_fetch_row( $result );
	return $row[0];
}


/*
 * funkcja pobiera z bazy danych nazwisko użytkownika podaneg ojako jego id
 */
function pobierzNazwisko( $link, $uzytk_id )
{
	$query = <<< QUERY
SELECT nazwisko
FROM uzytkownicy
WHERE uzytk_id = {$uzytk_id}
QUERY;
	$result = mysqli_query( $link, $query );
	$row = mysqli_fetch_row( $result );
	return $row[0];
}


/*
 * funkcja pobiera z bazy danych login użytkownika podaneg ojako jego id
 */
function pobierzLogin( $link, $uzytk_id )
{
	$query = <<< QUERY
SELECT login
FROM uzytkownicy
WHERE uzytk_id = {$uzytk_id}
QUERY;
	$result = mysqli_query( $link, $query );
	$row = mysqli_fetch_row( $result );
	return $row[0];
}



/*
 * funkcja sprawdza czy podane dane logowania się zgadzają
 * zwraca id użytkownika jeśli hasło i login są porawne
 * zwraca 0 w przeciwnym wypadku
 */
function sprawdzHaslo( $link, $login, $haslo )
{
	$sha = hash ( 'md5', $haslo );
	$query = "
	SELECT uzytk_id
	FROM uzytkownicy
	WHERE
	login = '{$login}' AND
	haslo = '{$sha}';
	";
	
	$result = mysqli_query($link, $query);
	
	if( mysqli_num_rows($result) > 1 )
		trigger_error("More than one {$login} user in database", E_USER_ERROR);
		
	if( mysqli_num_rows($result) == 1 )
	{
		$row = mysqli_fetch_row( $result );
		return $row[0];
	}
	else
		return 0;
}



/*
 * funkcja sprawdza czy hasło i id pasują do siebie
 * zwaca 1 jeśli pasuje i 0 w przeciwnym wypadku
 */
function sprawdzHasloID( $link, $uzytk_id, $haslo )
{
	$query = <<< QUERY
SELECT COUNT(haslo)
FROM uzytkownicy
WHERE haslo='{$haslo}'
AND uzytk_id={$uzytk_id}
QUERY;
	//echo var_dump( $query );

	$result = mysqli_query( $link, $query );
	$row = mysqli_fetch_row( $result );
	if( $row[0] == 1 )
		return 1;
	else
		return 0;
}

?>