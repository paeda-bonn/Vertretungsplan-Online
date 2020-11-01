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

function monat($monat)
{
    $monate = array();
    $monate[] = "";
    $monate[] = "Januar";
    $monate[] = "Februar";
    $monate[] = "März";
    $monate[] = "April";
    $monate[] = "Mai";
    $monate[] = "Juni";
    $monate[] = "Juli";
    $monate[] = "August";
    $monate[] = "September";
    $monate[] = "Oktober";
    $monate[] = "November";
    $monate[] = "Dezember";

    if (substr($monat, 0, 1) == 0) {
        return $monate[substr($monat, 1, 1)];
    } else {
        return $monate[$monat];
    }
}

function displaydate($day)
{
    $wochentage = array();
    $wochentage[] = "Sonntag";
    $wochentage[] = "Montag";
    $wochentage[] = "Dienstag";
    $wochentage[] = "Mittwoch";
    $wochentage[] = "Donnerstag";
    $wochentage[] = "Freitag";
    $wochentage[] = "Samstag";

    $timestamp = strtotime($day);
    $wochentag = date("w", $timestamp);
    $monat = date("m", $timestamp);
    $date = $wochentage[$wochentag] . ", " . date("d", $timestamp) . ". " . monat($monat) . " " . date("Y", $timestamp);
    return $date;
}

function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

function sortReplacementLessons($data)
{
    $entrys = array();
    for ($i = 0; $i < count($data); $i++) {
        $lesson = $data[$i]['Stunde'];
        if (isset($data[$i + 1])) {
            if ($data[$i]['Kurs'] == $data[$i + 1]['Kurs']) {
                if ($data[$i]['Fach'] == $data[$i + 1]['Fach']) {
                    if ($data[$i]['FachNew'] == $data[$i + 1]['FachNew']) {
                        if ($data[$i]['LehrerNeu'] == $data[$i + 1]['LehrerNeu']) {
                            if ($data[$i]['RaumNew'] == $data[$i + 1]['RaumNew']) {
                                if ($data[$i]['info'] == $data[$i + 1]['info']) {
                                    //echo $data[$i]['Stunde']." / ".$data[$i+1]['Stunde']."<br>";
                                    $lesson = $data[$i]['Stunde'] . " / " . $data[$i + 1]['Stunde'];
                                    $i++;
                                }
                            }
                        }
                    }
                }
            }
        }
        $data[$i]['Stunde'] = $lesson;
        array_push($entrys, $data[$i]);
    }
    return array_orderby($entrys, 'Kurs', SORT_ASC, 'Stunde', SORT_ASC);
}

$days = "";
$i = 0;
$url = Config::$url_api . 'vertretungsplan.php?active&secret=' . Config::$api_secret;
$json = file_get_contents($url);
$data = json_decode($json, true);
if (isset($data["access"])) {
    if (!$data["access"]) {
        die();
    }
}
$refreshed = "";
echo "<h1>".Language::$langhtmlonlivertheadline."</h1>
	<p>".Language::$langhtmlonlivertunderlineone."
	<br>
	<span>".Language::$langhtmlonlivertunderlinetwo." " . $data["info"]["refreshed"] . "</span>
	</p>";
foreach ($data["info"]["days"] as $day) {
    if (isset($data["data"]["vertretungen"][$day])) {
        $vertretungen = sortReplacementLessons($data["data"]["vertretungen"][$day]);


        $date = displaydate($day);
        echo "<p>
			<span class=\"vpfuer\">".Language::$langhtmlonlivertdayhead." <span class=\"vpfuerdatum\">" . $date . "</span></span>
			<br>
			</p>";
        echo "<table border=\"2\" class=\"tablekopf\">
			<tr>
			<th class=\"thlplanklasse\">".Language::$langhtmlonliverttablerowone."</th>
			<th class=\"thlplanstunde\">".Language::$langhtmlonliverttablerowtwo."</th>
			<th class=\"thlplanfach\">".Language::$langhtmlonliverttablerowthree."</th>
			<th class=\"thlplanvfach\">".Language::$langhtmlonliverttablerowfour."</th>
			<th class=\"thlplanvlehrer\">".Language::$langhtmlonliverttablerowfive."</th>
			<th class=\"thlplanvraum\">".Language::$langhtmlonliverttablerowsix."</th>
			<th class=\"thlplaninfo\">".Language::$langhtmlonliverttablerowseven."</th>
			</tr>";
        for ($i = 0; $i < count($vertretungen); $i++) {
            echo "<tr>
            <td class=\"tdaktionen\">" . $vertretungen[$i]['Kurs'] . "</td>
            <td class=\"tdaktionen\">" . $vertretungen[$i]['Stunde'] . "</td>
            <td class=\"tdaktionen\">" . $vertretungen[$i]['Fach'] . "</td>
            <td class=\"tdaktionen\">" . $vertretungen[$i]['FachNew'] . "</td>
            <td class=\"tdaktionen\">" . $vertretungen[$i]['LehrerNeu'] . "</td>
            <td class=\"tdaktionen\">" . $vertretungen[$i]['RaumNew'] . "</td>
            <td class=\"tdinfo\">" . $vertretungen[$i]['info'] . "</td>
            </tr>";
        }

        echo "</table>";
    }
    if (isset($data["data"]["aufsichten"][$day])) {
        $aufsichten = $data["data"]["aufsichten"][$day];
        echo "<span class=\"aufsichtenkopf\">".Language::$langhtmlonlivertdayunterline."</span><table>";
        foreach ($aufsichten as $row) {
            echo "<tr><td class=\"aufsicht\">" . $row["Zeit"] . ": " . $row["Ort"] . " --> " . $row["Lehrer"] . "</td></tr>";
        }
        echo "</table>";
    }
}