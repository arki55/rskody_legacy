<?php

/**
 * 
 * @version $Id$
 * @copyright 2003
 */

include "./GF.php";
include "./funcs.php";

session_start();

if(!isset($_SESSION['s_gfbits']))
	$_SESSION['s_gfbits'] = 4; 
if(!isset($_SESSION['s_full']))
	$_SESSION['s_full'] = false;
if(!isset($_SESSION['s_komb_l']))
	$_SESSION['s_komb_l'] = false;
if (!isset($_SESSION['s_komb_r']))
	$_SESSION['s_komb_r']  = false;
if (!isset($_SESSION['s_first_izo']))
	$_SESSION['s_first_izo']  = false;


if(!empty($_POST['gfbits']))
	$_SESSION['s_gfbits'] = $_POST['gfbits'];
if(!empty($_POST['full']))
	$_SESSION['s_full'] = true;
else
	$_SESSION['s_full']=false;
	
if(!empty($_POST['komb_l']))
	$_SESSION['s_komb_l'] = true;
else	
	$_SESSION['s_komb_l']=false;

if(!empty($_POST['komb_r']))
	$_SESSION['s_komb_r'] = true;
else
	$_SESSION['s_komb_r'] = false;

if(!empty($_POST['first_izo']))
	$_SESSION['s_first_izo'] = true;
else
	$_SESSION['s_first_izo'] = false;


if(!empty($_POST['action'])) {
	$_SESSION['s_action'] = $_POST['action'];
	$action = $_POST['action'];
}

?>

<html>
	<head>
		<title>Izomorfizmus konecnych poli</title>
 <meta http-equiv="Cache-Control" content="no-cache">
  <meta http-equiv="Pragma" content="no_cache">
  <meta http-equiv="content-type" content="text/html; charset=windows-1250">
  <meta name="author" content="Miroslav Ïurèík (študent @ktl.elf.stuba.sk)">
 
	</head>
	<body>
	
		<form method="post">
			<input type="text" name="gfbits" value="<?php echo $_SESSION['s_gfbits']; ?>">
			<BR><input type="checkbox" name="komb_l" value="1" <?php if ($_SESSION['s_komb_l']) echo "checked"; ?>> Vsetky lave primitivne polynomy<BR>
			<input type="checkbox" name="komb_r" value="1" <?php if ($_SESSION['s_komb_r']) echo "checked"; ?>> Vsetky prave primitivne polynomy<BR>
			<input type="checkbox" name="full" value="1" <?php if($_SESSION['s_full']) echo "checked"; ?>> Dokazy/vypisy zapnute<BR>
			<input type="checkbox" name="first_izo" value="1" <?php if($_SESSION['s_first_izo']) echo "checked"; ?>> Najdi len prvy izomorfizmus (pre urychlenie vyssich radov)<BR>
			<input type="submit" name="action" value="plus_grupy">
			<input type="submit" name="action" value="porovnanie1">
			<input type="submit" name="action" value="ries_x">					
		</form>
		
	</body>
</html>

<?php 


// minimalne a maximalne cislo gen.pol.
$min = (1 << $_SESSION['s_gfbits']) + 1;
$max = (1 << ($_SESSION['s_gfbits'] + 1)) - 1; 
// cyklus
$najdenych = 0;
$prim = array(); // sem sa ulozia vsetky primitivne polynomy daneho stupna
echo "Primitivne polynomy pre generovanie GF(2^".$_SESSION['s_gfbits'].") :<BR>\n<table>\n";
for ($cislo = $min; $cislo <= $max; $cislo += 2) {
    // vytvor string gen.pol.
    $genstr = "";
    for ($st = $_SESSION['s_gfbits']; $st >= 0; $st--) {
        if ($cislo &(1 << $st)) {
            if ($st == 0)
                $genstr .= "1";
            else
                $genstr .= "x" . $st . "+";
        } 
    } 
    // vytvor konecne pole
    $gen = &new GF($genstr); 
    // je v poriadku ?
    // if (($gen->isValid==true)&&($gen->TestDistinct())) ... todruhe je nadbytocne
    if (($gen->isValid == true)) {
        $najdenych++;
		$primit[] = $genstr;
        echo "<tr><td>" . ($najdenych) . ".</td><td class=\"click_polynom\" onclick=\"document.RS.fieldgenerator.value='" . $genstr . "';document.testuj_gf.fieldgenerator.value='" . $genstr . "';document.testuj_register.fieldgenerator.value='" . $genstr . "'\">" . FormatPolynom($genstr) . "</td></tr>\n";
    } 
    // znic ho
    unset ($genstr);
} 
echo "</table>\n";

