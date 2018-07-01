var jsConfigs = {
    rpc: 'includes/rpc.php'
}

var registration = {
    replaceString:/(^[\w_]+)([|])([\w_]+)([|])([\w_,\d]+)([|])([\w\d,]+)/,
    reg_login_id: '',
    submitSignature: function ( signature_field ) {
        console.log('submitSignature running');
        var fid=$('#'+signature_field);
        console.log('fido! '+fid);
        var fieldName=fid.attr("name");
        var fieldValue=fid.val();
        console.log('you are stuck on '+fieldName);
        if(fieldValue !='') {
            // no clearing signatures.
            var replaceString = registration.replaceString;
            // var replaceString=/(^[\w_]+)([|])([\w_]+)([|])([\w_,\d]+)([|])([\w\d,]+)/;
            // console.log(replaceString );
            var pre_edStr=fieldName.replace(replaceString, "1: $1 and 2: $2 and 3: $3 and 4: $4 and 5: $5 and 6: $6 and 7: $7");
            console.log('and so we will log this stringy thingy');
            console.log(pre_edStr);
            var update_target = fieldName.replace(replaceString, "$1");
            console.log('just checking because I thought yous aid the target was '+update_target);
            var dbFieldName   = fieldName.replace(replaceString, "$3");
            var idFieldName   = fieldName.replace(replaceString, "$5");
            var idFieldValue  = fieldName.replace(replaceString, "$7");
            editing.updateField(fid, update_target, dbFieldName, fieldValue, idFieldName,idFieldValue,false);
        } else {
            console.log('fieldValue is blank');
        }
    },

    resetWaiver: function (waiver_id) {
        // make a waiver editable again
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            dataType: "html",
            data: "action=update_waiver_status&reload_waiver=true&waiver_id="+waiver_id,
            success: function(result) {
                console.log(result);
                console.log(waiver_id);
                $('#waiver_container_'+waiver_id).html(result);
                editing.makeEditable ('editable');
                $('.waiver_tosign').click(function(e) {
                    var fieldToDisable = $(this).prev('input').attr('id');
                    registration.signWaiver(waiver_id, fieldToDisable);
                });
                // window.location.reload(true); 
            }
        });
    },
    signWaiver: function (waiver_id, fieldToDisable) {
        // ok this is messed up but the waiver name is updated as part of that massively convoluted function I have that updates waiver fields in general on blur. 
        // this function just seals the deal for the waiver, changing its status to 2 once the user clicks "sign"
        // honestly I have no clue how the waiver was getting assigned a status of 2 before now. Probably when they sign the registration or pay or something.
        // this thing also makes the field non-editable after you click the sign button. Then on subsequent reloads the waiver is done.
        // the only downside to this is that the waiver hasn't been paid for yet.
        // but that's what the payment signature is for
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            dataType: "json",
            data: "action=sign_waiver&waiver_id="+waiver_id,
            success: function(result) {
                console.log(result.status);
                if(result.status=="success") {
                    $('#'+fieldToDisable).attr('disabled', 'disabled');
                    // make the field uneditable
                }
            }
        });
    },
    checkRegistration: function ( login_id ) {
        // not used
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            dataType: "json",
            data: "action=check_register&reg_id=&login_id="+login_id,
            success: function(result) {
                console.log(result.status);
                if(result.status=="none") {
                    // generate a new reg
                    registration.genRegistration ( login_id );
                }
            }
        });
    },
    genRegistration: function ( login_id ) {
        // used from participants.php
        // generates a new registration button?
        console.log('gening a new registration');
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            dataType: "json",
            /* past-troy, why the hell did you leave reg_id blank */
            data: "action=gen_register&reg_id=&login_id="+login_id,
            success: function(result) {
                editing.makeEditable ('editable');
                console.log('and the result is');
                console.log(result);
                // this ... is wrong
            }
        });
    },
    newGetRegistration: function (reg_id, login_id, enabled) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            dataType: "html",
            data: "action=new_register&reg_id="+reg_id+"&login_id="+login_id,
            success: function(result) {
                console.log('enabled is '+enabled);
                $("#participant_reg_holder").html(result);
                login.activate_login_controls();
                // record signatures
                editing.makeEditable ('editable');
                $( ".register_radio" ).each(function ( index, button ) {
                    setTimeout(function () {
                        $(button).buttonset();
                        },0)
                    }); // loads them one at a time and still takes forever for all of them on an iPad 1
                $( "#payment_buttons" ).buttonset();
                $( '#payplease' ).button();
                // $( ".register_radio" ).buttonset(); // slow
                $( ".register_checkboxes" ).buttonset(); 
                $( "#participantRegTabs" ).tabs();
                $( "#addressTypeTabs" ).tabs();
                $( "#agreementPaymentTabs" ).tabs(); // aaaand works
                if(enabled == false ) {
                    $('#register_body').find('input, textarea, button, select').prop("disabled", true); // this doesn't seem to be working, or else I'm not checking enabled properly
                }
                $('.waiver_tosign').click(function(e) {
                    var waiver_arr = this.id.split(/_/);
                    var id_to_use = waiver_arr[1];
                    console.log('double sigh');
                    var fieldToDisable = $(this).prev('input').attr('id');
                    registration.signWaiver(id_to_use, fieldToDisable);
                });
                $('.waiver_reset').click(function(e) {
                    var waiver_arr = this.id.split(/_/);
                    var waiver_id = waiver_arr[1];
                    console.log('we use '+waiver_id);
                    registration.resetWaiver(waiver_id);
                });
                $('.choose_address').click(function(e) {
                    // var partInfo = this.id.split(/_/);
                    // eventParticipants.eventParticipantRemove (partInfo[1] );
                    var id_arr = this.id.split(/_/);
                    var id_to_use = id_arr[1];
                    console.log(id_to_use);
                    registration.copyPaymentAddress(id_to_use);
                });
                // not sure this is used. I can't find an "output" anywhere
                $('.output').each(function(i) {
                    var curId=this.id;
                    var curName = $('#'+this.id).attr('name');
                    if($('#'+this.id).val()=='' || $('#'+this.id).val()=='[]') {
                        // make sure empty fields are really empty
                        console.log('on line 109 I am resetting '+ this.id);
                        $('#'+this.id).val('');
                    }
                    var pad_sigid='pad_'+curId;
                    $('#'+pad_sigid).mouseup(function(e) {
                        console.log('mousing up!');
                        var thisid=this.id;
                        // I am so so sorry future-Troy
                        // but I'm beating the signature library to the hidden field so I need to give it a chance.
                        // damn past-troy
                        // that's ok past-future-troy, we got rid of the signature library, but I hope past-troy learned his lesson
                        setTimeout(function () {
                            registration.submitSignature(curId);
                            },500)
                    });

                });

                $('#reg').validate({
                    submitHandler: function(form)  {
                        console.log("rule obj is "+ruleObj);
                        $('#billing_reg').submit(); // this should be happening anyway but its validate is supposed to return false
                        return false;
                    },
                    errorPlacement: function(error, element) {
                        console.log('we are dealing with errorPlacement on '+element.attr('id'));
                        if(element.attr('id')=='registration_signature') {
                            console.log('we are dealing with registration_signature');
                            // error.insertAfter('#reg_sig_canvas');
                            error.insertAfter('#pad_registration_signature');
                            console.log('and we inserted it damnit');
                        } else {
                            console.log('placing after the element');
                            error.insertAfter(element);
                        }
                    },
                    ignore: ""
                });
                $('#billing_reg').validate({
                    // debug: true,
                    submitHandler: function(form)  {
                        console.log('validating based on billing_reg');
                        $('#reg').submit();
                        $(".output").each(function (i) {
                            console.log('should be submitting');
                           registration.submitSignature(this.id);
                        });
                        return false;
                    },
                    ignore: ""
                });
                $('#payplease').click(function ( event )  {
                        console.log('well you clicked something');
                    /*
                     * don't forgot to ... turn this on or something?
                    $(".output").each(function (i) {
                       registration.submitSignature(this.id);
                    });
                    */
                });
                // check the hidden fields for content. if they exist grab
                // the coordinates. You can then display them in the div
                // or else if the status is set make them pngs via js.
            }
        });
    },
    getRegistration: function (reg_id, login_id, enabled) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            dataType: "html",
            data: "action=register&reg_id="+reg_id+"&login_id="+login_id,
            success: function(result) {
                console.log('enabled is '+enabled);
                $("#participant_reg_holder").html(result);
                login.activate_login_controls();
                // record signatures
                editing.makeEditable ('editable');
                $( ".register_radio" ).each(function ( index, button ) {
                    setTimeout(function () {
                        $(button).buttonset();
                        },0)
                    }); // loads them one at a time and still takes forever for all of them on an iPad 1
                $( "#payment_buttons" ).buttonset();
                $( '#payplease' ).button();
                // $( ".register_radio" ).buttonset(); // slow
                $( ".register_checkboxes" ).buttonset(); 
                $( "#participantRegTabs" ).tabs();
                $( "#addressTypeTabs" ).tabs();
                $( "#agreementPaymentTabs" ).tabs(); // aaaand works
                if(enabled == false ) {
                    $('#register_body').find('input, textarea, button, select').prop("disabled", true); // this doesn't seem to be working, or else I'm not checking enabled properly
                }
                $('.choose_address').click(function(e) {
                    // var partInfo = this.id.split(/_/);
                    // eventParticipants.eventParticipantRemove (partInfo[1] );
                    var id_arr = this.id.split(/_/);
                    var id_to_use = id_arr[1];
                    console.log(id_to_use);
                    registration.copyPaymentAddress(id_to_use);
                });
                // hold this for me participant_waiver|signature|prid|4868,1
                $('.output').each(function(i) {
                    var curId=this.id;
                    var curName = $('#'+this.id).attr('name');
                    if($('#'+this.id).val()=='' || $('#'+this.id).val()=='[]') {
                        // make sure empty fields are really empty
                        console.log('on line 220 I am resetting '+ this.id);
                        $('#'+this.id).val('');
                    }
                    var pad_sigid='pad_'+curId;
                    $('#'+pad_sigid).mouseup(function(e) {
                        console.log('mousing up!');
                        var thisid=this.id;
                        // I am so so sorry future-Troy
                        // but I'm beating the signature library to the hidden field so I need to give it a chance.
                        // damn past-troy
                        setTimeout(function () {
                            registration.submitSignature(curId);
                            },500)
                    });

                });

                $('#reg').validate({
                    submitHandler: function(form)  {
                        console.log("rule obj is "+ruleObj);
                        // alert('reg.validate was called');
                        $('#billing_reg').submit(); // this should be happening anyway but its validate is supposed to return false
                        console.log('billing_reg went off');
                        return false;
                    },
                    errorPlacement: function(error, element) {
                        console.log('we are dealing with errorPlacement on '+element.attr('id'));
                        if(element.attr('id')=='registration_signature') {
                            console.log('we are dealing with registration_signature');
                            // error.insertAfter('#reg_sig_canvas');
                            error.insertAfter('#pad_registration_signature');
                            console.log('and we inserted it damnit');
                        } else {
                            console.log('placing after the element');
                            error.insertAfter(element);
                        }
                    },
                    ignore: ""
                });
                $('#billing_reg').validate({
                    // debug: true,
                    submitHandler: function(form)  {
                        console.log('another go at billing_reg');
                        $('#reg').submit();
                    },
                    ignore: ""
                });
                $('#payplease').click(function ( event )  {
                        console.log('clicked something in the old registration function');
                });
            }
        });
    },

    copyPaymentAddress: function ( from_id ) {
        // this hash is specific to intrix and to the US and that is a REAL PROBLEM. Bottom line is it needs to come from php somewhere.
        var hash= {"fname":"fname","lname":"lname","email":"email","phone_c":"phone","address_1":"address_1","city":"city","state":"state","zip":"zip","country":"country"}
        $.each(hash, function(index, value) {
            // console.log("index is "+index+" and value is "+value);
            var from_html_id = index+'_'+from_id;
            console.log('looking for value in '+from_html_id);
            var from_val = $('#'+from_html_id).val()
            console.log('giving '+value+' a val of '+from_val);
            $('#'+value).val(from_val);
        });
    }
}

