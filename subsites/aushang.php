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

$json = file_get_contents($config->url_api . 'aushang.php?secret=' . $config->api_secret);
$aushangdata = json_decode($json);
if (isset($aushangdata->access)) {
    if (!$aushangdata->access) {
        die();
    }
}
echo "<table height=20 style='table-layout:fixed;height:20px'></table>";
echo
"<table class='aushangtable' border=0 cellpadding=0 cellspacing=0 style='border-collapse:collapse;table-layout:fixed;width:100%'>
	<col style='width:50%'>
	<col style='width:50%'>";
foreach ($aushangdata as $row) {
    if (!isset($row->spalten)) {
        $zweispalten = false;
    } elseif ($row->spalten == "true") {
        $zweispalten = true;
    } else {
        $zweispalten = false;
    }

    if ($zweispalten) {

        echo '<tr height=24 style="height:18.0pt">
			<td colspan=1 height=24 class="aushang" style="background-color:' . $row->Color . ';height:18.0pt;">' . nl2br($row->Content) . '</td>';

        echo '<td colspan=1 height=24 class="aushang" style="background-color:' . $row->Color . ';height:18.0pt;">' . nl2br($row->Content2) . '</td>
		</tr>';

    } else {
        echo '<tr height=24 style="height:18.0pt">
						<td colspan=2 height=24 class="aushang" style=background-color:' . $row->Color . '; style="height:18.0pt;">' . nl2br($row->Content) . '</td>
						</tr>';

    }
}
echo "</table>";
?>