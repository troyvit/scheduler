<?php
if($_SESSION['log_level'] < 3) {
?>
    <h3><?php echo $sl->gp('Login page'); ?></h3>
    <?php require('includes/templates/login_form.php'); ?>
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
                        $('#login_error').html('<?php echo $sl->gp('login error'); ?>'); 
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
    <?php
        die();
}
    ?>
<div class="grid">
    <div class="col-1-1">
        <div id="nav_grid">
            <ul class="nav" id="main_nav">
            <li class="nav_item" id="nav_students"><a href="./participants.php"><?php echo $sl->gp('CRM'); ?></a></li>
                <li class="nav_item" id="nav_schedlue"><a href="./show_schedule.php"><?php echo $sl->gp('Schedule'); ?></a></li>
                <li class="nav_item" id="nav_student_event"><a href="./show_classes.php"><?php echo $sl->gp('Students and events'); ?></a></li>
                <li class="nav_item" id="nav_billing"><a href="./show_billing.php"><?php echo $sl->gp('Billing'); ?></a></li>
                <li class="nav_item" id="logout_button"><?php echo $sl->gp('Log Out'); ?></li>
            </ul>
        </div>
    </div>
</div>
