<?php 
session_save_path('/home1/freestp0/public_html/clients/swimfloatswim.com/schedule/includes/sessions');
ini_set('session.gc_probability', 1);
session_start(); ?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <script type="text/javascript" src="http://www.swimfloatswim.com/schedule/includes/jquery/js/jquery-1.8.2.js"> </script>
        <script type="text/javascript" src="http://www.swimfloatswim.com/schedule/includes/jquery/js/jquery-ui-1.9.1.custom.min.js"> </script>
        <script type="text/javaScript" src="http://www.swimfloatswim.com/schedule/js/main.js?cb=1"> </script>
        <link rel="stylesheet" href="http://www.swimfloatswim.com/schedule/includes/jquery/css/smoothness/jquery-ui-1.9.1.custom.css">
        <link rel="stylesheet" href="http://www.swimfloatswim.com/schedule/css/main.css?cb=2" />
        <link rel="stylesheet" href="http://www.swimfloatswim.com/schedule/css/simplegrid.css" />
<title>Swim Float Swim</title>
</head>
<body>
<form method="post" action="includes/rpc.php">
<input type="text" name="action" value="login">

<h3>session</h3>
<pre>
<?php
print_r($_SESSION);
?>
</pre>
    <h3><span class="phrase">login page</span></h3>
    <p>
<label class="login_label" id="login_email_label" for="email"><span class="phrase">log in</span></label>
<input type="text" name="email" id="email" value="">
</p>
<p>
<label class="login_label" id="login_password_label" for="password"><span class="phrase">password</span></label>
<input type="password" name="password" id="password" value=""> 
</p>
<p>
<input type="submit" value="login">
</p>
<div id="login_error"></div>
    <script type="text/javaScript">
                $('#login_button').click(function(e) {
                    console.log('clicked the login_button');
                    var email = $('#email').val();
                    var password = $('#password').val();
                    var success = login.admin_check_login (email, password, 'text');
                    if(success.myresult == 'true') {
                        // location.href=location.href+"?logged_in";
console.log('we are location hreffing');
                        location.href=location.href;
                    } else {
                        $('#login_error').html('<span class="phrase">login error</span>'); 
                    }
                });
        $('#logout_button').click(function(e) {
            console.log('clicked the logout');
            var success = login.admin_logout ();
            // location.href=location.href;
            console.log(success.myresult);
            if(success.myresult == 'true') {
                location.href=location.href+"?";
            } else {
                console.log (success.myresult + ' is false');
            }
        });
    </script>
    
</form>
</body>
</html>
