<div id="classSelectTabs">
    <ul>
    <li><a href="#dayFilter"><?php echo $sl->gp('Find by day'); ?></a></li>
    <li><a href="#classFilter"><?php echo $sl->gp('Find by type'); ?></a></li>
    <li><a href="#participantFilter"><?php echo $sl->gp('Find student'); ?></a></li>
        <!--<li><a href="#logIn"><?php echo $log_in_tab_title; ?></a></li> -->
    </ul>
    <div id="classFilter">
        <form id="event_typeFilterForm" name="classFilterForm">
            <select id="classSelect" name="classSelect">
                <?php echo $class_list;  ?>
            </select>
            <select name="event_type" id="event_type">
                <?php echo $event_list;  ?>
            </select>
            <input class="classLoadButton" type="button" id="get_events_by_type" value="Go">
        </form>
<!--
        <div class="instructions color_key">
            <h3><?php echo $sl->gp('Color Key'); ?></h3>
            <table style="width: 78%;">
            <tr>
            <td class="paid"><?php echo $sl->gp('Paid'); ?></td>
            <td class="unpaid"><?php echo $sl->gp('Unpaid'); ?></td></tr></table>
        </div>
-->
    </div>
    <div id="dayFilter">
        <form id="dayFilterForm" name="dayFilterForm">
            <select id="classSelect" name="classSelect">
                <?php echo $class_list;  ?>
            </select>
            <select id="day_select" name="day_select">
            <?php echo $day_list; ?>
            </select>
            <input class="classLoadButton" type="button" id="get_events_by_day" value="Go">
        </form>
<!--
        <div class="instructions color_key">
            <h3>Color Key</h3>
            <table style="width: 78%;">
            <tr>
            <td class="confirmed"><?php echo $sl->gp('Confirmed'); ?></td>
            <td class="unconfirmed"><?php echo $sl->gp('On Hold'); ?></td></tr></table>
        </div>
-->
    </div>
    <div id="participantFilter">
        <h3>Choose the desired session, start typing your student’s name, and select from the list.</h3>
        <form id="participantFilterForm" name="participantFilterForm">
            <select id="classSelect" name="classSelect">
                <?php echo $class_list;  ?>
            </select>
            <input type="text" name="participant" id="participant">
            <input type="hidden" name="participant_id" id="participant_id" value=""> 
        </form>
<!--
        <div class="instructions color_key">
            <h3>Color Key</h3>
            <table style="width: 78%;">
            <tr>
            <td class="confirmed"><?php echo $sl->gp('Confirmed'); ?></td>
            <td class="unconfirmed"><?php echo $sl->gp('On Hold'); ?></td></tr></table>
        </div>
-->
    </div>
<?php /*
    <div id="logIn">
        <div id="login_holder">
            <?php 
    if($_SESSION['log_level'] == 0) {
        require('./templates/login_form.php'); ?>
        <div id="request_password">
            <?php require('./templates/request_login.php'); ?>
        </div>
<?php } ?>
        </div>
    </div>
 */
?>
<?php
        /*
         * turn back on when you fix the display bug
        if(strlen($leader_boxes) > 0) { 
            echo '<div id="leader_boxes">
                <h3 style="margin-top: 6px; margin-left: 2px;">'.$sl->gp('To limit by teachers select the teachers you would like to see.').'</h3>'.
            $leader_boxes.'
            </div>';
        }
         */
?>
