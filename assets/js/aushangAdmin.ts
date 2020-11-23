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

const colors = {
    "#00000": "Schwarz"
}


async function loadActiveElementsTable() {
    let data: any[] = await loadDataAushang();
    let container = document.getElementById('activeTable');
    for (let i = 0; i < data.length; i++) {
        container.append(createElementRow(data[i], 'aushangAdminActiveRowTemplate'))
    }
}

async function loadPresetElementsTable() {
    let data: any[] = await loadPresetsAushang();
    let container = document.getElementById('presetsTable');
    for (let i = 0; i < data.length; i++) {
        container.append(createElementRow(data[i], 'aushangAdminPresetRowTemplate'))
    }
}

function createElementRow(dataset, presetId) {
    let row = <HTMLTableRowElement>document.getElementById(presetId).cloneNode(true);

    let leftContentArea = <HTMLAreaElement>row.getElementsByClassName('textAreaLeft').item(0);
    leftContentArea.innerText = dataset["content"][0];
    let rightContentArea = <HTMLAreaElement>row.getElementsByClassName('textAreaRight').item(0);
    if (dataset["content"].length > 1) {
        rightContentArea.innerText = dataset["content"][1];
    } else {
        rightContentArea.innerText = "Not active";
    }

    let colorColumn = <HTMLTableCellElement>row.getElementsByClassName('colorColumn').item(0);
    colorColumn.style.backgroundColor = dataset["color"];
    let colorSelector = <HTMLTableCellElement>colorColumn.getElementsByClassName('colorColumn').item(0);

    return row;
}


function submitElementToApi() {
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
            loadActiveElementsTable();
            // @ts-ignore
            $.notify("Erstellt", "success");
        }
    };

    //request.open("POST", "xmlhttp/xmlhttpAushang.php?action=create&username=" + username + "&password=" + password);
    json = JSON.stringify({"content1": content, "content2": content2, "color": color});
    console.log(json);
    request.setRequestHeader("Content-Type", "application/json");
    request.send(json);
    // @ts-ignore
    $.notify("Bitte warten", "info");
    resetInput();
}

function createElementFromPresets(id) {
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
            loadActiveElementsTable();
        }
    };
    request.open("POST", "xmlhttp/xmlhttpAushang.php?action=createPreset&username=" + "&password=");
    json = JSON.stringify({"id": id, "content1": content, "content2": content2, "color": color});
    request.setRequestHeader("Content-Type", "application/json");
    request.send(json);
}/*Manage Functions */

function enableEditing(button: HTMLButtonElement) {
    let row = <HTMLTableRowElement>button.parentElement.parentElement.parentElement;

    console.log(row)

    row.getElementsByTagName('textarea').item(0).disabled = false;
    row.getElementsByTagName('textarea').item(1).disabled = false;
    row.getElementsByTagName('select').item(0).disabled = false;
    //TODO enable saveButton

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
            loadActiveElementsTable();
            // @ts-ignore
            $(row).notify("Gespeichert");
        }
    };
    request.open("POST", "xmlhttp/xmlhttpAushang.php?action=update&username=" + "&password=");
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
            //TODO Filter
            loadActiveElementsTable();
            loadPresetElementsTable();
        }
    };
    if (type === "1") {
        request.open("POST", "xmlhttp/xmlhttpAushang.php?action=move&username=" + "&password=");
    } else {
        request.open("POST", "xmlhttp/xmlhttpAushang.php?action=movePreset&username=" + "&password=");
    }
    request.setRequestHeader("Content-Type", "application/json");
    request.send(JSON.stringify({"id": id, "direction": direction}));
}/*remove Functions */

function deleteElementById(id) {
    let request;
    request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            loadActiveElementsTable();
            // @ts-ignore
            $.notify("Gelöscht", "success");
        }
    };
    request.open("POST", "xmlhttp/xmlhttpAushang.php?action=delete&username=" + "&password=");
    request.setRequestHeader("Content-Type", "application/json");
    request.send(JSON.stringify({"id": id}));
}

document.addEventListener("DOMContentLoaded", async () => {
    console.log("DOMContentLoaded");
    loadActiveElementsTable();
    loadPresetElementsTable();
});