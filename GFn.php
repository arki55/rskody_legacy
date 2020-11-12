<?php

/**
 * 
 *
 * @version $Id$
 * @copyright 2003 
 **/

include "./GF.php";
include "./funcs.php";


class GFn extends GF
{
	function & GFn( $p_x, $posun=1 ) // posun je ako keby delta^posun (delta je x ako gener.prvok)
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
		// ak je nastaveny posun tak to gen.prvkom bude x^(posun)
		
		$dalsie = (int)2; // zacneme od x

		$pos = $posun-1;
		while($pos>0) {
			$dalsie*=2;
			$pos--;
			if ($dalsie>=$this->q) {					// ak to pretieklo cez nas limit dany generatorom
				$dalsie ^= $this->generator;			// XOR pomocou generatora
			}			
		}
		settype($dalsie, "integer");
		for ($in2 = 1; $in2<= ($this->q-1);$in2++)
		{		
			$in = $in2%($this->q-1);

			if ($dalsie>=$this->q) {					// ak to pretieklo cez nas limit dany generatorom
				$dalsie ^= $this->generator;			// XOR pomocou generatora
			}
			$this->array[$in] = $dalsie;
			$dalsie = ( $this->array[$in] * 2 );	// o jeden bit dolava			
			
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


}

?>