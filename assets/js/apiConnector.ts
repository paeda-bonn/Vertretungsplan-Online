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
const api_url = "https://vplan.moodle-paeda.de/apiBeta/index.php";
const token = localStorage.getItem("token");

function authError() {
    localStorage.removeItem("token");
    window.location.href = "/onlineBeta/views/login.html"
}

function requestApiToken(username: string, password: string) {
    return new Promise<string>(async (resolve, reject) => {
        let res = await fetch(api_url + '/login?username=' + username + "&password=" + password, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            //body: JSON.stringify({username: username, password: password})
        });
        if (res.status === 200) {
            resolve(await res.json());
        } else {
            reject();
        }
    });
}

async function loadDataKlausuren() {
    return new Promise<void>(async (resolve, reject) => {
        let res;
        try {
            let headers = new Headers();
            headers.append("Authorization", "Bearer " + token);

            let requestOptions: any = {
                method: 'GET',
                headers: headers,
                redirect: 'follow'
            };

            res = await fetch(api_url + "/klausuren/active", requestOptions);
        } catch (e) {
            console.log(e);
        }

        if (res.status === 200) {
            resolve(await res.json());
        } else if (res.status == 401) {
            authError();
            return [];
        } else {
            reject();
        }
    });
}

async function importKlausuren() {
    return new Promise(async (resolve, reject) => {
        let res;
        try {
            let headers = new Headers();
            headers.append("Authorization", "Bearer " + token);

            let requestOptions: any = {
                method: 'GET',
                headers: headers,
                redirect: 'follow'
            };

            res = await fetch("/onlineBeta/subsites/adminKlausuren.php", requestOptions);
        } catch (e) {
            console.log(e);
        }
        if (res.status === 200) {
            resolve(await res.text());
        } else if (res.status == 401) {
            authError();
            return [];
        } else {
            reject();
        }
    });
}

async function importUntis() {
    return new Promise(async (resolve, reject) => {
        let res;
        try {
            let headers = new Headers();
            headers.append("Authorization", "Bearer " + token);

            let requestOptions: any = {
                method: 'GET',
                headers: headers,
                redirect: 'follow'
            };

            res = await fetch("/onlineBeta/subsites/adminUntis.php", requestOptions);
        } catch (e) {
            console.log(e);
        }
        if (res.status === 200) {
            resolve(await res.text());
        } else if (res.status == 401) {
            authError();
            return [];
        } else {
            reject();
        }
    });
}

async function loadDataAushang(): Promise<any[]> {
    return new Promise(async (resolve, reject) => {
        let res: Response;
        try {
            let headers = new Headers();
            headers.append("Authorization", "Bearer " + token);

            let requestOptions: any = {
                method: 'GET',
                headers: headers,
                redirect: 'follow'
            };

            res = await fetch(api_url + "/aushang/active", requestOptions);
        } catch (e) {
            console.log(e);
        }

        if (res.status === 200) {
            resolve(await res.json());
        } else if (res.status == 401) {
            authError();
            return [];
        } else {
            reject();
        }
    });
}

function addAushang(aushang) {
    return new Promise(async (resolve, reject) => {

        let res = await fetch(api_url + "/aushang", {
            method: 'POST',
            headers: {
                "Authorization": "Bearer " + token,
                "Content-Type": "application/json"
            },
            body: JSON.stringify(aushang),
            redirect: 'follow'
        });
        resolve(res);
    });
}

function moveAushang(id: number, direction: string) {
    return new Promise(async (resolve, reject) => {

        let res = await fetch(api_url + '/aushang/id/' + id + '/move/' + direction, {
            method: 'PUT',
            headers: {
                "Authorization": "Bearer " + token
            },
            redirect: 'follow'
        });
        resolve(res);
    });
}

function deleteAushangById(id: number) {
    return new Promise(async (resolve, reject) => {

        let res = await fetch(api_url + "/aushang/id/" + id, {
            method: 'DELETE',
            headers: {
                "Authorization": "Bearer " + token
            },
            redirect: 'follow'
        });
        resolve(res);
    });
}

async function loadPresetsAushang(): Promise<any[]> {
    return new Promise(async (resolve, reject) => {
        let res: Response;
        try {
            let headers = new Headers();
            headers.append("Authorization", "Bearer " + token);

            let requestOptions: any = {
                method: 'GET',
                headers: headers,
                redirect: 'follow'
            };

            res = await fetch(api_url + "/aushang/presets", requestOptions);
        } catch (e) {
            console.log(e);
        }

        if (res.status === 200) {
            resolve(await res.json());
        } else if (res.status == 401) {
            authError();
            return [];
        } else {
            reject();
        }
    });
}

async function loadVplanActiveDays() {
    let headers = new Headers();
    headers.append("Authorization", "Bearer " + token);

    let requestOptions: any = {
        method: 'GET',
        headers: headers,
        redirect: 'follow'
    };

    let res = await fetch(api_url + "/vertretungsplan/activedays", requestOptions);
    if (res.status == 200) {
        return await res.json();
    } else if (res.status == 401) {
        authError();
        return [];
    } else {
        return [];
    }
}

async function fetchVplanByDay(date: string) {
    let headers = new Headers();
    headers.append("Authorization", "Bearer " + token);

    let requestOptions: any = {
        method: 'GET',
        headers: headers,
        redirect: 'follow'
    };

    let res = await fetch(api_url + "/vertretungsplan/vertretungen/date/" + date, requestOptions);
    return await res.json();
}