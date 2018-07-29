<?php 
require('includes/config.php');
require('includes/functions.php');
require('includes/language.php'); // I will regret this
require ('includes/head_include.php'); ?>
<title>View and modify schedule</title>
<link rel="stylesheet" href="css/weekly.css" />
<link rel="stylesheet" href="js/reveal/reveal.css" />

<style type="text/css"  media="print">
/* fixes for printing */
#hold_daily_schedule { overflow: visible; height: 100%; }

#hold_daily_schedule .daily_login a { color: #000000; text-decoration: none; }
/* #hold_daily_schedule .daily_box  { font-size: 24px; } */ /* this just doesn't work */
/* #hold_daily_schedule .daily_box * { font-size: 24px; } */ /* this works for anything that has a tag inside .daily_box */


/* things to hide while printing */
.schedule_nav, #hold_schedule, #hold_modal, #closehds { display: none; }
#hold_daily_schedule select { display: none; }
.daily_location, .daily_notes { display: none !important; }
span.phoneitem { display: none; }


/* things to unhide while printing */
body {
    font-size: 16px;    /* or pt if you want */
}

#hold_daily_schedule .daily_box { font-size: 250%; } 

h2 { color: #ff0000; }


</style>
<style type="text/css">



#hold_daily_schedule .daily_box { font-size: 14px; } 
</style>


<script type="text/javascript" src="js/svg-injector.js"></script>
<script type="text/javascript" src="js/reveal/jquery.reveal.js"></script>

<script type="text/javascript" src="./js/clipboard.min.js"> </script> <!-- clibpord js thing -->

<script type="text/javascript">

/* get icons */
var jsConfigs = {
    rpc: 'includes/rpc.php'
}

var scheduleManipulator = {
    scootSchedule: function(arrowButton, distance) {
        var of=$('#schedule').offset();
        var newleft= [ of.left + 250];
        var newright= [ of.left - 250];
        if(arrowButton.id=='goLeft') {
            $('#schedule').animate({ left: newleft }, 250, function() { stop();
                console.log('moved it from '+of.left+' to '+newleft);
            });
        }
        if(arrowButton.id=='goRight') {
            $('#schedule').animate( { left: newright }, 250, function() { stop();
                console.log('moved it from '+of.left+' to '+newleft);
            });
        }
        console.log('scooted');
    }
}

var dailySchedule = {
    day: '',
    showDaily: function (day, leader_id) {
        // this is the function called when you click the printer icon
        var class_id=$('#classSelect').val();
        var data="action=daily_schedule&class_id="+class_id+"&day="+day+"&leader_toshow="+leader_id;
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: data,
            dataType: "html",
            success: function(result) {
                $('#hold_daily_schedule').html(result);
                // $('#hold_daily_schedule').css({"display":"block"})
                $('#hold_daily_schedule').show();
                //  $(this).css({"list-style-type":"", "color":""});
                $('#closehds').click(function(e) {
                    $('#hold_daily_schedule').hide();
                });
                    
                $(document).keydown(function(event) { 
                    if (event.keyCode == 27) { 
                        $('#hold_daily_schedule').hide();
                    }
                });

                $('#filter_leader').change(function(e) {
                    var leader_id = $('#filter_leader').val();
                    dailySchedule.showDaily(dailySchedule.day, leader_id);
                });
                $('.edt_meta').click(function(e) { 
                    console.log('clicked on '+this.id);
                    var edt_meta_arr=this.id.split('_');
                    var edt_id = edt_meta_arr[2];
                    console.log(edt_id);
                    var to_hide='#edt_show_'+edt_id;
                    var to_show = '#edt_edit_'+edt_id;
                    console.log('showing '+to_show+' and hiding '+to_hide);
                    $(to_show).show();
                    $(to_hide).hide();
                    $('#edt_cancel_'+edt_id).mouseup(function(e) {
                        $.ajax({
                            url: "includes/rpc.php",
                            type: "POST",
                            data: 'action=get_edt_meta&&edt_id='+edt_id,
                            dataType: "json",
                            success: function(result) {
                                // alert(result.edt_meta);
                                $('edt_edit_text_'+edt_id).val(result.edt_meta);
                                $('edt_show_'+edt_id).text(result.edt_meta);
                                $(to_show).hide();
                                $(to_hide).show();
                            }
                        });
                        e.stopPropagation();
                    });
                    $('#edt_update_'+edt_id).mouseup(function(e) {
                        var edt_meta_arr=this.id.split('_');
                        var edt_meta = $('#edt_edit_text_'+edt_id).val();
                        console.log('we are updating for '+edt_id);
                        // ajax call here
                        $.ajax({
                            url: "includes/rpc.php",
                            type: "POST",
                            data: 'action=update_edt_meta&edt_meta='+edt_meta+'&edt_id='+edt_id,
                            dataType: "json",
                            success: function(result) {
                                // hide the text box and show the text
                                // success, make sure what was saved is what was shown
                                // yeah turning over a new leaf
                                // gave up on that pretty quick.
                                $('#edt_edit_text_'+edt_id).val(result.edt_meta);
                                $('#edt_show_'+edt_id).text(result.edt_meta);
                                // edt_show_77091
                                console.log("set "+edt_id+" to "+result.edt_meta);
                                $(to_show).hide();
                                $(to_hide).show();
                                /*
                                $.ajax({
                                    url: "includes/rpc.php",
                                    type: "POST",
                                    data: 'action=get_edt_meta&&edt_id='+edt_id,
                                    dataType: "json",
                                    success: function(result) {
                                        alert(to_show);
                                    }
                                });
                                 */
                                /*
                                $('#edt_meta_show_'+edt_id).html(result.edt_meta);
                                $(to_hide).show();
                                $(to_show).hide();
                                 */
                            }
                        });
                        e.stopPropagation();
                    });
                });

            }
        });
    },
    clearDaily: function (day) {
    }
}

