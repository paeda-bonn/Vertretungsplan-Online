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
const token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJ2cGxhbi5tb29kbGUtcGFlZGEuZGUiLCJhdWQiOiJ2cGxhbi5tb29kbGUtcGFlZGEuZGUiLCJ1c2VybmFtZSI6ImFkbWluIiwidXNlcnR5cGUiOiJhZG1pbiJ9.FIHqq-IL7dShts5PRfVi0Pm2m-wXXOWJvPgw2MEUDjjR8So4S3WcXYLqeBShHFifoShEPW9rBkAsY524-tl5Oeyw2TpluccsDZC7HmJS5A1Iqh6JefnolJ1Xoa3fw5mB_9ZEs0OsbYHZFE2Z55Jmmm1diQCIJALCfbOqOqHxatQto-88pFc1tHoUcVSniEWt0F3Ju4o7jP1-uZpRGZDSVstSzG8KKn-wptFXiv8Fxh43lOZaRZr9fM0wWRYwmyKVFL84bq_FiEBrms01k2yyGl86aCmToJhK_aMKhsv94GgAiM9MPdBY6fyGFKktAhtxwJki5lmu8qMHf0n1xGV6YhNDJQCLHdqS6G9uPTwgJbgE9jem46SdJnRD2kRVLCib2O1m1zMKmw9iMa8Yxd-Jzpj8gJSsVv5KIS5icse01zClCUDlxsIohyD_3XH-sUftQk9yMir_OcEsR4cu0bWp_aiNjW9BV2X0w2SVgxa2vQjHUjkXzrtT9fiyWa56KXLyf22_M-aoOe4wE9kuyaauoFrFyrLxrJ-Cb5igPZ3GHVS9RR-DWYUxYPcX5tY-YiCF8Qs7rmJ1eEMSmOpKiAcnEhK82nYDDQeaLMG06dBj9XYe4Dc5uq6ge1Z9l3nAPRF5pTby1JMfCdAm3m8quN6hBi89lEBmAu__BBHKk5As4x4";

async function loadDataKlausuren() {
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

            res = await fetch(api_url + "/klausuren/active", requestOptions);
        } catch (e) {
            console.log(e);
        }

        if (res.status === 200) {
            document.getElementById('klausurenTableBody').innerHTML = klausurenParse(await res.json()).innerHTML;
        }
        resolve();
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
        }else {
            reject();
        }

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
        }else {
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
    return await res.json();

}

async function fetchVplanByDay(date: String) {
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