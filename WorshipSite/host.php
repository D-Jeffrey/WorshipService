<html> 

 <head> 
 <title> host </title>
 </head>

 <body >

 <h1> Host </h1>

 



  <?php

$x = '<p><strong><span style=3D"text-decoration: underline;">Resources:</span></s=
trong><br />&nbsp;&nbsp;<a href=3D"getResource.php?sid=3D483&amp;rid=3D3632=
" target=3D"_blank">My Redeemer Lives (Chord Sheet) - Key G</a><br />&nbsp;=
&nbsp;<a href=3D"getResource.php?sid=3D483&amp;rid=3D3633" target=3D"_blank=
">My Redeemer Lives (Lead Sheet) - Key G</a><br />&nbsp;&nbsp;<a href=3D"ge=
tResource.php?sid=3D483&amp;rid=3D3634" target=3D"_blank">Rise Up and Prais=
e Him (Chord Sheet) - Key G</a><br />&nbsp;&nbsp;<a href=3D"getResource.php=
?sid=3D483&amp;rid=3D3635" target=3D"_blank">Rise Up and Praise Him (Lead S=
heet) - Key G</a><br />&nbsp;&nbsp;<a href=3D"getResource.php?sid=3D483&amp=
;rid=3D3636" target=3D"_blank">Take My Life and Let It Be (Chord Sheet) - K=
ey G</a><br />&nbsp;&nbsp;<a href=3D"getResource.php?sid=3D483&amp;rid=3D36=
37" target=3D"_blank">Take My Life and Let It Be (Lead Sheet) - Key G</a><b=
r />&nbsp;&nbsp;<a href=3D"getResource.php?sid=3D483&amp;rid=3D3638" target=
=3D"_blank">I Will Rise (Chord Sheet) - Key G</a><br />&nbsp;&nbsp;<a href=
=3D"getResource.php?sid=3D483&amp;rid=3D3639" target=3D"_blank">I Will Rise=
 (Lead Sheet) - Key G</a><br />&nbsp;&nbsp;<a href=3D"getResource.php?sid=
=3D483&amp;rid=3D3640" target=3D"_blank">I Will Run to You - Key G</a></p><=
/body></html>=';
echo $x;
echo "<HR>";

$x =  str_replace("href=3D\"getResource.php", "href=3D\"" . $_SERVER["HTTP_HOST"] . "/getResource.php", $x, $fixc);		

echo $x;

  ?>
   </body>
 </html>




