<?php


function GetGFPolys($gf_bits) //vrati pole primitinych pol. daneho stupna
  {
     // minimalne a maximalne cislo gen.pol.
     $min = (1 << $gf_bits ) + 1;
     $max = (1 << ($gf_bits+1) ) - 1;

     // cyklus
     $najdenych = 0;
     for ($cislo = $min; $cislo <= $max; $cislo+=2)
     {
       // vytvor string gen.pol.
       $genstr = "";
       for ($st = $gf_bits; $st>=0; $st--)
       {
          if ( $cislo & ( 1<<$st))
          {
            if ($st==0)
              $genstr .= "1";
            else
              $genstr .= "x" . $st . "+";
          }
       }

       // vytvor konecne pole
       $gen = & new GF($genstr);

       // je v poriadku ?
       if ($gen->isValid==true)
       {
         $najdenych++;
         $gfpolys[] = $genstr; // pridaj do pola dalsi prvok
       }
       // znic ho
       unset ($genstr);
     }
     
     if ($najdenych==0)
       return NULL;
     else
       return $gfpolys;
  } // GetGFPolys

/* sformatovanie polynomov */
function FormatPolynom($poly)
{
	$navrat = "<span class=\"nice_polynom\">";
	$ln = strlen($poly);
	
	$jeindex = false;
	$jesub  = false;
	$wasx = false;
	$wasa = false;
	for ($j=0; $j<$ln; $j++)
	{
		$znak = $poly{$j};
		if ($znak=='+') {
			if ($jeindex) {
				$navrat .= '</sup>';
				$jeindex = false;
			}
			if ($jesub) {
			    $navrat .= '</sub>';
			    $jesub = false;
			}
			$wasx = false;
			$wasa = false;
		}
		elseif(($znak=='x')||($znak=='X')) {
		    if ($jesub) {
		        $navrat .= '</sub>';
		        $jesub = false;
		    }		    
		    $wasx = true;
		    $wasa = false;
		}				
		elseif(($znak>='0')&&($znak<='9')) {
			if ($wasx) {
				$navrat .= "<sup>";
				$jeindex = true;
			}	
			if ($wasa) {
			    $navrat .="<sub>";
			    $jesub = true;
			}					
			$wasx = false;
			$wasa = false;
		}
		elseif (($znak=='a')||($znak=='A')) {
			$znak = '&#945;';
			$wasx = false;
			$wasa = true;
		}
						
		// pridaj znak
		$navrat .= $znak;
	}		
	if ($jeindex) $navrat .= '</sup>';
	if ($jesub) $navrat .= '</sub>';
	return $navrat;
}

?>