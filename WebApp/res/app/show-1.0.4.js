var show = {
    init: function(){
        $(function() {
            show.activateTooltips();
            show.monitorEditTransaction();
            show.monitorDeleteCheckboxes();
            show.monitorDuplicateTransaction();
        });
    },
    
    activateTooltips: function(){
        $('*[data-toggle="tooltip"]').tooltip();
    },

    monitorDuplicateTransaction: function(){
        $('.TrDuplicate').on('click', function(){
            i_id = $(this).attr('tr_id');
            $("#TrEdit").val(i_id);
            $("#btn_action").val("Duplicate");
            $("#Show_Function").trigger( "submit" );
        });
    },


    monitorEditTransaction: function(){
        $('.TrModify').on('click', function(){
            i_id = $(this).attr('tr_id');
            $("#TrEdit").val(i_id);
            $("#btn_action").val("Edit");
            $("#Show_Function").trigger( "submit" );
        });
    },

    monitorDeleteCheckboxes: function(){
        var btn_delete = $('#TrDelete');
        
        // Default state button
        btn_delete.hide();
        
        $('.do-delete').on('change', function(){
            if($('.do-delete:checked').length > 0){
                btn_delete.show();
                $('#btn_new').hide();
            }else{
                btn_delete.hide();
                $('#btn_new').show();
            }
        });
    }
    
};

show.init();
