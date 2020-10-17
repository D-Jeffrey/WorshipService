<?php
// testing only

$to = "Darrenjeffrey@hotmail.com";
$to = "Darren.jeffrey@fortisalberta.com";
$to = "dj@southcalgary.org";
$subject = "My HTML email test.";
$headers = "From: dj@southcalgary.org\r\n";
$headers .= "Reply-To: dj@southcalgary.org\r\n";
$headers .= "Return-Path: dj@southcalgary.org\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

$message = "<html><body>";
$message .= "<h1> This is a test </h1>";
$message .= "</body></html>";

if ( mail($to,$subject,$message,$headers) ) {
   echo "The email has been sent!";
   } else {
   echo "The email has failed!";
   }
?> 