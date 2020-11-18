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

function updateSavedCreds() {
    let username = (<HTMLInputElement>document.getElementById("username")).value;
    let password = (<HTMLInputElement>document.getElementById("password")).value;
    localStorage.setItem("username",username);
    localStorage.setItem("password",password);

    console.log("UpdateCreds");
}

function processForm(e) {
    updateSavedCreds();
    return false;
}

document.addEventListener("DOMContentLoaded", function(event) {
    let form = <HTMLFormElement> document.getElementById('loginForm');
    if (form.attachEvent) {
        form.attachEvent("submit", processForm);
    } else {
        form.addEventListener("submit", processForm);
    }
});