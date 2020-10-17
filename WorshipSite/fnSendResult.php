<?php
	
	// If buffering working then this would allow it to dyanmically update	
	echo "
<!DOCTYPE html>
<html lang='en'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<title>E-mail results</title>
<script>
function docprogress(message, progress){
		add_log(message);
		document.getElementById('progressor').style.width = progress + '%';
}
function add_log(message)
{
	var r = document.getElementById('results');
	r.innerHTML += message + '<br>';
	r.scrollTop = r.scrollHeight;
}
</script>
";
	
	ob_flush();
	flush();
	$title = "Email Service Order";
	$hlpID = 0;

	include("header.php");
	
echo "
<div style='text-align: center; ' >
<div >
	<div>
		<h2>Email Results</h2>
	</div>
</div>

<br />
	<div style='display: -webkit-inline-box;' >
	
        <div id='results' style='text-align: left; border:4px solid #000; padding:10px; width:600px; height:300px; overflow:auto; background:#eee;'></div>
      </div>
        <br />
         <div style='display: -webkit-inline-box;' >
        <div style='border:1px; border:solid; width:300px; height:20px; overflow:auto; background:#eee;'>
            <div id='progressor' style='background:#07c; width:0%; height:100%;'></div>
        </div>
        </div>
</div>
";        
?>