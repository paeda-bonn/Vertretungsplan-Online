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

$stufeone = "EF";
$stufetwo = "Q1";
$stufethree = "Q2";

$stufeonecolor = "#C00000";
$stufetwocolor = "#0000C0";
$stufethreecolor = "#00B050";

$weekdays = array("So", "Mo", "Di", "Mi", "Do", "Fr", "Sa");

$json = file_get_contents($config->url_api . 'klausuren.php?active&upcoming&secret=' . $config->api_secret);
$data = json_decode($json, true);
if (isset($data["access"])) {
    if (!$data["access"]) {
        die();
    }
}

echo
'<table border=0 cellpadding="0" cellspacing="0" style="border-collapse:collapse;table-layout:fixed;width:495px">
		<tr class="xl74" height="24" style="height:18.0px">
			<td id="rowTime"></td>
			<td id="rowCourse"></td>
			<td id="rowSpacer"></td>
			<td id="rowTeacher"></td>
			<td id="rowRoom"></td>
			<td id="row1"></td>
			<td id="row2"></td>
			<td id="row3"></td>
			<td id="row4"></td>
			<td id="row5""></td>
			<td id="row6"></td>
			<td id="row7"></td>
		</tr>
		<tr>
			<td colspan="12" class="xl94" style="height:56.25px">
				Der Unterricht bei den aufsichtsführenden Lehrern findet in den jeweiligen Stunden nicht statt
			</td>
		</tr>';
//PHP
$datebefore = "";
foreach ($data as $row) {
    if ($row['Stufe'] == $stufeone) {
        $color = $stufeonecolor;
    } elseif ($row['Stufe'] == $stufetwo) {
        $color = $stufetwocolor;
    } elseif ($row['Stufe'] == $stufethree) {
        $color = $stufethreecolor;
    } else {
        $color = "#000000";
    }
    if ($datebefore != $row['Datum']) {
        $vorstufe = $row['Stufe'];
        $datebefore = $row['Datum'];
        $datumdm = gmdate("d.m", $row['UnixDatum']);
        $weekday = $weekdays[date("w", $row['UnixDatum'])];

//HTML
        echo '<tr>
				<td colspan="12" style="border-bottom:2.0px double windowtext">&nbsp;</td>
			</tr>
			<tr style="height:9.0px"></tr>
			<tr height="24" style="page-break-before:always;height:18.0px">
				<td height="24" class="xl68" style="height:18.0px">' . $weekday . '</td>
				<td class="xl70">' . $datumdm . '</td>
				<td></td>
				<td></td>
				<td colspan="2" class="xl70">Klausur</td>
				<td></td>
				<td colspan="3" class="xl70">Aufsicht</td>
			</tr>
			<tr height="24" style="height:18.0px">
				<td height="24" class="xl69" style="height:18.0px">Std.</td>
				<td class="xl69">Kurs</td>
				<td class="xl71">Lehrer</td>
				<td></td>
				<td class="xl69">Raum</td>
				<td class="xl69">1</td>
				<td class="xl69">2</td>
				<td class="xl69">3</td>
				<td class="xl69">4</td>
				<td class="xl69">5</td>
				<td class="xl69">6</td>
				<td class="xl69">7</td>
			</tr>';
    } elseif ($vorstufe != $row['Stufe']) {
        $vorstufe = $row['Stufe'];
        echo '<tr height="24" style="height:18.0px"></tr>';
    }
    echo '<tr>
			<td height="24" class="xl78" style="height:18.0px; color:' . $color . '">' . $row['Std'] . '</td>
			<td class="xl78" style="color:' . $color . '">' . $row['Stufe'] . ' / ' . $row['Kurs'] . '</td>
			<td class="xl78" style="color:' . $color . '">' . $row['Lehrer'] . '</td>
			<td class="xl72"></td>
			<td class="xl74">' . $row['Raum'] . '</td>
			<td class="xl74">' . $row['1'] . '</td>
			<td class="xl74">' . $row['2'] . '</td>
			<td class="xl74">' . $row['3'] . '</td>
			<td class="xl74">' . $row['4'] . '</td>
			<td class="xl74">' . $row['5'] . '</td>
			<td class="xl74">' . $row['6'] . '</td>
			<td class="xl74">' . $row['7'] . '</td>
		</tr>';
}
echo "</table>";
?>