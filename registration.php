<?php 
require('includes/config.php');
require('includes/functions.php');
require('includes/language.php'); // I will regret this
require ('includes/bootstrap_include.php'); 

$ua=$_SERVER['HTTP_USER_AGENT'];
// echo $ua;
// Mozilla/5.0 (iPhone; U; CPU iPhone OS 3_0 like Mac OS X; en-us) AppleWebKit/528.18 (KHTML, like Gecko) Version/4.0 Mobile/7A341 Safari/528.16 

?>
<!-- signature stuff -->
<link href="js/signature/assets/jquery.signaturepad.css" rel="stylesheet">
<script src="js/signature/jquery.signaturepad.js"></script>
<script src="js/signature/assets/json2.min.js"></script>
<!-- end signature stuff -->
<!-- form validation -->
<script type="text/javascript" src="js/jquery.validate.js"></script>
<title>Swim Float Swim Registration</title>
<link rel="stylesheet" href="css/registration.css" />
<script type="text/javascript">
var enabled=true;
<?php if($_GET['ty']==true) { // better error checking will go here eventually ?>
    enabled=false;
<?php } ?>
$(document).ready(function() {
    registration.getRegistration("<?php echo $default_reg_id; ?>", "<?php echo $_REQUEST['login_id']; ?>", enabled); 
        // $("#register_body :input").attr("disabled", true);
});
</script>
</head>
<body id="register_body">
<img style="width:90%" src="images/reg_top.png">
<?php
if($_GET['ty']==true) {
    echo '<h1 style="color: #ffffff; ">Thanks for your registration!</h1>';
}
?>
<div id="login_reg_info"></div>
<div id="participant_reg_holder">
</div>
</body>
</html>