var editing = {
    default_update_target: '',
    focusedField: {"name":"test","value":"the test value","id":"test id"}, // do I use this? I think so. check participants.php
    updateField: function (field_id, update_target, fieldName, fieldValue, uniqueIdName, uniqueId,callback_func) {
        // so you change the names of your variables when coming from the function that calls this one. Pro-move.
        var data = "action=update_"+update_target+"&"+uniqueIdName+"="+uniqueId+"&field_name="+fieldName+"&field_val="+fieldValue;
        // debug // console.log(data);
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=update_"+update_target+"&"+uniqueIdName+"="+uniqueId+"&field_name="+fieldName+"&field_val="+fieldValue,
            dataType: "json", 
            success: function(result) {
                // console.log('success for '+field_id);
                $("#"+field_id).animate({"background-color":"#abffab" }, 80);
                $("#"+field_id).animate({"background-color":"#ffffff"}, 1000);
                // wtf is this here for a generic function?
                // var special_login_id=loginData.login_id;
                // console.log('here in our generic function login_id is '+special_login_id);
                // ^^ that is funny
                // participantData.getParticipantsByLogin($( special_login_id ));
            }
        });
    },
    makeEditable: function (className) {
        // var replaceString=/(^[\w_]+)([|])([\w_]+)([|])([\w_]+)([|])([\d]+)/;
        // var replaceString=/(^[\w_]+)([|])([\w_,]+)([|])([\w_,]+)([|])([\d,]+)/;
        var inputType=$('#'+this.id).prop("type")
        var replaceString=/(^[\w_]+)([|])([\w_]+)([|])([\w_,\d]+)([|])([\w\d,]+)/;
        $('.'+className).click(function(e) {
            var inputType=$('#'+this.id).prop("type")
            console.log('you clicked an editable with a type of '+inputType);

            // moved from focus
            editing.focusedField.name=this.name;
            console.log('up here this.name is '+this.name);
            editing.focusedField.value=$('#'+this.id).val();
            editing.focusedField.id=$('#'+this.id);

            if(inputType == 'checkbox' ) {
                var isChecked = $('#'+this.id).is(':checked');
                var fieldName=this.name;
                var fieldValue=$('#'+this.id).val();
                var pre_edStr=fieldName.replace(replaceString, "1: $1 and 2: $2 and 3: $3 and 4: $4 and 5: $5 and 6: $6 and 7: $7");
                var update_target = fieldName.replace(replaceString, "$1");
                var dbFieldName   = fieldName.replace(replaceString, "$3");
                var idFieldName   = fieldName.replace(replaceString, "$5");
                var idFieldValue  = fieldName.replace(replaceString, "$7");
                if(isChecked == true) {
                    // that means they clicked it, making it true
                    console.log('the checkbox is teerue');
                } 
                if(isChecked == false) {
                    // that means they clicked it, making it false
                    console.log('the checkbox is false!');
                    fieldValue='';
                } 
                if(editing.focusedField.name == fieldName) {
                    console.log('names match');
                    var uniqueId     = idFieldValue;
                    // debug // console.log('and the id I want is '+uniqueId);
                    // console.log('editings default_update_target is '+editing.default_update_target );
                    editing.updateField(this.id, update_target, dbFieldName, fieldValue, idFieldName,idFieldValue,false);
                } else {
                    // safari debug
                    console.log(editing.focusedField.name+' is not the same as '+fieldName);
                }
            }
            if(inputType == 'radio'/* || inputType == 'checkbox' */ ) {
                console.log('silly yes but we found a radio ');
                var fieldName=this.name;
                var fieldValue=$('#'+this.id).val();
                console.log( "we will radioly compare "+fieldValue+" to "+editing.focusedField.value+' and then get busy with '+replaceString);
                var pre_edStr=fieldName.replace(replaceString, "1: $1 and 2: $2 and 3: $3 and 4: $4 and 5: $5 and 6: $6 and 7: $7");
                console.log('so we have '+pre_edStr);
                // because either radio buttons suck or I do we always hit rpc with a click. Because it is not remembering 
                // the value of the old radio click. As in, you click on the male button and it never remembers if you had
                // previously selected the female.
                var update_target = fieldName.replace(replaceString, "$1");
                var dbFieldName   = fieldName.replace(replaceString, "$3");
                var idFieldName   = fieldName.replace(replaceString, "$5");
                var idFieldValue  = fieldName.replace(replaceString, "$7");
                console.log('target is '+update_target+' and dbFieldName is '+dbFieldName+' and idFieldName is '+idFieldName+' and idFieldValue is '+idFieldValue);
                if(editing.focusedField.name == fieldName) {
                    console.log('names match');
                    var uniqueId     = idFieldValue;
                    // debug // console.log('and the id I want is '+uniqueId);
                    // console.log('editings default_update_target is '+editing.default_update_target );
                    editing.updateField(this.id, update_target, dbFieldName, fieldValue, idFieldName,idFieldValue,false);
                } else {
                    console.log(editing.focusedField.name+' is not the same as '+fieldName);
                }
            }
        });
        $('.'+className).focus(function(e) {
            var inputType=$('#'+this.id).prop("type")
            console.log('you focused and this type is an '+inputType);
            console.log('on focus compare your field with '+editing.focusedField.value+' to '+editing.focusedField.name);
            if(inputType != 'radio' && inputType != 'checkbox' && inputType !='hidden') {
                console.log('focus on '+className+' of a type of '+inputType);
                editing.focusedField.name=this.name;
                editing.focusedField.value=$('#'+this.id).val();
                editing.focusedField.id=$('#'+this.id);
            }
        });
        $('.'+className).blur(function(e) {
            var inputType=$('#'+this.id).prop("type")
            if(inputType != 'radio' && inputType != 'checkbox' && inputType !='hidden') {
                // I will regret
                // I so do
                // debug // console.log('you got blurry');
                var fieldName=this.name;
                var fieldValue=$('#'+this.id).val();
                // debug // console.log( "we will justly compare "+this_id+" blah "+fieldValue+" of "+inputType+" to "+editing.focusedField.value);
                // var replaceString=/(^\w+)(_)(\w+)/;
                // check at the top of the function for replaceString
                var pre_edStr=fieldName.replace(replaceString, "1: $1 and 2: $2 and 3: $3 and 4: $4 and 5: $5 and 6: $6 and 7: $7");
                // 1 is what it is // which is whack
                // 2 is a pipe
                // 3 is the col
                // 4 is a pipe
                // 5 is the key name
                // 6 is a pipe
                // 7 is the key value
                // put another way:
                // table name | column name | row key | row key value
                var update_target=fieldName.replace(replaceString, "$1");
                // debug // console.log('update_target is '+update_target);
                var dbFieldName=fieldName.replace(replaceString, "$3");
                // debug // console.log('dbFieldName is '+dbFieldName);
                var idFieldName=fieldName.replace(replaceString, "$5");
                // debug // console.log('idFieldName is '+idFieldName);
                var idFieldValue=fieldName.replace(replaceString, "$7");
                // debug // console.log('idFieldValue is '+idFieldValue);
                // console.log('from '+fieldName+' to '+idFieldName);
                if(editing.focusedField.name == fieldName) {
                    console.log('names match for '+this.id+' of an input type of '+inputType);
                    console.log('and the values are like '+editing.focusedField.value+' vs '+fieldValue);
                    if(editing.focusedField.value != fieldValue) {
                        console.log('and values do not');
                        var uniqueId     = idFieldValue;
                        // debug // console.log('and the id I want is '+uniqueId);
                        // console.log('editings default_update_target is '+editing.default_update_target );
                        // have I already asked why the hell I didn't just do this on the php side?
                        // I probably asked over on the php side. You piss me off past-Troy.
                        editing.updateField(this.id, update_target, dbFieldName, fieldValue, idFieldName,idFieldValue,false);
                    } else {
                        // debug // console.log('they are the same');
                    }
                } else {
                    console.log('names do not match, editing.focusedField.name is '+editing.focusedField.name+' and fieldName is '+fieldName);
                }
            }
        });
    }
}

