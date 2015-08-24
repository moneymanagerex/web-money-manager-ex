var app = {
    init: function(){
        $(function() {
            app.activateTooltips();
            app.monitorEditRadio();
            app.monitorDeleteCheckboxes();
        });
    },
    
    activateTooltips: function(){
        $('*[data-toggle="tooltip"]').tooltip();
    },
    
    monitorEditRadio: function(){
        var btn_edit = $('#TrModify');
        
        // Default state button
        btn_edit.hide();
        
        $('.do-edit').on('change', function(){
            btn_edit.show();
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

app.init();