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

const orderBy = ["Kurs", "Stunde"];

const lessonTimes = {
    "1": 515,
    "2": 560,
    "3": 625,
    "4": 675,
    "5": 740,
    "6": 785,
    "7": 840,
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
    let activeDays: string[] = await loadVplanActiveDays();
    let nowDate = new Date();

    let todayString = nowDate.getFullYear() + "-" + (nowDate.getMonth() + 1).toString().padStart(2, "0") + "-" + nowDate.getDate().toString().padStart(2, "0")
    if (!activeDays.includes(todayString) && nowDate.getHours() < 16) {
        activeDays.push(todayString);
    }
    activeDays.sort();

    for (let i = 0; i < activeDays.length; i++) {
        let date = activeDays[i];
        if (date != "") {
            let dayContainer = <HTMLDivElement>document.getElementById('dayPreset').cloneNode(true);
            dayContainer.id = "Container-" + date;
            document.getElementById('tableContainer').append(dayContainer);

            (<HTMLSpanElement>dayContainer.getElementsByClassName('dateContainer').item(0)).innerText = timeDisplay(date);

            let eventsContainer = dayContainer.getElementsByTagName('tbody').item(1);
            eventsContainer.innerHTML = "";
            let events = await fetchVplanByDay(date);
            events.sort(function (e1, e2) {
                if (e1["Kurs"] < e2["Kurs"]) {
                    return -1;
                } else if (e1["Kurs"] > e2["Kurs"]) {
                    return 1;
                } else {
                    if (e1["Stunde"] < e2["Stunde"]) {
                        return -1;
                    } else if (e1["Stunde"] > e2["Stunde"]) {
                        return 1;
                    } else {
                        return 0;
                    }
                }
            });

            if (todayString == date) {
                for (let j = 0; j < events.length; j++) {
                    let event = events[j];
                    if (lessonTimes[event["Stunde"]] < ((nowDate.getHours() * 60) + nowDate.getMinutes())) {
                        events.splice(j, 1);
                        j--;
                    }
                }
            }

            for (let j = 0; j < events.length; j++) {
                let event = events[j];

                if (events[j + 1] != null) {
                    let next = events[j + 1];
                    if (event["Datum"] == next["Datum"]) {
                        if (event["Kurs"] == next["Kurs"]) {
                            if (event["Fach"] == next["Fach"]) {
                                if (event["FachNew"] == next["FachNew"]) {
                                    if (event["Lehrer"] == next["Lehrer"]) {
                                        if (event["LehrerNeu"] == next["LehrerNeu"]) {
                                            if (event["RaumNew"] == next["RaumNew"]) {
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

//Wait for Dom ready
document.addEventListener("DOMContentLoaded", async () => {
    await loadVplan();
});