var config_edit = {
    get_config_tables: function() {
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            dataType: "html",
            data: "action=mini_admin&mini_action=retrieve",
            success: function(result) {
                $("#mini_config").html(result);
                $(".config_edit").blur(function(e) {
                    config_edit.edit_config_value(this.id);
                });
            }
        });
    },
    edit_config_value: function(config_field) {
        console.log('gonna edit '+config_field);
        // var config_val = $('#field_'+config_field).val(); // gtfh jQuery
        var config_val = document.getElementById(config_field).value;
        console.log(config_val);
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            dataType: "html",
            data: "action=mini_admin&mini_action=update&id="+config_field+"&value="+config_val,
            success: function(result) {
                console.log(result);
            }
        });
    }
}

var login = {
    login_status: 'false',
    check_login: function (email, password) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            dataType: "html",
            data: "action=login&email="+email+"&password="+password+"&datatype=html",
            success: function(result) {
                    $("#login_holder").html(result);
                    login.activate_login_controls();
            }
        });
    },
    admin_check_login: function (email, password) {
	console.log('runin gadmin_check_login');
        // do NOT ask
        var ret = '{"myresult":"yort"}';
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            dataType: "json",
            data: "action=login&email="+email+"&password="+password+"&datatype=json",
            async: true,
            success: function(result) {
                $ret = result;
            },
            async: false
        });
        return $ret;
    },
    admin_logout: function () {
        var ret = false;
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            data: "action=logout&datatype=json",
            dataType: "json",
            success: function(result) {
                console.log(result);
                ret = result;
            },
            async: false,
        });
        console.log('returning '+ret);
        return ret;
    },
    logout: function () {
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            data: "action=logout",
            dataType: "html",
            success: function(result) {
                console.log(result);
                /* $("#login_holder").html(result); */
                login.view_classes_login_welcome('login_holder');
            }
        });
    },
    activate_login_controls: function() {
        $('#login_button').click(function(e) {
            var email = $('#email').val();
            var password = $('#password').val();
            login.check_login (email, password);
        });
        $('#logout_button').click(function(e) {
            console.log('clicked the logout');
            login.logout ();
        });
        $('#email_password_button').click(function(e) {
            console.log('clicked the email password');
            login.email_password($('#login_email_req').val());
        });
    },

    build_login: function () {
        // hopefully not used
        tosend='action=get_login_screen';
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: tosend,
            dataType: "html",
            success: function(result) {
                $('#login_holder').html(result);
                login.activate_login_controls();
            }
        });
    },

    email_password: function(email) {
        tosend='action=request_new_password&email='+email;
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: tosend,
            dataType: "html",
            success: function(result) {
                /* $('#login_holder').html(result); */
                $('#request_password').html(result);
                /* this is not right. you need a request_password id in the billing
                 * template and then you need to run:
                 * login.view_classes_login_welcome('login_holder'); instead of this.
                 * right now it breaks the login */
                login.activate_login_controls();
            }
        });
    },
    view_classes_login_welcome: function (result_holder) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            dataType: "html",
            data: "action=view_classes_login_welcome",
            success: function(result) {
                $("#"+result_holder).html(result);
                $("#"+result_holder).html(function() {
                    $(this).html(result);
                    // console.log('successing it all over and input_money is '+$('.input_money')+' and email is '+$('#email')+' and the button is '+$('#login_button')+' and result is '+result);
                    if($('#logout_button').html()!==undefined) {
                        $('#logout_button').click(function(e) {
                            console.log('clicked the logout');
                            login.logout (); // should be a callback
                            login.view_classes_login_welcome('login_holder');
                        });
                    }
                    if($('#login_button').val()!==undefined) {
                        console.log("totally not logged something");
                        // this code is copied from view_classses
                        $('#login_button').click(function(e) {
                            console.log('clicked the login_button in welcome');
                            var email = $('#email').val();
                            var password = $('#password').val();
                            var success = login.admin_check_login (email, password);
                            if(success.myresult == 'true') {
                                login.view_classes_login_welcome('login_holder');
                            } else {
                                $('#hold_classes').html('<h3>Login error.</h3><p>Please try again</p>');
                            }
                        });
                    }
                    $(".input_money").change(function() {
                        console.log('we have a change');
                        var sum = 0;
                        var total = 0;
                        $(".input_money").each(function() {
                            // sum += Number($(this).val());
                            total += $(this).val() * 1;
                        });
                        var totalShow=total.toFixed(2);
                        $('#calc_amount').html(totalShow);
                        $('#total').val(total);
                    });
                    $('#email_password_button').click(function(e) {
                        console.log('clicked the email password');
                        login.email_password($('#login_email_req').val());
                    });
                });
            }
        });
    }
}

