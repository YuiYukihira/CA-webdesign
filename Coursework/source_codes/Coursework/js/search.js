// Store the names of the values that need to be sent
var submitNames = [];

// Perform a get request to getStock.php and replace the table "newTable" with the results
function showStock() {
    submitNames = [];
    
    // Get the values of all the search terms
    sID = document.getElementById("getStockID").value;
    sName = document.getElementById("getStockName").value;
    sPrice = document.getElementById("getStockPrice").value;
    sQuantity = document.getElementById("getStockQuantity").value;
    sModOn = document.getElementById("getStockModOn").value;
    sModBy = document.getElementById("getStockModBy").value;
    sCreateBy = document.getElementById("getStockCreateBy").value;
    sCreateOn = document.getElementById("getStockCreateOn").value;
    
    // Use an asterisk to represent a wildcard (all values)
    if (sName == "") {
        sName = "*";
    }
    
    // Create a start of a query using the name search term
    query = "getStock.php?name=" + sName;
    
    // Add the extra search terms if they are present
    if (sID) {
        query += "&id=" + sID;
    }
    
    if (sPrice) {
        query += "&price=" + sPrice;
    }
    if (sQuantity) {
        query += "&quantity=" + sQuantity;
    }
    
    if (sModOn) {
        query += "&modon=" + sModOn;
    }
    
    if  (sModBy) {
        query += "&modby=" + sModBy;
    }
    
    if (sCreateBy) {
        query += "&createby=" + sCreateBy;
    }
    
    if (sCreateOn) {
        query += "&createon=" + sCreateOn;
    }
    
    // Get an object for performing a get request
    if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
    } else {
        // Needed for older browsers (IE5/6)
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    
    // Update the contents of the table once the get request is complete
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("newTable").innerHTML = this.responseText;
        }
    };
    
    // Make the request
    xmlhttp.open("GET", query);
    xmlhttp.send();
}

// Add an input onto the list of ones to send
function add(name) {
    if(submitNames.indexOf(name) == -1) {
        submitNames.push(name);
    }
}

// Check if a given input has been changed
function is_changed(name) {
    for(var i = 0; i < submitNames.length; i++) {
        if(name == submitNames[i]) {
            return name && true;
        }
    }
    return false;
}

// Disabled inputs are not sent in a post request, so by disabling the inputs that have not changed we don't send them
function before_submit() {
    var allInputs = document.forms["QuantityForm"].getElementsByClassName("QuantityChange");
    for(var i = 0; i < allInputs.length; i++) {
        var name = allInputs[i].name;
        if(!is_changed(name)) {
            allInputs[i].disabled = true;
        }
    }
}

// Clear the list of names that are in submitList and resets all the forms to the default value (null)
function reset() {
    submitNames = [];
    var allInputs = document.forms["QuantityForm"].getElementsByClassName("QuantityChange");
    for(var i = 0; i < allInputs.length; i++) {
        var input = allInputs[i];
        input.value = null;
    }
}
