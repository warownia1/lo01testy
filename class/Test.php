<?php

include __DIR__ .'/Stan.php';


/*
 * klasa przechowująca dane o teście
 * używana przy listowaniu testów w tablicy tests_list
 * przechowuje test aktualnie pisany w ciągu danej sesji, aby mozna było do niego wrócić
 */
class Test
{
	private $id;
	private $nazwa;
	
	private $wsz_pytan;
	private $il_pytan;
	private $dostepny;
	
	private $num_pytania;
	private $pytania = array();
	
	function __construct ( $link, $test_id )
	{
		$query = <<< QUERY
SELECT test_id, nazwa, wsz_pytan, il_pytan
FROM testy
WHERE test_id={$test_id}
QUERY;
		$result = mysqli_query( $link, $query );
		$result_row = mysqli_fetch_assoc( $result );
		
		$this->id = (int)$result_row['test_id'];
		$this->nazwa = $result_row['nazwa'];
		$this->wsz_pytan = (int)$result_row['wsz_pytan'];
		$this->il_pytan = (int)$result_row['il_pytan'];
		$this->num_pytania = 0;
		
		$this->ustawDostepny( $link );
	}
	
	private function ustawDostepny( $link )
	{
		if( !empty( $_SESSION['USER_ID'] ) )
		{
			$stan = self::sprawdzDostepny( $link, $this->id, $_SESSION['USER_ID'] );
			
			
			if( Stan::moznaPisac( $stan ) )
			{
				$podejsc = self::ileRazyPisal( $link, $_SESSION['USER_ID'], $this->id );
				$maks = self::maksPodejsc( $link, $_SESSION['USER_ID'], $this->id );
				
				if( $podejsc < $maks )
					$this->dostepny = $stan;
				else
					$this->dostepny = Stan::ZABLOKOWANY;
			}
			else
				$this->dostepny = $stan;
		}
		else
		{
			$this->dostepny = 0;
		}
	}
	
	
	/*
	 * funkcja sprawdza czy ilość podejść do testu nie została przekroczona
	 */
	
	
	
	function __sleep()
	{
		return array( 'id', 'nazwa', 'wsz_pytan', 'il_pytan', 'dostepny', 'num_pytania', 'pytania' );
	}
	
	
	/*
	 * przechodzi do następnego pytania
	 * zwraca wartosć false jeśli już nie ma więcej pytań
	 * true gdy są jeszcze pytania
	 */
	function nastepny()
	{
		$this->num_pytania ++;
		
		if( $this->num_pytania >= $this->il_pytan )
			return false;
		else
			return true;
	}
	
	
	/*
	 * sprawdza czy test został zakończony
	 * jeśli tak to zwraca true, jeśli nie to false
	 */
	function jestZakonczony()
	{
		if( $this->num_pytania >= $this->il_pytan )
			return true;
		else
			return false;
	}
	
	
	/*
	 * funkcja sprawdza czy ten test może być pisany
	 * czy jego stan uprawnia do pisania
	 */
	function moznaPisac()
	{
		return Stan::moznaPisac( $this->dostepny );
	}
	
	
	/*
	 * pobiera aktualnie pytanie z listy i zwaca id pytania
	 * jeśli test jest zakończony to zwraca -1
	 */
	function wezPytanie()
	{
		if( $this->jestZakonczony() )
			return -1;
		else
			return ($this->pytania[ $this->num_pytania ]);
	}
	
	
	/*
	 * pobiera dowlone pytanie z listy pytan o podanym numerze
	 * jeśli pytanie nie ma to zwraca 0
	 */
	function wezPytanieNumer( $i )
	{
		if( $i <= $this->il_pytan )
			return ($this->pytania[ $i-1 ]);
	}
	
	
	/*
	 * pobiera wszystkie id pytan jako tablicę
	 */
	function wezWszystkiePytania()
	{
		return $this->pytania;
	}
	
	
	/*
	 * funkcje dostepowe
	 */
	function wezNazwa()
	{
		return $this->nazwa;
	}
	
	function wezID()
	{
		return $this->id;
	}
	
	function wezIlPytan()
	{
		return $this->il_pytan;
	}
	
	function wezWszPytan()
	{
		return $this->wsz_pytan;
	}
	
