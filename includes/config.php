<?php
session_save_path('/var/tmp');
ini_set('session.gc_probability', 1);
session_start();

$db_con['database_host']      = 'localhost';
$db_con['database_name']      = 'freestp0_scheduler';
$db_con['database_user']      = 'freestp0_sfs';
$db_con['database_password']  = '^C(Ixd(7Kry3';

$default_front_page  = 'https://www.swimfloatswim.com';
$default_class_page  = 'https://www.swimfloatswim.com';
$default_return_link = 'https://www.swimfloatswim.com/schedule/utilities/process_payment.php';
$reg_process_link    = 'https://www.swimfloatswim.com/schedule/utilities/reg_process.php';

$default_title      = 'Swim Float Swim';

// config options
$days               = 7; // wtf really. yep. just in case somebody wants a 5 day calendar. I would need to add an offset to make that happen.
$start_day_name     = 'Sunday'; 
$days_block         = array('Sunday', 'Saturday'); // days we don't want to show on the schedule
// $start_day_name     = 'Monday'; 
$time_constraint    = '5'; // how do you put "10 minutes. just always assume minutes?
$daily_start_hour   = '07';
$daily_start_minute = '30';
$daily_end_hour     = '19';
$daily_end_minute   = '00';
$show_time_triggers = array('00','05', '10', '15', '20', '25', '30', '35', '40', '45', '50', '55');

$private_time_constraint = '10';
$daily_schedule_time_constraint = '5'; // for displaying the daily schedule

// calculate some settings
$this_month         = date('n');
$this_year          = date('Y');
$today              = date('w'); // day of the week, sunday being 0
$dom                = date('j'); // calendar date with no leading 0
// ok the start date needs to be the first Sunday on or before classes begin.
$start_day          = $dom-$today;
// this needs to be the first sunday of the selected season.
$start_date         = date('Y-m-d', mktime(0,0,0,$this_month, $start_day, $this_year));
$current_day_number = $start_day; // this is the var we increment

// styling configs
$extra_td_styles    = '';

$class_time_increment_choices=array('10', '15', '20', '30', '45');
$default_duration=30;
$default_number_participants=4;

$class_time_increment_rowspan=array(10=>1, 15=>2, 20=>3, 30=>3, 45=>5); // used in weekly_schedule.php

$time_row_height_multiplier=2.4;

// $day_list = array (0=>'Sunday', 1=>'Monday', 2=>'Tuesday', 3=>'Wednesday', 4=>'Thursday', 5=>'Friday', 6=>'Saturday');
$day_list = array (0=>'Sunday', 1=>'Monday', 2=>'Tuesday', 3=>'Wednesday', 4=>'Thursday', 5=>'Friday', 6=>'Saturday');

// default list of days selected
// $default_selected_privates=array(1,2,3,4,5);
$default_selected_privates = array(1,2,3,4);

$has_config=true;

// default settings that belong in the db so people can change them

# $default_class_id=3;
// $default_class_id=14; // fall 2016
$default_class_id=19; // summer 2018

// login defaults
// $logout_hours = 24; // after 24 hours you are logged out

$logout_params = array('time'=>'minute', 'increment'=>120);

// just stuff
$age_month_cutoff=36; // cutoff betweenshowing months and years for age.

// for swimming deposits
$require_deposit=false; // I DO use this
$deposit_amount = 96.00; // plan to use this anytime troy?

$payment['intrix']['key_id']='3734671';
$payment['intrix']['username']='heumann';
$payment['intrix']['merchantKeyText']='SXEV38r62k3U2593tp3247p6KgXZQYt8'; 
$payment['intrix']['cart']='https://secure.velocitypaymentsgateway.com/cart/cart.php';

$payment['intrix']['demo']=array(
    'key_id'   => '3785894',
    'username' => 'demo'
);

$payment_method='intrix';

// gotta start somewhere
$c='$'; // yeah but this should be a language reference not a currency
// just sayin', gotta start somewhere.

$lang='en';

// default prices
// eventually we'll tie the price to the event

// summer? $price['group']=253;
// fall
// $price['group']=345;
// $price['group']=525;
// $price['group']=390;
// $price['group']=96;
// $price['group']=546;
// $price['group']=286;
// $price['group']=66;
$price['group']=286;

$price['SWM'] = 650; // in db
$price['FLT'] = 430; // in db
$price['SFS'] = 650; // in db

$registration['orig']=0; // cost for registration for first child
$registration['sibling']=0; // cost for registration for second child

$default_reg_id=1;

/* signature pad */

$sig_width['agreement']=600;
$sig_height['agreement']=91;


$sig_width['registration']=600;
$sig_height['registration']=91;


$sig_width['waiver']=600;
$sig_height['waiver']=91;

$show_shelf = false; // show the little popup on event cards for non-admins
?>
