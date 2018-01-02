var participant = {

    classId: '',
    participantListLocation: '',
    callback: function() {},
    
    participantEditForm: function(id) {
        console.log(' in here it is '+id);
    },

    getParticipants: function() {
        // also exists in show_classes and must be removed
        // alert('get those students');
                // alert($('#classSelect option:selected').val());
        var class_id;
        var loc = this.participantListLocation;
        $.ajax({
            url: jsConfigs.rpc, 
            type: "POST",
            data: "action=get_class_students&class_id="+class_id,
            dataType: "html",
            success: function(result) {
                $(loc).html(result);
                $( "#studentList" ).accordion({
                     collapsible: true,
                     heightStyle: "content"
                });
                participant.callback();
            }
        });
    }
}
