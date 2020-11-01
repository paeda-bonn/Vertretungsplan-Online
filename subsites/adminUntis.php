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

if (!empty($config)) {
    $secret = $config->api_secret;
}
$supervisonTimes = array("0/1" => "Früh", "2/3" => "1. Pause", "4/5" => "2. Pause");
$supervisonTimesKeys = array("0/1", "2/3", "4/5");

function curlToApi($json, $urlargs){

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => Config::$url_api . "/vertretungsplan.php?" . $urlargs,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache"
        ),
    ));
    return curl_exec($curl);
}

function generateId($date, $kurs, $stunde, $teacher){

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
    echo "<td></td>";
    echo "<td></td>";
    echo "<td></td>";
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
    echo "<td></td>";
    echo "<td></td>";
    echo "<td>";
    echo $vertretung["info"];
    echo "</td>";
    echo "</tr>";
}

function loadXlsx() {
    global $supervisonTimes, $supervisonTimesKeys, $secret;
    $days = array();
    $i = 1;
    $vertretung = array();
    $aufsichten = array();

    copy(__DIR__ . '/../ImportFiles/Untis.xlsx', __DIR__ . '/../Imported/Untis-' . date("Y-m-d-H-i-s") . '_' . rand(10000, 999999) . '.xlsx');

    $refreshed = date('d.m.Y H:i', filemtime(__DIR__ . '/../ImportFiles/Untis.xlsx'));
    if ($xlsx = SimpleXLSX::parse(__DIR__ . '/../ImportFiles/Untis.xlsx')) {
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

            //echo "ROW:" . $rowNum . json_encode($row) . "<br>\n";
            if ($row[0] != "") {
                if ($row[1] == "Vertretung" || $row[1] == "Raum-Vtr." || $row[1] == "Betreuung" || $row[1] == "Statt-Vertretung" || $row[1] == "Lehrertausch" || $row[1] == "Trotz Absenz" || $row[1] == "Verlegung") {
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
                            $event["info"] = $row[1];
                        }
                        $event["class"] = $row[4];
                        if ($event["class"] == "Q1" || $event["class"] == "Q2" || $event["class"] == "EF") {
                            $event["class"] = $event["class"] . "/" . $subject[0];
                        }
                        $event["id"] = generateid($event["date"], $event["class"], $event["lesson"], $event["teacher"]);

                        //echo "Event:" . $rowNum . json_encode($event) . "<br>\n";
                        array_push($vertretung, $event);
                        createVertRow($event);
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
                            $event["id"] = generateid($event["date"], $event["class"], $event["lesson"], $event["teacher"]);

                            //echo "Event:" . $rowNum . json_encode($event) . "<br>\n";
                            if ($row[4] != "") {
                                array_push($vertretung, $event);
                                createVertRow($event);
                                if (!in_array($event["date"], $days)) {
                                    array_push($days, $event["date"]);
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
                        $event["id"] = generateid($event["date"], $event["class"], $event["lesson"], $event["teacher"]);
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
    $data = array();
    $data["mode"] = "insert";
    $data["type"] = "vertretungen";


    $data["data"] = $vertretung;
    $data["days"] = $days;

    $output[] = $data;

    $dates = "";
    foreach ($days as $day) {
        if ($i == 0) {
            $dates = $day;
            $i = 1;
        } else {
            $dates .= "," . $day;
        }
    }


    $data = array();
    $data["mode"] = "insert";
    $data["type"] = "aufsichten";
    $data["data"] = $aufsichten;

    $output[] = $data;


    $output[] = array("mode" => "update", "type" => "config", "data" => array("activeDates" => $dates, "lastRefreshed" => $refreshed));
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
$i = 0;
foreach ($insert[0]["days"] as $day) {
    if ($i == 0) {
        $dates = $day;
        $i = 1;
    } else {
        $dates .= "," . $day;
    }
}

$data = curlToApi("", "secret=" . Config::$api_secret . "&dates=" . $dates);
$activeData = json_decode($data, true);

$delete = array();

if (isset($activeData["data"]["vertretungen"])) {
    $activeIds = array();
    $deleteVert = array();

    foreach ($activeData["data"]["vertretungen"] as $dates) {
        foreach ($dates as $event) {
            $activeIds[] = $event["id"];
        }
    }

    $deleteVert["mode"] = "delete";
    $deleteVert["type"] = "vertretungen";
    $deleteVert["data"] = $activeIds;

    $response = curlToApi(json_encode(array($deleteVert)), "secret=" . Config::$api_secret . "&mode=edit");

}

if (isset($activeData["data"]["aufsichten"])) {
    $activeIds = array();
    $deleteAufsichten = array();

    foreach ($activeData["data"]["aufsichten"] as $dates) {
        foreach ($dates as $event) {
            $activeIds[] = $event["id"];
        }
    }

    $deleteAufsichten["mode"] = "delete";
    $deleteAufsichten["type"] = "aufsichten";
    $deleteAufsichten["data"] = $activeIds;

    $response = curlToApi(json_encode(array($deleteAufsichten)), "secret=" . Config::$api_secret . "&mode=edit");
}
$inserted = curlToApi(json_encode($insert), "secret=" . Config::$api_secret . "&mode=edit");
echo "<h1>Completed</h1>";