if ($najdenych == 0) {
    echo "<p class=\"chyba\">Pre tento stupeò nebol nájdený žiaden primitívny polynóm!</p>\n";
	die;
}

//  vygeneruje sa pole GF(q) z kazdeho primitivneho polynomu
$gfka = array();
foreach ($primit as $primitiv) {
	
	// vygeneruj gf(q)
    $gen = & new GF($primitiv); 
    // je v poriadku ?
    if (($gen->isValid == false)) die('jaaj.. zle GF(q)');
	
	// pridaj do pola
	$gfka[$primitiv] = & $gen;	
}
                                                

switch($action) {

	case "print_debug":
	{	
		// vypis vsetky
		foreach ($gfka as $primitiv=>$gfq) {
		echo "<HR>";
			echo "$primitiv:<BR>\n";
			$gfq->PrintDebug();
			echo "<HR>";
		}
		
		break;
	}


	case "plus_grupy": {
	
		// tabulky suctov
		foreach ($gfka as $primitiv=>$gff) {	
			echo "<HR>";
			echo "Tabulka suctov pre gf(q) z $primitiv:<BR>\n";
			TabulkaSuctov($gff);
			echo "<HR>";
		}
		
		break;
	}


	case "porovnanie1": {
	
		//-------------------------------------------
		// zobrazt prve GF(q) a porovnat s ostatnymi
		
		$kluc1 = array_keys($gfka);
		$kluc1 = $kluc1[0];
		$gf1 = & $gfka[$kluc1];
		foreach($gfka as $primitiv=>$gfko) {
			if($primitiv==$kluc1) continue;
			
			// zoberiem vsetky prvky z gf1 a skusim ich binarne vyjadrenie najst v druhom
			// pripady ked sa jedna o binarne 0 a 1 preskocime..
			// pretoze 0 je v kazdom GF(q) rovnaka, ako aj 1 je rovnaka v kazdom gf(q)
			echo "Binarne zhodnosti pre gf(q) gen. ".$kluc1." -> ".$primitiv."<BR>\n";
			for($beta=1; $beta < $gf1->q-1; $beta++ ) {
				$bin = $gf1->array[$beta];
				$beta_vpravo = $gfko->array_inverse[$bin];
				$rozdiel = $beta_vpravo - $beta;
				while( $rozdiel> ($gf1->q-2))
					$rozdiel-=$gf1->q-1;
				while( $rozdiel<0)
					$rozdiel+=$gf1->q-1;
					
				echo "&beta;<sup>".$beta."</sup> -> &beta;<sup>".($beta_vpravo)."</sup>; ".$rozdiel." <BR>\n";		
			}
		
		}

		break;
	}

	
	case "ries_x": {

		// teraz skusime overit ci plati f(alfa+beta) = f(alfa) + f(beta)
		//  kde funkcia f(x) bude vynasobenie .. zvysenie mozniny bety/alfy
		echo "<HR>Overenie f(alfa+beta) = f(alfa) + f(beta) <BR> pri zhodnosti alfa a beta je to dokaz ze f(0)=0, musi byt x=1<BR> ";
		echo "<table border=1><TR><th>A=>B</th><th></th><th>Prienik hodnot x pri nerovnakych alfa a beta</th><th>Pri rovnakych alfa a beta je vsade len jednotka</th></tr>\n";
		foreach($gfka as $kluc1=>$gf1) {
			foreach($gfka as $primitiv=>$gfko) {
				if($primitiv==$kluc1) continue;
				echo "<TR><TD>";
				echo "$kluc1 => $primitiv<BR>";
				echo "</td><td>";	
				// zoberiem vsetky prvky z gf1 a skusim ich binarne vyjadrenie najst v druhom
				// pripady ked sa jedna o binarne 0 a 1 preskocime..
				// pretoze 0 je v kazdom GF(q) rovnaka, ako aj 1 je rovnaka v kazdom gf(q)
			
					// teraz cez vsetky prvky
					$polia = array();
					$rovnake_1 = true;
					for ($alfa=0; $alfa < ($gf1->q-1); $alfa++ ) {
						for($beta = 0; $beta < ($gf1->q-1); $beta++) {			
							// vypocitaj co je nalavo teda  f( alfa+beta)
							$vlavo = $gf1->AddByGrade( $alfa, $beta );
							
							// vypocitaj napravo teda   f(alfa) + f(beta)			
							//$vpravo =  $gfko->AddByGrade( $jedna, $dva );
							
							// zisti pre ake deltak ako x plati ze x^vlavo = x^alfa + x^beta
							$nasiel = array();
							for($x=-1; $x< ($gf1->q-1); $x++) {
								
								$vlavo2 = $gfko->MultiplyByGrade( $x * $vlavo , 0 );
								$vpravo2 = $gfko->MultiplyByGrade(  $gfko->AddByGrade( $gfko->MultiplyByGrade($x*$alfa, 0) , $gfko->MultiplyByGrade($x*$beta,0) )  , 0 );
								
								if($vlavo2==$vpravo2) {
									$nasiel[] = $x; // nasiel sa izomorfizmus, teda delta k 
									if ($_SESSION['s_first_izo'])
										break; // skonci ak sme chceli len jeden izomorfizmus
								}
							}
			
							if($_SESSION['s_full']) {
								echo "alfa=&beta;<sup>$alfa</sup>, beta=&beta;<sup>$beta</sup>: x=&delta;<sup>".join('</sup>, &delta;<sup>',$nasiel)."</sup><BR>\n";
							}
								
							if($alfa!=$beta)
								$polia[] = $nasiel; // prienik potom spravim len vysledkov z nerovnakych prvkov
							else {
								// tu skontrolujem ci je tam len jednotka
								if(count($nasiel)!=1 || $nasiel[0]!=1)
									$rovnake_1 = false;
							}
							
							
							flush();
						} // beta
					} // alfa
			
				// a teraz prienik
				$prve= true;
				$pp=array();
				foreach($polia as $pole) {
					if($prve)
						$pp = $pole;
					else
						$pp = array_intersect( $pp , $pole );
				}
				echo "</td><td>&delta;<sup>".join('</sup>, &delta;<sup>',$pp)."</sup></td>\n";
				echo "<td>".($rovnake_1 ? "Ano" : "Nie")."";	
				echo "</td>";
				
				// dokaz ze to plati
				echo "<td>";
			
			if($_SESSION['s_full']) {

				for($delta=0; $delta< ($gf1->q-1); $delta++ ) {
					$zhodne = true;
					for ($alfa=0; $alfa < ($gf1->q-1); $alfa++ ) {
						for($beta = 0; $beta < ($gf1->q-1); $beta++) {							
							if($alfa==$beta) continue;
						
							// vypocitaj co je nalavo teda  f( alfa+beta)
							$vlavo = $gf1->AddByGrade( $alfa, $beta );
							$vlavo = $gfko->MultiplyByGrade( $delta * $vlavo, 0);
							
							$vpravo = $gfko->AddByGrade( $gfko->MultiplyByGrade($delta* $alfa, 0), $gfko->MultiplyByGrade($delta*$beta,0) );
							if($vlavo!=$vpravo)
								$zhodne = false;
						}
					}
					echo "fii=&delta;<sup>$delta</sup>:".($zhodne? "ano": "nie")."; ";
						
				}	
			}
				echo "</td>";
				
				echo "</tr>";

				if($_SESSION['s_komb_r']==false)
					break; // len jeden primitivny polynom vpravo
				
			}
			if($_SESSION['s_komb_l']==false)
				break; // porovnaj len s prvym, teda len jeden primitivny polynom nalavo
		}
		echo "</table>";

	}
}// select


	
/********************************************************/
/******************  F U N K C I E  *********************/
/********************************************************/

