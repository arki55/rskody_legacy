<?php

	/* ReedSolomon trieda - implementacia RS kodera/dekodera */

if (!isset($REEDSOLOMON_PHP))
{
   $REEDSOLOMON_PHP = 1;

	require("GF.php");
	require("Register.php");
	require("GFMatrix.php");

class ReedSolomon
{
	//////////////////// PARAMETRE Reed Solomon KODU ///////////////////////
	var $t = 0; // pocet opravujucich chyb  //unsigned short
	var $j = 0; // pociatocny index na vytvorenie gener. polynomu //unsigned short
	
	var $n = 0; // pocet bitov kodu //unsigned short
	var $k = 0; // pocet informacnych bitov //unsigned short
	///////////////////////////////////////////////////////////////
	var $isValid = false;				// az po uspesnom vytvoreni RS triedy sa nastavi na 'true' //bool
	var $hadErrors = false;    // ci pri poslednom dekodovani boli detekovane chyby (moze detekovat chyby aj ked ich nevie opravit (2*t))
	var $genPolyString = ""; //char genPolyString[200];	// po vytvoreni generujuceho polynomu sa vytvori retazec na vypisanie pripadne
	
	var $genField = NULL;				// objekt triedz GF ( finite field ) //CGF*
	var $matrix = NULL;			// matica na vypocet pol.lokatorov pri RS dekodovani //CGFMatrix *

  	var $genPoly = NULL;	// generujuci polynov (vytvoreny v konstruktore) /generatorSLL *
	var $virtRegister = NULL;	// virtualny HW register na delenie polynomov //CRegister*
	var $syndroms = NULL;		// miesto pre dekodovane syndromy //GF_ALFA*
	var $locators_cache = NULL;	// miesto pre vypocet lokatorov Cien.alg. //GF_ALFA*
	var $locators = NULL;	// miesto pre lokatory z Chien.alg. //GF_ALFA *
   //////////////////////////////////////////////////////////////////////

   // binary je pole bajtov
	function BinaryToPolynom(&$binary, &$polynom, $maxgrade) //bool
	{
      $bin_bit = 0;   // ktory bit v bajte
      $bin_index = 0; // index v poli
	   $destSLL = NULL;
	   $tmp = NULL;
	   for ($f = $maxgrade - 1; $f >= 0; $f--)
	   {
	      $alfka = $this->InputAlfa($binary, $bin_index, $bin_bit); 
	      if ($alfka != $this->genField->ALFA_INFINITY)
	      {
		      $tmp = & new generatorSLL($tmp);
		      if ($destSLL==NULL)
			      $destSLL = & $tmp;

            $tmp->exx = $f;
		      $tmp->alfa = $alfka; 
         }
		   if ($f==0) break;
	   }
	
      // 7. konverzia SLL - > string polynom
	   $this->genField->SLLToPolynom($destSLL, $polynom);

	   return true;	
	}
	
	function PolyToBinary(&$polynom, &$binary, $maxgrade) //bool
	{
	   $bin_bit = 0;
	   $bin_index = 0;

	   // 1. vytvor SLL strukturu z polynomu do buffra
	   $sourceSLL = NULL;
	   $tmp = NULL;
	   $sourceSLL = & $this->genField->PolynomToSLL($polynom);
      if ($sourceSLL == NULL)
         return false;

      // 3. ak je prva mocnina vacsia ako je mozne tak skonci
	   if ( $sourceSLL->exx >= $maxgrade)
		   return false;

      // 4. do buffra s udajmi	
	   for ($f = $maxgrade-1; $f>=0; $f--)
	   {
		   $tmp  = & $sourceSLL;
		   while ($tmp!=NULL)
		   {
			   if ( $tmp->exx==$f)
            {
				   $this->OutputAlfa( $tmp->alfa, $binary, $bin_index, $bin_bit);
				   break; // nasli sme 'f' tu mocninu v SLL
			   }
			   $tmp = & $tmp->next;
		   }
		   if ($tmp==NULL) // nenasli sme 'f'.tu mocninu, preto pridaj nulu
			$this->OutputAlfa($this->genField->ALFA_INFINITY, $binary, $bin_index, $bin_bit);
	   }
	   return true;	
	}

	/* nesystematicke a systematicke kodovanie a dekodovanie
	
		parametre:
				sourceBuf		zdrojovy buffer
				destBuf			cielovy buffer
				nSourceBit		kolky bit sa pouzije z 1.bajtu zdroj.buffra ako prvy (0..7)
				nDestBit		kolky bit sa pouzije z 1.bajtu zdroj.buffra ako prvy (0..7)	
	*/

   /* KODOVANIE A DEKODOVANIE BINARNE */	
	//bool EncodeNonSystematic ( char **sourceBuf, char **destBuf, unsigned short *nSourceBit, unsigned short *nDestBit );
	function EncodeSystematic ( &$sourceBuf, &$destBuf, &$nSourceIndex, &$nDestIndex, &$nSourceBit, &$nDestBit ) //bool
	{
		/*
		V Systematickom kode sa 'k * m' bitov cisto posle na vystup  a zaroven do delicky
		a poslednych '(n-k) * m ' bitov splachne z registra
	   */	

	   /* N A C I T A J   A L F A   P R V K Y */
	   $vstupne_alfy[] = 0; // sem sa nacita 'k' alfa prvkov o 'm' bitov (m je mocnina GF)
	   //vstupne_alfy = new GF_ALFA[k];

	   for ($f=0; $f < $this->k; $f++) // cez pocet Alfa prvkov			
		   // nacitaj alfa mocninu
	      $vstupne_alfy[$f] = $this->InputAlfa($sourceBuf, $nSourceIndex, $nSourceBit );

	   /* PREPCHAJ TIETO ALFA PRVKY CEZ APARAT DELENIM - NEBINARNY SHIFT REGISTER */
	
	   $this->virtRegister->Clear(); // vycisti register

	   $vystup = $this->genField->ALFA_INFINITY; // vystupna alfa
	   $vystup_bin = 0; // binarna reprezentacia alfy

	   for ($f=0; $f< $this->k; $f++) // narvi tam 'k' alfa prvkov
	   {
		   $this->virtRegister->PushSystematic( $vstupne_alfy[$f] );

		   // pridaj do Dest binarnu reprez. vystupnej alfy
		   $this->OutputAlfa($vstupne_alfy[$f], $destBuf, $nDestIndex, $nDestBit);
	   } // for f

	   // a este vyprazdni registre ( to je rezia uz vlastne)
	   for ($f=0; $f< ($this->virtRegister->regSize); $f++)
	   {
		   $vystup = $this->virtRegister->FlushRegister();
		   $this->OutputAlfa ( $vystup , $destBuf, $nDestIndex, $nDestBit );
	   }
	   
	   return true;
	}
	
	//bool DecodeNonSystematic ( char **sourceBuf, char **destBuf, unsigned short *nSourceBit, unsigned short *nDestBit );
	// $return_whole=true znamena ze sa vratia aj opravene alfa prvky z overhead casti
	function DecodeSystematic ( &$sourceBuf, &$destBuf, &$nSourceIndex, &$nDestIndex, &$nSourceBit, &$nDestBit, &$errorsRepaired, $return_n = false ) //bool
	{
	   /*   N A C I T A J   q-1   A L F A   P R V K O V   */
	   $vstupne_alfy[] = 0; // sem sa nacita 'n' alfa prvkov o 'm' bitov (m je mocnina GF)
	   //vstupne_alfy = new GF_ALFA[n];
      $this->hadErrors = false;

    	for ($f=0; $f < $this->n; $f++) // cez pocet Alfa prvkov			
		   // nacitaj alfa mocninu
		   $vstupne_alfy[$f] = $this->InputAlfa($sourceBuf, $nSourceIndex, $nSourceBit );
	
	   /*   P O D E L   G E N E R U J U C I M   P O L Y N O M O M   */
	   $this->virtRegister->Clear();
			// potrebujeme 2*t nebinarnych registrov (tu nakoniec zostane zvysok)							
			// informacia je prvych 'k*m' bitov
			// pravdaze len pokial zvysok po deleni bude 0 ( minus nekonecno prvky )
	
	   // postupne natlac prvky - s delenim
	   // to co bude z registra postupne vzchadzat vytvori dekodovanu informaciu	
	   for ($f=0; $f < $this->n; $f++ )
		   $this->virtRegister->PushDivision( $vstupne_alfy[$f] );

      // zisti ci je register nulovy ( mysli sa INFINITE prvky)
	   // v tomto pripade by bol vysledok platny, inac je zvysok syndrom
	   // a treba pocitat lokatory, a ich hodnoty
	   /*   A K   J E   Z V Y S O K   -0-   T A K   M A M E   V Y S L E D O K   */
	   if ( ! ( $this->virtRegister->isClear() ))
	   {
		   // no nejaka chyba.. treba aplikovat lokatory, ..
         $this->hadErrors = true;
		   //-----------------------------------------------
		   /* 1... VYRIESENIE ROVNICE O 't' NEZNAMYCH */
		   //-----------------------------------------------
		   $vstupPoly = NULL;
		   $tempPoly = NULL;
		   for ($f= $this->n -1; $f >= 0; $f--)
		   {
			   $tempPoly = & new generatorSLL($tempPoly);
			   if ($tempPoly==NULL) {echo 'out of memory!'; die;}
			   if ($vstupPoly==NULL)
				   $vstupPoly = & $tempPoly;

			   $tempPoly->exx= $f;
			   $tempPoly->alfa = $vstupne_alfy[($this->n-1)-$f];
		   }
	
		   for ($f=0; $f< 2 * $this->t; $f++)
			    //$this->syndroms[$f] = $this->genField->SolvePolynom($f,$vstupPoly);
			    $this->syndroms[$f+$this->j] = $this->genField->SolvePolynom( $this->genField->MultiplyByGrade($this->j,$f),$vstupPoly);  //asi bug bol

		   // napln maticu syndromamy
		   $this->matrix->InitFromRSSyndroms($this->syndroms, 2 * $this->t, $this->j);

		   // vyries maticu		
		   $this->matrix->Solve();

		   //-----------------------------------------------
		   /*	2... VYTVORENIE POLYNOMU LOKATOROV */		
		   //-----------------------------------------------

         $noo = NULL;
		   $lokatorPoly = & new generatorSLL($noo);
		   $this_lok = & $lokatorPoly;

		   $this_lok->exx = $this->t;
		   $this_lok->alfa= 0;

		   for ($f=0; $f< ($this->t); $f++)
		   {
		    if ($this->matrix->matrix[$f * $this->t + $f] == 0)
		    {
			   $this_lok = & new generatorSLL($this_lok);
			   if ($this_lok==NULL) {echo 'out of memory! (vytvorenie polynomu lok.)';die;}
			   if ($lokatorPoly==NULL)
				   $lokatorPoly = & $this_lok;
			
			   $this_lok->alfa = $this->matrix->rights[$f];
			   $this_lok->exx = $this->t - $f - 1;
			 }
         }

		   //-----------------------------------------------
		   /*	3... NAJDENIE KORENOV POLYNOMU LOKATOROV - polohy chyb
		   //		(korene najdeme dosadenim vsetkych Alfa prvkov) */
		   //-----------------------------------------------	
		   for ($f=0; $f < $this->t; $f++)
			   $this->locators[$f] = $this->genField->ALFA_INFINITY; // vynulovanie lokatorov
		   $f = 0;
		   for ($loca = 0 ; $loca< (($this->genField->q)-1); $loca++)
         {
			   $this->locators_cache[$loca] = $this->genField->SolvePolynom($loca, $lokatorPoly);
			   if ($this->locators_cache[$loca]==$this->genField->ALFA_INFINITY )
			   {
				   if ($f < $this->t)
               { // zapisovat mozno len 't' chyb..
					   // najdena pozicia chyby (lokator)
					   $this->locators[$f] = $loca;				
					   $f++;
				   }
			   }
		   }
		   // teraz 'f' obsahuje pocet najdenych chyb.. malo by to byt najviac 't'
		   $errorsRepaired = $f;
			
		   //-----------------------------------------------
		   /*  4... ZNOVA RIESENIE ROVNIC O 't' NEZNAMYCH
		   //		(najdenie hodnot jednotlivych chyb) */
		   //-----------------------------------------------
		   $this->matrix->InitForRSValues($this->syndroms, $this->locators, $this->j);	
		   //$this->matrix->DebugPrintState(); //DEBUG
		   $this->matrix->Solve();		

		   //-----------------------------------------------
		   /*  5... OPRAVA CHYB */
		   //-----------------------------------------------
		   for ($f=0; $f < $this->t; $f++)
         {
			   if ($this->locators[$f] != $this->genField->ALFA_INFINITY)
			   {
				   $vstupne_alfy[$this->n - $this->locators[$f] -1] =  $this->genField->AddByGrade( $this->matrix->rights[$f], $vstupne_alfy[$this->n - $this->locators[$f] -1] ) ;
				}
		   }		
	   }
	
	   /*   U L O Z   'k'   A L F A   P R V K O V   */
	   // zapis dekodovane alfy do vystupu
	   for ($f=0; $f< $this->k; $f++)
		   $this->OutputAlfa( $vstupne_alfy[$f], $destBuf, $nDestIndex, $nDestBit);
	
	   // a bude dobre ak sa niekde zapisu aj opravene znaky z nadbytocnosti - najlepsie 
	   // priamo do navratoveho pola, kedze aj tak funkcie pocet nacitanych Alfa prvkov
	   // si kontroluju samy z this->k 
	   if ($return_n) {
	   for ($f= $this->k ; $f< $this->n; $f++)
		$this->OutputAlfa( $vstupne_alfy[$f], $destBuf, $nDestIndex, $nDestBit);
	   }
		
	   // navrat
	   //delete vstupne_alfy;	
	   unset($vstupne_alfy);
	   return true;
	}

   /* KODOVANIE A DEKODOVANIE CEZ POLYNOMY */
   function EncodeSystematicPoly($sourcePoly, &$encodedPoly) //bool
   {
 	   // 1. vytvor buffer pre zdroj a ciel
	   $sourceBuf[] = 0;// = new char[ k * 7 + 1 ];
	   $destBuf[] = 0 ;// = new char[n*7+1];
	   $source = & $sourceBuf;
	   $dest = & $destBuf;
	   $source_bit = 0;
	   $dest_bit = 0;	
	   $source_index = 0;
	   $dest_index = 0;

	   // 2. sourcePoly -> binary
      if (!($this->PolyToBinary($sourcePoly, $sourceBuf, $this->k))) {
		   unset($sourceBuf); unset ($destBuf); return false; }

	   // 3. spusti encoding
	   $source = & $sourceBuf; $source_bit = 0; $source_index = 0;
	   if (!($this->EncodeSystematic($source,$dest,$source_index, $dest_index, $source_bit, $dest_bit))) {
		   unset ($sourceBuf); unset ($destBuf); return false; }

 	   // 4. vygeneruj SLL z prijatych udajov a konverzia na polynom
	   $this->BinaryToPolynom($destBuf, $encodedPoly, $this->n );

	   // 5. uspesny navrat
	   unset ($sourceBuf);
	   unset ($destBuf);
	   return true;
   }
   
   function DecodeSystematicPoly($receivedPoly, &$decodedPoly, &$errorsRepaired) //bool
   {
	   // 1. vytvor buffer pre zdroj a ciel
	   $sourceBuf[] = 0;// = new char[ k * 7 + 1 ];
	   $destBuf[] = 0;// = new char[n*7+1];
	   $source = & $sourceBuf;
	   $dest = & $destBuf;
	   $source_bit = 0;
	   $dest_bit = 0;	
	   $source_index = 0;
	   $dest_index = 0;

	   // 2. receivedPoly -> binary
	   if (!($this->PolyToBinary($receivedPoly, $sourceBuf, $this->n))) {
		   unset ($sourceBuf); unset ($destBuf); return false; }

	   // 3. spusti decoding
	   $errs = 0;
	   $source = & $sourceBuf; $source_bit = 0; $source_index = 0;
	   if (!($this->DecodeSystematic($source, $dest, $source_index, $dest_index, $source_bit, $dest_bit, $errorsRepaired))) {
		   unset ($sourceBuf); unset ($destBuf); return false; }

	   // 4. vygeneruj SLL z dekodovanych udajov a konverzia na polynom
	   $this->BinaryToPolynom($destBuf, $decodedPoly, $this->k );

	   // 5. uspesny navrat
	   unset ($sourceBuf);
	   unset ($destBuf);
	   return true;
   }

   /* VYTVOR + PREKONVERTUJ GENERUJUCI POLYNOM NA RETAZEC */
   function CreateGeneratorPolynomial($firstIndex, $errors) // funckia na vytvorenie generujuceho polynomu //bool
   {
	   $this->j = $firstIndex;
	   $this->t = $errors;

	   // 1. vytvor SLL prvkov ktore treba roznasobit - jedno SLL vlakno budu prvky na scitanie
	   //	  rozne vlakna su ako keby zatvorky	
	   $dvat = 2*$errors;
	   //typedef generatorSLL* pgenSLL;

	   //pgenSLL *zatvorky; // pocet zatvoriek je 2*t (t je opravujucich pocet chyb)
	   $zatvorky[$dvat] = NULL;// = new pgenSLL[dvat];

	   for ($i = 0; $i < ($dvat); $i++)
	   {
	      $nuu = NULL;
		   $zatvorky[$i] =  & new generatorSLL($nuu);	
		   if ($zatvorky[$i]==NULL) return false;
		   $novy = & $zatvorky[$i];		

		   // prva cast obsahu zatvorky je X
		   $novy->alfa = 0;
		   $novy->exx  = 1;

		   // druha cast zatvorky je Alfa ^ (i+firstIndex)
		   $novy = & new generatorSLL($novy);
		   if ($novy==NULL) return false;
		   $novy->exx=0;
		   $novy->alfa = $this->genField->MultiplyByGrade( $i, $firstIndex);
	   }

	   // 2. medzi sebou ich roznasob (vytvori sa mnozstvo prvkov)
	   $roznasobene = NULL;
	   $novy_element = NULL; // tu bude posledne pridany novy element
	   //unsigned int zatvoriek = 1;
	   //zatvoriek <<= errors; // 2 na pocet chyb teda zatvoriek je vysledny pocet elementov
	
      // postupne prinasob dalsie zatvorky, vzdy po prenasobeni zjednodus a preusporiadaj
      $roznasobene = & $zatvorky[0];
      for ( $nasob_zatvorka = 1; $nasob_zatvorka <  ($dvat); $nasob_zatvorka++)					
      {
        unset($nasob_element);
	     $nasob_element = & $roznasobene;		
	     unset($roznasobene);
	     $roznasobene = NULL; // a odznova nasobime	     	
	     unset($novy_element);
	     $novy_element = NULL;	   	
	   
	     while ($nasob_element != NULL)
	     {
			// mame jeden element z roznasobeneho polynomu (ci SLL vlastne)		
			$moznosti = 2;								
			while ($moznosti)
			{
            $novy_element =  & new generatorSLL($novy_element);
            if ($novy_element==NULL) return false;
				$novy_element->CopyFrom ($nasob_element);

				$moznosti--; //moznosti urcia (podla bitov) ktory element v ktorej zatvorke ma byt prinasobeny
				// aby sa vsetky moznosti vycerpali

				$nasob_element2 = & $zatvorky[$nasob_zatvorka];
				if ( $moznosti == 1 )
					$nasob_element2 = & $nasob_element2->next;
														
				// vynasob perimarny a sekundarny element do noveho elementu						
				$novy_element->Krat ($nasob_element2); 								
				$novy_element->alfa %= ( $this->genField->q - 1); // modulo operacia
	
				// pridaj
				if ($roznasobene == NULL)
					$roznasobene = & $novy_element;														
			} // while moznosti
			
			// dalsi prvok v prvej zatvorke
			$nasob_element = & $nasob_element->next;
	   } // while
	

	   // 3. najdi opakujuce sa , ak najdes dve rovnake, tak obidva znic (binarne minus ci plus)		
/*	
	   do
      {
      //$Kontrola1 = false;
                   //echo "pppp<BR>\n";
      
		   $looking = & $roznasobene;
		   $fClear = true;

         $pocitadlo1 = 0; // problem s nesting level musim nejak obist

     		while ($looking != NULL)
	    	{
		   	// skus najst pred tym takyto isty element ( cize exx a Alfa sa musia rovnat)
    			$look_prev = & $roznasobene;
			   $pocitadlo1++;
			
   			$fClear = true;

            $pocitadlo2 = 0; // namiestno $look_prev != $looking musim pocitat to inak...
            while ( ($look_prev != NULL) && ($pocitadlo1 != $pocitadlo2) )	   	
	   		//while ( ($look_prev != NULL) && ($look_prev != $looking) )	   	
		    	{
		    	   $pocitadlo2++;
				   if ( ($looking->exx == $look_prev->exx ) && ($looking->alfa == $look_prev->alfa) )
				   {				
					   // tieto prvky su rovnake.. obidva vymaz z radu
					   //if ($look_prev == $roznasobene )
					   if ($pocitadlo2==1 ) // ak je to prvy prvok
						   $roznasobene = & $look_prev->next;						
					   $looking->Delete();

					   //if ($looking==$roznasobene)
					   if ($pocitadlo1==1) // ak je ten prvy prvy
						   $roznasobene = & $looking->next;
					   $look_prev->Delete();				

                  //echo "kkkk $pocitadlo1 $pocitadlo2<BR>\n";

					   $fClear = false;					
					   break;
				   } // while									
				
				   $look_prev = & $look_prev->next;
			   }

            if ($fClear == false) break;   // skoc na do naspat

			   // na dalsi element
			   $looking = & $looking->next;
		   }

	   } while ($fClear==false); // pokym nieto co maza
*/
	   // 4. Preusporiadiaj od najvacsej po najmensiu mocninu X
	   //		zaroven rovnake scitaj a urob modulo pre Alfu
		
	   do
      {
		   $fVymena = false;
		   $looking = & $roznasobene;

		   while ($looking!=NULL)
		   {
			   // ak je X dalsieho prvku vacsie, tak prvky vymen
			   if ( ( $looking->next != NULL) && ($looking->exx < $looking->next->exx ))
			   {	
				   $looking->SwapNext();
				   $fVymena = true;
			   }

   			// ak sa X rovnaju... tak Alfy kombinuj do novej alfy (binarne podla GF)
			   if ( ($looking->next != NULL ) && ($looking->exx == $looking->next->exx))
			   {				
				   $looking->alfa = $this->genField->AddByGrade($looking->alfa, $looking->next->alfa );
				   // a vymaz toho suseda
				   $looking->next->Delete();
				   $fVymena = true;
			   }

			   // na dalsi element
			   $looking = & $looking->next;			
		   }
	   } while ($fVymena==true); // opakuj pokym to nie je zotriedene uplne		
	
	} // for zatvorka
	
	   // zlikviduj SLL struktury
	   for ($i=0;$i<(2*$errors);$i++) {
		   unset($zatvorky[$i]->next);
		   unset($zatvorky[$i]);
	   }	
	   unset ($zatvorky);
	   $this->genPoly = &$roznasobene;	
	   
	   return true;
   }
   
	function GeneratorPolynomToString (&$gener, &$buff) //bool
	{		
	   $buff = "";
	   while ($gener!=NULL)
	   {
		   if ($gener->alfa != $this->genField->ALFA_INFINITY)
		   {
			   // pridaj Alfa s cislom
			   if ($gener->alfa>0)
			      //sprintf($buff, "%sA%d", $buff, $gener->alfa);
			      $buff .= "A" . $gener->alfa;
            //buff+=strlen(buff);

			   // pridaj x s cislom
			   if ( ($gener->alfa==0) && ($gener->exx==0))
				   $buff .= "1"; // ak je oboje nula tak je to cele vlastne jednotka
			   else			
				   if ($gener->exx>0)
					   //sprintf($buff,"%sx%hd" , $buff, $gener->exx);
					   $buff .= "x" . $gener->exx;
			   //buff+=strlen(buff);

			   // pridaj plus ak je este dalsi prvok
			   if ($gener->next!=NULL)
				   $buff .= "+";
            //buff+=strlen(buff);

			   $gener = & $gener->next;
		   }
	   }
	   return true;	
	}

   /* KONSTRUKTOR a DESTRUKTOR */	
	function &ReedSolomon($fieldGenerator, $firstIndex, $errors) // polynom
	{
	   $this->genField = NULL;
	   $this->isValid = false;
	   $this->genPoly = NULL;
	   $this->n = 0;
	   $this->k = 0;
	   $this->genPolyString = "";
	   $this->matrix = NULL;
	   $this->syndroms = NULL;
	   $this->locators = NULL;
	   $this->locators_cache = NULL;

	   // vygeneruj GF(q) pole
	   $this->genField = & new GF($fieldGenerator);
	   if (($this->genField==NULL)||($this->genField->isValid==false)) return;


	   // kolko ma prvkov, aku mocninu a pod. zistime priamo z GF objektu
	   // vlastne RS kod je pomocou GF, firstIndex a errors definovany

	   // treba vytvorit generujuci polynom
	   if (!($this->CreateGeneratorPolynomial($firstIndex, $errors))) return;

	   // celkovy pocet bitov kodu je o jedna mensi ako je pocet prvkov GF pola
	   $this->n = $this->genField->q - 1;

	   // plati ze   n - k = 2.t , preto ...
	   $this->k = $this->n - 2 * $this->t;
	   if ($this->k <= 0) return; // noo.. nemame informacne bity

	   // a na koniec vytvor aj slovnu podobu generujuceho polynomu
	   if (!($this->GeneratorPolynomToString ($this->genPoly, $this->genPolyString))) return;

	   // vytvor virtualny HW register
	   $this->virtRegister = & new Register( 2 * $this->t, $this->genField, $this->genPoly);
	   if ($this->virtRegister==NULL) return;

	   // vytvor GF maticu
	   $this->matrix = & new GFMatrix($this->genField, $this->t, $this->t);
	   if ($this->matrix==NULL) return;

	   // alokuj miesto pre syndromy RS dekodovania
	   //syndroms = new GF_ALFA[2*t];

	   // alokuj miesto pre lokatory
	   //locators_cache = new GF_ALFA[ (genField->q) -1]; // tolko kolko je Alfa prvkov bez nekonecna
	   //locators = new GF_ALFA[t];

	   // uspech
	   $this->isValid = true;	
	}
	
	//virtual ~CReedSolomon();

	/* funkcie na nacitanie a ulozenie Alfa prvkov z GF ci vacsich blokov */
	/* sourceBuf a DestBuf su polia bajtov, indexy su indexy a bit je bit v bajte */
	function InputAlfa (&$sourceBuf, &$nSourceIndex, &$nSourceBit)				// nacita 'm' bitov a vrati rovno Alfa prvok //GF_ALFA
	{
	   $alfa_bin = 0;
	   $nAlfaBinBit = 0;
	   // nacitaj 'm' bitov do alfa_bin
	   for ($g=0; $g< $this->genField->m; $g++)
	   {	
		   if ( ($sourceBuf[$nSourceIndex]) & ( 1<<($nSourceBit) ) )
         {
			   $alfa_bin |= (1<<$nAlfaBinBit); // pridaj jeden bit			
		   }

		   $nAlfaBinBit++;

		   $nSourceBit++; // na dalsi bit sa posun
		   if (($nSourceBit)==8)
         {
			   $nSourceBit = 0;
			   $nSourceIndex++; // na dalsi byte
		   }
	   }

	   // najdi Alfa mocninu
	   $alfa_mocnina = $this->genField->ALFA_INFINITY; // je to alfa na minus nekonecno
	   if ($alfa_bin!=0)
	   {
		   for ($g=0; $g < $this->genField->q -1; $g++)
		   {
			   if ( $this->genField->array[$g] == $alfa_bin )
			   {
			      $alfa_mocnina = $g;
				   break;
            } // nasli sme mocninu
		   }
	   }

	   // navrat
	   return $alfa_mocnina;	
	}
	
	function OutputAlfa ($outAlfa, &$destBuf, &$nDestIndex, &$nDestBit)   // zapise do vystupu jeden Alfa prvok		 // bool
	{
	   $vystup_bin = 0;
		$nAlfaBinBit = 0;
		if ($outAlfa != $this->genField->ALFA_INFINITY)
		   $vystup_bin = $this->genField->array[$outAlfa];
		
		// pridaj do Dest binarnu reprez. ( teda 'm' bitov )
		for ($g=0;$g<( $this->genField->m ); $g++)
		{
			if ( ($nDestBit)==0)
				$destBuf[$nDestIndex] = 0;

			if ( (1<<$nAlfaBinBit) & $vystup_bin )
				$destBuf[$nDestIndex] |=  1<<($nDestBit);

			// na dalsi bit
			$nDestBit++;
			$nAlfaBinBit++;
			if (($nDestBit)==8) {
				$nDestBit=0;
				$nDestIndex++; // dalsi vystpny bajt
			}
		} // for

	   return true;
	}
};
	
}	
?>
