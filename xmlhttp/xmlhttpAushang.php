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

header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: https://splan.nils-witt.de');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization, Cache-Control, Pragma');
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, HEAD, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {

    die(200);
}

session_start();
require_once("../config.php");
$user = $_GET["username"];
$pass = $_GET["password"];
if (Config::loginAdmin($user, $pass)) {
    $admin = true;
} else {
    die ("Not authorized");
}

function curlToApi($mode, $json){

    $ch = curl_init(Config::$url_api . '/aushang.php?aushang=' . $mode . '&secret=' . Config::$api_secret);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json))
    );
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $result = curl_error($ch);
        curl_close($ch);
    }
    return $result;
}

function activeSelect($color, $id)
{
    $colors = array();
    $colors["red"] = "Rot";
    $colors["yellow"] = "Gelb";
    $colors["olive"] = "Olive";
    $colors["lime"] = "hell Grün";
    $colors["aqua"] = "hell Blau";
    $colors["orange"] = "Orange";
    $colors["fuchsia"] = "Hell-Magenta";
    $output = '<div class="input-group mb-3">
        <div class="input-group-prepend">
            <label class="input-group-text" for="colorselect">Farbe</label>
        </div>
        <select class="custom-select" id="color.' . $id . '" disabled>';
    for ($i = 0; $i < sizeof($colors); $i++) {
        if (key($colors) == $color) {
            $output .= "<option value=" . key($colors) . " selected>" . $colors[key($colors)] . "</option>";
        } else {
            $output .= "<option value=" . key($colors) . ">" . $colors[key($colors)] . "</option>";
        }

        next($colors);
    }

    $output .= '</select></div>';
    return $output;
}


$json = file_get_contents("php://input");
$data = json_decode($json, true);
$json = json_encode($data);
$action = htmlspecialchars($_GET["action"]);

