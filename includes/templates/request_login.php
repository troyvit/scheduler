    <h3><?php echo $sl->gp('Request new password'); ?></h3>
            <div id="password_request_message"></div>
            <label class="login_label" id="login_email_req_label" for="email"><?php echo $sl->gp('Email'); ?></label>
            <input type="text" name="email" id="login_email_req" value="<?php echo $email; ?>">
            <br>
            <input type="button" name="login_button" id="email_password_button" value="Request" />