function TabulkaSuctov(& $gfq) {
	echo '<table border="1">';	


	echo "<tr><TH>+</th>";
	for($stlpec=-1; $stlpec < $gfq->q; $stlpec++) {
		if($stlpec==0)
			continue;
		echo "<th>";		
		if($stlpec==-1)
			echo "0";
		elseif($stlpec==0)
			echo "1";
		else
			echo "&beta;<sup>".$stlpec."</sup>";
		echo "</th>";
	}
	echo "</tr>";
	
	
	for($riadok=-1; $riadok < $gfq->q; $riadok++ ) {
		if($riadok==0)
			continue;
		echo "<tr>";
		
		echo "<th>";		
		if($riadok==-1)
			echo "0";
		elseif($riadok==0)
			echo "1";
		else
			echo "&beta;<sup>".$riadok."</sup>";
		echo "</th>";
		
		  
		for($stlpec=-1; $stlpec < $gfq->q; $stlpec++) {
			if($stlpec==0)
				continue;
			echo "<td>";
			
			$sucet = $gfq->AddByGrade($riadok, $stlpec);
			if($sucet==-1)
				echo "0";
			elseif($sucet==0)
				echo "1";
			else
				echo "&beta;<sup>".$sucet."</sup>";
			echo "</td>";
		}
		echo "</tr>";
	}
	echo '</table>';
}
	
?>
