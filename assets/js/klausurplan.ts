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

async function loadKlausurenTable() {

    document.getElementById('klausurenTableBody').innerHTML = klausurenParse(await loadDataKlausuren()).innerHTML;
}

function klausurenParse(data) {

    let container = <HTMLTableSectionElement>document.createElement('tbody');

    let date = "";
    let grade = "";

    for (let entry in data) {
        let exam = data[entry];

        if (exam["date"] != date) {
            let dayHeader = <HTMLTableSectionElement>document.getElementById('klausurenDayHeaderTemplate').cloneNode(true);
            container.append(dayHeader)
            dayHeader.getElementsByClassName('weekday').item(0).innerHTML = getWeekdayByDate(exam["date"]).substr(0, 2);
            dayHeader.getElementsByClassName('date').item(0).innerHTML = klausurenDatum(exam["date"]);
            date = exam["date"];
            grade = exam["grade"];
        } else if (exam["grade"] != grade) {
            container.append(document.getElementById('gradeSpaceholderTemplate').cloneNode(true));
            grade = exam["grade"];
        }

        let color = "#000000";
        if (exam["grade"] === "EF") {
            color = "#C00000";
        } else if (exam["grade"] === "Q2") {
            color = "#00B050";
        } else if (exam["grade"] === "Q1") {
            color = "#0000C0";
        }

        let eventRow = <HTMLTableRowElement>document.getElementById('klausurenRowTamplate').cloneNode(true);
        container.appendChild(eventRow);

        let timeFrameTd = <HTMLTableRowElement>eventRow.getElementsByClassName("timeframe").item(0);
        let courseTd = <HTMLTableRowElement>eventRow.getElementsByClassName("course").item(0);
        let teacherTd = <HTMLTableRowElement>eventRow.getElementsByClassName("teacher").item(0);
        let roomTd = <HTMLTableRowElement>eventRow.getElementsByClassName("room").item(0);

        timeFrameTd.style.color = color;
        courseTd.style.color = color;
        teacherTd.style.color = color;

        timeFrameTd.innerText = exam["from"].substr(0, 5) + "-" + exam["to"].substr(0, 5);
        teacherTd.innerHTML = exam["teacher"];
        roomTd.innerHTML = exam["room"];

        for (const supervisorsKey in exam["supervisors"]) {
            try {
                let column = <HTMLTableRowElement>eventRow.getElementsByClassName("r" + supervisorsKey).item(0);
                column.innerText = exam["supervisors"][supervisorsKey];

            } catch (e) {
                console.log(e);
            }
        }

        if (exam["grade"] == null) {
            courseTd.innerText = exam["course"];
        } else {
            courseTd.innerText = exam["grade"] + ' / ' + exam["course"];
        }
    }
    return container;
}

function klausurenDatum(datum) {
    let date = new Date(datum);
    let day = date.getDate();
    let month = date.getMonth();
    let year = date.getFullYear();
    return day + "." + (month + 1) + "." + year;
}

//Wait for Dom ready
document.addEventListener("DOMContentLoaded", async () => {
    await loadKlausurenTable();
});