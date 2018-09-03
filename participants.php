<?php 
require('includes/config.php');
require('includes/functions.php');
require('includes/language.php'); // I will regret this
require ('includes/bootstrap_include.php'); 
?>
    <title>Student CRM</title>
    <style>
    #project-label {
        display: block;
        font-weight: bold;
        margin-bottom: 1em;
    }
    #project-description {
        margin: 0;
        padding: 0;
    }
    </style>
    <script>

var participantData = {
    focusedField: {"name":"test","value":"the test value"}, // do I use this? // yeah and it's kinda rad
    ps_group_hash: '',
    getParticipantsByLogin: function(login_id) {
        login_id=loginData.login_id; // don't ask
        console.log('inside getParticipantsByLogin we have a login of '+login_id);
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=get_participants_by_login&login_id="+login_id,
            dataType: "html",
            success: function(result) {
                $('.reset_waiver_status').hide(); // because I'm too lazy to edit a .css file
                // alert('did '+participant_id+' into '+event_id);
                // var obj = jQuery.parseJSON(result);
                // alert(result);
                $('#login_participants').html(result);
                console.log($('.participant_section_group'));
                /* just testing because shit was getting weird 
                $.each($('.participant_section_group'), function (index, value) {
                    console.log(this.id);
                    $('#'+this.id).change(function() {
                        alert("don't go changin'")
                    });
                });
                 */

                $('.participant_section_group').change(function() {
                    var ftc='for teh console. get it?!? G$H IT?!? ID is '+this.id+' and vlue is '+this.value+' and that\'s all she rote';
                    var waiver_id = '';
                    console.log(ftc);
                    // participant_id, reg_id, section_group_id, waiver id
                    var sg_arr = this.value.split(":");
                    var participant_id     = sg_arr[0];
                    var reg_login_id       = sg_arr[1];
                    var reg_id             = sg_arr[2];
                    var section_group_id   = sg_arr[3];
                    waiver_id              = sg_arr[4];
                    if(waiver_id.length > 0) {
                        $('.reset_waiver_status').show();
                        $('.reset_waiver_status').attr("id", 'waiver:'+waiver_id);
                        console.log('just set the id of reset_waiver_status to waiver:'+waiver_id);
                    } else {
                        $('.reset_waiver_status').hide();
                    }
                    participantData.updateParticipantSectionGroup (participant_id, section_group_id, reg_login_id, reg_id);
                });

                $('.reset_waiver_status').click(function() {
                    console.log('you totally clicked me');
                    var waiver_arr = this.id.split(":");
                    var participant_waiver_id = waiver_arr[1];
                    $.ajax({
                        url: jsConfigs.rpc,
                        type: "POST",
                        data: "action=update_waiver_by_id&participant_waiver_id="+participant_waiver_id+"&field_name=waiver_status&field_val=3",
                        dataType: "json",
                        success: function(result) {
                            console.log('we are getting particpants for '+login_id);
                            participantData.getParticipantsByLogin(login_id);
                        }
                    });
                });

                $('.participant_calendar').datepicker({ 
                    dateFormat: "yy-mm-dd" ,
                    changeMonth: "true",
                    changeYear: "true",
                    onClose: function() {
                        var fieldName=this.name;
                        var fieldValue=$('#'+this.id).val();
                        console.log(' inside datepicker we blurred '+fieldName+' and its id is '+this.id+' and its value is '+fieldValue);
                        if(participantData.focusedField.name == fieldName) {
                            if(this.id != 'dob_add') {
                                if(participantData.focusedField.value != fieldValue) {
                                    var participant_id = this.id.replace(fieldName+'_', '');
                                    participantData.updateParticipantField(fieldName, fieldValue, participant_id);
                                }
                            }
                        }
                    }
                }).bind('blur', function (event) { console.log('we really did blur and the id is '+this.id); 
                console.log('and the value is '+$('#'+this.id).val());

                $('.participant_section_group').change(function() {
                    console.log('we are changing');
                    console.log(this.id);
                    });
                });
                $('.add_participant').click(function(e) {
                    // var login_id = this.id.replace('add_participant_', ''); // not used but good to know for the update haha
                    // var login_id = $('#login_id').val(); // just debugging EH?
                    var login_id = loginData.login_id;
                    var fname=$('#fname_add').val();
                    var lname=$('#lname_add').val();
                    var dob=$('#dob_add').val();
                    participantData.insertParticipant(fname, lname, dob, loginData.login_id);
                    // insertParticipant also needs to add the participant to the registration. then I can include the registration button.
                    $( "#view_registration" ).show(); // show the registration since you found a record
                });
                $('.remove_participant').click(function(e) {
                    var participant_id = this.id.replace('del_participant_', '');
                    var login_id = loginData.login_id;
                    participantData.deleteParticipant(login_id, participant_id);
                });
                $('.participant_field').focus(function(e) {
                    participantData.focusedField.name=this.name;
                    participantData.focusedField.value=$('#'+this.id).val();
                    console.log(participantData.focusedField.name);
                });
                $('.participant_field').blur(function(e) {
                    // aww hell I still use this
                    var fieldName=this.name;
                    var fieldValue=$('#'+this.id).val();
                    console.log('we blurred '+fieldName+' and its id is '+this.id+' and its value is '+fieldValue);
                    if(participantData.focusedField.name == fieldName) {
                        if(participantData.focusedField.value != fieldValue) {
                            var participant_id = this.id.replace(fieldName+'_', '');
                            participantData.updateParticipantField(fieldName, fieldValue, participant_id);
                        }
                    }
                });
                // [abc123] Add an onChange for participant_section_group for updating which registration the participant needs to fill out
                // it needs to generate a participant_section_group_hash that the register button will pick up and send to the registration form.
                editing.makeEditable ('editable'); // registration cost uses the new field editing js, not this old crusty participant stuff that I need to remove
            }
        });
    },
    updateParticipantField: function (fieldName, fieldValue, participant_id) {
        console.log('tryint to updateParticipantField on '+fieldName);
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=update_participant&participant_id="+participant_id+"&field_name="+fieldName+"&field_val="+fieldValue,
            dataType: "html",
            success: function(result) {
                var special_login_id=loginData.login_id;
                participantData.getParticipantsByLogin($( special_login_id ));
            }
        });
    },
    insertParticipant: function(fname, lname, dob, login_id) {
        if(login_id =='') {
            console.log('no login id, but loginData.login_id is '+loginData.login_id);
        }
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=insert_participant&login_id="+login_id+"&fname="+fname+"&lname="+lname+"&dob="+dob,
            dataType: "json",
            success: function(result) {
                // alert('did '+participant_id+' into '+event_id);
                // var obj = jQuery.parseJSON(result);
                // alert(result);
                console.log(" action=insert_participant&login_id="+login_id+"&fname="+fname+"&lname="+lname+"&dob="+dob);
                // $('#login_participants').html(result);
                participantData.getParticipantsByLogin(login_id);
                $( "#view_registration" ).show(); // show the registration since you found a record
                $( "#view_registration").click(function(e) {
                    // document.location.href='registration.php?login_id='+result.enc_id;
                    document.location.href='new_registration.php?login_id='+result.enc_id;
                });
            }
        });
    },
    deleteParticipant: function (login_id, participant_id) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=remove_login_participant&login_id="+login_id+"&participant_id="+participant_id+"&perm=true",
            dataType: "html",
            success: function(result) {
                // alert('did '+participant_id+' into '+event_id);
                // var obj = jQuery.parseJSON(result);
                // alert(result);
                // $('#login_participants').html(result);
                participantData.getParticipantsByLogin(login_id);
            }
        });

    },

    updateParticipantSectionGroup: function (participant_id, section_group_id, reg_login_id, reg_id) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            data: "action=update_participant_section_group&participant_id="+participant_id+"&section_group_id="+section_group_id+"&reg_login_id="+reg_login_id+"&reg_id="+reg_id,
            dataType: "html",
            success: function(result) {
                // alert('booyah!');
                // excessive but it would ensure the user would see if the change wasn't made // participantData.getParticipantsByLogin(login_id);
            }
        });
    }
}

