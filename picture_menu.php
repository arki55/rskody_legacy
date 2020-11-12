<ul>
  <?php
  $krok = $_GET['step'];
  if (empty($krok)) $krok=1;
  
  echo "<li ".(($krok==1) ? 'class="hover"' : '')." ><A  href=\"?section=$section&amp;step=1\">Výber obrázka</a></li>\n";
  
  if ($krok>=2) echo "<li ".(($krok==2) ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=2\">Výber kódu</A></li>\n";
  else echo "<li>Výber kódu</li>\n";
  if ($krok>=5) echo "<li ".($krok==5 ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=5\">Príprava obrázkov</A></li>\n";
  else echo "<li>Príprava obrázkov</li>\n";
  if ($krok>=6) echo "<li ".($krok==6 ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=6\">Kódovanie</A></li>\n";
  else echo "<li>Kódovanie</li>\n";
  if ($krok>=7) echo "<li ".(($krok==7)||($krok==8) ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=7\">Zašumenie</A></li>\n";
  else echo "<li>Zašumenie</li>\n";
  if ($krok>=9) echo "<li ".(($krok==9)||($krok==10) ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=9&amp;again=1\">Dekódovanie</A></li>\n";
  else echo "<li>Dekódovanie</li>\n";
  if ($krok>=11) echo "<li ".($krok==11 ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=11\">Vyhodnotenie</A></li>\n";
  else echo "<li>Vyhodnotenie</li>\n";
  ?>
</ul>
