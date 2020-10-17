<script  src="<?php echo $baseFolder; ?>scripts/highslide/highslide-full.js"></script>
<script>
	// Apply the Highslide settings
	hs.graphicsDir = '<?php echo $baseFolder; ?>scripts/highslide/graphics/';
	hs.align = 'center';
	hs.transitions = ['expand', 'crossfade'];
	hs.outlineType = 'rounded-white';
	hs.fadeInOut = true;
	hs.dimmingOpacity = 0.75;
	hs.outlineWhileAnimating = true;
	hs.allowSizeReduction = false;
	// always use this with flash, else the movie will be stopped on close:
	hs.preserveContent = false;
	hs.wrapperClassName = 'draggable-header no-footer';
</script>
<link rel="stylesheet" type="text/css" href="<?php echo $baseFolder; ?>scripts/highslide/highslide.css" />
<?php
$isAdmin = (allow_access(Administrators)=="yes");
$showMenu = !isset($hideMenu) || !$hideMenu;
echo "<link rel=\"stylesheet\" href=\"{$baseFolder}css/tw.css\" type=\"text/css\">\n";
echo "<style>\n";
echo "form {\n";
echo "	padding:0px;\n";
echo "}\n";
echo "</style>\n";

if($showMenu) {
	echo "<script  src=\"/scripts/chromemenu/chromejs/chrome.js\"></script>\n";
	echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"/scripts/chromemenu/chrometheme/chromestyle.css\" />\n";
}
echo "</head>\n";
echo "<body>\n";
include($_SERVER["DOCUMENT_ROOT"]."/siteMenu.php");
?>
