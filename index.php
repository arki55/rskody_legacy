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

  <meta name="author" content="Miroslav �ur��k (�tudent @ktl.elf.stuba.sk)">

  <meta name="copyright" content="(C)2002,2003 Miroslav �ur��k">

  <link rel="Shortcut Icon" href="rs.ico">

  <link rel="stylesheet" type="text/css" href="styl.css">

  <TITLE>Reed Solomon k�dy - ro�n�kov� projekt</TITLE>

</HEAD>

<BODY>

<div id="domcek" >

   <h1>Reed&amp;Solomon - k�dy opravuj�ce chyby.</h1>

   <div id="logo_div">

      <!-- /* OBRAZKY HORE */ -->

      <IMG id="logo" SRC="pics/logo.png" ALT="logo ro�n�kov�ho projektu">

   </div>

<ul id="horne_menu">

     <!-- /* MENU HORE */ -->

        [<li><A class="horne_url" href="index.php">�vod</A></li>] [

        <li><A  class="horne_url" href="index.php?section=rsinfo">Nie�o o RS k�doch</A></li>] [

        <li><A  class="horne_url" href="index.php?section=rsparts">Komponenty</A></li>] [

	<li><A  class="horne_url" href="index.php?section=tests">Testy</A></li>] [

	<li><A  class="horne_url" href="index.php?section=retazec">Re�azec</A></li>] [

	<li><A  class="horne_url" href="index.php?section=picture">Obr�zok</A></li>]

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

     	  Srde�ne v�s v�tam na mojich str�nkach o samoopravn�ch Reed&amp;Solomon k�doch. 

     	  Str�nky vznikli ako s��as� ro�n�kov�ho projektu v 4.ro�n�ku m�jho bakal�rskeho �t�dia. 

     	  Ich �lohou nie je ani tak nau�i� niekoho v�etko o t�chto k�doch, to je �koly, vyu�uj�cich, kn�h, 

     	  ale hlavne:

     	  <ul>

     	    <li>Poda� z�kladn� inform�cie o princ�pe a v�zname RS k�dov.</li>

     	    <li>Zhrn��, �o v�etko je potrebn� na vytvorenie vlastn�ho RS k�dera/dek�dera, podobn�ho ako je pou�it�

     	    na t�chto str�nkach.</li>     	    

     	    <li>Pre t�ch, ktor� sa u�ia, alebo by sa chceli nau�i� RS k�dy da� k dispoz�cii n�stroj na kontrolu alebo  

     	     porovnanie vlastn�ch v�sledkov, �i pr�cu s v���mi polyn�mami ako je to na papieri mo�n� zvl�dnu�.</li>

     	    <li>"Ochr�� re�azec" - uk�ka jednej z mo�n�ch aplik�ci� pou�itia RS k�dov. Zak�dovanie zadan�ho re�azca, 

     	    �ubovo�n� pokazenie a sp�tn� dek�dovanie s oprvane�m ch�b. Na konci uk�ky je zhrnutie zmien kedy a na ktorom mieste

     	    nastali</li>

     	    <li>"za�umen� obr�zok" - Druh� uk�ka mo�nej aplik�cie RS k�dov. Prenos obr�zku cez virtu�lny prenosov�

     	    kan�l, ktor� znehodnot� inform�cie, obr�zok za�um�. A op� sp�tn�m dek�dovan�m je mo�n� chyby vzniknut� po�as prenosu

     	    odstr�ni�.</li>

     	  </ul>

     	</p>

     Ve�a ��itku zo str�nok v�m praje autor <A href="mailto: arki@pobox.sk"><i>Miroslav �ur��k</i></a>.

     

     </div>     

     <?php

  }



 /* UKONCENIE  STRANKY */



 ?> 



<div id="copyright">(c)2002, 2003 <A href="mailto:arki@pobox.sk">Miroslav �ur��k</A>, 4.ro�n�k, <A href="http://www.ktl.elf.stuba.sk">KTL</A> <A href="http://www.elf.stuba.sk">FEI</A> 
	<A href="http://www.stuba.sk">STU Bratislava</A>
</div>
</div>

<span id="sekund">
<?php
	$skript_stop = getmicrotime();
	$skript_sekund = round($skript_stop - $skript_start, 3);
	echo "Vygenerovan� za ".$skript_sekund." sec.";

	function getmicrotime(){ 
	    list($usec, $sec) = explode(" ",microtime()); 
	    return ((float)$usec + (float)$sec); 
	} 
?>
</span>
</BODY>
</HTML>
