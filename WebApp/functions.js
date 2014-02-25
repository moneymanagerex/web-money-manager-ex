function test_html5 ()
    {
        if (!Modernizr.inputtypes.date || !Modernizr.inputtypes.date)
            {
                alert("Seems that the browser doesn't supports HTML5" + '\n' + '\n' + "Please make attention because it doesn't validate filed!");
                //close();
            }
    }
function get_today ()
    {
        Date = new Date();
        y1= Date.getFullYear();
        m1 = Date.getMonth()+1;
        if(m1<10)
            m1="0"+m1;
        dt1 = Date.getDate();
        if(dt1<10)
            dt1 = "0"+dt1;
        d2 = y1+"-"+m1+"-"+dt1;
        
        return d2;
    }

function send_alert_and_redirect (alertmessage,newurl)
    {
        alert(alertmessage);
        window.location.href = newurl;
    }

function disable_element(element_to_disable,element_to_test,value_to_test)
    {
        if (document.getElementById(element_to_test).value !== value_to_test)
            {
                document.getElementById(element_to_disable).disabled = true;
            }
        else
            {
                document.getElementById(element_to_disable).disabled = false;
            }
    }