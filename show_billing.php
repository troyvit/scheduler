<?php 
require('includes/config.php');
require('includes/functions.php');
require('includes/language.php'); // I will regret this
require ('includes/bootstrap_include.php'); ?>
    <title>Billing</title>
    <script type="text/javaScript" src="js/jquery.table.js"> </script>
    <script type="text/javascript">
    var billingData = {
        getAllBills: function() {
            var extra_param = '';
            var class_id=$('#classSelect').val();
            if(class_id !=undefined) {
                if(class_id != 'orphans') {
                    extra_param = '&class_id='+class_id;
                } else {
                    extra_param = '&orphans=true';
                }
            }
            // for now just get all the bills associated with a class
            // eventually we'll add a dropdown menu
            $.ajax({
                url: jsConfigs.rpc,
                type: "GET",
                data: "action=get_billing_list"+extra_param,
                dataType: "html",
                success: function(result) {
                    $('#billing_list').html(result);
                    table_sorter( '#billing_table' ).hover().filter( '#filter1' );
                    editing.makeEditable ('editable');
                    $('#to_copy').click(function(e) {
                        billingData.getVisibleEmails();
                    });
                    $('a.download_button').button();
                }
            });
        },
        getVisibleEmails: function() {
            // get the emails of your filtered parents
            var mails=$('tr.visible a[href^="mailto:"]');
            // console.log(mails);
            var clean_mails='';
            var comma='';
            var mail_holder=[];
            mails.each(function() {
                var the_href=$(this).attr('href');
                var parts = the_href.split(':');
                var clean_mail=parts[1];
                if ($.inArray(clean_mail, mail_holder) == -1) {
                    mail_holder.push(clean_mail);
                }
            });
            clean_mails=mail_holder.join(",");
            $('#mails').val(clean_mails);
        }
    }

$(document).ready(function() {
    $.ajax({
        url: "includes/rpc.php",
        type: "POST",
        data: "action=get_all_classes",
        dataType: "json",
        success: function(result) {
            // $("#classSelect option").remove();
            cb = '<option value="">Select a class schedule</option>';
            $.each(result, function(i,data){
                // cb+='<option value="'+data.id+'">'+data.description+'<option/>';
                cb+='<option value="'+data.id+'">'+data.name+'</option>';
            });
                cb+='<option value="orphans">Show All Orphans</option>';
            $("#classSelect").append(cb);
            $('#classSelect').change(function(e) {
                billingData.getAllBills(); 
            });
        }
    });
    billingData.getAllBills(); 
    /* $("#billing_table").sortr(); */
});
    </script>
</head>
<body>
<?php require ('includes/templates/admin_nav.php'); ?>
<label for="filter1"><?php echo $sl->gp('Filter'); ?></label>  
<input type="text" name="filter1" value="" id="filter1" />
<select id="classSelect"></select>
<div id="billing_list">

</div>
<h3><?php echo $sl->gp('Emails to copy'); ?></h3>
<textarea id="mails" style="width: 400px; height: 200px;"><?php echo $sl->gp('email copy', true); ?></textarea>
<input type="button" id="to_copy" value="<?php echo $sl->gp('Click to copy found set', true); ?>"</>
</body>
</html>
