<?php

if (!isset($GFMATRIX_PHP))
{
   $GFMATRIX_PHP = 1;

   require("GF.php");

class GFMatrix
{
	/* C H A R A K T E R I S T I K Y   M A T I C E */
	var $isValid = false;				// az po uspesnom vytvoreni RS triedy sa nastavi na 'true' //bool
	var $rows = 0;      //unsigned int
	var $cols = 0;      //unsigned int

	var $matrix; // len matica  /GF_ALFA *
	var $rights; // prave strany  //GF_ALFA *

	var $genField = NULL;				// objekt triedy GF ( finite field ) //CGF*
	
	/* K O N S T R U K T O R   A   D E S T R U K T O R */
	function &GFMatrix(&$field, $nrows, $ncols)
	{
		$this->isValid = false;
	   $this->matrix = NULL;

	   // zapamataj si smernik na konecne pole
	   $this->genField = &$field;

	   $this->rows = $nrows;
	   $this->cols = $ncols;

	   // vytvor pole o rows * cols GF_ALFA prvkoch
	   // ( indexovanie bude od 0, prave strany budu v rights)
	   //$this->matrix[$this->rows * $this->cols] = 0;  //matrix = new GF_ALFA[(rows)*(cols)];
	   //$this->rights[$this->rows] = 0;  //rights = new GF_ALFA[rows];

    	// vynuluj obsah
	   $this->Reset();

	   // az teraz mozno objekt oznacit za platny
	   $this->isValid = true;	   			
	}
/*	
	virtual ~CGFMatrix()
	{
   	// dealokuj maticu
    	if ($this->matrix!=NULL)
	   	unset($this->matrix);
      if ($this->rights!=NULL)
		   unset ($this->rights);	
	}*/


	/* F U N K C I E   M A T I C E */
	
	// vymen dva riadky matice
	function SwapRows($row1, $row2)
	{
	   // vymen stlpce
	   for ($col = 0; $col < $this->cols; $col++)
	   {
	      $tmp = $this->matrix[$row1 * $this->cols + $col];
	      $this->matrix[$row1 * $this->cols + $col] = $this->matrix[$row2 * $this->cols + $col];
	      $this->matrix[$row2 * $this->cols + $col] = $tmp;
	   }
	   
	   // vymen prave strany
	   $right_tmp = $this->rights[$row1];
	   $this->rights[$row1] = $this->rights[$row2];
	   $this->rights[$row2] = $right_tmp;
	}
	
	// vyries maticu
	function Solve()	 //bool
	{	
	   // algoritmus aplikuj na vsetky stlpce v matici
	   for ($col = 0; $col < $this->cols; $col++)
	   {		
	   	
	      for ($nenul_row = $col; $nenul_row < $this->rows; $nenul_row++)
	      {
	         if ($this->matrix[$nenul_row * $this->cols + $col] != $this->genField->ALFA_INFINITY)
	         {
	            // nasli sme nenulovy .. vymen riadky
	            $this->SwapRows( $col, $nenul_row );
	            break;
	         }
	      }
	
		   $selrow = $col;
		   $selmax = $this->matrix[$selrow * $this->cols + $col];

     		// ak bol najdeny nejaky nenulovy prvok tak pokracuj...
	    	if ($nenul_row != $this->rows)
		   {	
			   //  normuj riadok podla daneho stlpca
			   for ($normcol = $col; $normcol < $this->cols; $normcol++)
				   $this->matrix[$selrow * $this->cols + $normcol] = $this->genField->DivideByGrade( $this->matrix[$selrow * $this->cols + $normcol], $selmax);
            $this->rights[$selrow] = $this->genField->DivideByGrade( $this->rights[$selrow], $selmax);

            // od ostatnych riadkov odcitaj vybraty riadok vynasobeny prvkom aktualneho stlpca
			   for ($row=0; $row < $this->rows; $row++)
			   {
				   if ($row!=$selrow)
				   {
					   $selmax = $this->matrix[$row * $this->cols + $col];
					   for ($selcol = $col; $selcol < $this->cols; $selcol++)
						   $this->matrix[$row * $this->cols + $selcol] = $this->genField->AddByGrade( $this->matrix[$row * $this->cols + $selcol] , $this->genField->MultiplyByGrade( $this->matrix[$selrow * $this->cols + $selcol], $selmax ) );
				      $this->rights[$row] = $this->genField->AddByGrade( $this->rights[$row] , $this->genField->MultiplyByGrade( $this->rights[$selrow], $selmax ) );
		         }
			   } // pre ostatne riadky
		   } // alg. pre nenulovy stlpec
		   
	//   echo "col:$col: <BR>\n";
     // $this->DebugPrintState(); //DEBUG
		
	   }// cez vsetky stlpce aplikuj alg.
	   return true;	
	}
	
	// nacitaj do matice syndromy z RS dekodovania
	function InitFromRSSyndroms(&$syndroms, $count, $j)	//bool
	{
      if ($this->cols!=$this->rows) return false; // musi to byt stvorcova matica
	   if ( ($this->cols * 2)!= $count ) return false;

	   // najprv vycisti maticu
	   $this->Reset();

	   for ($row=0; $row < $this->rows; $row++)
	   {
		   // uloz hodnotu riadku
		   $this->rights[$row] = $syndroms[$this->rows + $row+ $j];

		   // a teraz vsetky stlpce
		   for ($col=0; $col < $this->cols; $col++)
		   {
			   $this->matrix[$row * $this->cols + $this->cols -1 - $col] = $syndroms[$row + $col+$j];
         }// cez vsetky stlpce
	   } // cez vsetky riadky

	   return true;	
	}
	
	// nacitanie dat matice pre vypocet hodnot na poziciach lokatorov , $j je pociatocny index
	function InitForRSValues(&$syndroms, &$locators, $j) //bool
	{				
	   if ($this->cols != $this->rows) return false; // musi to byt stvorcova matica
	   //if ( (cols)!=count ) return false;

	   // najprv vycisti maticu
	   $this->Reset();

	   for ($row=0; $row < $this->rows; $row++)
	   {
		   $this->rights[$row] = $syndroms[$row+$j];

		   for ($col=0; $col < $this->cols; $col++)
		   {
		      if ($locators[$col] != $this->genField->ALFA_INFINITY)
			     $this->matrix[$row * $this->cols + $col] = $this->genField->MultiplyByGrade($locators[$col] * ($row + $j), 0);  // ofajc, aby to orezalo cez %
	      }// cez vsetky stlpce
      } // cez vsetky riadky
	   return true;	
	}
	
	// vynulovanie celej matice.
	function Reset()
	{
      // vymaz vsetky prvky matice	
	   for ($row=0;$row < $this->rows; $row++)
	   {
		   for ($col=0; $col < $this->cols; $col++)
         {
			    $this->matrix[$row * $this->cols + $col] = $this->genField->ALFA_INFINITY;
         }
		   $this->rights[$row] = $this->genField->ALFA_INFINITY;
	   }	
	}

	// vypise maticu do stdout
	function DebugPrintState()
	{	
	   echo("M A T R I X   S T A T E<BR>\n");
	   for ($row=0; $row < $this->rows; $row++)
	   {
		   for ($col=0; $col < $this->cols; $col++)
			    echo( $this->matrix[$row * $this->cols + $col] . "\t\t" );

         printf(" |  " . $this->rights[$row] . "<BR>\n");
      }
	   printf("<BR>\n");	
	}
}; // koniec triedy GFMatrix

}
?>
