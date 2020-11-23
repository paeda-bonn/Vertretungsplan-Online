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
/*
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOMContentLoaded");
    load();
});



function load() {
    loadActive();
    loadPresets();
}
*/
function textAreaAdjust(textarea) {
    textarea.style.height = "1px";
    textarea.style.height = (3 + textarea.scrollHeight) + "px";
}

function adjustTextAreas() {
    let elements = document.getElementsByTagName('textarea');
    for (var i = 0; i < elements.length; i++) {
        textAreaAdjust(elements[i])
    }
}

function resetInput() {
    let web0content = <HTMLInputElement>document.getElementById('web.0.content');
    let web0content2 = <HTMLInputElement>document.getElementById('web.0.content2');
    web0content.value = "";
    web0content2.value = ""
}

function getWeekday(weekday) {
    const weekdays = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];
    return weekdays[weekday - 1];
}

function getWeekdayByDate(datum) {
    let date = new Date(datum);
    let weekday = date.getDay();
    return getWeekday(weekday);
}

function getMonth(month) {
    const months = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];
    return months[month];
}

