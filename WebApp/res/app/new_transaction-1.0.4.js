var transactions = {
    init: function(){
        $(function() {
            transactions.monitorTypeSelector();
            transactions.monitorAccountSelectors();
            transactions.monitorFormSubmit();
        });
    },
    
    // Monitor type drop down
    monitorTypeSelector: function(){
        $('#Type').on('change', function(){
            var type = $(this).val();
            
            // Reset disabled Account values
            if(type !== 'Transfer'){
                $('#Account').find('option:disabled').prop('disabled', false);
                $('#ToAccount').val('None');
            }else{
                $('#Payee').val('');
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
    
    // Monitor form submit
    // - Check required fields
    // - Check new payee
    monitorFormSubmit: function(){        
        $('#Transaction').on('submit', function(e){
            var submit_form = false,
                form = $(this);
            
            // All fields valid
            if(app.isValidForm(form)){
                submit_form = true;
            }else{
                form.find(':invalid').first().focus();
                alert('Alcuni campi non sono validi');
            }

            // Confirm add new payee
            if(submit_form){
                if(app.confirmIfNotPresentInDatalist('Payee', PayeeList, "Confermi la creazione del nuovo beneficiario")){
                    submit_form = true;
                }else{
                    $('#Payee').focus();
                    submit_form = false;
                }
            }

            if(!submit_form){
                e.preventDefault();
            }
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