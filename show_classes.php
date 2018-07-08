<?php 
require('includes/config.php');
require('includes/functions.php');
require('includes/language.php'); // I will regret this
require ('includes/head_include.php'); ?>
<title>Students and Classes</title>

<link rel="stylesheet" href="css/event_card.css" />

<script type="text/javascript">
var jsConfigs = {
    rpc: 'includes/rpc.php',
    epp: '16', // events per page
}

var makePrintable = {
    oldContainerClass: '',
    oldContainerHeight: '',
    makeClassesPrintable: function () {
        $('#hold_students').hide();
        makePrintable.oldContainerClass = $('#hold_class_container').attr('class');
        console.log('saved it as '+makePrintable.oldContainerClass);
        $('#hold_class_container').attr('class', 'col-1-1');
        $('#hold_class_container').css('overflow', 'visible');
        makePrintable.oldContainerHeight = $('#hold_classes').css('height');
        $('#hold_classes').css('height', '100%');
        $('#hold_classes').css('overflow', 'visible');
        $('#printButton').attr('value', 'Return to normal');
        $('#printButton').click(function(e) {
            makePrintable.makeClassesUnPrintable();
            $('#printButton').attr('value', 'Print');
            $('#printButton').click(function(e) {
                makePrintable.makeClassesPrintable();
            });
        });
    },
    makeClassesUnPrintable: function () {
        $('#hold_students').show();
        console.log('turning it back into '+makePrintable.oldContainerClass);
        // $('#hold_class_container').attr('class', makePrintable.oldContainerClass);
        $('#hold_class_container').attr('class', 'col-7-12');
        $('#hold_class_container').css('border-left', '12px solid green');
        $('#hold_classes').css('height', makePrintable.oldContainerHeight);
        $('#hold_class_container').css('overflow', 'auto');
    },
    tellContainerClass: function() {
        console.log(makePrintable.old_container_class);
    }
}

var dropShit = {
    makeEventsDroppable: function (element_name) {
        $('#hold_classes .event_slot').droppable({
            tolerance: "pointer",
            greedy: true,
            hoverClass: "ui-state-active",
            activeClass: "ui-state-hover",
            drop: function(event, ui) {
                $( this )
                    .addClass ( "ui-state-highlight event_slot_taken" )
                    .removeClass('event_slot');
                console.log('inside the drop the id is '+this.id);
                var justDropped=this.id;
                justMarried[listItem]=this.id;
                eventParticipants.eventParticipantInsert(listItem, this.id);
                var eid_arr=this.id.split('_');
                var event_id=eid_arr[1];
                // eventManipulation.getEvent(event_id, classFiltering.prepEvent);
                $('#'+listItem).css('display', 'none'); // hide the name you dragged?
                $('#student_search').val(''); // clear search field
                $.each(justMarried, function (key, val) {
                    console.log("key is "+key+" and val is "+val);
                });
            },
            out: function ( event, ui) {
                $( this )
                    .removeClass ("ui-state-highlight" )
                    console.log("moving out: "+event.target.id);
            }
        });
    },

    elementReenable: function (myEl) {
        // alert(myEl);
        $('#'+myEl).droppable("enable");
        $('#'+myEl).removeClass("event_slot_taken");
        $('#'+myEl).addClass("event_slot");
        // document.getElementById(myEl).html('<p>yes yes yes</p>');
    }
}

var justMarried={};

var setStyles = {
    setStudentStyles: function() {
        console.log('adding student styles');
        // set the styles of the student fonts
       $('#studentList li').addClass("ui-menu-item"); // delete
    }
}
var classFiltering = {
    prepEvent: function() {
        console.log('totally droppable now');
        dropShit.makeEventsDroppable($('.event_slot'));
    },
}

