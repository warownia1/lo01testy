<?php

abstract class BazaDanych
{
	const login = 'lo01_testy';
	const password = 'lo01_testy';
	const database = 'lo01_testy';
	
	/*
	 * funkcja łącząca z bazą danych i zwaracjąca link do niej
	 */
	static function polacz()
	{
		return mysqli_connect( 'localhost', self::login, self::password, self::database );
	}
}

?>