var errorChecking = {
    check_field: function (fid) {
        console.log('we passed '+fid);
        if($('#'+fid).val()=='') {
            // alert('You left a blank field. Please check your work.');
            return false;
        }
        return $('#'+fid);
    },
    display_error: function (error) {
        var error_field='error_'+error;
        console.log(error_field);
        // $('#'+toShow).show( 'drop', {}, 500, '' );
        $('#'+error_field).show('slide', {}, 500, '' );
    }
}

var eventParticipants = {
    // here's where you insert and remove participants from events
    eventParticipantInsert: function (participant_id, event_id) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=insert_event_participant&participant_id="+participant_id+"&event_id="+event_id,
            dataType: "json",
            success: function(result) {
                // alert('did '+participant_id+' into '+event_id+' for '+result.event_id);
                eventManipulation.getEvent(result.event_id, classFiltering.prepEvent);
            }
        });
    },
    eventParticipantRemove: function(event_participant_id) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=remove_event_participant&event_participant_id="+event_participant_id,
            dataType: "html",
            success: function(result) {
                $('#participant_insert_message').html(result);
            }
        });
    },
    eventParticipantOrphan: function (event_participant_id) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            data: "action=orphan_event_participant&event_participant_id="+event_participant_id,
            dataType: "html",
            success: function(result) {
                $('#participant_insert_message').html(result);
            }
        });
    },
    eventParticipantConfirmToggle: function(event_participant_id, status_id, event_id) {
        console.log('function with big long name');
        $.ajax({
            url: jsConfigs.rpc,
            type: "POST",
            // data: "action=toggle_participant_status&participant_id="+participant_id+"&event_id="+event_id+"&status_id="+status_id,
            data: "action=toggle_participant_status&event_participant_id="+event_participant_id+"&status_id="+status_id,
            dataType: "html",
            success: function(result) {
                console.log('you were successful in toggling');
                $('#participant_insert_message').html(result);
                /*
                $('#p_fname').val('');
                $('#p_lname').val('');
                 */
                eventManipulation.getEvent(event_id);
                console.log('getEvent inside the toggle');
            }
        });
    }
}