if ($action == "create") {
    echo $json;
    echo "Res0";
    echo curlToApi( "create", $json);
} elseif ($action == "createPreset") {
    echo curlToApi( "createPreset", $json);
} elseif ($action == "delete") {
    echo curlToApi( "delete", $json);
} elseif ($action == "update") {
    echo curlToApi( "update", $json);
} elseif ($action == "move") {
    echo curlToApi( "updateOrder", $json);
} elseif ($action == "movePreset") {
    echo curlToApi( "updateOrderPreset", $json);
} elseif ($action == "load") {
    $json = file_get_contents(Config::$url_api . '/aushang.php?aushang=1&secret=' . Config::$api_secret);
    $aushangdata = json_decode($json);

    foreach ($aushangdata as $row) {
        if (!isset($row->spalten)) {
            $zweispalten = false;
        } elseif ($row->spalten == "true") {
            $zweispalten = true;
        } else {
            $zweispalten = false;
        }
        $colorselect = activeSelect($row->Color, $row->ID);
        if ($zweispalten) {

            echo '<tr id="row.' . $row->ID . '">
                <td style="max-width:10px">' . $row->Order . '</td>
                <td>
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <button type="button" class="btn btn-danger" onClick="removeElementFromApi(' . $row->ID . ')" ><i class="material-icons">delete_forever</i></button>
                        <button type="button" class="btn btn-warning" id="edit.' . $row->ID . '" onClick="editElementFromApi(' . $row->ID . ')" ><i class="material-icons">edit</i></button>
                        <button type="button" class="btn btn-success" id="save.' . $row->ID . '" onClick="updateToApi(' . $row->ID . ')" style="display: none;"><i class="material-icons">save</i></button>
                        <button type="button" class="btn btn-primary" onClick="moveElement(' . $row->ID . ',\'up\',\'1\')"><i class="material-icons">arrow_upward</i></button>
                        <button type="button" class="btn btn-primary"onClick="moveElement(' . $row->ID . ',\'down\',\'1\')" ><i class="material-icons">arrow_downward</i></button>
                    </div>
                    <div class="btn-group" role="group" aria-label="Actions">
                        <button type="button" class="btn btn-primary" onClick="addElementToPresets(' . $row->ID . ')" ><i class="material-icons">archive</i></button>
                    </div>
                </td>
                <td style="background-color:' . $row->Color . '"><textarea id="textarea.' . $row->ID . '.content" class="form-control" onkeyup="textAreaAdjust(this)" disabled>' . $row->Content . '</textarea></td>
                <td style="background-color:' . $row->Color . '"><textarea id="textarea.' . $row->ID . '.content2" class="form-control" onkeyup="textAreaAdjust(this)" disabled>' . $row->Content2 . '</textarea></td>
                <td style="background-color:' . $row->Color . '">' . $colorselect . '</td>
            </tr>';
        } else {
            echo '<tr id="row.' . $row->ID . '">
                <td style="max-width:10px">' . $row->Order / 10 . '</td>
                <td style="max-width:100px">
                    <div class="btn-group" role="group" aria-label="Actions">
                        <button type="button" class="btn btn-danger" onClick="removeElementFromApi(' . $row->ID . ')" ><i class="material-icons">delete_forever</i></button>
                        <button type="button" class="btn btn-warning" id="edit.' . $row->ID . '" onClick="editElementFromApi(' . $row->ID . ')" ><i class="material-icons">edit</i></button>
                        <button type="button" class="btn btn-success" id="save.' . $row->ID . '" onClick="updateToApi(' . $row->ID . ')" style="display: none;"><i class="material-icons">save</i></button>
                        <button type="button" class="btn btn-primary" onClick="moveElement(' . $row->ID . ',\'up\',\'1\')"><i class="material-icons">arrow_upward</i></button>
                        <button type="button" class="btn btn-primary" onClick="moveElement(' . $row->ID . ',\'down\',\'1\')" ><i class="material-icons">arrow_downward</i></button>
                    </div>
                    <div class="btn-group" role="group" aria-label="Actions">
                        <button type="button" class="btn btn-primary" onClick="addElementToPresets(' . $row->ID . ')" ><i class="material-icons">archive</i></button>
                    </div>
                </td>
                <td style="background-color:' . $row->Color . '"><textarea id="textarea.' . $row->ID . '.content" class="form-control" onkeyup="textAreaAdjust(this)" disabled>' . $row->Content . '</textarea></td>
                <td style="background-color:' . $row->Color . '"></td>
                <td style="background-color:' . $row->Color . '">' . $colorselect . '</td>
                <!--<td style="background-color:' . $row->Color . '"></td>-->
            </tr>';
        }


    }
} elseif ($action == "presets") {
    $json = file_get_contents(Config::$url_api.'/aushang.php?aushang=presets&secret='.Config::$api_secret);
    $aushangdata = json_decode($json);

    foreach ($aushangdata as $row) {
        if (!isset($row->spalten)) {
            $zweispalten = false;
        } elseif ($row->spalten == "true") {
            $zweispalten = true;
        } else {
            $zweispalten = false;
        }
        $colorselect = activeSelect($row->Color, $row->ID);
        if ($zweispalten) {

            echo '<tr id="row.' . $row->ID . '">
                <td style="max-width:10px">' . $row->Order . '</td>
                <td>
                    <div class="btn-group" role="group" aria-label="Basic example">
                        <button type="button" class="btn btn-success" onClick="addElementFromPresets(' . $row->ID . ')" ><i class="material-icons">add_box</i></button>
                        <button type="button" class="btn btn-primary" onClick="moveElement(' . $row->ID . ',\'up\',\'3\')"><i class="material-icons">arrow_upward</i></button>
                        <button type="button" class="btn btn-primary" onClick="moveElement(' . $row->ID . ',\'down\',\'3\')" ><i class="material-icons">arrow_downward</i></button>
                        <button type="button" class="btn btn-danger" onClick="removeElementFromApi(' . $row->ID . ')" ><i class="material-icons">delete_forever</i></button>
                        <button type="button" class="btn btn-warning" id="edit.' . $row->ID . '" onClick="editElementFromApi(' . $row->ID . ')" ><i class="material-icons">edit</i></button>
                        <button type="button" class="btn btn-success" id="save.' . $row->ID . '" onClick="updateToApi(' . $row->ID . ')" style="display: none;"><i class="material-icons">save</i></button>
                    </div>
                    <div class="btn-group" role="group" aria-label="Actions">
                        <button type="button" class="btn btn-success" onClick="addElementFromPresets(' . $row->ID . ')" ><i class="material-icons">unarchive</i></button>
                    </div>
                </td>
                <td style="background-color:' . $row->Color . '"><textarea id="textarea.' . $row->ID . '.content" class="form-control" onkeyup="textAreaAdjust(this)" disabled>' . $row->Content . '</textarea></td>
                <td style="background-color:' . $row->Color . '"><textarea id="textarea.' . $row->ID . '.content2" class="form-control" onkeyup="textAreaAdjust(this)" disabled>' . $row->Content2 . '</textarea></td>
                <td style="background-color:' . $row->Color . '">' . $colorselect . '</td>
            </tr>';
        } else {
            echo '<tr id="row.' . $row->ID . '">
                <td style="max-width:10px">' . $row->Order / 10 . '</td>
                <td style="max-width:100px">
                    <div class="btn-group" role="group" aria-label="Actions">
                        <button type="button" class="btn btn-danger" onClick="removeElementFromApi(' . $row->ID . ')" ><i class="material-icons">delete_forever</i></button>
                        <button type="button" class="btn btn-warning" id="edit.' . $row->ID . '" onClick="editElementFromApi(' . $row->ID . ')" ><i class="material-icons">edit</i></button>
                        <button type="button" class="btn btn-success" id="save.' . $row->ID . '" onClick="updateToApi(' . $row->ID . ')" style="display: none;"><i class="material-icons">save</i></button>
                        <button type="button" class="btn btn-primary" onClick="moveElement(' . $row->ID . ',\'up\',\'3\')"><i class="material-icons">arrow_upward</i></button>
                        <button type="button" class="btn btn-primary" onClick="moveElement(' . $row->ID . ',\'down\',\'3\')" ><i class="material-icons">arrow_downward</i></button>
                    </div>
                    <div class="btn-group" role="group" aria-label="Actions">
                        <button type="button" class="btn btn-success" onClick="addElementFromPresets(' . $row->ID . ')" ><i class="material-icons">unarchive</i></button>
                    </div>
                </td>
                <td style="background-color:' . $row->Color . '"><textarea id="textarea.' . $row->ID . '.content" class="form-control" onkeyup="textAreaAdjust(this)" disabled>' . $row->Content . '</textarea></td>
                <td style="background-color:' . $row->Color . '"></td>
                <td style="background-color:' . $row->Color . '">' . $colorselect . '</td>
                <!--<td style="background-color:' . $row->Color . '"></td>-->
            </tr>';
        }
    }
}