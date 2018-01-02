<?php
$headers = "From: info@swimfloatswim.com";
$emailto='troy@troyvit.com';
$email='test email to Troy';
$success = mail($emailto, 'Swim Float Swim Registration Receipt', $email, $headers, '-f info@swimfloatswim.com');

echo "success is $success \n";
?>
