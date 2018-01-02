<?php
session_save_path('/home1/freestp0/public_html/clients/swimfloatswim.com/schedule/includes/sessions');
ini_set('session.gc_probability', 1);
session_start();
echo "anything?";
echo '<pre>';
print_r($_SESSION);
