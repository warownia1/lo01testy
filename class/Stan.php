<?php

/*
 * klasa-enum przechowująca poszczególne stany dostępności
 * będzie również wybierała 'ważniejszy' stan
 */
abstract class Stan
{
	const DISABLED = 0;
	const EXPIRED = 1;
	const CURRENT = 2;
	const IMPORTANT = 3;
	const LOCKED = 4;
	
	const NIEAKTYWNY = 0;
	const WYGASL = 1;
	const AKTUALNY = 2;
	const WAZNY = 3;
	const ZABLOKOWANY = 4;
	
	/*
	 * funkcja zwraca stan o wyższym priorytecie
	 * używana jest gdy dany uczeń przynależy do kilku grup i trzeba rozwiązać konflikt kilku uprawnień
	 * funkcja zwraca ważniejsze z dwóch podanych uprawnień
	 * hierarchia:
	 * najsłabsze. . . . . . . . . . . . . . . . . .najmocniejsze
	 * DISABLED 0 < EXPIRED 1 < CURRENT 2 < IMPORTANT 3 < LOCKED 4
	 */
	function prior( $a, $b )
	{
		if( $a > $b )
			return $a;
		else
			return $b;
	}
	
	/*
	 * funkcja zwraca, czy dany stan uprawnia do napisania testu
	 */
	static function isAvailable( $state )
	{
		trigger_error( '@deprecated użyj moznaPisac($stan)' );
		return self::moznaPisac( $state );
	}
	
	/*
	 * funckja sprawdza czy podany stan kwalifikuje test do pisania go
	 * stany umożliwiające pisanie to: WAZNY AKTUALNY
	 * zwraca true jeśli mozna pisać dany test i false w przeciwnym razie
	 */
	static function moznaPisac( $stan )
	{
		switch( $stan )
		{
			case self::AKTUALNY :
			case self::WAZNY :
				return true;
			default:
				return false;
		}
	}
	
	
	static function doString( $stan )
	{
		switch( $stan )
		{
			case self::NIEAKTYWNY :
				return "nieaktywny";
			case self::WYGASL :
				return "wygasł";
			case self::AKTUALNY :
				return "aktualny";
			case self::WAZNY :
				return "ważny";
			case self::ZABLOKOWANY :
				return "zablokowany";
			default :
				return "nieznany stan";
		}
	}
}

?>