var modalActivator = {
    participant_search: true,
    openModal: function( data ) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: data,
            dataType: "html",
            success: function(result) {
                // $("#event_type").append(cb);
                if(modalActivator.participant_search==true) {
                    participantData.search_field='private_participant';
                    participantData.result_field='private_participant_id';
                    var param_string='';
                    participantData.returnParticipantData(param_string).done(participantData.participantAutoComplete);
                    var add_private=true;
                    $('#hold_modal').html(result);
                    $('#myModal').tabs();
                    $('#day_boxes').buttonset();
                    $('#myModal').reveal();
                }
                // this is wrong and bad. I should use a callback for it.
                $('#private_start').datepicker({ dateFormat: "yy-mm-dd" });
                $('#private_end').datepicker({ dateFormat: "yy-mm-dd" });
                $('#ped_meta').click(function(e) {
                    $('#privateNoteEdit').val('Save note edit'); // I will never know the future-troy that will regret this
                });
                var et_json = $('#et_json').val();
                var et_json_decoded = decodeURIComponent((et_json+'').replace(/\+/g, '%20'));
                var et_obj = JSON.parse(et_json_decoded);

                console.log('our json is '+et_obj);
                console.log(et_json);
                for(var someId in et_obj) {
                    var cur_obj = et_obj[someId];
                    console.log('someId is '+someId);
                }

                var pretendObj = et_obj[6];
                console.dir(pretendObj);
                $('#event_type').change(function(e) {
                    console.log('event_type is changed. it is now '+$('#event_type').val());
                    var cur_et_id = $('#event_type').val();
                    console.log('and taking a stab at it we are dealing with '+et_obj[cur_et_id]+' which is a part of ');
                    var et_activity_level = et_obj[cur_et_id].et_activity_level;
                    if(et_activity_level == 2) {
                        console.log('it is a private class');
                        $('#number_participants').val(1);
                        $('#duration').val(10);
                        $('#location_id').val(1);
                        $('#occurance_rate').val('daily');
                    }
                });

                $('#privateNoteEdit').click(function(e) {
                    console.log('you are where you think you are');
                    var ped_meta_id = $('#ped_meta_id').val();
                    var ped_meta    = document.getElementById('ped_meta').value; // yeah so kinda screw you jquery
                    var ped_meta_id = document.getElementById('ped_meta_id').value; 
                    console.log('we are editing '+ped_meta_id+' with '+ped_meta);
                    $.ajax({
                        url: "includes/rpc.php",
                        type: "POST",
                        data: 'action=update_ped_meta&ped_meta='+ped_meta+'&ped_meta_id='+ped_meta_id,
                        dataType: "json",
                        success: function(result) {
                            $('#privateNoteEdit').val('Note saved'); // I will never know the future-troy that will regret this
                            // hide the text box and show the text
                            /*
                            console.log('and we are returned ');
                            console.log(result.ped_meta);
                            $(to_hide).show();
                            $(to_show).hide();
                             */
                        }
                    });
                });
                $('.classManipulatorButton').click(function(e) {
                    $('#action').val(this.id); // I keep wanting to say how I hate this
                    // Thanks Past-Troy. I hate trying to track down wtf is going on when I need to figure it out.
                    // That's ok past-future-troy, I'm getting quicker at it. This time it was only 4 minutes or so
                    // And only 2 minutes this time. I'm starting to remember this idiotic conversation
                    // hahaha it's been so long that when I started hunting for this I actually thought (mistakenly) that this was kind of clever
                    if(this.id=='add_class') {
                        add_private=false;
                    }
                    console.log('the val of action is '+this.id+' and add_private is '+add_private);
                    if(add_private == undefined || add_private == false) {
                        var triggerButton='classButton';
                    } else {
                        var triggerButton='scheduleButton';
                    }
                    
                    // debug // 
                    var tosend=$('#classManipulator').serialize();
                    console.log('id is '+this.id);
                    if(this.id == 'add_private_event') {
                        if($('#private_participant').val().length==0) {
                            alert('adding with no participant');
                        }
                    }
                    console.log('we are about to say what we are gonna send');
                    console.log(tosend);
                    $.ajax({
                        url: "includes/rpc.php",
                        type: "POST",
                        data: tosend,
                        dataType: "html",
                        success: function(result) {
                            // close modal
                            $(".close-reveal-modal").trigger('click');
                            $("#"+triggerButton).trigger('click');
                        }
                    });
                });


            }
        });
    }
}

