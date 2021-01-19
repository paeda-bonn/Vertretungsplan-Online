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

require_once('../dependencies/SimpleXLSX.php');
require_once 'Klausur.php';
require_once 'VplanEntry.php';
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


const apiUrl = "https://vplan.moodle-paeda.de/api/index.php";

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
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'Authorization: ' . $_SERVER["HTTP_AUTHORIZATION"]
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    if ($response == "401") {
        http_response_code(401);
        die("401");
    }
}

authTest(apiUrl);

$supervisonTimes = array("0/1" => "Früh", "2/3" => "1. Pause", "4/5" => "2. Pause");
$supervisonTimesKeys = array("0/1", "2/3", "4/5");

function formatDate($string)
{
    return date("Y-m-d", strtotime($string . date("Y")));
}

header('Content-Type: application/json');
function loadXLSXtoObject($path)
{
    $acceptedEntryTypes = ["Betreuung", "Raumänderung", "Entfall", "Klausur", "Vertretung", "Klausur"];

    $data = array();
    $data["activeDays"] = [];

    if ($xlsx = SimpleXLSX::parse($path)) {
        $rows = $xlsx->rows();
        for ($i = 0; $i < sizeof($rows); $i++) {
            $row = $rows[$i];
            if (in_array($row[1], $acceptedEntryTypes)) {

                for ($j = 0; $j < sizeof($row); $j++) {
                    $row[$j] = str_replace("<s>", "", str_replace("</s>", "", $row[$j]));
                }
                if (!isset($data[$row[1]])) {
                    $data[$row[1]] = array();
                }

                if ($row[1] == "Klausur") {
                    $entry = new Klausur();
                    $entry->setDate(formatDate($row[2]));
                    $entry->setTeacher($row[5]);
                    $entry->setGrade($row[4]);
                    $entry->setCourse($row[7]);
                    $entry->setRoom($row[6]);
                    $entry->setType($row[1]);
                    $lessons = explode(" - ", $row[3]);
                    for ($k = 0; $k < sizeof($lessons); $k++) {
                        $lessons[$k] = intval($lessons[$k]);
                    }
                    if (sizeof($lessons) > 1) {
                        if ($lessons[0] + 1 == $lessons[1]) {
                            $entry->setLessons($lessons);
                        } else {
                            $lesson = $lessons[0];
                            $tmpLess = array();
                            do {

                                array_push($tmpLess, $lesson);
                                $lesson++;
                            } while ($lesson <= $lessons[1]);
                            $entry->setLessons($tmpLess);
                        }
                    } else {
                        $entry->setLessons($lessons);
                    }

                    array_push($data[$row[1]], $entry);

                } elseif ($row[1] == "Entfall") {

                    $vplanEntry = new VplanEntry();
                    $vplanEntry->setDate(formatDate($row[2]));

                    if (!in_array(formatDate($row[2]), $data["activeDays"])) {
                        array_push($data["activeDays"], formatDate($row[2]));
                    }

                    $vplanEntry->setTeacher($row[5]);
                    $vplanEntry->setGrade($row[4]);
                    $vplanEntry->setCourse($row[4]);
                    $vplanEntry->setSubject($row[7]);
                    $vplanEntry->setNewSubject($row[7]);
                    $vplanEntry->setType($row[1]);
                    if ($row[10] == "") {
                        $row[10] = $row[1];
                    }
                    $vplanEntry->setInfo($row[10]);

                    $lessons = explode(" - ", $row[3]);
                    for ($k = 0; $k < sizeof($lessons); $k++) {
                        $lessons[$k] = intval($lessons[$k]);
                    }
                    if (sizeof($lessons) > 1) {
                        if ($lessons[0] + 1 == $lessons[1]) {
                            $vplanEntry->setLessons($lessons);
                        } else {
                            $lesson = $lessons[0];
                            $tmpLess = array();
                            do {

                                array_push($tmpLess, $lesson);
                                $lesson++;
                            } while ($lesson <= $lessons[1]);
                            $vplanEntry->setLessons($tmpLess);
                        }
                    } else {
                        $vplanEntry->setLessons($lessons);
                    }
                    array_push($data[$row[1]], $vplanEntry);
                } elseif ($row[1] == "Vertretung" || $row[1] == "Betreuung") {

                    $vplanEntry = new VplanEntry();
                    $vplanEntry->setDate(formatDate($row[2]));
                    $vplanEntry->setGrade($row[4]);
                    $vplanEntry->setCourse($row[4]);
                    $vplanEntry->setSubject($row[7]);
                    $vplanEntry->setNewSubject($row[7]);

                    $teacher = explode("→", $row[5]);
                    $vplanEntry->setTeacher($teacher[0]);
                    $vplanEntry->setNewTeacher($teacher[1]);
                    $vplanEntry->setType($row[1]);
                    $rooms = explode("→", $row[6]);
                    $vplanEntry->setRoom($rooms[sizeof($rooms) - 1]);

                    if ($row[10] == "") {
                        $row[10] = $row[1];
                    }
                    $vplanEntry->setInfo($row[10]);

                    $lessons = explode(" - ", $row[3]);
                    for ($k = 0; $k < sizeof($lessons); $k++) {
                        $lessons[$k] = intval($lessons[$k]);
                    }
                    if (sizeof($lessons) > 1) {
                        if ($lessons[0] + 1 == $lessons[1]) {
                            $vplanEntry->setLessons($lessons);
                        } else {
                            $lesson = $lessons[0];
                            $tmpLess = array();
                            do {

                                array_push($tmpLess, $lesson);
                                $lesson++;
                            } while ($lesson <= $lessons[1]);
                            $vplanEntry->setLessons($tmpLess);
                        }
                    } else {
                        $vplanEntry->setLessons($lessons);
                    }
                    array_push($data[$row[1]], $vplanEntry);
                } elseif ($row[1] == "Raumänderung") {

                    $vplanEntry = new VplanEntry();
                    $vplanEntry->setDate(formatDate($row[2]));
                    $vplanEntry->setTeacher($row[5]);
                    $vplanEntry->setNewTeacher($row[5]);
                    $vplanEntry->setGrade($row[4]);
                    $vplanEntry->setCourse($row[4]);
                    $vplanEntry->setSubject($row[7]);
                    $vplanEntry->setNewSubject($row[7]);
                    $vplanEntry->setInfo($row[10]);
                    $vplanEntry->setType($row[1]);
                    $teacher = explode("→", $row[5]);

                    $rooms = explode("→", $row[6]);
                    $vplanEntry->setRoom($rooms[sizeof($rooms) - 1]);

                    $lessons = explode(" - ", $row[3]);
                    for ($k = 0; $k < sizeof($lessons); $k++) {
                        $lessons[$k] = intval($lessons[$k]);
                    }
                    if (sizeof($lessons) > 1) {
                        if ($lessons[0] + 1 == $lessons[1]) {
                            $vplanEntry->setLessons($lessons);
                        } else {
                            $lesson = $lessons[0];
                            $tmpLess = array();
                            do {

                                array_push($tmpLess, $lesson);
                                $lesson++;
                            } while ($lesson <= $lessons[1]);
                            $vplanEntry->setLessons($tmpLess);
                        }
                    } else {
                        $vplanEntry->setLessons($lessons);
                    }
                    array_push($data[$row[1]], $vplanEntry);
                }
            }
        }
    } else {
        echo SimpleXLSX::parseError();
    }

    return $data;
}

