<?php 
require('includes/config.php');
require('includes/functions.php');
require('includes/language.php'); // I will regret this
require ('includes/head_include.php'); ?>
<title><?php echo $default_title; ?></title>
</head>
<body>
<!--
<?php
echo "Session is $_SESSION <br>";
echo '<pre>';
print_r($_SESSION);
?>-->
<?php require ('includes/templates/admin_nav.php');  ?>

</body>
</html>