var loginData = {

    login_id: '',
    getSingleLogin: function(login_id) {
    },
    getAllLogins: function() {
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=get_logins_json",
            dataType: "json",
            success: function(result) {
                // alert('did '+participant_id+' into '+event_id);
                // var obj = jQuery.parseJSON(result);
                // alert(result);
                loginData.buildLoginInterface(result);
                
            }
        });
    },
    getAllParticipants: function() {
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=get_participants_json",
            dataType: "json",
            success: function(result) {
                // alert('did '+participant_id+' into '+event_id);
                // var obj = jQuery.parseJSON(result);
                // alert(result);
                loginData.buildParticipantInterface(result);
                
            }
        });

        // return projects;
    },

    gatherLoginData: function ( login_obj ) {
        var login_id=login_obj.login_id;
        loginData.login_id=login_obj.login_id
        console.log('gathering login data for '+login_id+' which came from '+login_obj);
        participantData.getParticipantsByLogin(login_obj.login_id);
    },

    buildParticipantInterface: function(serializedParticipants) {
        console.log('you found me at '+serializedParticipants);
        console.log('participant search is '+$( "#participant_search" ));
        $( "#participant_search" ).autocomplete({
            minLength: 0,
            source: serializedParticipants,
            focus: function( event, ui ) {
                return false;
            },
            select: function (event, ui ) {
                console.log('select was fired for participant');
                // console.log('gathering login data for '+login_id+' which came from '+login_obj);
                var login_id = ui.item.login_id;
                var enc_id = ui.item.enc_id;
                console.log('up here enc_id is '+enc_id+' as a part of '+ui.item.enc_id+' which is the encoded version of '+ui.item.login_id);
                loginData.login_id=ui.item.login_id
                $.ajax({
                    url: jsConfigs.rpc,
                    type: "POST",
                    data: "action=get_logins_json&login_id="+login_id,
                    dataType: "json",
                    success: function(result) {
                        var wtf=JSON.stringify(result);
                        console.log('result is '+result+' and the wtf is '+wtf);
                        loginData.populateLoginData(result);
                        var enc_id = result.enc_id;
                        console.log('on success though we have an enc_id of '+enc_id);
                        $( "#view_registration" ).show(); // show the registration since you found a record
                        $( "#view_registration").click(function(e) {
                            // get all the for ids for each participant id
                            document.location.href='new_registration.php?login_id='+enc_id;
                        });
                    }
                });
                participantData.getParticipantsByLogin(login_id);
                console.log('found me: '+ ui.item.login_id);
            },
            response: function ( event, ui ) {
                if(ui.item == null) {
                    console.log('item for participant is null so we will give the option to add a new login');
                    $( "#add_login" ).show();
                } else {
                    console.log('part search is changed and the value of item is '+ui.item.value);
                }
            },
        })
    },

    populateLoginData: function (login_obj) {
        console.log('populating login data with stuff like '+login_obj.fname);
        $( "#login_fname" ).val(login_obj.fname);
        $( "#login_lname" ).val(login_obj.lname);
        $( "#login_email" ).val(login_obj.email);
        $( "#editable_login_id" ).val(login_obj.login_id); // ostensibly for debugging 
        $( "#login_loglevel" ).val(login_obj.log_level);
        console.log('setting loginData.login_id to '+login_obj.login_id);

        // change the name of the fields to something usable by the edit function
        // name='event_participant_billing|amount_due|pb_id|$pb_id'
        $( "#login_fname" ).attr("name", "login|fname|login_id|"+login_obj.login_id);
        $( "#login_lname" ).attr("name", "login|lname|login_id|"+login_obj.login_id);
        $( "#login_email" ).attr("name", "login|email|login_id|"+login_obj.login_id);
        $( "#login_loglevel" ).attr("name", "login|log_level|login_id|"+login_obj.login_id);
        $( "#login_password" ).attr("name", "login|password|login_id|"+login_obj.login_id);
    },

    buildLoginInterface: function(serializedLogins) { // what a bad function name
        // debug // console.log(loginData);
        $( "#login_search" ).autocomplete({
            minLength: 0,
            // source: projects,
            source: serializedLogins,
            /* autoFocus: true, */
            focus: function( event, ui ) {
                // $( "#login_search" ).val( ui.item.value );
                return false;
            },
            select: function( event, ui ) {
                loginData.populateLoginData (ui.item);
                /*
                $( "#login_fname" ).val(ui.item.fname);
                $( "#login_lname" ).val(ui.item.lname);
                $( "#login_email" ).val(ui.item.email);
                $( "#editable_login_id" ).val(ui.item.login_id); // ostensibly for debugging 
                $( "#login_loglevel" ).val(ui.item.log_level);
                // if things ever settle down you can try to replace the 2
                // lines below with loginData.gatherLoginData (which is a 
                // middling horrible name)
                console.log('setting loginData.login_id to '+ui.item.login_id);

                // change the name of the fields to something usable by the edit function
                // name='event_participant_billing|amount_due|pb_id|$pb_id'
                $( "#login_fname" ).attr("name", "login|fname|login_id|"+ui.item.login_id);
                $( "#login_lname" ).attr("name", "login|lname|login_id|"+ui.item.login_id);
                $( "#login_email" ).attr("name", "login|email|login_id|"+ui.item.login_id);
                $( "#login_loglevel" ).attr("name", "login|log_level|login_id|"+ui.item.login_id);
                $( "#login_password" ).attr("name", "login|password|login_id|"+ui.item.login_id);
                 */

                // uhhh wasn't I supposed to make a button for this?
                // nah keep it but you need to run it when you create a new participant too. or something.
                registration.genRegistration (ui.item.login_id);
                console.log('I want to run gatherLoginData now');
                loginData.gatherLoginData(ui.item);
                editing.makeEditable ('editable'); // remove?
                $( "#view_registration" ).show(); // show the registration since you found a record
                $( "#view_registration").click(function(e) {
                    // get all the for ids for each participant id
                    document.location.href='new_registration.php?login_id='+ui.item.enc_id;
                });
                return false;
            },
            response: function ( event, ui ) {
                // alert('we are responding');
                // this takes a really long time to run and slows down the rest of the script
                if( ui.item == null ) {
                    console.log('item is null so we will give the option to add a new login');
                    $( "#add_login" ).show();
                } else {
                    console.log('search is changed and the value of item is '+ui.item.value);
                }
            },
        })
    },
}