var eventManipulation = {
    html_loc: 'holdme',
    getEvent: function(event_id, callback_func) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            data: "action=get_event_card&event_id="+event_id,
            dataType: "html",
            success: function(result) {
                $('#event_holder_'+event_id).html(result);
                $( ".ep_config" ).hide();
                console.log(typeof callback_func);
                if(typeof callback_func === "function") {
                    console.log('running the callback!');
                    callback_func();
                }
                eventManipulation.participantEditor(callback_func); // is this run during the callback
            }
        });
    },
    getEvents: function(filter_param, filter, class_id, callback_func) {
        // debug // alert('we have '+filter_param+' and '+filter+' for '+class_id);
        // alert('get those classes');
        var class_id=$('#'+filter_param+'FilterForm #classSelect').val();
        // you overwrite the class_id brought into the function there. why?
        console.log( "you need to find the classSelect inside #"+filter_param+"FilterForm");
        console.log('and class is '+class_id);
        var et_id=$('#event_type').val();
        console.log('et_id is '+et_id);
        var mydata = '';
        if(filter_param == 'event_type') {
            console.log('we are paraming by event_type ');
            mydata ="action=get_events&class_id="+class_id+"&et_id="+filter;
        }
        if(filter_param == 'day') {
            console.log('we are paraming by day ');
            mydata ="action=get_events&class_id="+class_id+"&day="+filter;
        }
        if(filter_param == 'participant') {
            console.log('we are paraming by participant ');
            mydata ="action=get_event_by_participant&class_id="+class_id+"&participant_id="+filter;
        }
        console.log('or else we are paraming by nothing at all and filter_param is '+filter_param);
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            data: mydata,
            dataType: "html",
            success: function(result) {
                $(eventManipulation.html_loc).html(result);
                console.log('using '+eventManipulation.html_loc);
                $( ".ep_config" ).hide();
                eventManipulation.participantEditor(callback_func);
                if(typeof callback_func === "function") {
                    console.log('running the callback in getEvents!');
                    callback_func();
                }
            }
        });
    },
    participantEditor: function(callback_func) {
        if(typeof callback_func === "function") {
            console.log('parted knows we have a callback');
        }
        $( "#hold_classes .editable" ).click(function(e) {
            console.log('you clicked editable');
            var id = this.id;
            var toShow = id.replace('conf_', 'shelf_');
            var thisId='#'+toShow;
            $('#'+toShow).show("fade", {}, "slow");
            // $('#'+toShow).show( 'blind', {}, 500, '' );
            $( "#"+toShow+" .status_buttons" ).buttonset();
            $( "#"+toShow+" .participant_remove" ).button();
            $( "#"+toShow+" .participant_orphan" ).click(function(e) {
                var statClasses = this.className.split(/\s+/) ;
                var partInfo = this.id.split(/_/);
                eventParticipants.eventParticipantOrphan (partInfo[1] );
                console.log('orphaned a participant from an event');
                eventManipulation.getEvent(statClasses[1], callback_func);
            });
            $( "#"+toShow+" .participant_remove" ).click(function(e) {
                var statClasses = this.className.split(/\s+/) ;
                var partInfo = this.id.split(/_/);
                eventParticipants.eventParticipantRemove (partInfo[1] );
                console.log('getEvent inside participant Editor');
                eventManipulation.getEvent(statClasses[1], callback_func);
            });
            $( "#"+toShow+" .status_radio" ).click(function(e) {
                // alter status
                var statClasses = this.className.split(/\s+/) ;
                var partInfo = this.id.split(/_/);
                console.log('right before the toggle');
                eventParticipants.eventParticipantConfirmToggle(partInfo[1], partInfo[2], statClasses[1] );
                console.log('alter status for '+toShow+' based on partinfo1: '+partInfo[1]+' partinfo2: '+partInfo[2]+' and statclasses1: '+statClasses[1]);
                eventManipulation.getEvent(statClasses[1], callback_func);
            });
            $(document).keyup(function(e) {
                if (e.keyCode == 27) {  
                    $( "#"+toShow+":visible" ).fadeOut();
                }   // esc
            });

        });
    }
}

