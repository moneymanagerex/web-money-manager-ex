var app = {
    init: function(){
        $(function() {
            app.monitorTypeSelector();
            app.monitorAccountSelectors();
        });
    },
    
    monitorTypeSelector: function(){
        $('#Type').on('change', function(){
            var type = $(this).val();
            
            // Reset disabled Account values
            if(type !== 'Transfer'){
                $('#Account').find('option:disabled').prop('disabled', false);
                
                $('#ToAccount').val('None');
            }
        });
    },
    
    // Disable the same (To)Account when type is transfer
    // You can't transfer to the same account
    monitorAccountSelectors: function(){
        $('#Account, #ToAccount').on('change', function(){
            var current = $(this),
                type = $('#Type').val();
            
            $('#Account, #ToAccount').find('option:disabled').prop('disabled', false);
            
            //if(type === 'Transfer'){
                if(current.prop('id') === 'Account') {
                    $('#ToAccount option[value="' + current.val() + '"]').prop('disabled', true);
                }
                
                if(current.prop('id') === 'ToAccount') {
                    $('#Account option[value="' + current.val() + '"]').prop('disabled', true);                    
                }
                
            //}           
            
        });
    }
};

app.init();