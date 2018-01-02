<p>
<label class="login_label" id="login_email_label" for="email"><?php echo $sl->gp('Log in'); ?></label>
<input type="text" name="email" id="email" value="<?php echo $email; ?>">
</p>
<p>
<label class="login_label" id="login_password_label" for="password"><?php echo $sl->gp('Password'); ?></label>
<input type="password" name="password" id="password" value="<?php echo $password; ?>"> 
</p>
<p>
<input type="button" name="login_button" id="login_button" value="Log in" />
<?php if(is_logged_in() !== false) { ?>
    <!--<div id="logout"><input type="button" id="logout_button" value="Log out" /></div> -->
    <input type="button" id="logout_button" value="<?php echo $sl->gp('Log out'); ?>" />
<?php } ?>
</p>
