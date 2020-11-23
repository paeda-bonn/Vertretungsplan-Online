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

const api_URL = "https://vplan.moodle-paeda.de/apiBeta/index.php"
const authKey = "yJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJ2cGxhbi5tb29kbGUtcGFlZGEuZGUiLCJhdWQiOiJ2cGxhbi5tb29kbGUtcGFlZGEuZGUiLCJ1c2VybmFtZSI6ImFkbWluIiwidXNlcnR5cGUiOiJhZG1pbiJ9.FIHqq-IL7dShts5PRfVi0Pm2m-wXXOWJvPgw2MEUDjjR8So4S3WcXYLqeBShHFifoShEPW9rBkAsY524-tl5Oeyw2TpluccsDZC7HmJS5A1Iqh6JefnolJ1Xoa3fw5mB_9ZEs0OsbYHZFE2Z55Jmmm1diQCIJALCfbOqOqHxatQto-88pFc1tHoUcVSniEWt0F3Ju4o7jP1-uZpRGZDSVstSzG8KKn-wptFXiv8Fxh43lOZaRZr9fM0wWRYwmyKVFL84bq_FiEBrms01k2yyGl86aCmToJhK_aMKhsv94GgAiM9MPdBY6fyGFKktAhtxwJki5lmu8qMHf0n1xGV6YhNDJQCLHdqS6G9uPTwgJbgE9jem46SdJnRD2kRVLCib2O1m1zMKmw9iMa8Yxd-Jzpj8gJSsVv5KIS5icse01zClCUDlxsIohyD_3XH-sUftQk9yMir_OcEsR4cu0bWp_aiNjW9BV2X0w2SVgxa2vQjHUjkXzrtT9fiyWa56KXLyf22_M-aoOe4wE9kuyaauoFrFyrLxrJ-Cb5igPZ3GHVS9RR-DWYUxYPcX5tY-YiCF8Qs7rmJ1eEMSmOpKiAcnEhK82nYDDQeaLMG06dBj9XYe4Dc5uq6ge1Z9l3nAPRF5pTby1JMfCdAm3m8quN6hBi89lEBmAu__BBHKk5As4x4";

const orderBy = ["Kurs","Stunde"];

function getWeekday(weekday) {
    const weekdays = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
    return weekdays[weekday - 1];
}

function getMonth(month) {
    const months = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    return months[month];
}

function timeDisplay(time) {
    let timestamp = new Date(time).getTime();
    let date = new Date(timestamp);
    let weekday = date.getDay();
    let day = date.getDate();
    let month = date.getMonth();
    let year = date.getFullYear();
    return getWeekday(weekday) + ", " + day + ". " + getMonth(month) + " " + year;
}