$(document).ready(function() {
    // tell the obj that grabs classes where to put them
    eventManipulation.html_loc='#hold_classes';

    var class_id=''; // wtf is this for anyway
    /* load all classes into a json array so you can build a select */
    $.ajax({
        url: jsConfigs.rpc,
        type: "POST",
        data: "action=get_class_nav",
        dataType: "html",
        success: function(result) {
            $('#class_select_content').html(result);
            $('#get_events_by_type').click(function(e) {
                eventManipulation.getEvents('event_type', $('#event_type').val(), $('#classSelect').val(), classFiltering.prepEvent);
            });
            $('#get_events_by_day').click(function(e) {
                eventManipulation.getEvents('day', $('#day_select').val(), $('#classSelect').val(), classFiltering.prepEvent);
            });
            $('.classLoadButton').click(function(e) {
                console.log('you are loading some classes');
                // load locations to filter by
                $('#location_boxes').show();

                // load the search form for participants
                $( "#studentSearchForm").show();
                participantData.getParticipantData('all');
            });
            $( '#printButton' ).click(function(e) {
                makePrintable.makeClassesPrintable();
            });
            $( "#classSelectTabs" ).tabs();
            // put location filter here
            $('#location_boxes').hide(); // don't show until the user might need to click them
            $('#location_boxes').buttonset();
            $('#location_boxes input').click(function(e) {
                console.log("filtering by "+this.id);
                event_card_filter.filter_on('located_in', 'location_boxes');
                // var response = $('label[for="' + this.id + '"]').html();
                /*
                console.log($('label[for="' + this.id + '"]').html());
                console.log($('label[for="' + this.id + '"]').text());
                 */
                var selected_location = $('label[for="' + this.id + '"]').text();
                $('#location_header').html(': ' + selected_location);
                // $('.class_name_print').append('<span id="location_header">: ' + selected_location + '</span>');
            });
            $("#filter_reset").click(function(e) {
                // $("#location_boxes input").attr("checked", false).button("refresh");
                event_card_filter.uncheck_filter_settings ('location_boxes');
            });
        }
     });
});
</script>
<style type="text/css">
    .event_day >h3  { float: left; }
    .event_time:before { float: left; content: ': '; margin-left: -1em; }
    #studentSearchForm { display: none; }
</style>
<style type="text/css"  media="print">
    /* body { margin: 12.5mm 12.5mm 12.5mm 12.5mm; } */
    body { 
        margin: 0mm 0mm 0mm 0mm; 
        margin-left: 0mm !important;
        margin-right -5mm !important;
        margin-top: 0mm !important;
        margin-bottom: 0mm !important;
    }
    .grid { margin: 0mm !important; padding: 0mm !important; }
    .navLeft { display: none; }
    .event_card { width: 4cm !important; height: 5cm; border: 1mm solid #000000 !important; }
    .page_break { display: block; page-break-before: always; }
    #hold_students { display: none; }
    #hold_classes { padding: 0mm !important; margin: 0mm; !important; }
    .empty_event { /* display: none;*/ }
    body .event_card .unconfirmed { font-weight: normal; }
    #main_nav { visibility: hidden; display: none; }
    @page { margin: 0; }
    .header, .hide { visibility: hidden; }
    h3.class_name_print { margin-top: 0cm; margin-bottom: 5mm; }
    .location_header { visibility: hidden; display: none; }
</style>
</head>
<body id="show_classes_bd">
<?php require ('includes/templates/admin_nav.php'); ?>
<div class="grid">
    <div class="col-4-12 navLeft" style="overflow: visible">
        <div id="class_select_content" class="content" style="overflow: visible"> </div>
        <div id="studentSearchForm">
        <h3><?php echo $sl->gp('To search'); ?></h3>
        <input id="student_search" type="text">
        <div id="studentList" class="content" style="overflow: visible"></div>
        </div>
    </div>
    <div id="hold_class_container" class="col-8-12">
        <div id="hold_classes" class="content"> </div>
    </div>
</div>
</body>
</html>
