<div id="tests_obsah">
<?php
  include "GF.php";
  include "./funcs.php";
  
  /* ak nie su session premenne nastavene tak ich nastav */
  if (empty($_SESSION['s_errors'])) $_SESSION['s_errors'] = '2';
  if (empty($_SESSION['s_gfbits'])) $_SESSION['s_gfbits'] = '3';
  if (empty($_SESSION['s_fieldgenerator'])) $_SESSION['s_fieldgenerator'] = 'x3+x+1';
  if (empty($_SESSION['s_firstindex'])) $_SESSION['s_firstindex'] = '0';
  if (empty($_SESSION['s_sourcepoly'])) $_SESSION['s_sourcepoly'] = 'A3x2+A5x+A0';
  if (empty($_SESSION['s_encodedpoly'])) $_SESSION['s_encodedpoly'] = 'A3x6+A5x5+x4+A6x3+A4x2+A1x1+A2';
  if (empty($_SESSION['s_syndroms'])) $_SESSION['s_syndroms'] = '';
  
  /* precitat POST premenne ak su nastavit ich do session premennych  */
  if (isset($_POST['errors'])) $_SESSION['s_errors'] = $_POST['errors'];
  if (isset($_POST['gfbits'])) $_SESSION['s_gfbits'] = $_POST['gfbits'];
  if (isset($_POST['fieldgenerator'])) $_SESSION['s_fieldgenerator'] = $_POST['fieldgenerator'];
  if (isset($_POST['firstindex'])) $_SESSION['s_firstindex'] = $_POST['firstindex'];
  if (isset($_POST['sourcepoly'])) $_SESSION['s_sourcepoly'] = $_POST['sourcepoly'];
  if (isset($_POST['encodedpoly'])) $_SESSION['s_encodedpoly'] = $_POST['encodedpoly'];
  if (isset($_POST['syndroms'])) $_SESSION['s_syndroms'] = $_POST['syndroms'];
  
  
  echo "<h2>Test Reed&amp;Solomon kódera a jeho èastí</h2>\n";
  echo '<div id="vysledky">';
  
  /* testuj GF - vypis PrintDebug */
  if (isset($_POST['test_gf']))
	{
	   echo "<H3>Test GF(q) s primitívnym polynómom ".$_SESSION['fieldgenerator']."</H3>\n";
		$genField = & new GF($_SESSION['s_fieldgenerator']);
		if ($genField->isValid == true)
		  	$genField->PrintDebug();
      		else
        		echo "<p class=\"chyba\">Neúspech!</p>\n";
	}

   /* Riesenie samotnych syndromov */
   if (isset($_POST['ries_syndromy']))
   {
      echo "<H3>Test poèítania syndrómov: ".$_SESSION['s_syndroms']."</H3>\n";
		$genField = & new GF($_SESSION['s_fieldgenerator']);
      $gfmatrix = & new GFMatrix($genField, $_SESSION['s_errors'], $_SESSION['s_errors'] );

      $syndrom_field = explode(",",$_SESSION['s_syndroms']);

      $gfmatrix->InitFromRSSyndroms($syndrom_field, $_SESSION['s_errors'] * 2, $_SESSION['s_firstindex']);

      $gfmatrix->DebugPrintState();

      $gfmatrix->Solve();

      $gfmatrix->DebugPrintState();
   }

  /* napis generujuci polynom zvoleneho RS kodu */
  if (isset($_POST['show_generator']))
  {
     echo "<H3>Generujúci polynóm je:</h3>\n";
	  $ReedSol = & new ReedSolomon($_SESSION['s_fieldgenerator'], $_SESSION['s_firstindex'], $_SESSION['s_errors']);
	  if ($ReedSol->isValid == true)
	    echo FormatPolynom(chunk_split( $ReedSol->genPolyString, CUT_CHARS, "<BR>\n")) . "<BR>\n";
     else
       echo "<p class=\"chyba\">Neúspech</p>\n";
  }

  /* systematicke kodovanie */
  if (isset($_POST['rs_sys_encode']))
  {
  	echo '<h3>Systematické kódovanie</h3>';
  	echo '<table><colgroup class=""></colgroup>';
	  $ReedSol = & new ReedSolomon($_SESSION['s_fieldgenerator'], $_SESSION['s_firstindex'], $_SESSION['s_errors']);
	  if ($ReedSol->isValid == true)
	  {
	    //echo "<tr><td class=\"popis\">Generujúci polynóm: </td><td>".FormatPolynom(chunk_split( $ReedSol->genPolyString, CUT_CHARS, "<BR>\n" )) . "</td></tr>\n";
	    echo "<tr><td class=\"popis\">Informácia: </td><td>" . FormatPolynom(chunk_split($_SESSION['s_sourcepoly'], CUT_CHARS,"<BR>\n")) . "</td></tr>\n";
	    $dest_poly = "";
	    if ( $ReedSol->EncodeSystematicPoly($_SESSION['s_sourcepoly'], $dest_poly))
	    {
	       echo "<tr><td class=\"popis\">kódové slovo: </td><td>" . FormatPolynom(chunk_split($dest_poly, CUT_CHARS, "<BR>\n")) . "</td></tr>\n";
	       $_SESSION['s_encodedpoly'] = $dest_poly; // nastav rovno do formulara
	    }
       else
          echo "<p class=\"chyba\">Kódovanie bolo neúspešne</p>\n";
         echo '</table>'."\n";
     }
     else
       echo "<p class=\"chyba>Neúspech</p>\n";
  }

  if (isset($_POST['rs_sys_decode']))
  {
     echo '<h3>Systematické dekódovanie</h3>';
     echo '<table><colgroup class=""></colgroup>';     
	  $ReedSol = & new ReedSolomon($_SESSION['s_fieldgenerator'], $_SESSION['s_firstindex'], $_SESSION['s_errors']);
	  if ($ReedSol->isValid == true)
	  {
	    //echo "<tr><td class=\"popis\">Generujúci polynóm: </td><td>".FormatPolynom(chunk_split( $ReedSol->genPolyString, CUT_CHARS, "<BR>\n" )) . "</td></tr>\n";
	    echo "<tr><td class=\"popis\">Kódove slovo: </td><td>" . FormatPolynom(chunk_split( $_SESSION['s_encodedpoly'], CUT_CHARS, "<BR>\n" )) . "</td></tr>\n";
	
       $dest_poly = "";
       $errors_found = 0;
       if ($ReedSol->DecodeSystematicPoly($_SESSION['s_encodedpoly'], $dest_poly, $errors_found))
       {
       	   echo "<tr><td class=\"popis\">Dekódovaná informácia: </td><td>" . FormatPolynom(chunk_split( $dest_poly, CUT_CHARS, "<BR>\n" )) . "</td></tr>\n";
       	   //echo "<tr><td class=\"popis\">Poèet nájdenıch chıb:</td><td>".($ReedSol->hadErrors == false ? 'bez chıb' : $ReedSol->errorsDetected )."</td></tr>\n";
       	   echo "<tr><td class=\"popis\">Poèet opravenıch chıb:</td><td>".($ReedSol->hadErrors == false ? 'bez chıb' : $errors_found )."</td></tr>\n";
       }
       else
          echo "<p class=\"chyba\">Dekódovanie bolo neúspešné</p>\n";
	echo "</table>\n";          
     }
     else
       echo "<p class=\"chyba\">Neúspech</p>\n";
  }

  /* Hladanie primitivnych polynomov */
  if (isset($_POST['search_gf']))
  {
     if ($_SESSION['s_gfbits']<=0)
     {
       echo "<p class=\"chyba\">Poèet bitov (stupeò) musí by kladnı !</p>\n";
       
     }
     elseif ($_SESSION['s_gfbits']>15)
     {
       echo "<p class=\"chyba\">Stupeò je príliš ve¾kı!</p>\n";
       die;
     }
     else {
     	echo "<h3>Primitívne polynómy stupòa ".$_SESSION['s_gfbits']." :</h3>\n";

     	// minimalne a maximalne cislo gen.pol.
     	$min = (1 << $_SESSION['s_gfbits'] ) + 1;
     	$max = (1 << ($_SESSION['s_gfbits'] +1) ) - 1;

     	// cyklus
     	$najdenych = 0;
     	echo "<table>\n";
     	for ($cislo = $min; $cislo <= $max; $cislo+=2)
     	{
       // vytvor string gen.pol.
       $genstr = "";
       for ($st = $_SESSION['s_gfbits']; $st>=0; $st--)
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
       	//if (($gen->isValid==true)&&($gen->TestDistinct())) ... todruhe je nadbytocne
       	if (($gen->isValid==true))
       	{
         $najdenych++;
         echo "<tr><td>".($najdenych). ".</td><td class=\"click_polynom\" onclick=\"document.RS.fieldgenerator.value='".$genstr."';document.testuj_gf.fieldgenerator.value='".$genstr."';document.testuj_register.fieldgenerator.value='".$genstr."'\">" .FormatPolynom($genstr) . "</td></tr>\n";
       	}

       // znic ho
       unset ($genstr);
     	}
     	echo "</table>\n";
     
     	if ($najdenych==0)
       	echo "<p class=\"chyba\">Pre tento stupeò nebol nájdenı iaden primitívny polynóm!</p>\n";
       }
  }