$(document).ready(function() {
    var projects = loginData.getAllLogins();
    var participant_projects = loginData.getAllParticipants();
    // var projects = loginData.getAllLogins();
    // you should move the interface below into a template
    // except you don't do that anywhere else
    loginManipulation.get_log_levels('login_loglevel');
    $('#add_login').click(function(e) {
        $( '#action' ).val('insert_login');
        var tosend = $( 'form input' ).serialize();
        loginManipulation.insert_login(tosend, loginData.gatherLoginData);
    });
});

</script>
<style type="text/css">
.content div label { width: 250px; }
.participant_status { display: none; }
</style>
</head>
<body>
<?php require ('includes/templates/admin_nav.php'); ?>
<div class="grid">
    <div class="col-12-12">
        <div class="content">
            <h3>Instructions for Login-search</h3>
            <p>
            <?php echo $sl->gp('login search'); ?>
            <ul class="headers">
                <li><?php echo $sl->gp('First Name'); ?></li>
                <li><?php echo $sl->gp('Last Name'); ?></li>
                <li><?php echo $sl->gp('Email'); ?></li>
            </ul>
            </p>
        </div>
    </div>
</div>
<form class="login" name="manage_login" id="manage_login">
<input type="hidden" id="action" name="action" value="">
<div>
</div>
<table id="login_edit">
    <tr>
        <td>
            <label class="participant_label" for="participant_search"><?php echo $sl->gp('Search by student'); ?></label>
        </td>
        <td colspan="5">
            <input name="participant_search" id="participant_search" type="text">
        </td>
    </tr>
    <tr>
        <td>
            <label style="width:100%" class="login_label" for="login_search"><?php echo $sl->gp('Search by login'); ?></label>
	</td>
        <td>
            <input name="login_search" id="login_search" type="text">
        </td>
    </tr>
	<tr>
		<td colspan="2"> <h3>Add new registrant</h3></td>
	</tr>
    <tr>
        <td>
            <label class="login_label" for="login_fname"><?php echo $sl->gp('First Name'); ?></label>
                <input type="text" class="login_class editable" name="login_fname" id="login_fname" />
        </td>
        <td>
                <label class="login_label" for="login_lname"><?php echo $sl->gp('Last Name'); ?></label>
                <input type="text" class="login_class editable" name="login_lname" id="login_lname" />
        </td>
        <td>
            <label class="login_label" for="login_email"><?php echo $sl ->gp('Email'); ?></label>
            <input type="text" class="login_class editable" name="login_email" id="login_email" />
            <input type="hidden" name="editable_login_id" id="editable_login_id" /><!-- not really needed but kept for debugging -->
        </td>
        <td>
                <label class="login_label" for="login_password"><?php echo $sl->gp('Password'); ?></label>
                <input type="text" class="login_class editable" name="login_password" id="login_password" />
        </td>
        <td>
                <label class="login_label" for="login_loglevel"><?php echo $sl->gp('Permissions'); ?></label>
                <select name="login_loglevel" class="login_class editable" id="login_loglevel"></select>
        </td>
        <td>
                <label class="login_label" for="add_login"></label>
                <input type="button" id="add_login" value="Add" />
        </td>
    </tr>
</table>
<div>
<input type="button" id="view_registration" style="display: none;" value="Registration" />
</div>
</form>
<div id="login_participants">
            <!-- <h3>participants go here</h3> -->
</div>
</body>
</html>
