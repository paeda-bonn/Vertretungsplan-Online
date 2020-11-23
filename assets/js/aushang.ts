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

document.addEventListener("DOMContentLoaded", async () => {
    let dataArray: any[] = await loadDataAushang();
    let container = document.getElementById('aushangTableBody');
    for (let i = 0; i < dataArray.length; i++) {
        let dataset = dataArray[i];

        let dataRow = <HTMLTableRowElement>document.getElementById('aushangRowTemplate').cloneNode(true);
        container.append(dataRow);
        let column = <HTMLTableCellElement> dataRow.getElementsByTagName('td').item(0);
        column.innerText = dataset["content"][0];
        for (let j = 1; j < dataset["content"].length; j++) {
            console.log(j);
            column.colSpan = 1;
            column = <HTMLTableCellElement> column.cloneNode(true);
            dataRow.append(column);
            column.innerText = dataset["content"][j];
        }
    }
});