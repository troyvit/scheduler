<?php 
// wtf is this doing here
require('includes/config.php');
require('includes/functions.php');
/* you need to have this thing limited by class somehow.
 *
 * I was thinking too that you should only pull in participants who are in an event, but if you
 * do that then a parent who thinks their kid is in an event but isn't won't get any real feed-
 * back to that extent. I don't know. I think I need to revisit the jquery search. 
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><title>View Classes</title>
<meta charset="UTF-8">
<?php require ('includes/head_include.php'); ?>
<link rel="stylesheet" href="css/main.css" />
<link rel="stylesheet" href="css/event_card.css" />

<style type="text/css">
body { padding: 0px; } 

.event_card_holder {
    min-height: 220px;
    float: left
}

.event_name { display: block; border-bottom: 1px solid #333333; padding-top: 4px; margin-bottom: 3px; }
.event_time { float: left; height: 20px; }
.event_day { float: left; height: 20px; }
.event_desc { clear: left;  }
.event_leader { display: block; clear: both; }

.event_name h3 { font-size: 18px; text-align: center; }

.event_time h3, .event_desc h3, .event_day h3, .event_desc h3 .event_leader h3 {
    font-size: 12px; 
}

body div.event_card { 
    border: 1px solid #004fa0;
    border-top: 2px solid #EE3631; 
}

body #hold_classes { 
padding: 0px; 
overflow: visible;

}

body .confirmed, body .unconfirmed, body .event_slot { border: 0px solid #666666; border-top: 1px solid #666666; /* border-right: 0px solid #666666; border-left: 0px solid #666666; border-bottom: 0px solid #666666; */ padding: 4px; }

</style>

<script type="text/javaScript">


var login = {
    is_logged_in: 'true',
    privilegeMissing: 'Please log in first',
    submit_login: function (email, password) {
        $('.login_error').hide();
        returnFalse=false;
        /* LOSING LOSING */
        var email_tf = errorChecking.check_field(email);
        console.log(email_tf);
        if(email_tf == false) {
            errorChecking.display_error(email);
            login.is_logged_in=false;
            returnFalse=true;
        }
        var pw_tf = errorChecking.check_field(password);
        if(pw_tf == false) {
            errorChecking.display_error(password);
            login.is_logged_in=false;
            returnFalse=true;
        }
        if(returnFalse == true) {
            return false;
        }
        console.log(pw_tf);
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=login&email="+email_tf.val()+"&password="+pw_tf.val(),
            dataType: "html",
            success: function(result) {
                login.is_logged_in = $.trim(result);
                if(login.is_logged_in == 'true') {
                    $('#login_msg').addClass('login_success');
                    $('#login_msg').html('Thanks for logging in. Click a tab to browse classes.');
                    $('#login_msg').show('slide', {}, 500, '' );
                } else {
                    /*
                    $('#login_msg').addClass('login_error');
                    $('#login_msg').html('Please try again');
                    $('#login_msg').show('slide', {}, 500, '' );
                     */
                    login.display_error('Please try again');
                }
            }
         });
    },

    display_error: function (message) {
        // debug // alert(message);
        $('#login_msg').addClass('login_error');
        $('#login_msg').html(message);
        $('#login_msg').show('slide', {}, 500, '' );
    },

    perform_privileged_function: function (myfunction) {
        if(login.is_logged_in == 'false') {
            $('#loginclick').trigger('click');
            login.display_error(login.privilegeMissing);
        } else {
            myfunction();
        }
    }
}

var participantData = {

    blarg: function() {
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=get_participants_json",
            dataType: "json",
            success: function(result) {
                participantData.buildParticipantInterface(result);
                console.log('it us success');
                // no success on this one
            }
        });
    },
    getAllParticipants: function() {
        alert('lost again');
    },
    buildParticipantInterface: function(participantJson) {

        $( "#participant" ).autocomplete({
            minLength: 0,
            source: participantJson,
            focus: function( event, ui ) {
                console.log('focus on #participant');
                $( "#participant" ).val( ui.item.value );
                return false;
            },
            select: function( event, ui ) {
                $( "#participant").val(ui.item.value);
                $( "#participant_id").val(ui.item.participant_id);
                participantData.participant_id=ui.item.value;
                console.log('select on #participant');
                eventManipulation.getEvents('participant_id', ui.item.participant_id, $('#classSelect').val())
                return false;
            }
        })
        .data( "autocomplete" )._renderItem = function( ul, item ) {
                console.log('autocomplete on #participant');
            return $( "<li>" )
                .data( "item.autocomplete", item )
                .append( "<a>" + item.value + "</a>" )
                .appendTo( ul );
        };
    },
}

$(document).ready(function() {
    $( "#classSelectTabs" ).tabs();
    $.ajax({
        url: jsConfigs.rpc,
        type: "POST",
        data: "action=student_get_class_nav",
        dataType: "html",
        success: function(result) {
            $('#hold_nav').html(result);
                eventManipulation.html_loc='#hold_classes';
            $('#get_events_by_type').click(function(e) {
                login.perform_privileged_function(function() { 
                    eventManipulation.getEvents('event_type', $('#event_type').val(), $('#classSelect').val())
                });
            });
            $('#get_events_by_day').click(function(e) {
                login.perform_privileged_function(function() {
                    eventManipulation.getEvents('day', $('#day_select').val(), $('#classSelect').val())
                });
            });
            $( "#classSelectTabs" ).tabs();
            // var projects = participantData.getAllParticipants();
            var projects = participantData.blarg();
        }
     });
});
</script>
</head>
<body>

        <div id="hold_nav" class="content"> </div>
        <div id="hold_classes" class="content"> </div>
</body>
</html>