var event_card_filter = {
    // filter events
    filter_on: function (card_param, filter_id) {
        console.log('I am on');
        // card_param is
        // and filter_id is
         var n = $("#"+filter_id+" input:checked");
        console.log('n is '+n);
         var i = 0;
         if(n.length > 0) {
             console.log('hiding all');
             // hide all of 'em
             $('.event_card_holder').hide();
             while(i < n.length) {
                // console.log(n[i].id+" checked");
                var filtering_by = n[i].id.split('_')[1];
                console.log('filtering by '+filtering_by);
                i++;
                console.log('showing '+card_param+'_'+filtering_by);
                $('.'+card_param+'_'+filtering_by).show();
             }
         } else {
             $('.event_card_holder').show();
         }
    },
    uncheck_filter_settings: function (filter_id) {
        console.log('unchecking all');
        $("#"+filter_id+" input").attr('checked', false);
        $("#"+filter_id+" input").button('refresh');
        $('.event_card_holder').show();
    }
}

// object for holding feedback on activities
var feedback = {
    formFieldSuccess: function(formObject) {
        console.log('not done yet');
    }
}

var loginManipulation = {
    insert_login: function(tosend, callback_func) {
        // tosend is some serialized data
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            data: tosend,
            dataType: "json",
            success: callback_func
        });
    },
    get_log_levels: function(id_to_append) {
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            data: "action=get_login_levels",
            dataType: "html",
            success: function(result) {
                $('#'+id_to_append).append(result);
            }
        });
    }
}

