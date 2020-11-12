<?php
if (!isset($REGISTER_PHP))
{
   $REGISTER_PHP = 1;
   
   require("GF.php");
     
class Register
{
	var $regSize = 0; // rozmer pola registra

	var $genField = NULL;			// CGF* finite field ( potrebny na operacie s Alfa prvkami )

	var $genPoly = NULL;	// generatorSLL* generujuci polynom kodu vyuzivajuceho tento register
	var $registre;		// GF_ALFA * tu bude struktura vytvorenych registrov

	/*   V Y T V O R   R E G I S T E R   O   'size'   P O L O Z K A C H   */
	function &Register($size, & $field, & $codeGenerator)
   {
    	/* vytvor registre */    	
     	//$this->registre = new GF_ALFA[size];
	   $this->regSize = $size;

	   // zapamataj si GF pole
	   $this->genField = & $field;

	   // zapamataj si code generator
	   $this->genPoly = & $codeGenerator;

	   // vyscisti register
	   $this->Clear();
   }		

	/*   V Y C I S T E N I E   R E G I S T R A   */
	function Clear()
	{
   	for ($f=0;$f< $this->regSize ;$f++)
		    $this->registre[$f] = $this->genField->ALFA_INFINITY; // vynulovat registre		
	}

	/*   Z I S T I   C I   J E   R E G I S T E R   C I S T Y   */ //(vhodne na zistenie nulovosti zvysku pri dekodovani)
	function isClear() // bool
	{
	   for ($f=0; $f< $this->regSize; $f++)
		    if ( $this->registre[$f] != $this->genField->ALFA_INFINITY )
			    return false; // nasli sme nieco ine ako je infinity prvok

      return true; // vsetko je ciste	
	}

	/*   V T L A C E N I E   A L F A   P R V K U   V   S T Y L E : (delenie)
		    ---<----<----<---
		    |          |    |
		->- + r0 -> r1 + r2 + ---> 	 */
	function PushDivision($alfa) //GF_ALFA
	{	
     	// to co je v registre[ regSize - 1] daj na vystup
	   $vystup = $this->registre[ $this->regSize -1];

	   // posun medzi registrami od najnizsej mocniny po najvyssiu Alfa prvky		
	   for ($g=($this->regSize - 1); $g>0; $g--) // od 0 po 2*t = regSize
		    $this->registre[$g] = $this->registre[$g - 1];

	   // do nulteho daj vstup
	   $this->registre[0]=$alfa;

	   // to co vyslo z registra von ako vystup aplikuj podla mocnin gener.polyn. tam kde treba (urobi delenie)	
	   $gen_poly = & $this->genPoly;
	   while ($gen_poly!=NULL)
	   {
		  if ($gen_poly->exx < $this->regSize)
    			$this->registre[ $gen_poly->exx ] = $this->genField->AddByGrade ( $this->registre[ $gen_poly->exx ], $this->genField->MultiplyByGrade( $vystup , $gen_poly->alfa ) );

	    	// na dalsiu mocninu X
		  $gen_poly = & $gen_poly->next;
	   }

 	   return $vystup;	
	}

	/*   V T L A C E N I E   A L F A   P R V K U   V   S T Y L E : (pri nesyst.kodovani)
		-->------->-------->--
			|           |    |         // toto je ale zle... ma to byt opacne... ale nejdem to uz prerabat, tak ako je v dokumentacii to ma byt
			-> r0 -> r1 + r2 + -->   */
	function PushNormal($alfa)	//GF_ALFA
	{
      // to co je v registre[ regSize - 1] daj na vystup + ( to co je na vstupe * Alfa z najvacsieho prvky z gen.poly )
	   $vystup = $this->genField->AddByGrade( $this->registre[ $this->regSize - 1],  $this->genField->MultiplyByGrade( $alfa , $this->genPoly->alfa  )) ;

	   // posun medzi registrami od najnizsej mocniny po najvyssiu Alfa prvky		
	   for ($g=($this->regSize - 1); $g>0; $g--) // od 0 po 2*t = regSize
		    $this->registre[$g] = $this->registre[$g - 1];
      $this->registre[$g]=$this->genField->ALFA_INFINITY;

      // pridaj vstupnu alfu k pozadovanym registrom ( podla generujuceho polynomu)
	   $gen_poly = & $this->genPoly;
	   while ($gen_poly!=NULL)
	   {
		   if ($gen_poly->exx < $this->regSize)
			   $this->registre[ $gen_poly->exx ] = $this->genField->AddByGrade ( $this->registre[ $gen_poly->exx ], $this->genField->MultiplyByGrade( $alfa , $gen_poly->alfa ) );

		   // na dalsiu mocninu X
		   $gen_poly = & $gen_poly->next;
	   }

	   return $vystup;	
	}

	/*   V T L A C E N I E   A L F A   P R V K U   V   S T Y L E : (pri syst.kodovani)
		    ---<----<----<---         je to ako pri nesystematickom lenze vstup
		    |          |    |         kombinovany s vystupom nejde len von ale aj dnu
		    - r0 -> r1 + r2 + ---> 	
			                |
	                     ->--     */
	function PushSystematic($alfa)	 //GF_ALFA
	{	     	
      // to co je v registre[ regSize - 1] daj na vystup + ( to co je na vstupe * Alfa z najvacsieho prvky z gen.poly )
	   $vystup = $this->genField->AddByGrade( $this->registre[ $this->regSize -1],  $this->genField->MultiplyByGrade( $alfa , $this->genPoly->alfa  )) ;

	   // posun medzi registrami od najnizsej mocniny po najvyssiu Alfa prvky		
	   for ($g=($this->regSize-1); $g>0; $g--) // od 0 po 2*t = regSize
		    $this->registre[$g] = $this->registre[$g-1];
      $this->registre[$g]=$this->genField->ALFA_INFINITY;

	   // pridaj vystup k pozadovanym registrom ( podla generujuceho polynomu)
	   $gen_poly = & $this->genPoly;
	   while ($gen_poly!=NULL)
	   {
		   if ($gen_poly->exx < $this->regSize)
			   $this->registre[ $gen_poly->exx ] = $this->genField->AddByGrade ( $this->registre[ $gen_poly->exx ], $this->genField->MultiplyByGrade( $vystup , $gen_poly->alfa ) );

         // na dalsiu mocninu X
		   $gen_poly = & $gen_poly->next;
	   }

	   return $vystup;
	}

	/*   V Y P L A C H N U T I E   J E D N E H O   P R V K U   Z   R E G I S T R A
		   Alfa(-nek.) -> r0 -> r1 -> r2 -> --> vystup  */
	function FlushRegister() //GF_ALFA
	{
	  $rett = $this->registre[$this->regSize-1]; // co vrati... posledny prvok
	   // posun medzi registrami od najnizsej mocniny po najvyssiu Alfa prvky		
	   for ($g=($this->regSize-1); $g>0; $g--) // od 0 po 2*t = regSize
		    $this->registre[$g] = $this->registre[$g-1];
	   $this->registre[0] = $this->genField->ALFA_INFINITY; // do prveho sme vlozili bin.nulu
	  
      return $rett;
	}
     /*
	virtual ~CRegister()
	{
	   // znic registre
	   if ($this->registre!=NULL)
		   unset($this->registre);	
	} */
	
};

}
?>
