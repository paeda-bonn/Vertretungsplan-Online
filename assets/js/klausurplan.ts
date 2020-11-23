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
const api_URLKL = "https://vplan.moodle-paeda.de/apiBeta/index.php"

async function loadDataKlausuren() {
    return new Promise(async (resolve, reject) => {
        let res;
        try {
            let myHeaders = new Headers();
            myHeaders.append("Authorization", "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJ2cGxhbi5tb29kbGUtcGFlZGEuZGUiLCJhdWQiOiJ2cGxhbi5tb29kbGUtcGFlZGEuZGUiLCJ1c2VybmFtZSI6ImFkbWluIiwidXNlcnR5cGUiOiJhZG1pbiJ9.FIHqq-IL7dShts5PRfVi0Pm2m-wXXOWJvPgw2MEUDjjR8So4S3WcXYLqeBShHFifoShEPW9rBkAsY524-tl5Oeyw2TpluccsDZC7HmJS5A1Iqh6JefnolJ1Xoa3fw5mB_9ZEs0OsbYHZFE2Z55Jmmm1diQCIJALCfbOqOqHxatQto-88pFc1tHoUcVSniEWt0F3Ju4o7jP1-uZpRGZDSVstSzG8KKn-wptFXiv8Fxh43lOZaRZr9fM0wWRYwmyKVFL84bq_FiEBrms01k2yyGl86aCmToJhK_aMKhsv94GgAiM9MPdBY6fyGFKktAhtxwJki5lmu8qMHf0n1xGV6YhNDJQCLHdqS6G9uPTwgJbgE9jem46SdJnRD2kRVLCib2O1m1zMKmw9iMa8Yxd-Jzpj8gJSsVv5KIS5icse01zClCUDlxsIohyD_3XH-sUftQk9yMir_OcEsR4cu0bWp_aiNjW9BV2X0w2SVgxa2vQjHUjkXzrtT9fiyWa56KXLyf22_M-aoOe4wE9kuyaauoFrFyrLxrJ-Cb5igPZ3GHVS9RR-DWYUxYPcX5tY-YiCF8Qs7rmJ1eEMSmOpKiAcnEhK82nYDDQeaLMG06dBj9XYe4Dc5uq6ge1Z9l3nAPRF5pTby1JMfCdAm3m8quN6hBi89lEBmAu__BBHKk5As4x4");

            let requestOptions: any = {
                method: 'GET',
                headers: myHeaders,
                redirect: 'follow'
            };

            res = await fetch(api_URLKL + "/klausuren/active", requestOptions);
        } catch (e) {
            console.log(e);
        }

        if (res.status === 200) {
            document.getElementById('klausurenTableBody').innerHTML = klausurenParse(await res.json()).innerHTML;
        }
        resolve();
    });
}

