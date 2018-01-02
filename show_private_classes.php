<?php 
require('includes/config.php');
require('includes/functions.php');
require('includes/language.php'); // I will regret this
require ('includes/bootstrap_include.php'); ?>

    <title>Private Class Schedule</title>
    <script type="text/javascript">

    var privateData = {
        getPrivateBlocks: function(peDates, card_type) {
            // get all private blocks
            // past-troy -- you just sound weirder than usual when it comes to private classes.
            $.ajax({
                url: "includes/rpc.php",
                type: "GET",
                data: "action=private_event_card&card_type="+card_type+"&pe_dates="+peDates,
                dataType: "html",
                success: function(result) {
                    console.log('card_type is '+card_type);
                    if(card_type == 'block') {
                        $('#card_holder').html(result);
                    }
                    if(card_type == 'week') {
                        $('#private_event_card').html(result);
                    }
                    $('#pecard_picker').change(function() {
                        var peDates = $('#pecard_picker').val();
                        privateData.getPrivateBlocks(peDates, 'week');
                    });
                }
            });
        },
        getPrivateLayout: function($range) {
            // not really sure what I"m going to do here.
        },
    }

    $(document).ready(function() {
        // READY TO GO
        $.ajax({
            url: "includes/rpc.php",
            type: "POST",
            data: "action=private_event_card",
            dataType: "html",
            success: function(result) {
                $('#card_holder').html(result);
                $('#pe_dates').change(function() {
                    var peDates = $('#pe_dates').val();
                    privateData.getPrivateBlocks(peDates, 'block');
                });
            }
        });
    });
    </script>
</head>
<body>
<div id="card_holder"></div>
</body>
</html>
