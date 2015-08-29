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
    }
};

app.init();