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
    container.innerHTML = "";
    for (let i = 0; i < data.length; i++) {
        container.append(createElementRow(data[i], 'aushangAdminActiveRowTemplate'))
    }
}

async function loadPresetElementsTable() {
    let data: any[] = await loadPresetsAushang();
    let container = document.getElementById('presetsTable');
    container.innerHTML = "";
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


    let deleteButton = <HTMLButtonElement>row.getElementsByClassName('deleteElement').item(0);
    deleteButton.onclick = async () => {
        await deleteAushangById(dataset["id"]);
        await loadActiveElementsTable();
        await loadPresetElementsTable();
    }

    let moveElementUp = <HTMLButtonElement>row.getElementsByClassName('moveElementUp').item(0);
    moveElementUp.onclick = async () => {
        await moveAushang(dataset["id"], 'up');
        await loadActiveElementsTable();
        await loadPresetElementsTable();
    }
    let moveElementDown = <HTMLButtonElement>row.getElementsByClassName('moveElementDown').item(0);
    moveElementDown.onclick = async () => {
        await moveAushang(dataset["id"], 'down');
        await loadActiveElementsTable();
        await loadPresetElementsTable();
    }
    let createPreset = <HTMLButtonElement>row.getElementsByClassName('createPreset').item(0);
    if(createPreset != null){
        createPreset.onclick = async () => {
            let locDataset = dataset;
            locDataset["type"] = 3;
            await addAushang(locDataset);
            await loadPresetElementsTable();
        }
    }


    return row;
}


async function submitElementToApi() {
    let content;
    let contentContainer = <HTMLInputElement>document.getElementById('web.0.content');
    let content2;
    let content2Container = <HTMLInputElement>document.getElementById('web.0.content2');
    let colorSelector = <HTMLSelectElement>document.getElementById("web.0.color");

    let dataset = {
        "type": 1,
        "color": "",
        "content": []
    }

    // @ts-ignore
    $.notify("Bitte warten", "info");

    content = contentContainer.value;
    content2 = content2Container.value;

    if (content == "" || content == null) {
        // @ts-ignore
        $(document.getElementById('web.0.content')).notify("Inhalt eingeben");
        return
    }
    dataset.content.push(content);

    if (!(content2 === "" || content2 == null)) {
        dataset.content.push(content2);
    }

    dataset.color = colorSelector.options[colorSelector.selectedIndex].value;
    if (dataset.color === "none") {
        // @ts-ignore
        $(colorSelector).notify("Farbe auswählen");
        return
    }

    try {
        await addAushang(dataset);
        loadActiveElementsTable();
        // @ts-ignore
        $.notify("Erstellt", "success");
    } catch (e) {
        console.error(e);
    }

    resetInput();
}

function enableEditing(button: HTMLButtonElement) {
    /*
    let row = <HTMLTableRowElement>button.parentElement.parentElement.parentElement;
    console.log(row)
    row.getElementsByTagName('textarea').item(0).disabled = false;
    row.getElementsByTagName('textarea').item(1).disabled = false;
    row.getElementsByTagName('select').item(0).disabled = false;
    // @ts-ignore
    $(row).notify("Bearbeitung aktiv", "success");
     */
}

document.addEventListener("DOMContentLoaded", async () => {
    console.log("DOMContentLoaded");
    loadActiveElementsTable();
    loadPresetElementsTable();
});