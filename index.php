<?php

		

	/* Rocnikovy projekt - testovacia stranka */





// nejake konstanty

define('SOURCE_DIR', 'rs_pics/');  // kde su zdrojove obrazky

define('TEMP_DIR', 'rs_temp/');    // na ulozenie zakodovanych obrazkov

define('PROJEKT_DIR', '/home/clients/durcik.sk/durcik.sk/rocnikovy/');



$GLOBALS['rs_pics'] = array('pocitac.png', 'arki.png', 'kacicky.png' ); // zoznam obrazkov

//$GLOBALS['rc_thumbs'] = array('pocitac_th.jpg', 'arki_th.jpg', 'kacicky_th.jpg');

	

	

   require("ReedSolomon.php");

   define("CUT_CHARS", 60);

   

   if ( isset($fieldGenerator))

      $fieldGenerator = str_replace(" ", "+", $fieldGenerator);



   if ( isset($source_poly))

      $source_poly = str_replace(" ", "+", $source_poly);



   if (isset($encoded_poly))

      $encoded_poly = str_replace(" ", "+", $encoded_poly);



	// zisti cas startu skriptu

	$skript_start  = getmicrotime();





       /* zmaz stare subory */	

    $cesta = PROJEKT_DIR.TEMP_DIR;

    if (is_dir($cesta)) {

        if ($dir = @opendir($cesta)) {

            while (($file = readdir($dir)) !== false) {                

                // ak je tento subor starsi ako niekolko hodin tak to vymaz                

                if (($status = stat($cesta.$file))!==FALSE)

                {                    

                    $rozd = time() - $status['ctime'];                    

                    if (($rozd>=7200)&&($file!='.')&&($file!='..'))

                        unlink($cesta.$file); // vymaz ho je starsi ako dve hodiny

                }// if mam status               

                

            }  // while

            closedir($dir);

        }// if          

    }// if

    





	session_start();

// ZAKLADNA  KOSTRA  STRANKY

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"

            "http://www.w3.org/TR/html4/strict.dtd">

<HTML>

<HEAD>

  <meta http-equiv="Cache-Control" content="no-cache">

  <meta http-equiv="Pragma" content="no_cache">

  <meta http-equiv="content-type" content="text/html; charset=windows-1250">

  <meta name="author" content="Miroslav Ïurèík (študent @ktl.elf.stuba.sk)">

  <meta name="copyright" content="(C)2002,2003 Miroslav Ïurèík">

  <link rel="Shortcut Icon" href="rs.ico">

  <link rel="stylesheet" type="text/css" href="styl.css">

  <TITLE>Reed Solomon kódy - roèníkovı projekt</TITLE>

</HEAD>

<BODY>

<div id="domcek" >

   <h1>Reed&amp;Solomon - kódy opravujúce chyby.</h1>

   <div id="logo_div">

      <!-- /* OBRAZKY HORE */ -->

      <IMG id="logo" SRC="pics/logo.png" ALT="logo roèníkového projektu">

   </div>

<ul id="horne_menu">

     <!-- /* MENU HORE */ -->

        [<li><A class="horne_url" href="index.php">Úvod</A></li>] [

        <li><A  class="horne_url" href="index.php?section=rsinfo">Nieèo o RS kódoch</A></li>] [

        <li><A  class="horne_url" href="index.php?section=rsparts">Komponenty</A></li>] [

	<li><A  class="horne_url" href="index.php?section=tests">Testy</A></li>] [

	<li><A  class="horne_url" href="index.php?section=retazec">Reazec</A></li>] [

	<li><A  class="horne_url" href="index.php?section=picture">Obrázok</A></li>]

</ul>

<?php /* MENU VLAVO */

              if ((isset($section)))

              {

              	 echo '<DIV id="lave_menu">';

                 @include './'.$section."_menu.php";

                 echo '</div>';

              }

              ?>

<?php /* OBSAH CASTI PRE DOKUMENTY */



  if (isset($section))

  {

     require './'.$section."_content.php";

  }

  else

  {

     /* zatial nebola kliknuta ziadna sekcia */

     ?>

     <div id="main_obsah">

     	<h2>Vitajte priatelia!</h2>

     	<p class="odstavec">

     	  Srdeène vás vítam na mojich stránkach o samoopravnıch Reed&amp;Solomon kódoch. 

     	  Stránky vznikli ako súèas roèníkového projektu v 4.roèníku môjho bakalárskeho štúdia. 

     	  Ich úlohou nie je ani tak nauèi niekoho všetko o tıchto kódoch, to je školy, vyuèujúcich, kníh, 

     	  ale hlavne:

     	  <ul>

     	    <li>Poda základné informácie o princípe a vızname RS kódov.</li>

     	    <li>Zhrnú, èo všetko je potrebné na vytvorenie vlastného RS kódera/dekódera, podobného ako je pouitı

     	    na tıchto stránkach.</li>     	    

     	    <li>Pre tıch, ktorí sa uèia, alebo by sa chceli nauèi RS kódy da k dispozícii nástroj na kontrolu alebo  

     	     porovnanie vlastnıch vısledkov, èi prácu s väèšími polynómami ako je to na papieri moné zvládnu.</li>

     	    <li>"Ochráò reazec" - ukáka jednej z monıch aplikácií pouitia RS kódov. Zakódovanie zadaného reazca, 

     	    ¾ubovo¾né pokazenie a spätné dekódovanie s oprvaneím chıb. Na konci ukáky je zhrnutie zmien kedy a na ktorom mieste

     	    nastali</li>

     	    <li>"zašumenı obrázok" - Druhá ukáka monej aplikácie RS kódov. Prenos obrázku cez virtuálny prenosovı

     	    kanál, ktorı znehodnotí informácie, obrázok zašumí. A opä spätnım dekódovaním je moné chyby vzniknuté poèas prenosu

     	    odstráni.</li>

     	  </ul>

     	</p>

     Ve¾a úitku zo stránok vám praje autor <A href="mailto: arki@pobox.sk"><i>Miroslav Ïurèík</i></a>.

     

     </div>     

     <?php

  }



 /* UKONCENIE  STRANKY */



 ?> 



<div id="copyright">(c)2002, 2003 <A href="mailto:arki@pobox.sk">Miroslav Ïurèík</A>, 4.roèník, <A href="http://www.ktl.elf.stuba.sk">KTL</A> <A href="http://www.elf.stuba.sk">FEI</A> 
	<A href="http://www.stuba.sk">STU Bratislava</A>
</div>
</div>

<span id="sekund">
<?php
	$skript_stop = getmicrotime();
	$skript_sekund = round($skript_stop - $skript_start, 3);
	echo "Vygenerované za ".$skript_sekund." sec.";

	function getmicrotime(){ 
	    list($usec, $sec) = explode(" ",microtime()); 
	    return ((float)$usec + (float)$sec); 
	} 
?>
</span>
</BODY>
</HTML>