function removeExamSupervisors($data)
{
    if (isset($data["Klausur"]) && isset($data["Entfall"])) {
        $exams = [];
        for ($i = 0; $i < sizeof($data["Klausur"]); $i++) {
            $exam = $data["Klausur"][$i];

            if (!isset($exams[$exam->getDate()])) {
                $exams[$exam->getDate()] = [];
            }
            for ($j = 0; $j < sizeof($exam->getLessons()); $j++) {
                if (!isset($exams[$exam->getDate()][$exam->getLessons()[$j]])) {
                    $exams[$exam->getDate()][$exam->getLessons()[$j]] = [];
                }
                $exams[$exam->getDate()][$exam->getLessons()[$j]][] = $exam;
            }
        }
        $woSupervision = [];
        $entfalls = $data["Entfall"];
        for ($i = 0; $i < sizeof($entfalls); $i++) {
            $entfall = $entfalls[$i];
            $supervisor = false;

            if (isset($exams[$entfall->getDate()])) {
                for ($j = 0; $j < sizeof($entfall->getLessons()); $j++) {
                    if (isset($exams[$entfall->getDate()][$entfall->getLessons()[$j]])) {
                        $lessonExams = $exams[$entfall->getDate()][$entfall->getLessons()[$j]];
                        for ($k = 0; $k < sizeof($lessonExams); $k++) {
                            $exam = $lessonExams[$k];
                            if ($exam->getTeacher() == $entfall->getTeacher()) {
                                $supervisor = true;
                            }
                        }
                    }
                }
            }
            if (!$supervisor) {
                array_push($woSupervision, $entfall);
            }
        }
        $data["Entfall"] = $woSupervision;
    }

    return $data;
}

function checkValidity($vplanEntry){

}


try {
    $json = loadXLSXtoObject(__DIR__ . '/../../online/ImportFiles/Untis.xlsx');
    $data = removeExamSupervisors($json);

//Create Payload

    $payload = [];
    if (isset($data["Entfall"])) {
        $payload = array_merge($payload, $data["Entfall"]);
    }
    if (isset($data["Vertretung"])) {
        $payload = array_merge($payload, $data["Vertretung"]);
    }
    if (isset($data["Betreuung"])) {
        $payload = array_merge($payload, $data["Betreuung"]);
    }
    if (isset($data["Raumänderung"])) {
        $payload = array_merge($payload, $data["Raumänderung"]);
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => apiUrl . '/vertretungsplan/vertretungen/date/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_POSTFIELDS => json_encode($data["activeDays"]),
        CURLOPT_HTTPHEADER => array(
            'Authorization: ' . $_SERVER["HTTP_AUTHORIZATION"],
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => apiUrl . '/vertretungsplan/activedates/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data["activeDays"]),
        CURLOPT_HTTPHEADER => array(
            'Authorization: ' . $_SERVER["HTTP_AUTHORIZATION"],
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => apiUrl . '/vertretungsplan/vertretungen/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => array(
            'Authorization: ' . $_SERVER["HTTP_AUTHORIZATION"],
            'Content-Type: application/json'
        ),
    ));
    $response = curl_exec($curl);
    echo json_encode($data);
} catch (Exception $e) {
    echo $e;
}
?>