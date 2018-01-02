<?php 
require('includes/config.php');
require('includes/functions.php');
require ('includes/head_include.php'); ?>
<title>Make a payment</title>
<link rel="stylesheet" href="css/event_card.css" />

<style type="text/css">
body { padding: 0px; } 

.event_card_holder {
    height: 220px;
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

.content p span.phrase {
    text-transform: none;
}

body div.event_card { 
    border: 1px solid #004fa0;
    border-top: 2px solid #EE3631; 
}

body #hold_classes { 
padding: 0 0 0 30px; 
overflow: visible;

}

/*
* moved to css/event_card.css */
/*
body .confirmed, body .unconfirmed, body .event_slot { border: 0px solid #666666; border-top: 1px solid #666666; padding: 4px; }
*/

</style>

<script type="text/javaScript">

var leader_filter = {
    filter_on: function() {
        // turn on the filter
        /* so the logic is that it's going to track what's clicked. If nothing is clicked 
         * show all teachers. If something is clicked, only show that 
        */
         var n = $("#leader_boxes input:checked");
         var i = 0;
         if(n.length > 0) {
             // hide all of 'em
             $('.event_card_holder').hide();
             while(i < n.length) {
                // console.log(n[i].id+" checked");
                var lead_by_id = n[i].id.split('_')[1];
                console.log(lead_by_id);
                i++;
                $('.led_by_'+lead_by_id).show();
             }
         } else {
             $('.event_card_holder').show();
         }
    },
    uncheck_leaders: function() {
        console.log('totally unchecked those guys');
        $("#leader_boxes input").attr('checked', false);
        $("#leader_boxes input").button('refresh');
    }
}

/* copied to js/main.js */
var participantData = {
    blarg: function() {
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=get_participants_json",
            dataType: "json",
            success: function(result) {
                participantData.buildParticipantInterface(result);
                // no success on this one
            }
        });
    },
    getAllParticipants: function() {
        console.log('lost again');
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
                // eventManipulation.getEvents('participant_id', ui.item.participant_id, $('#classSelect').val())
                eventManipulation.getEvents('participant', ui.item.participant_id, $('#classSelect').val())
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
            console.log('running view_classes_login_welcome from inside view_classes');
            login.view_classes_login_welcome('login_holder');
            var projects = participantData.blarg();  // do you ... use this?
});
</script>
</head>
<body id="view_classes_bd">

        <div id="login_holder" class="content"> </div>
</body>
</html>
