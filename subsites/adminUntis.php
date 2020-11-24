<?php
require_once('../dependencies/SimpleXLSX.php');
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
function authTest($apiUrl)
{
    $curl = curl_init();


    curl_setopt_array($curl, array(
        CURLOPT_URL => $apiUrl . '/vertretungsplan',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: ' . $_SERVER["HTTP_AUTHORIZATION"]
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    if ($response == "401") {
        http_response_code(401);
        die("401");
    } else {
        return;
    }
}

authTest(apiUrl);


$supervisonTimes = array("0/1" => "Früh", "2/3" => "1. Pause", "4/5" => "2. Pause");
$supervisonTimesKeys = array("0/1", "2/3", "4/5");

function generateId($date, $kurs, $stunde, $teacher)
{

    $timestamp = strtotime($date);
    $kursex = explode("/", str_replace(' ', '', $kurs));
    $stufe = $kursex[0];
    if (isset($kursex[1])) {
        $kurs = $kursex[1];
    } else {
        $kurs = "";
    }


    $wochentag = date("w", $timestamp) - 1;
    $stunde = $stunde - 1;
    $woche = date("W", $timestamp);
    $jahr = date("Y", $timestamp);

    $id = $stufe . "/" . $kurs . "/" . $teacher . "/" . $wochentag . "/" . $stunde . "/" . $woche . "/" . $jahr;

    return $id;
}

function createVertRow($vertretung)
{

    echo "<tr style='background-color: lightgreen'>";
    echo "<td colspan='3'></td>";
    echo "<td>";
    echo $vertretung["date"];
    echo "</td>";
    echo "<td>";
    echo $vertretung["lesson"];
    echo "</td>";
    echo "<td>";
    echo $vertretung["class"];
    echo "</td>";
    echo "<td>";
    echo $vertretung["newTeacher"];
    echo "</td>";
    echo "<td>";
    echo $vertretung["newRoom"];
    echo "</td>";
    echo "<td>";
    echo $vertretung["subject"];
    echo "</td>";
    echo "<td colspan='2'></td>";
    echo "<td>";
    echo $vertretung["info"];
    echo "</td>";
    echo "</tr>";

}

function loadXlsx()
{
    global $supervisonTimes, $supervisonTimesKeys, $secret;
    $days = array();
    $i = 1;
    $vertretung = array();
    $aufsichten = array();

    $sourcePath = __DIR__ . '/../../online/ImportFiles/Untis.xlsx';
    copy($sourcePath, __DIR__ . '/../Imported/Untis-' . date("Y-m-d-H-i-s") . '_' . rand(10000, 999999) . '.xlsx');

    $refreshed = date('d.m.Y H:i', filemtime($sourcePath));
    if ($xlsx = SimpleXLSX::parse($sourcePath)) {
        $rowNum = 1;

        foreach ($xlsx->rows() as $row) {
            foreach ($row as $key => $value) {
                $removeSequences = array("<s>", "</s>");
                $row[$key] = str_replace($removeSequences, "", $value);
            }

            echo "<tr>";
            echo "<td>";
            echo $rowNum;
            echo "</td>";
            foreach ($row as $key => $value) {
                echo "<td>";
                echo $row[$key];
                echo "</td>";
            }
            echo "</tr>";


            if ($row[0] != "") {
                $vertretungsTypes = array("Vertretung", "Statt-Vertretung", "Unterricht geändert", "Lehrertausch", "Sondereins.", "Raum-Vtr.", "Betreuung", "Trotz Absenz", "Raumänderung", "Verlegung");
                if (array_search($row[1], $vertretungsTypes) !== false) {
                    $lessons = explode("-", $row[3]);
                    $teacher = explode("→", $row[5]);
                    $subject = explode("→", $row[7]);
                    $room = explode("→", $row[6]);
                    $dateParts = explode(".", $row[2]);
                    foreach ($lessons as $lesson) {

                        $event = array();
                        $event["date"] = "2020-" . $dateParts[1] . "-" . $dateParts[0];
                        $event["lesson"] = $lesson;
                        $event["subject"] = explode("-", $subject[0])[0];
                        if (sizeof($subject) > 1) {
                            $event["newSubject"] = explode("-", $subject[1])[0];
                        } else {
                            $event["newSubject"] = explode("-", $subject[0])[0];
                        }

                        $event["teacher"] = $teacher[0];
                        if (sizeof($teacher) > 1) {
                            $event["newTeacher"] = $teacher[1];
                        } else {
                            $event["newTeacher"] = $teacher[0];
                        }

                        if (sizeof($room) > 1) {
                            $event["newRoom"] = $room[1];
                        } else {
                            $event["newRoom"] = $room[0];
                        }

                        $event["info"] = $row[10];
                        if ($event["info"] == "") {
                            if($row[1] == "Raum-Vtr."){
                                $event["info"] = "Raumänderung";
                            }else{
                                $event["info"] = $row[1];
                            }

                        }
                        $event["class"] = $row[4];
                        if ($event["class"] == "Q1" || $event["class"] == "Q2" || $event["class"] == "EF") {
                            $event["class"] = $event["class"] . "/" . $subject[0];
                        }
                        $event["id"] = generateId($event["date"], $event["class"], $event["lesson"], $event["teacher"]);

                        if ($row[4] != "" && $row[7] != "---") {
                            array_push($vertretung, $event);
                            createVertRow($event);
                        }
                    }

                    if (!in_array($event["date"], $days)) {
                        array_push($days, $event["date"]);
                    }
                } elseif ($row[1] == "Entfall") {
                    if ($row[11] != "KL") {
                        $lessons = explode("-", $row[3]);
                        $teacher = explode("→", $row[5]);
                        $subject = explode("→", $row[7]);
                        $dateParts = explode(".", $row[2]);
                        foreach ($lessons as $lesson) {
                            $event = array();
                            $event["date"] = "2020-" . $dateParts[1] . "-" . $dateParts[0];
                            $event["lesson"] = $lesson;
                            $event["subject"] = explode("-", $subject[0])[0];
                            $event["newSubject"] = "---";
                            $event["teacher"] = $teacher[0];
                            $event["newTeacher"] = "---";
                            $event["newRoom"] = "---";

                            $event["info"] = $row[10];
                            if ($event["info"] == "") {
                                $event["info"] = $row[1];
                            }
                            $event["class"] = $row[4];
                            if ($event["class"] == "Q1" || $event["class"] == "Q2" || $event["class"] == "EF") {
                                $event["class"] = $event["class"] . "/" . $subject[0];
                            }
                            $event["id"] = generateId($event["date"], $event["class"], $event["lesson"], $event["teacher"]);

                            //echo "Event:" . $rowNum . json_encode($event) . "<br>\n";
                            $dateString = str_pad($dateParts[0], 2, "0", STR_PAD_LEFT) . "-" . str_pad($dateParts[1], 2, "0", STR_PAD_LEFT) . "-2020";
                            //$examsInLesson = json_decode(file_get_contents(Config::$url_web . "/api/klausuren.php?date=" . $dateString . "&secret=" . Config::$api_secret));

                            $isKLSup = false;
                            $lesson = str_replace(" ", "", $lesson);
                            if ($row[4] != "" && $row[7] != "---") {
                                if (!$isKLSup) {
                                    createVertRow($event);
                                    array_push($vertretung, $event);

                                    if (!in_array($event["date"], $days)) {
                                        array_push($days, $event["date"]);
                                    }
                                }
                            }
                        }
                    }
                } elseif ($row[1] == "Raum-Vtr.") {
                    $lessons = explode("-", $row[3]);
                    $subject = explode("→", $row[7]);
                    $room = explode("→", $row[6]);
                    $dateParts = explode(".", $row[2]);


                    foreach ($lessons as $lesson) {

                        $event = array();
                        $event["teacher"] = $row[5];
                        $event["newTeacher"] = $row[5];
                        $event["date"] = "2020-" . $dateParts[1] . "-" . $dateParts[0];
                        $event["lesson"] = $lesson;
                        $event["subject"] = explode("-", $subject[0])[0];
                        if (sizeof($subject) > 1) {
                            $event["newSubject"] = $subject[1];
                        } else {
                            $event["newSubject"] = $subject[0];
                        }
                        if (sizeof($room) > 1) {
                            $event["newRoom"] = $room[1];
                        } else {
                            $event["newRoom"] = $room[0];
                        }

                        $event["info"] = $row[10];
                        if ($event["info"] == "") {
                            $event["info"] = $row[1];
                        }
                        $event["class"] = $row[4];
                        if ($event["class"] == "Q1" || $event["class"] == "Q2" || $event["class"] == "EF") {
                            $event["class"] = $event["class"] . "/" . $subject[0];
                        }
                        $event["id"] = generateId($event["date"], $event["class"], $event["lesson"], $event["teacher"]);
                        //echo "Event:" . $rowNum . json_encode($event) . "<br>\n";
                        array_push($vertretung, $event);
                        createVertRow($event);
                    }

                    if (!in_array($event["date"], $days)) {
                        array_push($days, $event["date"]);
                    }
                } elseif ($row[1] == "Pausenaufsicht") {
                    $dateParts = explode(".", $row[2]);
                    $teacher = explode("→", $row[5]);
                    $event = array();
                    $event["date"] = "2020-" . $dateParts[1] . "-" . $dateParts[0];
                    if (in_array($row[3], $supervisonTimesKeys)) {
                        $event["time"] = $supervisonTimes[$row[3]];
                    } else {
                        $event["time"] = $row[3];
                    }

                    $event["teacher"] = $teacher[1];
                    $event["location"] = $row[6];
                    //echo "Event:" . $rowNum . json_encode($event) . "<br>\n";
                    array_push($aufsichten, $event);
                }
            }

            $rowNum++;
        }

    } else {
        echo SimpleXLSX::parseError();
    }

    $output = array();
    $output["vertretungen"] = $vertretung;
    $output["days"] = $days;
    $output["aufsichten"] = $aufsichten;

    return $output;
}

?>
    <table>
        <tbody>
        <?php

        $insert = loadXlsx();

        ?>
      </tbody>
    </table>

<?php

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => apiUrl.'/vertretungsplan/vertretungen/date/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'DELETE',
    CURLOPT_POSTFIELDS => json_encode($insert["days"]),
    CURLOPT_HTTPHEADER => array(
        'Authorization: '. $_SERVER["HTTP_AUTHORIZATION"],
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);
curl_close($curl);

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => apiUrl.'/vertretungsplan/activedates/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($insert["days"]),
    CURLOPT_HTTPHEADER => array(
        'Authorization: '. $_SERVER["HTTP_AUTHORIZATION"],
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);
curl_close($curl);

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => apiUrl.'/vertretungsplan/vertretungen/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($insert["vertretungen"]),
    CURLOPT_HTTPHEADER => array(
        'Authorization: '. $_SERVER["HTTP_AUTHORIZATION"],
        'Content-Type: application/json'
    ),
));

$response = curl_exec($curl);
curl_close($curl);

echo "<h1>Completed</h1>";