var studentManipulation = {
    getStudents: function() {
        // alert('get those students');
                // alert($('#classSelect option:selected').val());
        var class_id=$('#classSelect').val();
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            data: "action=get_class_students&class_id="+class_id,
            dataType: "html",
            success: function(result) {
                $('#hold_students').html(result);
                // $('#studentList').addClass("ui-menu ui-widget ui-widget-content ui-corner-all")
                setStyles.setStudentStyles();
                $( ".draggable").mousedown ( function () {
                    listItem=this.id;
                    if(typeof justMarried[listItem] != "undefined") {
                        // alert('totally, totally defined: '+justMarried[listItem]);
                        // dropShit.elementReenable(justMarried[listItem]);
                    } else {
                        // this participant hasn't been put in an event yet.
                        // alert('do some shitn ow ');
                    }
                });
                $( ".draggable" ).draggable({ revert: "invalid", containment: "body" });
                // $("#studentList" ).accordian({ event: "click hoverintent", collapsible: true });
                $( "#studentList" ).accordion({
                                collapsible: true,
                                heightStyle: "content"
                                            });
                // dropShit.dragEvents();
            }
        });
    }
}

var participantData = {
    participantSearchFieldLocation: 'student_search',  // blarg
    search_field: '',
    result_field: '',
    

    returnParticipantData: function(param_string) {
        param_string='class_id=all'; // just get it done
        console.log('running returnParticipantData ');
            return $.ajax({
                url: jsConfigs.rpc,
                type: "GET",
                data: "action=get_participants_json&"+param_string,
                dataType: "json"
            })
    },
    participantAutoComplete: function (part_json) {
        console.log('runing participantAutoComplete');
        search_field=participantData.search_field;
        result_field=participantData.result_field;
        console.log( "search_field is "+search_field+" and result is "+result_field);
        // debug // console.log(part_json);
        $('#'+search_field).autocomplete({
            minLength: 0,
            source: part_json,
            focus: function( event, ui ) {
                console.log('focus on '+search_field);
                $("#"+search_field).val( ui.item.value );
                return false;
            },
            select: function ( event, ui ) {
                // not used right? // $( "#p"+search_field).val(ui.item.value);
                participantData.participant_id=ui.item.participant_id;
                console.log('select on #'+search_field);
                $( '#'+result_field).val( ui.item.participant_id );
            }

        })
    }, /* returnParticipantData(param_string).done(participantAutoComplete); /* then it needs to grab values loaded into participantData */
    getParticipantData: function(class_id) {
        if(class_id=='') {
            class_id='all';
        }
        $.ajax({
            url: jsConfigs.rpc,
            type: "GET",
            data: "action=get_participants_json&class_id="+class_id,
            dataType: "json",
            success: function(result) {
                // this would be a lot more useful if I knew how to use callbacks
                fieldLocation = participantData.participantSearchFieldLocation;
                participantData.buildParticipantInterface(result, fieldLocation, 'studentList');
            }
        });
    },
    getAllParticipants: function() {
        alert('lost again');
    },
    buildParticipantInterface: function(participantJson, fieldLocation, resultLocation) {
        $( "#"+fieldLocation ).autocomplete({
            minLength: 0,
            source: participantJson,
            focus: function( event, ui ) {
                console.log('focus on #participant');
                $( "#"+fieldLocation ).val( ui.item.value );
                return false;
            },
            select: function( event, ui ) {
                $( "#p"+fieldLocation).val(ui.item.value);
                // $( "#participant_id").val(ui.item.participant_id);
                participantData.participant_id=ui.item.participant_id;
                console.log('select on #'+fieldLocation);
                /* this should be called from outside this function on success of selecting a participant */
                // damn straight it should. Crap.
                var theHtml='<p class="participant draggable" id="'+participantData.participant_id+'">'+ui.item.value+'</p>';
                $( "#"+resultLocation).html(theHtml);
                $( ".draggable").mousedown ( function () {
                    listItem=this.id;
                    if(typeof justMarried[listItem] != "undefined") {
                        // alert('totally, totally defined: '+justMarried[listItem]);
                        dropShit.elementReenable(justMarried[listItem]);
                    } else {
                        // this participant hasn't been put in an event yet.
                        // alert('do some shitn ow ');
                    }
                });
                $( ".draggable" ).draggable({ revert: "invalid", containment: "body" });
                return false; // because when things go right I like to return false
            }
        })
        .data( "autocomplete" )._renderItem = function( ul, item ) {
                console.log('not-understood-autocomplete on #participant');
            return $( "<li>" )
                .data( "item.autocomplete", item )
                .append( "<a>" + item.value + "</a>" )
                .appendTo( ul );
        };
    },
}

/* stupid ie9 or stupid me */
if(!(window.console && console.log)) {
  console = {
    log: function(){},
    debug: function(){},
    info: function(){},
    warn: function(){},
    error: function(){}
  };
}