$(document).ready(function() {
    var mySVGsToInject = document.querySelectorAll('.iconic-sprite');
    SVGInjector(mySVGsToInject);

    $('#week_start').datepicker({ dateFormat: "yy-mm-dd" });
    $('#week_end').datepicker({ dateFormat: "yy-mm-dd" });
    var class_id='';
    /* load all classes into a json array so you can build a select */
    $.ajax({
        url: "includes/rpc.php",
        type: "POST",
        data: "action=get_all_classes",
        dataType: "json",
        success: function(result) {
            // $("#classSelect option").remove();
            var selected = '';
            cb = '<option value="">Select a class schedule</option>';
            $.each(result, function(i,data){
                // cb+='<option value="'+data.id+'">'+data.description+'<option/>';
                if(data.default == 'true') {
                    selected = ' SELECTED ';
                } else {
                    selected = '';
                }
                cb+='<option '+selected+' value="'+data.id+'">'+data.name+'</option>';
            });
            $("#classSelect").append(cb);
            // debug // $('#selectClass').html(result);
        }
     });
    $('.scheduleActivator').click(function(e) {
        // var class_id=this.value;
        if(this.id == 'classButton') {
            var class_id=$('#classSelect').val();
            var theData="action=load_schedule&class_id="+class_id;
        }
        if(this.id == 'scheduleButton') {
            var private_classes=true;
            var start=$('#week_start').val();
            var end=$('#week_end').val();
            var theData="action=load_schedule&sched_type=date_range&start="+start+"&end="+end;
        }
        $.ajax({
            url: "includes/rpc.php",
            type: "POST",
            data: theData,
            dataType: "html",
            success: function(result) {
                $('.scrollArrow').show();
                $('#scroll_nav_title').show();
                $('#scroll_nav_holder').show();
                $('#goRight').mouseup(function(e) {
                    scheduleManipulator.scootSchedule(this, 430);
                });
                $('#goLeft').mouseup(function(e) {
                    scheduleManipulator.scootSchedule(this, 430);
                }); // riding wild
                // populate the page with a schedule
                $('#hold_schedule').html(result);

                // load the printing function
                $('.print_day').click(function(e) {
                    console.log('you clicked me man');
                    var day = this.id.replace('print_', '');
                    dailySchedule.day=day;
                    dailySchedule.showDaily(day, '');
                });

                // load up the modal
                $('.time_row').click(function(e) {
                    // debug // alert('you clicked me');
                    e.preventDefault();
                    $('#dateTime').val(this.id);
                    $('#classId').val($('#classSelect').val());
                    var class_id=$('#classSelect').val();
                    var date_time=this.id;
                    var modalData= "action=get_modal&class_id="+class_id+"&date_time="+date_time;
                    if(private_classes==true) {
                        modalActivator.participant_search=true;
                        var private_start =  $('#week_start').val();
                        // not sure why this is here // var private_end   =  $('#week_end').val();
                        // because javascript shits itself if private_end is undefined
                        var private_end   =  $('#week_end').val();
                        modalData=modalData+"&private_classes=true&private_start="+private_start+"&private_end="+private_end;
                        participantData.participantSearchFieldLocation='private_participant';

                    }
                    modalActivator.openModal(modalData);
                 });
                $('.event_daily_display').click(function(e) {
                    // check to see if they clicked on a group event or a private event
                    var ndateTime=$(this).parent().parent().attr("id");
                    $('#dateTime').val(ndateTime); // used in myModal

                    var fullClassName=$(this).attr('class');
                    if(fullClassName.indexOf('private_event') > 0) {
                        // private class
                        var id_hash_arr=this.id.split(':');
                        var private_event_id=id_hash_arr[0];
                        var private_event_daytime_id=id_hash_arr[1];
                        var action = 'get_private_event';
                        var private_start =  $('#week_start').val();
                        var private_end   =  $('#week_end').val();
                        var modalData="action=get_private_event&private_start="+private_start+"&private_end="+private_end+"&private_event_id="+private_event_id+"&private_event_daytime_id="+private_event_daytime_id+"&date_time="+ndateTime; // just ... WOW. 
                        participantData.participantSearchFieldLocation='private_participant';
                        modalActivator.participant_search=true;
                    } else {
                        var event_id=this.id;
                        var edt_id = $(this).find(".cascade_edt_id").val();
			console.log(edt_id);
                        var modalData="action=get_event&event_id="+event_id+"&date_time="+ndateTime+"&edt_id="+edt_id; 
                    }
                    /* var modalData= "action=get_modal&class_id="+class_id+"&date_time="+ndateTime; */
                    modalActivator.openModal(modalData);
                    e.stopPropagation();
                });
            }
        });
    });

        $('#classSelect').change(function(e) {
        // alert($('#classSelect').val());
        class_id=$('#classSelect').val();
    });

    var btn = document.getElementById('emailCopybutton');
    var clipboard = new Clipboard(btn);

});
</script>
</head>
<body id="show_schedule">
<?php require ('includes/templates/admin_nav.php'); ?>
<!-- icon sprite -->
<img src="images/sprite/open-iconic.svg" class="iconic-sprite" style="display:none;" />