	function wezDostepny()
	{
		return $this->dostepny;
	}
	
	
	/*
	 * funkcja zwraca pięcioelementową z ilośćą pytań w każdym z poziomów trudności
	 * ilość wyliczana jest na podstawie poziomu ucznia
	 * $ilosc[0] pytania banalne
	 * $ilosc[1] łatwe
	 * $ilosc[2] średnie
	 * $ilosc[3] trudne
	 * $ilość[4] niewyobrażalnie trudne
	 */
	private function dobierzTrudnosc( $poziom )
	{
		$ilosc = array(0, 0, 0, 0, 0);
		
		switch( $poziom )
		{
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
			case 6:
			case 7:
			default:
				$ilosc[4] = $this->il_pytan;
		}
		
		return $ilosc;
	}
	
	
	/*
	 * pobiera z bazy $link ilość pytań podaną jako $ilość o danym poziomie trudności
	 * pobrane pytania są zapisywane bezpośrenio do właściwości obiektu
	 *
	 * funkcja zwraca ilość pytań, która została pobrana
	 */
	private function pobierzPytania( $link, $ilosc, $trudnosc )
	{
		$query = <<< QUERY
SELECT pytanie_id
FROM pytania
WHERE test_id={$this->id}
AND trudnosc={$trudnosc}
ORDER BY RAND()
LIMIT {$ilosc}
QUERY;

		$result = mysqli_query( $link, $query );
		$ilosc_pobranych = mysqli_num_rows( $result );
				
		while( $row = mysqli_fetch_row( $result ) )
		{
			$this->pytania[] = (int) $row[0];
		}
		
		return $ilosc_pobranych;
	}
	
	
	/*
	 * funkcja wywołana po rozpoczęciu testu
	 * jej zadaniem jest wylosowanie listy pytan dla danego poziomu trudności i zapisane ich we właściwości obiektu
	 * jako parametry przyjmuje link do bazy danych i poziom trudnosći
	 * jesli wszystko pójdzie bezbłędnie to funkcja zwróci 1
	 */
	function losujPytania( $link, $poziom )
	{
		$il_pytan = $this->il_pytan;
		
		$il_na_poziom = $this->dobierzTrudnosc( $poziom );
		
		for( $i = 4; $i >= 0; --$i )
		{
			$il_pobr = $this->pobierzPytania( $link, $il_na_poziom[$i], $i );
			
			/* niewykorzystana ilość pytań z danego poziomu zostaje przeniesiona na poziom niższy
			 * od danego poziomu odejmujęilość pobranych i dodaję na niższy */
			$il_na_poziom[$i] -= $il_pobr;
			
			if( $i > 0 )
			{
				$il_na_poziom[$i-1] += $il_na_poziom[$i];
				$il_na_poziom[$i] = 0;
			}
		}
		
		/* gdy zdarzy się tak, że po wszystkim nadal zostały jakieś pytania na najniższym poziomie
		 * to robię odwrotny proces, pobieram przy rosnącej trudności */
		if( $il_na_poziom[0] > 0 )
		{
			for( $i = 1; $i <= 4; ++$i )
			{
				/* pobieram z niższego poziomu i zużywam ile mogę, a następnie odejmuję zużyte*/
				$il_na_poziom[$i] = $il_na_poziom[$i-1];
				$il_pobr = $this->pobierzPytania( $link, $il_na_poziom[$i], $i );
				$il_na_poziom[$i] -= $il_pobr;
			}
		}
		
		/* jeśli nadal nie zużyłem wszystkich to coś tu jest nie tak i w bazie jest mniej pytań niż wylosowałem
		 * w takiej sytuacji należy sprawdzić, czy dobierzTrudnosc dziala poprawnie i czy w bazie nie ma za mało pytań. */
		if( $il_na_poziom[4] > 0 )
		{
			trigger_error("Too few questions drawn; amount left: {$il_na_poziom[4]}", E_USER_ERROR);
			return 0;
		}
		return 1;
	}
	
	
	/*
	 * funkcja sprawdzająca w bazie dostepność testu dla danego użytkownika
	 * jako parametr przyjmuje link do bazy mysql, id szukanego testu i id użytkownika
	 * zwraca wartość true jesli test jest dostępny i false w przeciwnym razie
	 */
	static function jestDostepny( $link, $test_id, $uzytk_id )
	{
		return Stan::moznaPisac( self::sprawdzDostepny( $link, $test_id, $uzytk_id )  );
	}
	
	
	
	/*
	 * funkcja sprawdzająca w bazie dostepność testu dla danego użytkownika
	 * jako parametr przyjmuje link do bazy mysql, id szukanego testu i id użytkownika
	 * zwraca wartość liczbę odpowiadającą dostępnosci testu
	 */
	static function sprawdzDostepny( $link, $test_id, $uzytk_id )
	{
		$query = <<< QUERY
SELECT MAX(grupa_test.wartosc)
FROM grupa_test, uzytk_grupa
WHERE grupa_test.test_id = {$test_id}
AND uzytk_grupa.uzytk_id = {$uzytk_id}
AND uzytk_grupa.grupa_id = grupa_test.grupa_id
QUERY;
		$result = mysqli_query( $link, $query );
		
		$row = mysqli_fetch_row( $result );
		return $row[0];
	}
	


	/*
	 * funkcja zwraca ile podejść miał już uczeń o danym id do danego testu
	 */
	static function ileRazyPisal( $link, $uzytk_id, $test_id )
	{
		$query = <<< QUERY
SELECT COUNT(uzytk_id)
FROM wyniki
WHERE uzytk_id='{$uzytk_id}'
AND test_id='{$test_id}'
QUERY;
		$result = mysqli_query( $link, $query );
		$row = mysqli_fetch_row( $result );
		return $row[0];
	}


	/*
	 * funkcja zwraca ile maksymalnie podejść może pojdąć użytkownik
	 */
	static function maksPodejsc( $link, $uzytk_id, $test_id )
	{
		$query = <<< QUERY
SELECT MAX(podejscia)
FROM grupa_test
INNER JOIN uzytk_grupa
ON grupa_test.grupa_id=uzytk_grupa.grupa_id
WHERE uzytk_id='{$uzytk_id}'
AND test_id='{$test_id}'
QUERY;

		$result = mysqli_query( $link, $query );
		$row = mysqli_fetch_row( $result );
		return $row[0];
	}


}

?>