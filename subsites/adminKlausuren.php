<?php
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

const apiUrl = "https://vplan.moodle-paeda.de/apiBeta/index.php";
function authTest($apiUrl){
    $curl = curl_init();


    curl_setopt_array($curl, array(
        CURLOPT_URL => $apiUrl.'/vertretungsplan',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: '. $_SERVER["HTTP_AUTHORIZATION"]
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    if($response == "401"){
        http_response_code(401);
        die("401");
    }else{
        return;
    }
}
authTest(apiUrl);


$lessons = array();
$lessons[1] = array();
$lessons[1]["begin"] = "07:50";
$lessons[1]["end"] = "07:50";

$lessons[2] = array();
$lessons[2]["begin"] = "08:35";
$lessons[2]["end"] = "09:20";

$lessons[3] = array();
$lessons[3]["begin"] = "09:40";
$lessons[3]["end"] = "10:25";

$lessons[4] = array();
$lessons[4]["begin"] = "10:30";
$lessons[4]["end"] = "11:15";

$lessons[5] = array();
$lessons[5]["begin"] = "11:35";
$lessons[5]["end"] = "12:20";

$lessons[6] = array();
$lessons[6]["begin"] = "12:20";
$lessons[6]["end"] = "13:05";

$lessons[7] = array();
$lessons[7]["begin"] = "13:15";
$lessons[7]["end"] = "14:00";

$tableHead = "<tr><th>Zeile</th><th>Datum</th><th>Anzeigen</th><th>Von</th><th>Bis</th><th>Stufe</th><th>Kurs</th><th>Raum</th><th>Lehrer</th><th>INFO</th></tr>";

$tableALL = "<table id='all'>" . $tableHead;
$tableErr = "<table id='err' style='visibility: collapse'>" . $tableHead;
$tableIgnored = "<table id='ingnored' style='visibility: collapse'>" . $tableHead;
$tableSubmitted = "<table id='submitted' style='visibility: collapse'>" . $tableHead;
$all = 0;
$err = 0;
$ignored = 0;
$submitted = 0;


function convertStdToTime($std, $lessons)
{
    if (strlen($std) == 5) {
        $stunden = explode("-", $std);
        $von = $lessons[substr($stunden[0], 0, 1)]["begin"] . ":00";
        $bis = $lessons[substr($stunden[1], 0, 1)]["end"] . ":00";
        //print_r($stunden);
        //echo $von."<br>";
        return array($von, $bis);
    } else {
        $stunden = explode("-", $std);
        $von = $stunden[0];
        $bis = $stunden[1];
        if (isTime($stunden[0] . ":00")) {
            $von = $stunden[0] . ":00";
        }
        if (isTime($stunden[1] . ":00")) {
            $bis = $stunden[1] . ":00";
        }
        return array($von, $bis);
    }
}

function isTime($time)
{
    if (preg_match("/^([1-2][0-3]|[01]?[1-9]):([0-5]?[0-9]):([0-5]?[0-9])$/", $time)) {
        return true;
    }

    return false;
}

function xmlToArray($xml)
{
    global $lessons;
    global $tableErr, $err;
    global $tableIgnored, $ignored;
    global $tableALL, $all;
    global $tableSubmitted, $submitted;

    $data = array();

    for ($i = 0; $i < count($xml); $i++) {

        $dpRow = false;
        $row = "";
        $valid = 0;
        $invalid = 0;
        $message = "->";


        $exceldatum = $xml->klausur[$i]->datum;
        $anzeigen = $xml->klausur[$i]->anzeigen;
        $std = $xml->klausur[$i]->stunde;
        $stufe = $xml->klausur[$i]->stufe;
        $kurs = $xml->klausur[$i]->kurs;

        $lehrer = $xml->klausur[$i]->lehrer;
        $raum = $xml->klausur[$i]->raum;

        $eins = $xml->klausur[$i]->eins;
        $zwei = $xml->klausur[$i]->zwei;
        $drei = $xml->klausur[$i]->drei;
        $vier = $xml->klausur[$i]->vier;
        $funf = $xml->klausur[$i]->fünf;
        $sechs = $xml->klausur[$i]->sechs;
        $sieben = $xml->klausur[$i]->sieben;


        $unixdatum = ($exceldatum - 25569) * 86400;
        $datum = gmdate("Y-m-d", $unixdatum);

        if ($std == "") {
            $std = "-";
        }
        if ($raum == "") {
            $raum = "-";
        }

        $time = convertStdToTime($std, $lessons);
        $von = $time[0];
        $bis = $time[1];

        $dataset = array();

        $row .= "<tr>";
        $row .= "<td>" . ($i + 2) . "</td>";

        $dataset["date"] = json_decode(json_encode($datum), true);
        if ($dataset["date"] == "1899-12-30") {
            $row .= "<td style='background-color: red'>" . "Err" . "</td>";
            $dpRow = true;
            $invalid++;
            if ($dataset["excelDate"] == "") {
                $message .= "(Datum): Kein Datum gesetzt; ";
            } else {
                $message .= "(Datum): Datum kann nicht gelesen werden; ";
            }
        } else {
            $row .= "<td>" . $dataset["date"] . "</td>";
            $valid++;
        }

        //$dataset["excelDate"] = json_decode(json_encode($exceldatum), true)[0];
        //$dataset["unixtime"] = json_decode(json_encode($unixdatum), true);

        if(json_decode(json_encode($anzeigen), true)[0] == "x"){
            $dataset["active"] = true;
        }else{
            $dataset["active"] = false;
        }
        $row .= "<td>" . $dataset["display"] . "</td>";


        if ($von == "" && $bis == "") {
            $dataset["from"] = "00:00:00";
            $dataset["to"] = "16:00:00";
            $dpRow = true;
            $message .= "(Zeit): Keine Zeit gesetzt; ";
            $invalid++;
            $invalid++;
            $row .= "<td style='background-color: red'>" . "ERR(T)" . "</td>";
            $row .= "<td style='background-color: red'>" . "ERR(T)" . "</td>";
        } else {
            if (isTime(json_decode(json_encode($von), true))) {
                $dataset["from"] = json_decode(json_encode($von), true);
                $row .= "<td >" . $dataset["from"] . "</td>";
            } else {

                $row .= "<td style='background-color: red'>" . "ERR(S)" . "</td>";
                $dpRow = true;
                $invalid++;
                $message .= "(Zeit(Von): Fehlerhaftes Format; ";
            }
            if (isTime(json_decode(json_encode($bis), true))) {
                $dataset["to"] = $bis;
                $row .= "<td>" . $dataset["to"] . "</td>";
                $valid++;
            } else {
                $row .= "<td style='background-color: red'>ERR(S)</td>";
                $invalid++;
                $message .= "(Zeit(Bis): Fehlerhaftes Format; ";
            }
        }

        //$dataset["lesson"] = json_decode(json_encode($std), true)[0];
        $dataset["grade"] = json_decode(json_encode($stufe), true)[0];
        if ($dataset["grade"] == "") {
            $row .= "<td style='background-color: red'>" . "ERR" . "</td>";
            $dpRow = true;
            $message .= "(Stufe): nicht Übertragen; ";
            $invalid++;
        } else {
            $row .= "<td>" . $dataset["grade"] . "</td>";
            $valid++;
        }
        $dataset["course"] = json_decode(json_encode($kurs), true)[0];
        if ($dataset["course"] == "") {
            $row .= "<td style='background-color: red'>" . "ERR" . "</td>";
            $dpRow = true;
            $invalid++;
            $message .= "(Kurs): Kein Kurs gesetzt; ";
        } else {
            $row .= "<td>" . $dataset["course"] . "</td>";
            $valid++;
        }
        $dataset["room"] = json_decode(json_encode($raum), true)[0];
        if ($dataset["room"] == "") {
            $message .= "(Raum): Kein Raum gesetzt; ";
            $row .= "<td style='background-color: red'>" . "ERR" . "</td>";
            $dpRow = true;
            $invalid++;

        } else {
            $row .= "<td>" . $dataset["room"] . "</td>";
            $valid++;
        }

        $dataset["supervisors"] = ["1" => "a"];

        $dataset["supervisors"]["1"] = json_decode(json_encode($eins), true)[0];
        $dataset["supervisors"]["2"] = json_decode(json_encode($zwei), true)[0];
        $dataset["supervisors"]["3"] = json_decode(json_encode($drei), true)[0];
        $dataset["supervisors"]["4"] = json_decode(json_encode($vier), true)[0];
        $dataset["supervisors"]["5"] = json_decode(json_encode($funf), true)[0];
        $dataset["supervisors"]["6"] = json_decode(json_encode($sechs), true)[0];
        $dataset["supervisors"]["7"] = json_decode(json_encode($sieben), true)[0];

        if (json_decode(json_encode($lehrer), true)[0] != NULL) {
            $dataset["teacher"] = json_decode(json_encode($lehrer), true)[0];
            $row .= "<td>" . $dataset["teacher"] . "</td>";
            $valid++;
        } else {
            $dataset["teacher"] = "";
            $row .= "<td style='background-color: red'>" . "ERR" . "</td>";
            $dpRow = true;
            $invalid++;
            $message .= "(Lehrer): Kein Lehrer gesetzt; ";
        }


        $row .= "<td>" . "Gültig:" . $valid . "; Ungültig: " . $invalid . "; Info(s):" . $message . "</td>";

        $row .= "</tr>";
        if ($dataset["grade"] != "") {
            if ($dpRow) {
                $tableErr .= $row;
                $err++;
            }

            $data[] = $dataset;
            $tableSubmitted .= $row;
            $submitted++;
        } else {
            $tableIgnored .= $row;
            $ignored++;
        }
        $tableALL .= $row;
        $all++;

    }
    $tableALL .= "</table>";
    $tableIgnored .= "</table>";
    $tableErr .= "</table>";
    $tableSubmitted .= "</table>";

    return $data;
}
$xml = simplexml_load_file(__DIR__ . '/../../online/ImportFiles/klausuren.xml');

//DELETE all exams
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => apiUrl.'/klausuren/all',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'DELETE',
    CURLOPT_HTTPHEADER => array(
        'Authorization: '. $_SERVER["HTTP_AUTHORIZATION"]
    ),
));
$response = curl_exec($curl);

curl_close($curl);

//Add exams from table
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => apiUrl.'/klausuren/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode(xmlToArray($xml)),
    CURLOPT_HTTPHEADER => array(
        'Authorization: '. $_SERVER["HTTP_AUTHORIZATION"],
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);

curl_close($curl);

echo "<h1>Completed</h1>";

echo "<h2>Alle (" . $all . "): </h2>";
echo $tableALL;
?>



