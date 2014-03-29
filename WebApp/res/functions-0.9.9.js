/*!
 * WebApp functions v0.9.9
 * Copyright 2014 Gabriele-V
 */

function test_html5 ()
    {
        if  (      !Modernizr.inputtypes.date
                || !Modernizr.inputtypes.number
                || !Modernizr.input.required
                //|| !Modernizr.input.autocomplete  //Used but not prerequisite 
                //|| !Modernizr.input.placeholder   //Used but not prerequisite
                || !Modernizr.input.min
                //|| !Modernizr.input.step          //Used but not prerequisite
            )
            {
                alert("Seems that the browser doesn't supports HTML5" + '\n' + '\n' + "Please make attention because it doesn't validate filed!");
                //close();
            }
    }
    
var substringMatcher = function(strs)
    {
        return function findMatches(q, cb)
        {
            var matches, substringRegex;
            matches = [];
            substrRegex = new RegExp(q, 'i');
            $.each(strs, function(i, str)
                {
                    if (substrRegex.test(str))
                        {matches.push({ value: str });}
                });
            if (matches.length == 1 && matches[0].value == q)
                {matches = [];}
        cb(matches);
        };
    };

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

function enable_element(element_to_enable,element_to_test,value_to_enable)
    {
        if (document.getElementById(element_to_test).value !== value_to_enable)
            {document.getElementById(element_to_enable).disabled = true;}
        else
            {document.getElementById(element_to_enable).disabled = false;}
    }
    
function disable_element(element_to_disable,element_to_test,value_to_disable)
    {
        if (document.getElementById(element_to_test).value == value_to_disable)
            {document.getElementById(element_to_disable).disabled = true;}
        else
            {document.getElementById(element_to_disable).disabled = false;}
    }

function disable_confirm_password_if_empty()
    {
        if (document.getElementById("Set_Password").value == "")
            {document.getElementById("Set_Confirm_Password").disabled = true;}
        else
            {document.getElementById("Set_Confirm_Password").disabled = false;}
    }

function check_passwor_error()
    {
        if (document.getElementById("Set_Password").value != document.getElementById("Set_Confirm_Password").Value)
            {
                document.getElementById("Set_Confirm_Password").setAttribute("class", "form-control has-error");
                //document.getElementById("Set_Confirm_Password").ClassName = " has-error";
                console.log (document.getElementById("Set_Confirm_Password").ClassName);
            }
        else
            {
                //Normal
            }
    }

function check_password_match_and_submit (Password1,Password2,formid)
    {
        if (document.getElementById(Password1).value !== document.getElementById(Password2).value)
            {alert("Password doesn't match!");}
        else
            {document.getElementById(formid).submit();}
    }
    
function confirm_if_not_present_in_datalist (Field,Datalist,Confirm_Question)
    {
        datalist_element = document.getElementById(Datalist);
        datalist_node = datalist_element.getElementsByTagName("option"); 
        field_value = document.getElementById(Field).value;
        ifound = false;
        for (i=0;i<datalist_node.length;i++)
            {
                if (datalist_node[i].value == field_value) 
                    {ifound = true;}
            }
        if (ifound == true || !Boolean(field_value))
            {return true;}
        else
            {return confirm (Confirm_Question+' "'+field_value+'"?');}
    }

function set_default_category ()
    {
        PayeeName = document.getElementById("Payee").value;
        $.getJSON("query.php?get_default_category="+PayeeName, function(json) {
            if (!jQuery.isEmptyObject(json))
            {
                document.getElementById("Category").value = json.DefCateg;
                document.getElementById("SubCategory").value = json.DefSubCateg;
                populate_sub_category();
            }
            else
            {
                document.getElementById("Category").value = "";
                document.getElementById("SubCategory").value = "";
            }
        });
    }

function populate_sub_category ()
    {
        CategoryName = document.getElementById("Category").value;
        var SubCategoryList = [];
        $('#SubCategory').typeahead('destroy');
        $.getJSON("query.php?get_subcategory="+CategoryName, function(json) {
            $.each(json,function(i,item){
                SubCategoryList.push(item.SubCategoryName);
            });
        });
        $('#SubCategory').typeahead(
            {hint: true, highlight: true, minLength: 1},
            {name: 'SubCategoryList', displayKey: 'value',source: substringMatcher(SubCategoryList)}
        );
    }