function klausurenParse(data) {

    let container = <HTMLTableSectionElement>document.createElement('tbody');

    let date = "";
    let grade = "";

    for (let entry in data) {
        let exam = data[entry];

        if(exam["date"] != date){
            let dayHeader = <HTMLTableSectionElement>document.getElementById('klausurenDayHeaderTemplate').cloneNode(true);
            container.append(dayHeader)
            dayHeader.getElementsByClassName('weekday').item(0).innerHTML = klausurenGetWeekday(exam["date"]).substr(0,2);
            dayHeader.getElementsByClassName('date').item(0).innerHTML = klausurenDatum(exam["date"]);
            date = exam["date"];
            grade = exam["grade"];
        }else if(exam["grade"] != grade){
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
        let r1Td = <HTMLTableRowElement>eventRow.getElementsByClassName("r1").item(0);

        timeFrameTd.style.color = color;
        courseTd.style.color = color;
        teacherTd.style.color = color;

        timeFrameTd.innerText = exam["from"].substr(0,5) + "-" + exam["to"].substr(0,5);
        teacherTd.innerHTML = exam["teacher"];
        roomTd.innerHTML = exam["room"];

        for (const supervisorsKey in exam["supervisors"]) {
            console.log(supervisorsKey);
            try {
                let column = <HTMLTableRowElement>eventRow.getElementsByClassName("r" + supervisorsKey).item(0);
                column.innerText = exam["supervisors"][supervisorsKey];

            }catch (e){
                console.log(e);
            }
        }

        if (exam["grade"] == null) {
            courseTd.innerText = exam["course"];
        } else {
            courseTd.innerText = exam["grade"] + ' / ' + exam["course"];
        }


        /*

        if (data.hasOwnProperty(date)) {
            let weekday;
            let day;
            for (let grade in data[date]) {
                if (data[date].hasOwnProperty(grade)) {
                    for (let entry in data[date][grade]) {
                        if (data[date][grade].hasOwnProperty(entry)) {
                            day = klausurenDatum(data[date][grade][entry]["date"]);
                            weekday = klausurenGetWeekday(data[date][grade][entry]["date"]);
                        }
                    }
                }
            }

            let k = 0;
            for (let grade in data[date]) {
                if (data[date].hasOwnProperty(grade)) {
                    for (let entry in data[date][grade]) {
                        if (data[date][grade].hasOwnProperty(entry)) {
                            let klausur = data[date][grade][entry];
                            let color = "#000000";
                            if (klausur["grade"] === "EF") {
                                color = "#C00000";
                            } else if (klausur["grade"] === "Q2") {
                                color = "#00B050";
                            } else if (klausur["grade"] === "Q1") {
                                color = "#0000C0";
                            }

                            if (k === 0) {
                                let dayHeader = <HTMLTableSectionElement>document.getElementById('klausurenDayHeaderTemplate').cloneNode(true);
                                container.append(dayHeader)

                                dayHeader.getElementsByClassName('weekday').item(0).innerHTML = weekday;
                                dayHeader.getElementsByClassName('date').item(0).innerHTML = day;
                                k = 1;
                            }

                            let eventRow = <HTMLTableRowElement>document.getElementById('klausurenRowTamplate').cloneNode(true);
                            container.appendChild(eventRow);

                            let timeFrameTd = <HTMLTableRowElement>eventRow.getElementsByClassName("timeframe").item(0);
                            let courseTd = <HTMLTableRowElement>eventRow.getElementsByClassName("course").item(0);
                            let teacherTd = <HTMLTableRowElement>eventRow.getElementsByClassName("teacher").item(0);
                            let roomTd = <HTMLTableRowElement>eventRow.getElementsByClassName("room").item(0);
                            let r1Td = <HTMLTableRowElement>eventRow.getElementsByClassName("r1").item(0);
                            let r2Td = <HTMLTableRowElement>eventRow.getElementsByClassName("r2").item(0);
                            let r3Td = <HTMLTableRowElement>eventRow.getElementsByClassName("r3").item(0);
                            let r4Td = <HTMLTableRowElement>eventRow.getElementsByClassName("r4").item(0);
                            let r5Td = <HTMLTableRowElement>eventRow.getElementsByClassName("r5").item(0);
                            let r6Td = <HTMLTableRowElement>eventRow.getElementsByClassName("r6").item(0);
                            let r7Td = <HTMLTableRowElement>eventRow.getElementsByClassName("r7").item(0);

                            timeFrameTd.style.color = color;
                            courseTd.style.color = color;
                            teacherTd.style.color = color;

                            timeFrameTd.innerText = klausur["Std"];
                            teacherTd.innerHTML = klausur["teacher"];
                            roomTd.innerHTML = klausur["room"];
                            r1Td.innerHTML = klausur["1"];
                            r2Td.innerHTML = klausur["2"];
                            r3Td.innerHTML = klausur["3"];
                            r4Td.innerHTML = klausur["4"];
                            r5Td.innerHTML = klausur["5"];
                            r6Td.innerHTML = klausur["6"];
                            r7Td.innerHTML = klausur["7"];
                            if (klausur["Stufe"] == null) {
                                courseTd.innerText = klausur["Kurs"];
                            } else {
                                courseTd.innerText = klausur["Stufe"] + ' / ' + klausur["Kurs"];
                            }
                        }
                    }
                    container.append(document.getElementById('gradeSpaceholderTemplate').cloneNode(true));
                }
            }
           }
         */

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

function klausurenGetWeekday(datum) {
    let date = new Date(datum);
    let weekday = date.getDay();
    return getWeekday(weekday);
}

function getWeekday(weekday) {
    const weekdays = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
    return weekdays[weekday - 1];
}

//Wait for Dom ready
document.addEventListener("DOMContentLoaded", async () =>{
    await loadDataKlausuren();
});