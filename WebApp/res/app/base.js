var app = {
    init: function(){
        $(function() {
            app.keepAnchorsInApp();
        });
    },
    
    keepAnchorsInApp: function(){
        if (("standalone" in window.navigator) && window.navigator.standalone) {
            $("a").on("click", function(e){
                var new_location = $(this).attr("href");
                
                if (new_location != undefined
                    && new_location.substr(0, 1) != "#"
                    && new_location!=''
                    && event.target.target != "_blank"
                    && $(this).attr("data-method") == undefined){
                    e.preventDefault();
                    window.location = new_location;
                }
            });
        }
    },

    confirmIfNotPresentInDatalist: function(FieldId, Datalist, Confirm_Question){
        var field_value = $('#' + FieldId).val(),
            ifound = false;
        
        for (i=0; i < Datalist.length; i++){
            if (Datalist[i] == field_value){
                ifound = true;
                break;
            }
        }

        if (ifound == true || !Boolean(field_value)){
            return true;
        }else{
            return confirm (Confirm_Question+' "' + field_value + '"?');
        }
    },
    
    isValidForm: function(form){
        var invalid = form.find(":invalid");

        if(invalid.length == 0){
            return true;
        }else{
            return false;
        }
    }
};

app.init();