<ul>
  <?php
  $krok = $_GET['step'];
  if (empty($krok)) $krok=1;
  
  echo "<li ".(($krok==1) ? 'class="hover"' : '')." ><A  href=\"?section=$section&amp;step=1\">V�ber obr�zka</a></li>\n";
  
  if ($krok>=2) echo "<li ".(($krok==2) ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=2\">V�ber k�du</A></li>\n";
  else echo "<li>V�ber k�du</li>\n";
  if ($krok>=5) echo "<li ".($krok==5 ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=5\">Pr�prava obr�zkov</A></li>\n";
  else echo "<li>Pr�prava obr�zkov</li>\n";
  if ($krok>=6) echo "<li ".($krok==6 ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=6\">K�dovanie</A></li>\n";
  else echo "<li>K�dovanie</li>\n";
  if ($krok>=7) echo "<li ".(($krok==7)||($krok==8) ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=7\">Za�umenie</A></li>\n";
  else echo "<li>Za�umenie</li>\n";
  if ($krok>=9) echo "<li ".(($krok==9)||($krok==10) ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=9&amp;again=1\">Dek�dovanie</A></li>\n";
  else echo "<li>Dek�dovanie</li>\n";
  if ($krok>=11) echo "<li ".($krok==11 ? 'class="hover"' : '')."><A href=\"?section=$section&amp;step=11\">Vyhodnotenie</A></li>\n";
  else echo "<li>Vyhodnotenie</li>\n";
  ?>
</ul>
