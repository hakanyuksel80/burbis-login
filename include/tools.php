<?php


class Tools
{
	// 
	public static function tarih_gecmis($tarih)
	{
		$bugun = date("d-m-Y",time());		
		
		$durum = new DateTime($bugun) >	 new DateTime($tarih);
		
		return $durum;
	}
	
	
	/*
	 * AdSoyadBul Fonsiyonu
	 *
	 * parametre olarak verilen birleşik Ad Soyad Bilgisinin içindeki en son boşluğu kullanarak Ad ve Soyad olarak ayırır ve
	 * dizi olarak döndürür. Dizinin 0. elemanı Ad , 1. elemanı Soyad bilgisini taşır.
	 * Eğer metin içinde boşluk bulamassa Ad Soyad bilgisinin tamamını Ad olarak döndürür.
	 */
	public static function AdSoyadBul($adsoyad)
	{
		$data = array();
		
		$adsoyad = trim($adsoyad);
		
		if (!strrpos($adsoyad," "))
		{
			$ad = $adsoyad;
			$soyad = "";
		} else
		{
			$ad = substr($adsoyad,0,strrpos($adsoyad," "));
			$soyad = substr ($adsoyad, strrpos($adsoyad," ")+1,strlen($adsoyad)-strrpos($adsoyad," ")+1);
		}
		
		$data[0] = trim($ad);
		$data[1] = trim($soyad);
		
		return $data;
	}


	public static function tr_strcmp ( $a , $b ) {
		$lcases = array( 'a' , 'b' , 'c' , 'ç' , 'd' , 'e' , 'f' , 'g' , 'ğ' , 'h' , 'ı' , 'i' , 'j' , 'k' , 'l' , 'm' , 'n' , 'o' , 'ö' , 'p' , 'q' , 'r' , 's' , 'ş' , 't' , 'u' , 'ü' , 'w' , 'v' , 'y' , 'z' );
		$ucases = array ( 'A' , 'B' , 'C' , 'Ç' , 'D' , 'E' , 'F' , 'G' , 'Ğ' , 'H' , 'I' , 'İ' , 'J' , 'K' , 'L' , 'M' , 'N' , 'O' , 'Ö' , 'P' , 'Q' , 'R' , 'S' , 'Ş' , 'T' , 'U' , 'Ü' , 'W' , 'V' , 'Y' , 'Z' );
		$am = mb_strlen ( $a , 'UTF-8' );
		$bm = mb_strlen ( $b , 'UTF-8' );
		$maxlen = $am > $bm ? $bm : $am;
		for ( $ai = 0; $ai < $maxlen; $ai++ ) {
			$aa = mb_substr ( $a , $ai , 1 , 'UTF-8' );
			$ba = mb_substr ( $b , $ai , 1 , 'UTF-8' );
			if ( $aa != $ba ) {
				$apos = in_array ( $aa , $lcases ) ? array_search ( $aa , $lcases ) : array_search ( $aa , $ucases );
				$bpos = in_array ( $ba , $lcases ) ? array_search ( $ba , $lcases ) : array_search ( $ba , $ucases );
				if ( $apos !== $bpos ) {
					return $apos > $bpos ? 1 : -1;
				}
			}
		}
		return 0;
	 
	}

	public static function tr_strToUpper($text)
	{
		setlocale(LC_CTYPE, 'tr_TR.UTF-8');
		
		$search=array("ç","i","ı","ğ","ö","ş","ü");
		$replace=array("Ç","İ","I","Ğ","Ö","Ş","Ü");
		$text=str_replace($search,$replace,$text);
		$text=mb_strtoupper($text);
		return $text;
	}
} 






