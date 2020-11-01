/*
 * MIT License
 *
 * Copyright (c) 2020. Nils Witt
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

let username = window.localStorage.getItem("username");
let password = window.localStorage.getItem("password");
function loadActive() {
    let request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("active").innerHTML = this.responseText;
            adjustTextAreas();
        }
    };
    request.open("GET", "xmlhttp/xmlhttpAushang.php?action=load&username=" + username + "&password=" + password, true);
    request.send();
}

function loadPresets() {
    let request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            document.getElementById("presets").innerHTML = this.responseText;
            adjustTextAreas();
        }
    };
    request.open("GET", "xmlhttp/xmlhttpAushang.php?action=presets&username=" + username + "&password=" + password, true);
    request.send();
}

function addElementFromWeb() {
    let request;
    let content;
    let content2;
    let colorSelector;
    let color;
    let json;
    content = (<HTMLInputElement>document.getElementById('web.0.content')).value;
    content2 = (<HTMLInputElement>document.getElementById('web.0.content2')).value;
    if (content === "" || content == null) {
        // @ts-ignore
        $(document.getElementById('web.0.content')).notify("Inhalt eingeben");
        return
    }
    colorSelector = document.getElementById("web.0.color");
    color = colorSelector.options[colorSelector.selectedIndex].value;
    if (color === "none") {
        // @ts-ignore
        $(colorSelector).notify("Farbe auswählen");
        return
    }
    request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            load();
            // @ts-ignore
            $.notify("Erstellt", "success");
        }
    };
    request.open("POST", "xmlhttp/xmlhttpAushang.php?action=create&username=" + username + "&password=" + password);
    json = JSON.stringify({"content1": content, "content2": content2, "color": color});
    console.log(json);
    request.setRequestHeader("Content-Type", "application/json");
    request.send(json);
    // @ts-ignore
    $.notify("Bitte warten", "info");
    resetInput();
}

function addElementFromPresets(id) {
    let content;
    let content2;
    let colorSelector;
    let color;
    let request;
    content = (<HTMLInputElement>document.getElementById('textarea.' + id + '.content')).value;
    content2 = "";
    // @ts-ignore
    if (document.getElementById('textarea.' + id + '.content2') != null && document.getElementById('textarea.' + id + '.content2') !== "") {
        content2 = (<HTMLInputElement>document.getElementById('textarea.' + id + '.content2')).value;
    }
    colorSelector = document.getElementById("color." + id);
    color = colorSelector.options[colorSelector.selectedIndex].value;
    request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            load();
        }
    };
    request.open("POST", "xmlhttp/xmlhttpAushang.php?action=create&username=" + username + "&password=" + password);
    let json = JSON.stringify({"id": id, "content1": content, "content2": content2, "color": color});
    request.setRequestHeader("Content-Type", "application/json");
    request.send(json);
}

function addElementToPresets(id) {
    let content;
    let content2 = "";
    let colorSelector;
    let color;
    let request;
    let json;
    content = (<HTMLInputElement>document.getElementById('textarea.' + id + '.content')).value;
    // @ts-ignore
    if (document.getElementById('textarea.' + id + '.content2') != null && document.getElementById('textarea.' + id + '.content2') !== "") {
        let content2 = (<HTMLInputElement>document.getElementById('textarea.' + id + '.content2')).value;
    }
    colorSelector = document.getElementById("color." + id);
    color = colorSelector.options[colorSelector.selectedIndex].value;
    request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            load();
        }
    };
    request.open("POST", "xmlhttp/xmlhttpAushang.php?action=createPreset&username=" + username + "&password=" + password);
    json = JSON.stringify({"id": id, "content1": content, "content2": content2, "color": color});
    request.setRequestHeader("Content-Type", "application/json");
    request.send(json);
}/*Manage Functions */
function editElementFromApi(id) {
    let row;
    row = document.getElementById('row.' + id);
    (<HTMLInputElement>document.getElementById("textarea." + id + ".content")).disabled = false;
    if (!!document.getElementById("textarea." + id + ".content2")) {
        (<HTMLInputElement>document.getElementById("textarea." + id + ".content2")).disabled = false;
    }
    (<HTMLInputElement>document.getElementById('color.' + id)).disabled = false;
    document.getElementById('edit.' + id).style.display = "none";
    document.getElementById('save.' + id).style.display = "block";
    // @ts-ignore
    $(row).notify("Bearbeitung aktiv", "success");
}

function updateToApi(id) {
    let content;
    let content2;
    let colorSelector;
    let request;
    let color;
    let json;
    content = (<HTMLInputElement>document.getElementById('textarea.' + id + '.content')).value;
    if (!!document.getElementById('textarea.' + id + '.content2')) {
        content2 = (<HTMLInputElement>document.getElementById('textarea.' + id + '.content2')).value
    }
    colorSelector = document.getElementById('color.' + id);
    color = colorSelector.options[colorSelector.selectedIndex].value;
    request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            load();
            // @ts-ignore
            $(row).notify("Gespeichert");
        }
    };
    request.open("POST", "xmlhttp/xmlhttpAushang.php?action=update&username=" + username + "&password=" + password);
    request.setRequestHeader("Content-Type", "application/json");
    if (content2 != null) {
        json = JSON.stringify({"id": id, "content1": content, "content2": content2, "color": color});
    } else {
        json = JSON.stringify({"id": id, "content1": content, "color": color});
    }
    request.send(json);
    console.log(json);
}

function moveElement(id, direction, type) {
    let request;
    request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            load();
        }
    };
    if (type === "1") {
        request.open("POST", "xmlhttp/xmlhttpAushang.php?action=move&username=" + username + "&password=" + password);
    } else {
        request.open("POST", "xmlhttp/xmlhttpAushang.php?action=movePreset&username=" + username + "&password=" + password);
    }
    request.setRequestHeader("Content-Type", "application/json");
    request.send(JSON.stringify({"id": id, "direction": direction}));
}/*remove Functions */

function removeElementFromApi(id) {
    let request;
    request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            load();
            // @ts-ignore
            $.notify("Gelöscht", "success");
        }
    };
    request.open("POST", "xmlhttp/xmlhttpAushang.php?action=delete&username=" + username + "&password=" + password);
    request.setRequestHeader("Content-Type", "application/json");
    request.send(JSON.stringify({"id": id}));
}