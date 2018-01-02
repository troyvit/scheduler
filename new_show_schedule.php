<?php 
require('includes/config.php');
require('includes/functions.php');
require('includes/language.php'); // I will regret this
require ('includes/head_include.php'); ?>
<title>View and modify schedule</title>
<link rel="stylesheet" href="css/weekly.css" />
<link rel="stylesheet" href="js/reveal/reveal.css" />

<style type="text/css"  media="print">
.schedule_nav, #hold_schedule, #hold_modal, #closehds { display: none; }
#hold_daily_schedule select { display: none; }
</style>

<script type="text/javascript" src="js/reveal/jquery.reveal.js"></script>

<script type="text/javascript" src="./js/clipboard.min.js"> </script> <!-- clibpord js thing -->

<script type="text/javascript">

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
        var data="action=daily_schedule&day="+day+"&leader_toshow="+leader_id;
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
                $('.ped_meta').click(function(e) { // this shit ... not even used. it talks to classes on another friggen screen.
                    /*
                    console.log(this.id);
                    var ped_meta_arr=this.id.split('_');
                    var ped_id = ped_meta_arr[2];
                    console.log(ped_id);
                    var to_hide='#ped_meta_show_'+ped_id;
                    var to_show = '#edit_ped_meta_container_'+ped_id;
                    $(to_show).show();
                    $(to_hide).hide();
                    $('.edit_ped_meta').blur(function(e) {
                        var ped_meta_arr=this.id.split('_');
                        var ped_meta = $('#edit_ped_meta_'+ped_id).val();
                        console.log('we are updating for '+ped_id);
                        // ajax call here
                        console.log('ped_meta is '+ped_meta);
                        $.ajax({
                            url: "includes/rpc.php",
                            type: "POST",
                            data: 'action=update_ped_meta&ped_meta='+ped_meta+'&ped_id='+ped_id,
                            dataType: "json",
                            success: function(result) {
                                // hide the text box and show the text
                                console.log('and we are returned ');
                                console.log(result.ped_meta);
                                $('#ped_meta_show_'+ped_id).html(result.ped_meta);
                                $(to_hide).show();
                                $(to_show).hide();
                            }
                        });
                    });
                    */
                });

            }
        });
    },
    clearDaily: function (day) {
    },

    dailyDetail: function() {
        // this function looks for a detail_hover div and acts if it detects a hover
        $('.detail_hover').hover(function(e) {
            console.log('you hit detail_hover');
            // https://www.youtube.com/watch?v=WBupia9oidU
            var active_event_type = $(this).attr('active_event_type');
            var active_daytime_id = $(this).attr('active_daytime_id');
            var idToShow          = $(this).attr('parent_holder'); // make sure this is showing the right thing for group vs private
            var oldParentId       = $(this).parent().attr('id'); // box
            console.log('up here we have an aet of '+active_event_type+' and a daytime of '+active_daytime_id+' and an idtoshow of '+idToShow+' and an oldparentid of '+oldParentId);
            $.ajax({
                url: "includes/rpc.php",
                type: "POST",
                data: 'action=event_details&active_event_type='+active_event_type+'&active_daytime_id='+active_daytime_id,
                dataType: "html",
                success: function(result) {
                   // example //  $('#hold_daily_schedule').html(result);
                    console.log('success, now trying to show '+idToShow);
                    console.log('hiding '+oldParentId);
                    $('#'+oldParentId).hide();
                    $('#'+idToShow).html(result);
                    $('#'+idToShow).show();
                    dailySchedule.dailyDetail();
                    $('#'+idToShow).mouseleave(function() {
                        console.log('moused out of '+idToShow);
                        $('#'+idToShow).hide();
                        console.log('hid '+idToShow);
                        var parentId = $(this).parent().parent().attr('id'); // remov ethis teeroy
                        $('#'+parentId+' div.event_daily_display').show();
                        console.log('showing all the stuff again for the divs belonging to '+parentId);
                    });
                }
            });
        });
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
                    console.log(this.id);
                    if(this.id == 'add_private_event') {
                        if($('#private_participant').val().length==0) {
                            alert('adding with no participant');
                        }
                    }
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
            var theData="action=load_schedule&class_id="+class_id+"&phase=2";
        }
        if(this.id == 'scheduleButton') {
            var private_classes=true;
            var start=$('#week_start').val();
            var end=$('#week_end').val();
            var theData="action=load_schedule&sched_type=date_range&start="+start+"&end="+end+"&phase=2";
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
                        var private_end   =  $('#week_end').val();
                        modalData=modalData+"&private_classes=true&private_start="+private_start+"&private_end="+private_end;
                        participantData.participantSearchFieldLocation='private_participant';

                    }
                    modalActivator.openModal(modalData);
                 });
                $('.event_daily_display').hover(function(e) {
                    // get parent id
                    var parentId = $(this).parent().parent().attr('id');
                    var sfsId = $(this).attr('sfs_id');
                    console.log(sfsId);
                    $('#'+parentId+' div.event_daily_display').hide();
                    var theId = this.id;
                    var fullClassName=$(this).attr('class');
                    if(fullClassName.indexOf('private_event') > 0) {
                        var idToShow = $(this).attr('sfs_id');
                        idToShow='detail_'+idToShow;
                        var id_hash_arr=this.id.split(':');
                        var active_daytime_id = id_hash_arr[1];
                        var active_event_type = 'private';
                    }  else {
                        var active_event_type = 'group';
                        var event_id=this.id;
                        var idToShow = 'detail_'+event_id;
                        var active_daytime_id = $(this).find(".cascade_edt_id").val();
                    }


                    $.ajax({
                        url: "includes/rpc.php",
                        type: "POST",
                        data: 'action=event_details&active_event_type='+active_event_type+'&active_daytime_id='+active_daytime_id,
                        dataType: "html",
                        success: function(result) {
                           // example //  $('#hold_daily_schedule').html(result);
                            console.log('trying to show '+idToShow);
                            $('#'+idToShow).html(result);
                            $('#'+idToShow).show();
                            $('#'+idToShow).mouseleave(function() {
                                console.log('moused out of '+idToShow);
                                $('#'+idToShow).hide();
                                console.log('hid '+idToShow);
                                $('#'+parentId+' div.event_daily_display').show();
                                console.log('showing all the stuff again for the divs belonging to '+parentId);
                            });

                            dailySchedule.dailyDetail();



                            // $('#privateNoteEdit').val('Note saved'); // I will never know the future-troy that will regret this
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
<span id="phase2">
<?php require ('includes/templates/admin_nav.php'); ?>
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
</span>
</body>
</html>