<!--
<svg viewBox="0 0 8 8" class="icon">
    <use xlink:href="#check" class="check"></use>
</svg>


<svg viewBox="0 0 8 8" class="icon">
    <use xlink:href="#ban" class="ban"></use>
</svg>
-->

<table class="schedule_nav"><tr>
<td>
<h3><?php echo $sl -> gp('Select a class to work with a group schedule'); ?></h3>
</td>
<td>
<h3><?php echo $sl -> gp('Pick a date range to work with private lessons'); ?></h3>
</td>
<td id="scroll_nav_title">
    <h3><?php echo $sl -> gp('Scroll through the schedule using the arrows below'); ?></h3>
</td>
</tr>
<tr>
<td>
    <select id="classSelect"></select>
    <input type="button" class="scheduleActivator" id="classButton" value="Go!">
</td>
<td>
    <label for="week_start"><?php echo $sl -> gp('Start'); ?></label>
    <input type="text" name="week_start" id="week_start" value="<?php echo $_GET['week_start']; ?>">
    <label for="week_end"><?php echo $sl -> gp('End'); ?></label>
    <input type="text" name="week_end" id="week_end" value="<?php echo $_GET['week_end']; ?>">
    <input type="button" class="scheduleActivator" id="scheduleButton" value="Go!">
</td>
<td id="scroll_nav_holder">
    <input type="button" class="scrollArrow" id="goLeft" value="<">
    <input type="button" class="scrollArrow" id="goRight" value=">">
</td>
</tr>
</table>

<div id="hold_schedule">
</div>
<div id="hold_modal">
</div>
<div id="hold_daily_schedule"> </div>

</body>
</html>