echo "</div>\n";
?>

<?php    
    	/* Globalny test */      	     	     	
    	$rstemp = & new ReedSolomon($_SESSION['s_fieldgenerator'], $_SESSION['s_firstindex'], $_SESSION['s_errors']);
	
echo '<FIELDSET><LEGEND>Globálny test:</legend>'     	;    	
     	echo "<TABLE id=\"global_test_table\">";
        
     	echo "<FORM name=\"RS\" METHOD=\"POST\" action=\"?section=$section&amp;sub=$sub\">"     	
     	."<TR><TD class=\"popis\">Poèet bitov prvkov GF(q): </td><TD colspan=\"2\" class=\"input\"><INPUT TYPE=\"TEXT\" size=\"2\" maxlength=\"2\" name=\"gfbits\" id=\"gfbits\" VALUE=\"".$_SESSION['s_gfbits']."\"></TD><TD class=\"submit\"><INPUT TYPE=\"submit\" name=\"search_gf\" value=\"H¾adaj primitívne polynómy\"></TD></TR>"
	."<TR><TD class=\"popis\">Primitívny polynóm pre GF(q): </TD><TD colspan=\"2\" class=\"input\"><INPUT TYPE=\"text\" name=\"fieldgenerator\" onchange=\"testuj_gf.fieldgenerator.value=this.value;testuj_register.fieldgenerator.value=this.value\" id=\"fieldgenerator\" value=\"" .$_SESSION['s_fieldgenerator']. "\"></TD><TD class=\"submit\"><INPUT TYPE=\"submit\" name=\"test_gf\" value=\"Testuj GF(q)\" ></TD></TR>"
     	."<TR><TD class=\"popis\">Poèet monıch chıb:</TD><TD colspan=\"2\" class=\"input\"><INPUT TYPE=\"text\" name=\"errors\" id=\"errors\" onchange=\"testuj_register.regsize.value=2*this.value\" value=\"" .$_SESSION['s_errors']."\"></TD><TD class=\"submit\"><INPUT TYPE=\"submit\" name=\"show_generator\" value=\"Generuj generujúci polynóm\"></TD></TR>"
	."<TR><TD class=\"popis\">Poèiatoènı index:</TD><TD colspan=\"2\" class=\"input\"><INPUT TYPE=\"text\" name=\"firstindex\" id=\"firstindex\" value=\"".($_SESSION['s_firstindex'])."\"></TD><TD class=\"submit\"><INPUT TYPE=\"submit\" name=\"show_generator\" value=\"Generuj generujúci polynóm\"></TD></TR>"
	//."<INPUT TYPE=\"hidden\" name=\"firstindex\" value=\"".$_SESSION['s_firstindex']."\">"
     	."<TR><TD class=\"popis\">Syndrómy oddelené èiarkami:</TD><TD colspan=\"2\" class=\"input\"><INPUT TYPE=\"text\" name=\"syndroms\" id=\"syndroms\" value=\"".$_SESSION['s_syndroms']."\"></TD><TD class=\"submit\"><INPUT TYPE=\"submit\" name=\"ries_syndromy\" value=\"Rieš syndrómy\"></TD></TR>"
	."<TR><TD class=\"popis\">Informaènı polynóm:</TD><TD colspan=\"2\" class=\"input\"><INPUT size=\"40\" TYPE=\"text\" name=\"sourcepoly\" id=\"sourcepoly\" value=\"".$_SESSION['s_sourcepoly']."\"></TD></TD><TD class=\"submit\"><INPUT TYPE=\"submit\" name=\"rs_sys_encode\" value=\"Systematické kódovanie\"></TD></TR>"
	."<TR><TD class=\"popis\">Kódové slovo:</TD><TD colspan=\"2\" class=\"input\"><INPUT size=\"40\" TYPE=\"text\" name=\"encodedpoly\" id=\"encodedpoly\" value=\"".$_SESSION['s_encodedpoly']."\"></TD><TD class=\"submit\"><INPUT TYPE=\"submit\" name=\"rs_sys_decode\" value=\"Systematické dekódovanie\"></TD></TR></FORM>"
	.'<tr><td></td><td align="center"><form name="testuj_gf" action="index.php?section=rsparts&amp;sub=gf" method="POST"><input type="hidden" name="submitt" value="GF"><input type="hidden" name="fieldgenerator" value="'.$_SESSION['s_fieldgenerator'].'"><input type="submit" name="submit" value="GF kalkulaèka"></form></td>'
	.'<td align="center"><form name="testuj_register" action="index.php?section=rsparts&amp;sub=register&amp;genpoly=auto" method="POST"><input type="hidden" name="submitt" value="Register"><input type="hidden" name="fieldgenerator" value="'.$_SESSION['s_fieldgenerator'].'"><input type="hidden" name="regsize" value="'.(2*$_SESSION['s_errors']).'"><input type="submit" name="submit" value="HW register"></form></td></tr>'     	
    ."</TABLE></fieldset>"; 
?>
</div>