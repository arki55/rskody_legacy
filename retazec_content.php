<div id="retazec_obsah">
<?php
/**/
  require "GF.php";
  require "./funcs.php";

   

   if (empty($step)) $step=1;
   /*****************************/
   /* zakoduj a odkoduj retazec */
   /*****************************/
   
      /* zobrazenie bude prebiehat v krokoch */
      /* bude pouzita sada printable znakov base64 */


      /* 4. Dekodovanie, vypisanie dekodovaneho retazca,
       *    vypisanie aj pocet zistenych chyb */
      if($step=="4")
      {
         $rs = & new ReedSolomon( $gfpoly, 0, $errors);
         if ($rs->isValid == true)
         {
            /* skontroluj text */                                    
            if ( (strlen($encodedtext)<$encoded_must_length) || ( !CheckBase64String($encodedtext)))
            {
               if (strlen($encodedtext)<$encoded_must_length)
               	echo "<p class=\"chyba\">Text nemal potrebn� po�et znakov!</p>";
               else
               echo "<p class=\"chyba\">Text obsahuje nepovolen� znaky!</p>";
               $step = 3; // naspat na krok 3
            }
            else
            {
              /* vytvor polynom z potrebnych znakov */
              $nDestIndex = 0;
              $nDestBit = 0;
              $nSourceIndex = 0;
              $nSourceBit = 0;
              for ($idcko=0; $idcko < $rs->n; $idcko++)
                $rs->OutputAlfa( -1, $sourceBuf, $nSourceIndex, $nSourceBit);// vynuluj zdrojovy buffer
              for ($idcko=0; $idcko < $rs->n; $idcko++) // n kvoli tomu ze chceme precitat aj opravenu overhead cast
                $rs->OutputAlfa( -1, $destBuf, $nDestIndex, $nDestBit); // vynuluj cielovy buffer

              //$sourceBuf2 = explode ("<>", chunk_split($encodedtext,1,"<>"));
              // kazdy bajt v sourceBuf zmen na 6 bit only
              $nSourceIndex = 0;
              $nSourceBit = 0;
              //foreach ($sourceBuf2 as $idcko => $nieco)
              for ($hj=0; $hj<strlen($encodedtext); $hj++) {
                $nieco = $encodedtext{$hj};
                $sixx = ToSixBit($nieco) - 1;
                $rs->OutputAlfa( $sixx , $sourceBuf, $nSourceIndex, $nSourceBit);
              }

              /* odkoduj */
              $nDestIndex = 0;
              $nDestBit = 0;
              $nSourceIndex = 0;
              $nSourceBit = 0;
              $errors_found = 0;
              if ($rs->DecodeSystematic($sourceBuf, $destBuf, $nSourceIndex, $nDestIndex, $nSourceBit, $nDestBit , $errors_found, true )) // posledne true znamena ze citame aj overhead cast
              {
                  // a teraz tie binarne data naspat prehod do stringu
                 $nDestIndex = 0;
                 $nDestBit = 0;
                 $decodedtext ="";
                 for ($idcko=0; $idcko< ($rs->n); $idcko++) // v kvoli tomu ze citame okrem informacie aj opravenu overhead cast
                 {
                    $decodedtext .= FromSixBit (1+ $rs->InputAlfa($destBuf, $nDestIndex, $nDestBit) );
                 }
              }
              else
              {
                 echo "<p class=\"chyba\">Nepodarilo sa odk�dova� zdrojov� text!</p>\n";
                 
              }

              // a zobraz dekodovany retazec s poctom chyb
              echo '<h3>Krok 4: Vyhodnotenie testu</h3>';
              $dopln = "<td colspan=\"".($rs->k - 20)."\"></td><td class=\"bejska64_cols_overhead\" title=\"Tieto bity tvoria zabezpe�ovaciu �as� k�dov�ho slova\" colspan=\"".(2*$rs->t)."\">zabezpe�enie</td>";
              /* formular */
              echo "<table class=\"bejska64_tab\">\n";
              echo "<tr><td class=\"medziriadkami\" colspan=\"20\">P�vodn� text</td></tr>\n";
              StringToRow($sourcetext);
              ArrowsRow( $encodedtext_good, '', $sourcetext);
              echo "<tr><td class=\"medziriadkami\" colspan=\"20\">Zak�dovan� text</td>$dopln</tr>\n";                            
              StringToRow($encodedtext_good);
              ArrowsRow( $encodedtext, $encodedtext_good, $encodedtext_good );
              echo "<tr><td class=\"medziriadkami\" colspan=\"20\">Pokazen� text</td>$dopln</tr>\n";              
              StringToRow($encodedtext, $encodedtext_good);
                ArrowsRow( $decodedtext, $encodedtext, $encodedtext_good);              
              echo "<tr><td class=\"medziriadkami\" colspan=\"20\">Dek�dovan� - opraven� text</td>$dopln</tr>\n";              
              StringToRow($decodedtext, $encodedtext_good , $encodedtext);
              //echo "Povodny (vstupny)retazec   : '". $sourcetext)."'<BR><BR>\n";
              //echo "Zakodovany (prijaty)retazec: '".str_replace(" ", "&nbsp;",$encodedtext)."'<BR><BR>\n";
              //echo "Odkodovany retazec (opravenych $errors_found znakov)<BR><BR>'".str_replace(" ","&nbsp;",$decodedtext)."'<BR>\n";
              echo "</table>\n";
              switch ($errors_found) {
              	case 0: {echo "<p>Pri�om dek�der neopravil �iaden znak.</p>\n"; break; }
              	case 1: {echo "<p>Pri�om dek�der opravil 1 znak.</p>\n"; break; }
              	case 2:
              	case 3:
              	case 4: {echo "<p>Pri�om dek�der opravil $errors_found znaky.</p>\n"; break; }
              	default: 
              		{echo "<p>Pri�om dek�der opravil $errors_found znakov.</p>\n"; break; }
              } // switch
              if (strcmp($encodedtext_good, $decodedtext)==0)
              	echo '<p>Zdrojov� a dek�dovan� re�azec sa zhoduj�, zvolen� k�d bol posta�uj�ci.</p>'."\n";
              else
                echo '<p>Zdrojov� a dek�dovan� re�azec sa nezhoduj�, zvolen� k�d nebol posta�uj�ci. 
                Prijat� re�azec obsahoval viacej chybn�ch znakov ako bol k�d schopn� zvl�dnu� opravi�.</p>'."\n";
            }
         }
         else
         {
            echo "<p class=\"chyba\">Nepodarilo sa vytvori� platn� RS k�der!</p>\n";
            $step = 1; // radsej na zaciatok...
         }

    }   

      /* 3. Zakodujeme zadany text , zobrazit nech ho trosku pokazi */
      if($step=="3")
      {
         $rs = & new ReedSolomon( $gfpoly, 0, $errors);
         if ($rs->isValid == true)
         {
            /* skontroluj text */            
            
            if (!CheckBase64String($sourcetext))
            {
               echo "<p class=\"chyba\">Text obsahuje nepovolen� znaky!</p>";
               $step = 2; // naspat na krok 2
            }
            else
            {
              /* vytvor polynom z potrebnych znakov */
              $nDestIndex = 0;
              $nDestBit = 0;
              $nSourceIndex = 0;
              $nSourceBit = 0;
              for ($idcko=0; $idcko < $rs->k; $idcko++)
                $rs->OutputAlfa( -1, $sourceBuf, $nSourceIndex, $nSourceBit);// vynuluj zdrojovy buffer
              for ($idcko=0; $idcko < $rs->n; $idcko++)
                $rs->OutputAlfa( -1, $destBuf, $nDestIndex, $nDestBit); // vynuluj cielovy buffer
                
              $nSourceIndex = 0;
              $nSourceBit = 0;

              // dopln text na potrebny pocet znakov
              while ( strlen($sourcetext)< ($rs->k) )
                $sourcetext.= ' ';

              for ($hj=0; $hj<strlen($sourcetext); $hj++) {
                $nieco = $sourcetext{$hj};
				$sixx =  ToSixBit($nieco) - 1;
                $rs->OutputAlfa($sixx, $sourceBuf, $nSourceIndex, $nSourceBit);
              }

              /* zakoduj */
              $nDestIndex = 0;
              $nDestBit = 0;
              $nSourceIndex = 0;
              $nSourceBit = 0;
              if ($rs->EncodeSystematic($sourceBuf, $destBuf, $nSourceIndex, $nDestIndex, $nSourceBit, $nDestBit ))
              {
                  // a teraz tie binarne data naspat prehod do stringu
                 $nDestIndex = 0;
                 $nDestBit = 0;
                 $encodedtext ="";
                 for ($idcko=0; $idcko< $rs->n; $idcko++)
                 {
                    $encodedtext .= FromSixBit (1+ $rs->InputAlfa($destBuf, $nDestIndex, $nDestBit) );
                 }
              }
              else
              {
                 echo "<p class=\"chyba\">Nepodarilo sa zak�dova� zdrojov� text!</p>\n";
                 break;
              }

              // a zobraz zakodovany retazec s moznostou pokazenia
              /* formular */
              echo '<h3>Krok 3: Interakt�vne pokazenie textu</h3>';
              echo "<p class=\"odstavec\">V� zadan� re�azec bol zak�dovan� zvolen�m k�dom <strong>systematickou</strong> met�dou. Teraz ho m��ete 
              �ubovo�ne <strong>pokazi�</strong>, pri�om ale <strong>po�et znakov</strong> v textovom poli mus� by� zachovan�<strong>!</strong> Nesmie d�js� k posunu
              nejej �asti textu, in�� nebude re�azec mo�n� sp�tne dek�dova�. </p>
              Zak�dovan� re�azec (m��ete pokazi� a� <strong>$errors</strong> znakov):\n";
              echo '<FORM METHOD="POST" ACTION="?section='.$section.'&amp;sub='.$sub.'&amp;step=4">';
              echo '<INPUT TYPE="HIDDEN" NAME="gfpoly" VALUE="'.$gfpoly.'">'."\n";
              echo '<INPUT TYPE="HIDDEN" NAME="errors" VALUE="'.$errors.'">'."\n";
              echo '<INPUT TYPE="HIDDEN" NAME="sourcetext" VALUE="'.$sourcetext.'">'."\n";
              echo '<INPUT TYPE="HIDDEN" NAME="encoded_must_length" VALUE="'.$rs->n.'">'."\n";
              echo '<INPUT TYPE="HIDDEN" NAME="encodedtext_good" VALUE="'.$encodedtext.'">'."\n";              
              echo "<INPUT TYPE=TEXT SIZE=\"60\" NAME=\"encodedtext\" VALUE=\"$encodedtext\" MAXLENGTH=".$rs->n.">";
/*              echo "<div id=\"table_edit_text\">";
              echo "<ul>";
              for ($ff=0; $ff < strlen($encodedtext); $ff++)
                if ($ff==0)
                	echo '<li onclick="JavaScript: KlikniZnak('.$ff.')" id="znak_'.$ff.'" class="current" >'.(($encodedtext{$ff}==' ') ? "&nbsp;" : $encodedtext{$ff} ).'</li>';
                else
              		echo '<li onkeydown="alert(ff)" onclick="JavaScript: KlikniZnak('.$ff.')" id="znak_'.$ff.'">'.(($encodedtext{$ff}==' ') ? "&nbsp;" : $encodedtext{$ff} ).'</li>';
              echo "</ul>";
              echo "</div>\n"; */
              echo "<INPUT TYPE=SUBMIT VALUE=\"Krok 4\">\n";
              echo "</FORM>\n";
            }
         }
         else
         {
            echo "<p class=\"chyba\">Nepodarilo sa vytvori� platn� RS k�der!</p>\n";
            $step = 1;
         }
      }


      /* 2. Teraz nech user zada nejaky text */
      if($step=="2")
      {
         // vytvor RS kod, ak je dobry, tak zobraz jeho udaje a text box na text
         $rs = & new ReedSolomon( $gfpoly, 0, $errors);
         if ($rs->isValid == true)
         {
           echo '<h3>Krok 2: Info o zvolenom k�de + zadanie textu</h3>';
           echo "<TABLE>\n";
           echo "<TR><TD class=\"popis\">GF primit�vny polyn�m:</TD><TD>".FormatPolynom($gfpoly)."</TD></TR>\n";
           echo "<TR><TD class=\"popis\">Po�et bitov Alfa prvkov:</TD><TD>".$rs->genField->m."</TD></TR>\n";
           echo "<TR><TD class=\"popis\">Po�et Alfa prvkov:</TD><TD>".$rs->genField->q."</TD></TR>\n";           
           echo "<TR><TD class=\"popis\">Po�et symbolov k�dov�ho slova:</TD><TD>".$rs->n."</TD></TR>\n";
           echo "<TR><TD class=\"popis\">Po�et symbolov informa�n�ho slova:</TD><TD>".$rs->k."</TD></TR>\n";
           echo "<TR><TD class=\"popis\">RS generuj�ci polyn�m:</TD></tr>
           	<tr><TD colspan=\"2\">".nl2br(FormatPolynom(wordwrap(  $rs->genPolyString, 40, "\n", true)))."</TD></TR>\n";
           echo "</TABLE>\n";
           /* formular */
           echo "<p><strong>Akceptovan� znaky:</strong> 'a' a� 'z', 'A' a� 'Z', '0' a� '9', medzera a plus. V�etky znaky s� bez diakritiky.</p>\n";
           
           echo '<FORM METHOD="POST" ACTION="?section='.$section.'&amp;sub='.$sub.'&amp;step=3">';
           echo "<INPUT TYPE=HIDDEN NAME=\"gfpoly\" VALUE=\"$gfpoly\">\n";
           echo "<INPUT TYPE=HIDDEN NAME=\"errors\" VALUE=\"$errors\">\n";
           echo "<INPUT TYPE=TEXT SIZE=\"60\" NAME=\"sourcetext\" MAXLENGTH=".$rs->k."><INPUT TYPE=SUBMIT VALUE=\"Krok 3\">\n";
           echo "</FORM>\n";
         }
         else
         {
           echo "<p class=\"chyba\">Nepodarilo sa vytvori� platn� RS k�der!</p>\n";
           $step = 1; // naspat na krok 1 radsej
         }
      }
      
      
      
      /* 1. RS kod bude mat GF(2na6)=64 znakov
       *    takze vygeneruj combo box na volbu prim. polynomu stupna 6
       *    plus musi hned zadat pocet opravujucich chyb
       */
      
      
