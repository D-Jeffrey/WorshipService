<?php
require_once('SLLists.class.php');
$sortableLists = new SLLists('scriptaculous');
$sortableLists->addList('categories','categoriesListOrder');
$sortableLists->addList('divContainer','divOrder','div');
$sortableLists->addList('imageContainer','imageOrder','img');
$sortableLists->addList('imageFloatContainer','imageFloatOrder','img',"overlap:'horizontal',constraint:false");
$sortableLists->debug = true;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Scriptaculous Lists with PHP</title>
<link rel="stylesheet" type="text/css" href="css/lists.css"/>
<?php
$sortableLists->printTopJS();
?>

</head>
<body>
<a href="http://www.gregphoto.net">Back to Graphics by Greg</a>

<h1>Scriptaculous Lists with PHP</h1>
by Greg Neustaetter
<h2>Scriptaculous Sortables</h2>

<p><a href="http://script.aculo.us">Scriptaculous</a> is one of many great new JavaScript libraries created to answer the call for well
written 'Web 2.0' JavaScript libraries.  Written by Thomas Fuchs, scriptaculous has many features that can be used in AJAX-ified applications,
drag-and-drop effects, and a whole slew of visual effects.  The drag-and-drop effects, most notably the sortables, caught my eye because
the look great, they are so easy to implement, and they're just so much nicer than the standard listbox with up/down arrows that we see in
most of today's applications and administration tools.</p>

<h2>SLLists - a PHP wrapper around scriptaculous sortables</h2>

