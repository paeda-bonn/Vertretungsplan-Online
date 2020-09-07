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

$secret = $config->api_secret;

function curlToApi($json, $urlargs)
{
    global $config;
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $config->url_api . "/vertretungsplan.php?" . $urlargs,
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

function curlToApibeta($json, $urlargs)
{
    global $config;
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.vplan.nils-witt.de/vertretungsplan.php?" . $urlargs,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $json,
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache"
        ),
    ));
    return curl_exec($curl);
}

function convertklasse($klasse, $fach)
{

    if (strpos($klasse, "/") == true) {

        $explode = explode("/", $klasse);

        $gruppe = $fach . "-" . substr($explode[1], strlen($fach) + 1);
        $output = $explode[0] . "/" . $gruppe;

        return $output;
    } else {
        return $klasse;
    }
}

function generateid($date, $kurs, $stunde)
{

    $timestamp = strtotime($date);
    $kursex = explode("/", str_replace(' ', '', $kurs));
    if ($kursex[0] != "EF" || $kursex[0] != "Q1" || $kursex[0] != "Q2") {
    }
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

    $id = $stufe . "/" . $kurs . "/" . $wochentag . "/" . $stunde . "/" . $woche . "/" . $jahr;

    return $id;
}

function convertdate($input)
{
    $months = array();
    $months['Januar'] = "01";
    $months['Februar'] = "2";
    $months['März'] = "3";
    $months['April'] = "4";
    $months['Mai'] = "5";
    $months['Juni'] = "6";
    $months['Juli'] = "7";
    $months['August'] = "8";
    $months['September'] = "9";
    $months['Oktober'] = "10";
    $months['November'] = "11";
    $months['Dezember'] = "12";

    $explode = explode(",", $input);
    $date = array();
    $dayex = explode(".", $explode[1]);
    $monthex = explode(" ", $explode[1]);

    $date['day'] = substr($dayex[0], 1);
    $date['month'] = $months[$monthex[2]];
    $date['year'] = $monthex[3];

    return $date['year'] . "-" . $date['month'] . "-" . $date['day'];
}

function loadXml()
{

    $days = array();
    $i = 1;
    $vertretung = array();
    $aufsichten = array();
    $date = "";
    $refreshed = "";

    $xml = simplexml_load_file(__DIR__ . '/../ImportFiles/Vertretungsplan Lehrer.xml');

    if (!$xml) {
        die("XML File not found");
    }
    $handle = fopen(__DIR__ . '/../Imported/Vertretungsplan-' . date("Y-m-d-H-i-s") . '_' . rand(10000, 999999) . '.xml', "w");
    fwrite($handle, $xml->asXML());
    fclose($handle);
    foreach ($xml->children() as $child) {
        $childname = $child->getName();
        if ($childname == "kopf") {

            $date = convertdate($child->titel);
            $refreshed = $child->datum;

        } elseif ($childname == "haupt") {

            foreach ($child->children() as $aktion) {

                $stunde = $aktion->stunde;
                $fach = $aktion->fach;
                $lehrer = $aktion->lehrer;
                $klasse = $aktion->klasse;
                $vfach = $aktion->vfach;

                if (strpos($aktion->vlehrer, ")") > 0) {
                    $vlehrer = "---";
                } else {
                    $vlehrer = $aktion->vlehrer;
                }

                $vraum = $aktion->vraum;
                //echo $vraum;
                if ($vraum == " ") {
                    $vraum = "---";
                    //echo "tr";
                }
                //echo ",";
                $info = $aktion->info;

                $event = array();
                $event["date"] = $date;
                $event["stunde"] = $stunde;
                $event["fach"] = $fach;
                $event["lehrer"] = $lehrer;
                $event["vfach"] = $vfach;
                $event["vlehrer"] = $vlehrer;
                $event["vraum"] = $vraum;
                $event["info"] = $info;

                $event["klasse"] = convertklasse($klasse, $fach);
                $event["id"] = generateid($date, $klasse, $stunde);

                array_push($vertretung, $event);
                if (in_array($date, $days)) {

                } else {
                    $days[] = $date;
                }
            }
        } elseif ($childname == "aufsichten") {

            foreach ($child->children() as $aufsicht) {

                $data = $aufsicht->aufsichtinfo;
                $event = array();
                $event["date"] = $date;
                $event["zeit"] = explode(":", $data)[0] . ":" . explode(":", $data)[1];
                $event["lehrer"] = trim(explode("-->", $data)[1], " \t\n\r\0\x0B");
                $event["ort"] = trim(explode("-", $data)[1], " \t\n\r\0\x0B");
                array_push($aufsichten, $event);
            }
        }
    }

    $output = array();
    $data = array();
    $data["mode"] = "insert";
    $data["type"] = "vertretungen";

    $entrys = array();

    $trans = json_encode($vertretung);
    $vertretung = json_decode($trans, true);

    foreach ($vertretung as $entry) {
        $dataset = array();
        $dataset["date"] = $entry["date"];
        $dataset["lesson"] = $entry["stunde"][0];
        $dataset["subject"] = $entry["fach"][0];
        $dataset["teacher"] = $entry["lehrer"][0];

        if (is_array($entry["klasse"])) {
            $dataset["class"] = $entry["klasse"][0];
        } else {
            $dataset["class"] = $entry["klasse"];
        }

        $dataset["newTeacher"] = $entry["vlehrer"][0];
        $dataset["newSubject"] = $entry["vfach"][0];
        $dataset["newRoom"] = $entry["vraum"][0];
        $dataset["info"] = $entry["info"][0];
        if ($dataset["info"] == null) {
            $dataset["info"] = "";
        }
        $dataset["id"] = $entry["id"];

        $entrys[] = $dataset;
    }

    $data["data"] = $entrys;
    $data["days"] = $days;

    $output[] = $data;

    $trans = json_encode($aufsichten);
    $aufsichten = json_decode($trans, true);

    $entrys = array();

    foreach ($aufsichten as $entry) {
        $dataset = array();
        $dataset["date"] = $entry["date"];
        $dataset["time"] = $entry["zeit"];
        $dataset["teacher"] = $entry["lehrer"];
        $dataset["location"] = $entry["ort"];

        $entrys[] = $dataset;
    }
    $data = array();
    $data["mode"] = "insert";
    $data["type"] = "aufsichten";
    $data["data"] = $entrys;

    $output[] = $data;

    $i = 0;
    $dates = "";
    foreach ($days as $day) {
        if ($i == 0) {
            $dates = $day;
            $i = 1;
        } else {
            $dates .= "," . $day;
        }
    }


    $refreshed = json_encode($refreshed);
    $refreshed = json_decode($refreshed, true);
    $output[] = array("mode" => "update", "type" => "config", "data" => array("activeDates" => $dates, "lastRefreshed" => $refreshed[0]));
    return $output;
}

echo "<h1>Disabled</h1>";
exit();
$insert = loadXml();

$i = 0;
foreach ($insert[0]["days"] as $day) {
    if ($i == 0) {
        $dates = $day;
        $i = 1;
    } else {
        $dates .= "," . $day;
    }
}

$data = curlToApi("", "secret=" . $secret . "&dates=" . $dates);
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

    $response = curlToApi(json_encode(array($deleteVert)), "secret=" . $secret . "&mode=edit");

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

    $response = curlToApi(json_encode(array($deleteAufsichten)), "secret=" . $secret . "&mode=edit");
}
$inserted = curlToApi(json_encode($insert), "secret=" . $secret . "&mode=edit");
echo "<h1>Completed</h1>";
echo(json_encode($insert));
?>