if ($step==1) { ?>
      	 <H2>"Ochr�� re�azec"</h2>
         <p class="odstavec">Test pozost�va zo zak�dovania re�azca (spomedzi 64 tla�ite�n�ch znakov)
         pomocou zvolen�ho RS k�du s po�adovan�m po�tom opravite�n�ch ch�b, 
         jeho n�sledn� pokazenie, a sp�tn� dek�dovanie.</p>
         <span>Pros�m, vyberte niektor� primit�vny polyn�m a maxim�lny po�et mo�n�ch ch�b:</span>
         <?php
         echo'<FORM name="string_test" METHOD="POST" ACTION="?section='.$section.'&amp;sub='.$sub.'&amp;step=2">'."\n";
         echo "<div class=\"form\"><SELECT NAME=\"gfpoly\">";
         
         $gfpolys = GetGFPolys(6);
         if ($gfpolys==NULL) die;
         foreach($gfpolys as $jeden)
         {
           echo "<OPTION VALUE=\"$jeden\">".($jeden)."</OPTION>\n";
         }
         echo "</SELECT>\n";
         echo "<SELECT NAME=\"errors\">\n";
         echo "<OPTION value=\"1\">1</OPTION>\n";
         echo "<OPTION value=\"2\">2</OPTION>\n";
         echo "<OPTION value=\"3\">3</OPTION>\n";
         echo "<OPTION value=\"4\">4</OPTION>\n";
         echo "<OPTION value=\"5\">5</OPTION>\n";
         echo "<OPTION value=\"6\">6</OPTION>\n";
         echo "<OPTION value=\"7\">7</OPTION>\n";
         echo "<OPTION value=\"8\">8</OPTION>\n";
         echo "</SELECT>\n";
         echo "<INPUT TYPE=SUBMIT VALUE=\"Krok 2\"></div>\n";
         echo"</FORM>\n";
      }
      



