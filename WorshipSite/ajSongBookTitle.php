<?php
echo "	<form name='frmTitle' method='post'>\n";
echo "	<b>Book Title:</b>&nbsp;\n";
echo "	<input type='text' name='bookTitle' maxlength='100' size='25' value='".stripslashes(htmlentities($_REQUEST["t"],ENT_QUOTES))."' /><br />\n";
echo "	<b>Private</b>&nbsp;\n";
$chk = $_REQUEST["ac"]=="add"?" checked":"";
echo "	<input type='checkbox' name='private' value='1'$chk /><br />\n";
echo "	<input type='button' name='updBook' value='Create' onClick='editBookTitle();return hs.close(this);'><input type='button' name='cancel' value='Cancel' onClick='return hs.close(this);'>\n";
echo "	</form>\n";
?>