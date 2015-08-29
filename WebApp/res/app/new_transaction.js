var transactions = {
    init: function(){
        $(function() {
            transactions.monitorTypeSelector();
            transactions.monitorAccountSelectors();
        });
    },
    
    monitorTypeSelector: function(){
        $('#Type').on('change', function(){
            var type = $(this).val();
            
            // Reset disabled Account values
            if(type !== 'Transfer'){
                $('#Account').find('option:disabled').prop('disabled', false);
                $('#ToAccount').val('None');
            }else{
                transactions._disableSelectValue($('#Account'));
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
            
            transactions._disableSelectValue(current);
        });
    },
    
    _disableSelectValue: function(selected){
        if(selected.prop('id') === 'Account') {
            $('#ToAccount option[value="' + selected.val() + '"]').prop('disabled', true);
        }
        
        if(selected.prop('id') === 'ToAccount') {
            $('#Account option[value="' + selected.val() + '"]').prop('disabled', true);                    
        }
    }
};

transactions.init();