async function loadVplan() {
    //TODO set last refreshed
    let activeDays = await loadActiveDays();
    for (let i = 0; i < activeDays.length; i++) {
        let date = activeDays[i];
        if (date != "") {
            let dayContainer = <HTMLDivElement>document.getElementById('dayPreset').cloneNode(true);
            dayContainer.id = "Container-" + date;
            document.getElementById('tableContainer').append(dayContainer);

            (<HTMLSpanElement>dayContainer.getElementsByClassName('dateContainer').item(0)).innerText = timeDisplay(date);

            let eventsContainer = dayContainer.getElementsByTagName('tbody').item(1);
            eventsContainer.innerHTML = "";
            let events = await fetchByDay(date);
            events.sort(function (e1, e2) {
                if (e1["Kurs"] < e2["Kurs"]) {
                    return -1;
                } else if (e1["Kurs"] > e2["Kurs"]) {
                    return 1;
                }else{
                    if (e1["Stunde"] < e2["Stunde"]) {
                        return -1;
                    } else if (e1["Stunde"] > e2["Stunde"]) {
                        return 1;
                    }else {
                        return 0;
                    }
                }

            });

            for (let j = 0; j < events.length; j++) {

                let event = events[j];
                if(events[j+1] != null){
                    let next = events[j+1];
                    if(event["Datum"] == next["Datum"]){
                        if(event["Kurs"] == next["Kurs"]){
                            if(event["Fach"] == next["Fach"]){
                                if(event["FachNew"] == next["FachNew"]){
                                    if(event["Lehrer"] == next["Lehrer"]){
                                        if(event["LehrerNeu"] == next["LehrerNeu"]){
                                            if(event["RaumNew"] == next["RaumNew"]){
                                                event["Stunde"] = event["Stunde"] + " / " + next["Stunde"];
                                                j++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                let row = <HTMLTableRowElement>document.getElementById('vplanRowTemplate').cloneNode(true);
                eventsContainer.append(row);
                row.getElementsByClassName("lesson").item(0).innerHTML = event["Stunde"];
                row.getElementsByClassName("course").item(0).innerHTML = event["Kurs"];
                row.getElementsByClassName("subject").item(0).innerHTML = event["Fach"];
                row.getElementsByClassName("newSubject").item(0).innerHTML = event["FachNew"];
                row.getElementsByClassName("newTeacher").item(0).innerHTML = event["LehrerNeu"];
                row.getElementsByClassName("newTeacher").item(0).innerHTML = event["LehrerNeu"];
                row.getElementsByClassName("newRoom").item(0).innerHTML = event["RaumNew"];
                row.getElementsByClassName("info").item(0).innerHTML = event["info"];
            }
        }
    }
}


async function loadActiveDays() {
    let myHeaders = new Headers();
    myHeaders.append("Authorization", "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJ2cGxhbi5tb29kbGUtcGFlZGEuZGUiLCJhdWQiOiJ2cGxhbi5tb29kbGUtcGFlZGEuZGUiLCJ1c2VybmFtZSI6ImFkbWluIiwidXNlcnR5cGUiOiJhZG1pbiJ9.FIHqq-IL7dShts5PRfVi0Pm2m-wXXOWJvPgw2MEUDjjR8So4S3WcXYLqeBShHFifoShEPW9rBkAsY524-tl5Oeyw2TpluccsDZC7HmJS5A1Iqh6JefnolJ1Xoa3fw5mB_9ZEs0OsbYHZFE2Z55Jmmm1diQCIJALCfbOqOqHxatQto-88pFc1tHoUcVSniEWt0F3Ju4o7jP1-uZpRGZDSVstSzG8KKn-wptFXiv8Fxh43lOZaRZr9fM0wWRYwmyKVFL84bq_FiEBrms01k2yyGl86aCmToJhK_aMKhsv94GgAiM9MPdBY6fyGFKktAhtxwJki5lmu8qMHf0n1xGV6YhNDJQCLHdqS6G9uPTwgJbgE9jem46SdJnRD2kRVLCib2O1m1zMKmw9iMa8Yxd-Jzpj8gJSsVv5KIS5icse01zClCUDlxsIohyD_3XH-sUftQk9yMir_OcEsR4cu0bWp_aiNjW9BV2X0w2SVgxa2vQjHUjkXzrtT9fiyWa56KXLyf22_M-aoOe4wE9kuyaauoFrFyrLxrJ-Cb5igPZ3GHVS9RR-DWYUxYPcX5tY-YiCF8Qs7rmJ1eEMSmOpKiAcnEhK82nYDDQeaLMG06dBj9XYe4Dc5uq6ge1Z9l3nAPRF5pTby1JMfCdAm3m8quN6hBi89lEBmAu__BBHKk5As4x4");

    let requestOptions: any = {
        method: 'GET',
        headers: myHeaders,
        redirect: 'follow'
    };

    let res = await fetch(api_URL + "/vertretungsplan/activedays", requestOptions);
    return await res.json();

}

async function fetchByDay(date: String) {

    let myHeaders = new Headers();
    myHeaders.append("Authorization", "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJ2cGxhbi5tb29kbGUtcGFlZGEuZGUiLCJhdWQiOiJ2cGxhbi5tb29kbGUtcGFlZGEuZGUiLCJ1c2VybmFtZSI6ImFkbWluIiwidXNlcnR5cGUiOiJhZG1pbiJ9.FIHqq-IL7dShts5PRfVi0Pm2m-wXXOWJvPgw2MEUDjjR8So4S3WcXYLqeBShHFifoShEPW9rBkAsY524-tl5Oeyw2TpluccsDZC7HmJS5A1Iqh6JefnolJ1Xoa3fw5mB_9ZEs0OsbYHZFE2Z55Jmmm1diQCIJALCfbOqOqHxatQto-88pFc1tHoUcVSniEWt0F3Ju4o7jP1-uZpRGZDSVstSzG8KKn-wptFXiv8Fxh43lOZaRZr9fM0wWRYwmyKVFL84bq_FiEBrms01k2yyGl86aCmToJhK_aMKhsv94GgAiM9MPdBY6fyGFKktAhtxwJki5lmu8qMHf0n1xGV6YhNDJQCLHdqS6G9uPTwgJbgE9jem46SdJnRD2kRVLCib2O1m1zMKmw9iMa8Yxd-Jzpj8gJSsVv5KIS5icse01zClCUDlxsIohyD_3XH-sUftQk9yMir_OcEsR4cu0bWp_aiNjW9BV2X0w2SVgxa2vQjHUjkXzrtT9fiyWa56KXLyf22_M-aoOe4wE9kuyaauoFrFyrLxrJ-Cb5igPZ3GHVS9RR-DWYUxYPcX5tY-YiCF8Qs7rmJ1eEMSmOpKiAcnEhK82nYDDQeaLMG06dBj9XYe4Dc5uq6ge1Z9l3nAPRF5pTby1JMfCdAm3m8quN6hBi89lEBmAu__BBHKk5As4x4");

    let requestOptions: any = {
        method: 'GET',
        headers: myHeaders,
        redirect: 'follow'
    };
    let res = await fetch(api_URL + "/vertretungsplan/vertretungen/date/" + date, requestOptions);
    return await res.json();
}

//Wait for Dom ready
document.addEventListener("DOMContentLoaded", async () =>{
    await loadVplan();
});