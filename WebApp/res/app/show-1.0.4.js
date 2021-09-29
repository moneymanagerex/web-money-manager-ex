var show = {
    init: function(){
        $(function() {
            show.activateTooltips();
            show.monitorEditRadio();
            show.monitorEditTransaction();
            show.monitorDeleteCheckboxes();
        });
    },
    
    activateTooltips: function(){
        $('*[data-toggle="tooltip"]').tooltip();
    },

    monitorEditTransaction: function(){
        $('.TrModify').on('click', function(){
            tr_id = $(this).attr('id').split('_');
            i_id = tr_id[1];
            $("#TrEdit").val(i_id);
            $("#TrModify").val("Edit");
            if (($('.do-delete:checked').length == 0))
            {
                $("#Show_Function").trigger( "submit" );
            }
        });
    },

    monitorDeleteCheckboxes: function(){
        var btn_delete = $('#TrDelete');
        
        // Default state button
        btn_delete.hide();
        
        $('.do-delete').on('change', function(){
            if($('.do-delete:checked').length > 0){
                btn_delete.show();
            }else{
                btn_delete.hide();
            }
        });
    }
    
};

show.init();