// FUNKCIE ////////////////

// zabalit jeden retazec do tabulky
function StringToRow($retazec, $ret_src='', $ret_enc='')
{
	$ln = strlen($retazec);
	echo '<tr class="bejska64">';
	for ($f=0; $f<$ln; $f++)
	{
		$pism = $retazec{$f};		
		$pism_u = $pism;
		if ($pism_u==' ') $pism_u = "&nbsp;";
		if (($ret_src!='')&&($ret_enc!='')&&($pism!=$ret_enc{$f})&&($pism==$ret_src{$f})) // dobre opravene..
		  echo '<td title="Spr�vny znak" class="bejska64_good">'.$pism_u.'</td>';
		elseif (($ret_src!='')&&($pism!=($ret_src{$f}))&&($ret_src{$f}!=''))
		  echo '<td  title="Nespr�vny znak" class="bejska64_bad">'.$pism_u.'</td>';
		else 
		  echo '<td>'.$pism_u.'</td>';
	}
	echo '</tr>'."\n";	
}
// riadok tabulky so sipkami
// string, string v predoslom kroku, spravny string
// string_previous moze ale nemusi byt..
function ArrowsRow( $string, $string_previous, $good_string)
{
	$sz_string = strlen($string);	
	$sz_prev = strlen($string_previous);
	$sz_good = strlen($good_string);
	echo '<tr class="arrows">';
	for ($f=0; $f < $sz_string; $f++	)
	{
		$znak = $string{$f};
		$znak_good = $good_string{$f};		
		if (($sz_prev>0) &&( $sz_good > $f ))
		{ // predosly je.. budu cervene aj dobre( zelene?) sipky - nejde o to ci string
		  // je spravny, ale ktory znak bol ako pozmeneny, ci klepsiemu alebo horsiemu
			$znak_prev = $string_previous{$f};
			if ( ($znak_prev!=$znak) && ($znak==$znak_good) && ($sz_prev>$f) && ($sz_good>$f))
				echo '<td title="Opravenie znaku" class="sipka_good">&darr;</td>';
			elseif ( ($znak_prev!=$znak) && ($znak!=$znak_good) && ($sz_prev>$f) && ($sz_good>$f))
				echo '<td title="Pokazenie znaku" class="sipka_bad">&darr;</td>';
			else
				echo '<td title="Pokazenie znaku" class="sipka_bad">&nbsp;</td>';
		}
		else
		{ // nie je predosly.. budu len cervene sipky
			if (($znak!=$znak_good)&&($sz_good>$f))
				echo '<td title="Pokazenie znaku" class="sipka_bad">&darr;</td>';
			else
				echo '<td title="Pokazenie znaku" class="sipka_bad">&nbsp;</td>';
		}
		
	}
	
	echo '</tr>'."\n";	
}


