<?php
	/* Implementacia Triedy konecneho pola */
	
	define('MAX_POLYNOM_SIZE', 4000);
	
if (!isset($GF_PHP))
{
$GF_PHP = 1 ;

class GF
{	
	var $ALFA_INFINITY ;// ked je alfa minus nekonecno mysli sa binarne nula

	/* parametre a interne premenne konecneho pola */	
	var $array;	// pole intov binarnej reprezentacie Alfa prvkov
	var $array_inverse; // inversne pole(teda index je binarna repr. a obsah je Alfa)
							// limit: q najviac 2 na 32 z tohoto vyplyva
	var $generator = "";		// polynom ktory vygeneroval toto GF(q)
	var $m = 0;		// mocnina v GF(2 na 'm')
	var $q = 0;		// q = 2 na 'm'

	var $isValid;			// v konstruktore nastavene po uspesnom vytvoreni GF(q)

	/* O P E R A C I E   N A D   K O N E C N Y M   P O L O M */
	function AddByGrade($first, $second) // scitanie podla mocniny, musi taktiez lokalizovat 
	{
		if ($first==$this->ALFA_INFINITY)
			return $second;
		if ($second==$this->ALFA_INFINITY)
			return $first;

		while ($first > ($this->q -2))
			$first -= ($this->q -1);
		while ($second > ($this->q -2))
			$second -= ($this->q -1);

		$jedna = $this->array[$first];
		$dva = $this->array[$second]; // ziskanie binarnej reprezentacie mocnin alfy

		$vysledok = $this->Add($jedna, $dva);

		$mocnina = $this->ALFA_INFINITY;
		if ($vysledok!=0)
		   $mocnina = $this->array_inverse[$vysledok];
		/*for ( $f = 0; $f< ($this->q-1);$f++)
		{
			if ($vysledok == $this->array[$f])
			{
				$mocnina = $f;
				break;
			}
		}*/
		
		return $mocnina;
	}

	function MultiplyByGrade ($first, $second) // nasobenie (cize scitanie mocniny)
	{
		if ($first==$this->ALFA_INFINITY)
			return $this->ALFA_INFINITY;
		if ($second==$this->ALFA_INFINITY)
			return $this->ALFA_INFINITY;
	
		$vysledok = $first + $second; // scitame mocniny

		while ($vysledok > ($this->q -2))
			$vysledok -= ($this->q -1);

		return $vysledok;
	}

	function PrintBinaryDebug ($binar, $bitcount)
	{
		$maska = 1 << $bitcount;	
		while ($bitcount>0)
		{
			$maska >>= 1;
			if ($maska & $binar)
				echo "1";
			else echo "0";
			$bitcount--;
		}
	}

	function PrintDebug()
	{
		echo ("<TABLE class=\"GFPrintDebug\" cols=\"2\">		
		<TR><TH colspan=\"2\">PrintDebug</TH></TR>\n");
		echo("<TR><TD class=\"popis\">Valid:</TD><TD>".($this->isValid)."</TD></TR>\n");
		echo("<TR><TD class=\"popis\">q:</TD><TD>".($this->q)."</TD></TR>\n");
		echo("<TR><TD class=\"popis\">m:</TD><TD>".($this->m)."</TD></TR>\n");
		echo("<TR><TD class=\"popis\">generator:</TD><TD>");
		$this->PrintBinaryDebug($this->generator, $this->m + 1);
		echo "</TD></TR>";
		$opak = $this->TestDistinct();
		echo '<tr><td class="popis">TestDistinct():</td><td>'.($opak ? 'áno' : 'nie' ).'</td></tr>'."\n";
	
		for ($gg = 0; $gg< ($this->q-1);  $gg++)
		{
			echo "<TR><TD class=\"popis\">$gg:</TD><TD>";
			$this->PrintBinaryDebug($this->array[$gg], $this->m);
			echo "</TD></TR>\n";
		}
		echo "</TABLE>\n";
	}

	function DivideByGrade ($up, $down) // delenie (cize odcitanie mocniny)													
					   // ktora mocnina alfy je vysledkom
	{
	   if ($down==$this->ALFA_INFINITY) echo "Delenie nulou v GF->DivideByGrade!!<BR>\n";
		if ( ($up==$this->ALFA_INFINITY) || ($down==$this->ALFA_INFINITY))
			return $this->ALFA_INFINITY;

		$vysledok = $up;
		if ($up<$down)
			$vysledok += ($this->q -1);
		$vysledok -= $down;

		return $vysledok;
	}

	function Add ($first, $second) // scita napriklad Alfa(0) + Alfa(4) binarne = trebars Alfa(3) . 
	{
	/*	$sucet = 0;
		$sucet = $first;
		$sucet ^= $second;
		return $sucet;*/
		return ($first ^ $second);
	}
												 // Aplikuje aj modulo

	function SolvePolynom($unknown, & $polynom)	// vyries rovnicu kde za exx zadaj vzdy 'unknown'
	{
		// ak nic ine tak vrat aspon nulu
		$vysledok = $this->ALFA_INFINITY;
        $poc = 0;

		while ($polynom!=NULL)
		{
		    $poc++;
		    if ($poc==MAX_POLYNOM_SIZE) {echo 'prilis velka dlzka polynomu v SolvePolynom!';  die;}
			if ( ($unknown!=$this->ALFA_INFINITY) || ($polynom->exx==0) && ($polynom->alfa!=$this->ALFA_INFINITY) )
			{
				$vysledok = $this->AddByGrade ( $vysledok, $this->MultiplyByGrade( $polynom->alfa, $unknown * $polynom->exx) );
			}
			// na dalsiu cast polynomu
			$polynom = & $polynom->next;
		}

		return $vysledok;		
	}


	/* K O N V E R Z I A   M E D Z I   P O L Y N O M O M (string), 
	   S L L   S T R U K T U R O U   A   O P A C N E */
	function SLLToPolynom($sll, &$destPoly)
	{
		$destPoly = "";

		while ($sll!=NULL) // cez vsetky SLL elementy
		{
			if ($sll->alfa!= $this->ALFA_INFINITY) // nulove elementy nezapisujeme do polynomu
			{			
				if (($sll->alfa==0)&&($sll->exx==0))
					$destPoly .= "1"; // X je nula ako aj Alfa preto zapis len '1'
			
				else
				{
					// zapis Alfa
					if ($sll->alfa!= 0)
						$destPoly .= "A" . $sll->alfa ;
	
					// zapis X
					if ($sll->exx!=0)
						$destPoly .= "x" . $sll->exx ;
				}
		
			}

			// treba dat plus nakoniec ?
			if ( ($sll->next!=NULL) && ($sll->next->alfa != $this->ALFA_INFINITY ))
				$destPoly .= "+";

			// na dalsi element
			$sll = & $sll->next;
		}

		return true;
	}

	function &PolynomToSLL($polynom)
	{
		$item = NULL;	
		$item2 = NULL;
		$first = NULL;

		if ($polynom==NULL) return false;
		if ($polynom=="") return false;

		$previousX = false; // posledny znak bol X
		$previousXnum = false;

		$previousAlfa = false; // posledny znak bol A ako alfa
		$previousAlfanum = false;

		$tempNext = true;	// hned vytvor dalsi prvok

		while ($polynom != "")
		{	

			switch ( substr($polynom,0,1) )
			{
			case 'x' :
			case 'X' : 
				{			
					if ($previousAlfa==true)
						$item->alfa=1; // pred X bola samostatna Alfa

					$previousX = true;
					if ($tempNext==true) {	// vytvor novy element					

						$item = & new generatorSLL($item);

						if ($item==NULL) {
							$item=&$first;
							while ($item!=NULL)
							{
								$item2=&$item->next;
								unset( $item);
								$item=&$item2;
							}
							
							return false; } // chyba !!!					
						$tempNext = false;
						if ($first==NULL) { 
							$first = &$item; 
						}
					}				

					$previousAlfa = false;
					$previousAlfanum = false;
					$previousXnum = false;
					break; }			
			case 'a':
			case 'A':
				{
					if ($previousX==true)
						$item->exx=1;

					$previousAlfa = true;
					if ($tempNext==true) {	// vytvor novy element
						$item = & new generatorSLL($item);
						if ($item==NULL) {
							$item=&$first;
							while ($item!=NULL)
							{
								$item2= & $item->next;
								unset( $item);
								$item=&$item2;
							}

							return false; } // chyba !!!					
						$tempNext = false;
						if ($first==NULL) $first = & $item; }				
					$previousX = false;
					$previousXnum = false;
					$previousAlfanum = false;
					break;
				}
			case '0':
			case '1':
			case '2':
			case '3':
			case '4':
			case '5':
			case '6':
			case '7':
			case '8':
			case '9':
				{			
					if ( ($previousX==false) && ($previousAlfa==false) && ($tempNext==true) && ( substr($polynom,0,1)=='1') ) 
					{				
						// ak je to samostatna jednotka - vytvor prvok A0
						$item = & new generatorSLL($item);
						if ($item==NULL) 
						{
							$item=&$first;
							while ($item!=NULL)
							{
								$item2=& $item->next;
								unset( $item);
								$item=&$item2;
							}

							return false;
						} // chyba !!!	
						$tempNext = false;					
						if ($first==NULL) $first = &$item;  
					}				
					else if ( ($previousX==false) && ($previousAlfa == false) && ($tempNext==true) ) 
					{				
						// ak je to novy prvok, bez predosleho X a iny znak tak chyba
						$item=&$first;
						while ($item!=NULL)
						{
							$item2=& $item->next;
							unset( $item);
							$item=&$item2;
						}

						return false;
					}	// chyba !!!				
					else  
					{				
						// je to znak 0-9 po X ci inej ciselnej cifre po X
						if ($previousX||$previousXnum) {
							$item->exx *= 10;
							$item->exx += ord( substr($polynom,0,1)) - ord('0');  $previousXnum = true;}
						if ($previousAlfa||$previousAlfanum) {
							$item->alfa *= 10;
							$item->alfa += ord(substr($polynom,0,1)) - ord('0'); $previousAlfanum=true;}
					}				
					$previousX = false;	
					$previousAlfa = false;
					break; 
				}			
			case '+': {			
					if ($previousX)
					{
						// bolo to samotne x... tak dajme ze to je x o mocnine 1.
						$item->exx=1;
					}
					else if($previousAlfa)
					{
						$item->alfa=1;
					}
					$tempNext = true; // dalsi spravny znak vytvori novy element
					$previousX = false; 
					$previousAlfa = false;
					$previousXnum=false;
					$previousAlfanum = false;

					break; }			
			default: {
					$item=& $first;
					while ($item!=NULL)
					{
						$item2=& $item->next;
						unset( $item);
						$item=&$item2;	
					}

					return false;} // failure !!!
			}
			$polynom = substr($polynom,1);
		}

		// uspesny prevod	

/*
		$rrr = & $first;
		// DEBUG!!!!
		while ($rrr!=NULL)
		{		
			printf("Kon: exx:%d alfa:%d <br>\n", $rrr->exx, $rrr->alfa);
			$rrr = & $rrr->next;	
		}
		///////////
*/		
		return $first;

		//return true;
	}

	function &GF($p_x)			// konstruktor - GF(q) jednoznacne definovane generatorom p(x)
	{
		// najprv nastav neplatnost objektu triedy
		$this->isValid = false;
		$this->array = NULL;
		$this->array_inverse = NULL;
		$this->generator = 0;
		$this->ALFA_INFINITY = -1;
		$this->m = 0;
		$this->q = 0;

		$first = NULL;

		////////////////////////////////////////////////////////////////////////
		// 1. skonvertovat retazec p_x to SLL struktury + detekcia chyby
		$first = NULL;

		$first =& $this->PolynomToSLL ($p_x);
		if ($first == NULL) return;

		//////////////////////////////////////////////////////////////////////
		// 2. prejst SLL a najst najvyssiu mocninu = 'm', pritomnost duplicit, Alfa musi byt 0
		//		nutnost jednotky (alebo mocniny x0) + vypln generator premennu
		$item = & $first;
		$item2 = NULL;
		$hasJednotka = false;
		$max_exx = 0;
		$this->generator = 0;
		while ($item!=NULL)
		{
			if (($item->exx) > $max_exx) $max_exx = $item->exx; // najdi najvyssiu mocninu X
		
			if ($item->exx == 0) $hasJednotka=true; // nutna jednotka

			if ($item->alfa!=0)
			{
				return; // chyba, alfa nie je nula, tu su len X povolene
			}

			$item2 = & $first;
			$tempp = 1; $tempp <<= $item->exx; // pridaj tento bit do generatora
			$this->generator |= $tempp;

			$item = & $item->next; // dalsi prvok
		}
		if ($hasJednotka==false) return; // nema jednotku, preto skonci


		/////////////////////////////////////////////////////////////
		// 3. vytvorit staticke pole o rozmere  q-1 = (2 na 'm') - 1
		$this->m = $max_exx; // nastav najvyssiu mocninu		
		$this->q = 1;
		$this->q <<= $this->m;
		  //$array = malloc ( sizeof(GF_POLY) * (q-1) );
		$this->array[$this->q-1] = ""; 			// ??????????????????????????
			//if (array == NULL) return;
		for ($ind=0; $ind <= $this->q -1; $ind++)
          $this->array_inverse[$ind] = 0;


		/////////////////////////////////////////////////////////////
		// 4. od prvku A0 = 1 nastavit ostatne A(q-2)
		for ($in = 0;$in< ($this->q-1);$in++)
		{
			if ($in==0) $this->array[$in] = 1;
			else {
				$this->array[$in] = ( $this->array[$in-1] * 2 );	// o jeden bit dolava
				if ($this->array[$in]>=$this->q) {					// ak to pretieklo cez nas limit dany generatorom
					$this->array[$in] ^= $this->generator;			// XOR pomocou generatora
				}
			}
			
			$invind = $this->array[$in]; // inverzny index
			if ( $this->array_inverse[$invind] != 0)
			   // chybaaa.... uz obsadene
			   return false;
         else
            $this->array_inverse[$invind] = $in; // naplnenie inverzneho pola
		}

      /////////////////////////////////////////////////////////////
      // 5. test jedinecnosti, ak prvky Alfa nie su jedinecne, tak je to cele nanic...
      //if (!$this->TestDistinct()) return;

		// uspech, preto nastav platnost objektu tejto triedy
		$this->isValid = true;

	}

	/*
	function ~CGF()
	{

	}
*/

  /* OVER CI SU ALFA PRVKY JEDINECNE, IBA VTEDY JE GF PLATNE ! */
  function TestDistinct()
  {
     for ($aprvy = 0; $aprvy < ($this->q -1); $aprvy++)
     {
        if ($this->array[$aprvy] == $this->ALFA_INFINITY) return false; // prvok alfa nemoze byt nulovy
        for ($adruhy=0; $adruhy < $aprvy; $adruhy++)
        {
          if ($this->array[$aprvy] == $this->array[$adruhy])
             return false; // ach nie, rovnake prvky..
        }
     }
     return true; // ano, prvky su jedinecne
  }

};



// trieda na docasne pouzitie - predspracovanie stringu generatora pola
class generatorSLL
{
	var $exx = 0 ;
	var $alfa = 0;
	var $next = NULL;		// dalsi prvok SLL
	var $previous = NULL;	// rozsirenie na DLL
	
	var $objID = 0; // IDcko na obidenie probl. s nesting level. Kazdy novy objekt
	                // bude mat nove cislo

	function &generatorSLL(& $last)
	{
	   global $objectID;
	   if (!isset($objectID))
	      $objectID = 1;
	   $this->objID = $objectID++;
	
	//echo $this->objID . "<BR>\n";
		$this->exx=0;
		$this->alfa = 0;
		$this->next=NULL;
		$this->previous=NULL;
		if ($last!=NULL) {
			$last->next=& $this;
			$this->previous = & $last;
		}
	}

	function CopyFrom(&$second)
	{
		// prekopiruj udaje zo 'second' do this
		$this->alfa = $second->alfa;
		$this->exx = $second->exx;
	}

	function Krat(&$second)
	{
		// vynasob moje elemenrty s elementami 'second' prvku
		// vynasobenie ale vlastne znamena spocitanie
		$this->alfa += $second->alfa;
		$this->exx += $second->exx;
	}	
	
	function SwapNext()	// vymeni tento prvok s jeho dalsim susedom
	{
		if ($this->next != NULL)
		{
			// zaloha << ja		
			$t_exx = $this->exx;
			$t_alfa = $this->alfa;
						
			// ja << sused
			$this->exx = $this->next->exx;
			$this->alfa = $this->next->alfa;
		
			// sused << zaloha
			$this->next->exx = $t_exx;
			$this->next->alfa = $t_alfa;		
		}
	}

	function Delete()
	{
		$t_next = & $this->next;
		$t_prev = & $this->previous;
	
		if ($this->next!=NULL)
			$this->next->previous = & $t_prev;

		if ($this->previous!=NULL)
			$this->previous->next = & $t_next;

		unset($this);
	}	
};

}

?>
