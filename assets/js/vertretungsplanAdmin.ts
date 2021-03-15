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

async function loadDataUntis() {
    let json = await importUntis();
    let data;
    if (typeof json === "string") {
        data = JSON.parse(json)
    }
    let container = document.createElement("table");


    let daysTbody = <HTMLTableRowElement>document.getElementById('dayBodyTemplate').cloneNode(true);
    if(data.hasOwnProperty("activeDays")){
        for (let i = 0; i < data["activeDays"].length; i++) {
            let row = <HTMLTableRowElement>document.getElementById('dayRowTemplate').cloneNode(true);
            row.getElementsByTagName('td')[0].innerText = data["activeDays"][i];
            daysTbody.append(row);
        }
    }
    container.append(daysTbody);

    let datasetsTbody = <HTMLTableRowElement>document.getElementById('datasetsBody').cloneNode(true);
    if(data.hasOwnProperty("events")){
        for (let i = 0; i < data["events"].length; i++) {
            let event = data["events"][i];

            let row = <HTMLTableRowElement>document.getElementById('datasetRowTemplate').cloneNode(true);
            row.getElementsByClassName("date").item(0).innerHTML = event["date"];
            row.getElementsByClassName("lesson").item(0).innerHTML = event["lessons"];
            row.getElementsByClassName("course").item(0).innerHTML = event["course"];
            row.getElementsByClassName("subject").item(0).innerHTML = event["subject"];
            row.getElementsByClassName("newSubject").item(0).innerHTML = event["newSubject"];
            row.getElementsByClassName("newTeacher").item(0).innerHTML = event["newTeacher"];
            row.getElementsByClassName("newRoom").item(0).innerHTML = event["room"];
            row.getElementsByClassName("info").item(0).innerHTML = event["info"];
            datasetsTbody.append(row);
        }
    }
    container.append(datasetsTbody);

    document.getElementById('responseData').innerHTML = container.innerHTML;
    if (typeof json === "string") {
        //document.getElementById('container').innerHTML = json;
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    loadDataUntis();
});