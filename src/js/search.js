var submitNames = [];

function showStock() {
    submitNames = [];
    
    sID = document.getElementById("getStockID").value;
    sName = document.getElementById("getStockName").value;
    sPrice = document.getElementById("getStockPrice").value;
    sQuantity = document.getElementById("getStockQuantity").value;
    sModOn = document.getElementById("getStockModOn").value;
    sModBy = document.getElementById("getStockModBy").value;
    sCreateBy = document.getElementById("getStockCreateBy").value;
    sCreateOn = document.getElementById("getStockCreateOn").value;
    
    if (sName == "") {
        sName = "*";
    }
    
    query = "getStock.php?name=" + sName;
    
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
    
    if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
    } else {
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }
    
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("newTable").innerHTML = this.responseText;
            console.log(this.responseText);
        }
    };
    xmlhttp.open("GET", query);
    xmlhttp.send();
}

function add(name) {
    if(submitNames.indexOf(name) == -1) {
        submitNames.push(name);
    }
}

function is_changed(name) {
    for(var i = 0; i < submitNames.length; i++) {
        if(name == submitNames[i]) {
            return name && true;
        }
    }
    return false;
}

function before_submit() {
    var allInputs = document.forms["QuantityForm"].getElementsByClassName("QuantityChange");
    for(var i = 0; i < allInputs.length; i++) {
        var name = allInputs[i].name;
        if(!is_changed(name)) {
            allInputs[i].disabled = true;
        }
    }
}


function reset() {
    submitNames = [];
    var allInputs = document.forms["QuantityForm"].getElementsByClassName("QuantityChange");
    for(var i = 0; i < allInputs.length; i++) {
        var input = allInputs[i];
        input.value = null;
    }
}