function CheckBase64String($str)
  {
	for ($ii=0; $ii<strlen($str); $ii++)
	{
		$jj = $str{$ii};
	     // vrat true ak pouzite znaky su zo sady base 64
		 if (!(  (($jj>='a')&&($jj<='z')) || (($jj>='A')&&($jj<='Z')) || (($jj>='0')&&($jj<='9')) ||
			($jj=='+') || ($jj==' ') )) return false;
	}
    return true;
  }
  
  function ToSixBit($asc)
  {
	settype($asc, 'string');
	$num = ord($asc);
	if ( ($num>=ord('a')) && ($num<=ord('z')))
		return ($num-ord('a')+26);
	else if ( ($num>=ord('A')) && ($num<=ord('Z')))
		return ($num-ord('A')+0);
	else if (($num>=ord('0'))&&($num<=ord('9')))
		return ($num-ord('0')+52);
	else if ($num==ord('+'))
		return 62;
	else if ($num==ord(' ')) // namiesto lomitka je medzera
		return 63;

	return 63; // no.. vlastne tu by mala byt chyba
  }

  function FromSixBit($sixb)
  {
	settype($sixb, 'integer');
    if ( ($sixb>=0) && ($sixb<=25))
		return chr($sixb-0+ord('A'));
	else if (($sixb>=26)&&($sixb<=51))
		return chr($sixb-26+ord('a'));
	else if (($sixb>=52)&&($sixb<=61))
		return chr($sixb-52+ord('0'));
	else if ($sixb==62)
		return '+';
	else if ($sixb==63)
		return ' ';

	return ' ';
  }


?>
</div>