<p>So scriptaculous lists are really easy to use and implement...so why put together a PHP script to wrap around it all?  I did this for a
couple reasons (no the following list isn't sortable!):</p>
	<ul>
		<li>It isn't very obvious from scriptaculous (or other similar drag-and-drop sorting libraries) how sortables can actually be used in a real application
		to perform a useful function.</li>
		<li>I wanted a simple way to serialize the result of all this snazzy ordering into a simple PHP array with which I could update a database</li>
		<li>Many people are just plain scared of JavaScript and don't know what to do with it - I'm one of these folks too!</li>
		<li>PHP is fun.</li>
	</ul>
<p>The resulting PHP class is a very simple class that makes it easy - with a couple lines of code to get a sortable list (or sortable just
about anything) onto a page and to translate the result into a PHP array.  I've got no documentation except for this sample page...so here's 
a brief rundown of the features:</p>
<ul>
	<li>SLLists - constructor that basically sets the path to the JavaScript files</li>
	<li>addList - adds a list or other element as a new sortable entity</li>
	<li>printTopJS - prints the JavaScript into the head of a PHP file</li>
	<li>printForm - prints an HTML form that contains the hidden inputs needed.  Alternatively users can create their own forms or use the printHiddenInputs functions to put
	these hidden inputs in existing forms</li>
	<li>printBottomJS - prints the JavaScript that should go right before the closing body tag</li>
	<li>getOrderArray - returns an array with items and their order after being passed an input with the serialzed scriptaculous list</li>
</ul>
<p><a href="http://www.gregphoto.net/sortable/download/sllists0.01.zip">Download Version 0.01</a>  of the class (and this sample page) to get started.  All the examples on this page were
created using the class - no JavaScript needed!</p>

<h2>Example: Sorting a regular list</h2>

<p>This is a list of the photography sections on my website.  I put them all in a standard 'ul' list - each item is a 'li' list item.
I might use this list to help me choose a new order for the categories on my site.  All I would need to do is drag and drop the list items
until I have an order that I'm happy with.  Once I'm done I would submit my changes - in the background, I'm using the scriptaculous
<a href="http://wiki.script.aculo.us/scriptaculous/show/Sortable.serialize">serialize function</a> to get the list of items (in order) into a hidden input - in this case I've made the input visible.  I can then submit
the form and get an array back.</p>

<p>My PHP class has a function that knows how to understand the scriptaculous serialized string and translates
it into a simple PHP array with the items and there order.  Once I have this, it's easy to do things like submit the new order to a database.
If you click the submit button, you can see the update statements I would send to the database.  Clicking the 'View Serialized Lists' will
fill the inputs with the serialized lists.</p>

<br>
<ul id="categories" class="sortableList">
	<li id="item_1">Galleries</li>
	<li id="item_2">Art</li>
	<li id="item_3">Roadtrip</li>
	<li id="item_4">Yosemite</li>
	<li id="item_5">Animals</li>
	<li id="item_6">Australia</li>
	<li id="item_7">Wesleyan</li>
	<li id="item_8">California</li>
</ul>

<?php
$sortableLists->printForm($_SERVER['PHP_SELF'], 'POST', 'Submit', 'button');
?>

<br>

<?php
if(isset($_POST['sortableListsSubmitted'])) {
	?>
	<br><br>
	The update statements below would update the database with the new order:<br><br>
	<div style="margin-left:40px;">
	<?php
	$orderArray = SLLists::getOrderArray($_POST['categoriesListOrder'],'categories');
	foreach($orderArray as $item) {
		$sql = "UPDATE categories set orderid=".$item['order']." WHERE carid=".$item['element'];
		echo $sql.'<br>';
	}
	?>
	</div>
	<?php
}
?>
<br><br><br>

<h2>Example: Sorting divs</h2>
<p>Sorting isn't just for lists.  Here we're sorting divs - some of them even have some complex markup inside them including styling,
links, tables, and form elements.  In order to style elements other than lists, we need to pass a third argument to the addList function
that tells scriptaculous which elements should be draggable.</p>
<div id="divContainer">
	<div id="div_1">This is the first div</div>
	<div id="div_2">This is the second div</div>
	<div id="div_3">This is the third div</div>
	<div id="div_4">
		This is the fourth div, it also has a lot of text and will cover more than one line.  It's bigger than the other divs here, but that should 
		really be no problem.  Oh yeah, it also has <b>formatting</b>, and <a href="#">links</a>
	</div>
	<div id="div_5">This is the sixth div</div>
	<div id="div_6">
		This is the seventh div.  It also has a table below with some form inputs
		<table>
			<tr><td>First Name:</td><td><input type="text"></td></tr>
			<tr><td>Nice Demo?</td><td><input type="radio"> Yes<br><input type="radio"> No</td></tr>
		</table>
	</div>
	<br style="clear: both;">
</div>


<br><br>

<h2>Example: Sorting images</h2>

<div id="imageContainer">
	<img id="img_1" src="images/01_small.jpg">
	<img id="img_2" src="images/02_small.jpg">
	<img id="img_3" src="images/03_small.jpg">
	<img id="img_4" src="images/04_small.jpg">
	<img id="img_5" src="images/05_small.jpg">
	<img id="img_6" src="images/06_small.jpg">
</div>

<h2>Example: Float sort</h2>
<p>All the examples so far have shown sorting vertically - this one shows horizontal and vertically.  It also demonstrates how to pass
extra options to the addList method.  In this case we passed overlap and constraint settings to scriptaculous.  For a list of all the options
please see the <a href="http://wiki.script.aculo.us/scriptaculous/show/Sortable.create">sortable.create page</a> on the scriptaculous site.  The only option that
you don't have to pass this way is the 'tag' option.</p>
<div id="imageFloatContainer">
	<img id="img_1" src="images/01_small.jpg">
	<img id="img_2" src="images/02_small.jpg">
	<img id="img_3" src="images/03_small.jpg">
	<img id="img_4" src="images/04_small.jpg">
	<img id="img_5" src="images/05_small.jpg">
	<img id="img_6" src="images/06_small.jpg">
	<hr style="clear:both;border:0;visibility:none;">
</div>
<br><br><br><br>
Enjoy!<br>
- Greg
<br><br><br><br>
<?php
$sortableLists->printBottomJS();
?>
</body>

</html>
