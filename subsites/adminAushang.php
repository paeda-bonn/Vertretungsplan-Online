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

?>
<script src="https://code.jquery.com/jquery-3.3.1.js" integrity="sha256-2Kok7MbOyxpgUVvAk/HJ2jigOSYS2auK4Pfzbm7uH60="
        crossorigin="anonymous"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
      integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet" type="text/css">
<script src="assets/js/utils.js"></script>
<script src="assets/js/dataProcessing.js"></script>
<table class="table table-bordered table-fixed">
    <thead id="header" style="background-color: white; z-index: 100">
    <tr>
        <th scope="col" style="width:50px">#</th>
        <th scope="col" style="width:250px">Actions</th>
        <th scope="col">Linke Spalte</th>
        <th scope="col">Rechte Spalte</th>
        <th scope="col" style="width:100px">Farbe</th>
        <!--<th scope="col">Ablauf</th>-->
    </tr>
    </thead>
    <tbody id="active">

    </tbody>

    <tbody id="footer">
    <tr>
        <td>+</td>
        <td scope="row">
            <button type="button" class="btn btn-success" onclick="addElementFromWeb()"><i class="material-icons">add_box</i>
            </button>
        </td>
        <td><textarea class="form-control" id="web.0.content" rows="4"></textarea></td>
        <td><textarea class="form-control" id="web.0.content2" rows="4"></textarea></td>
        <td style="width: 250px">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <label class="input-group-text" for="colorselect">Farbe</label>
                </div>
                <select class="custom-select" id="web.0.color">
                    <option value="none" selected>Auswählen...</option>
                    <option value="red">Rot</option>
                    <option value="yellow">Gelb</option>
                    <option value="olive">Olive</option>
                    <option value="lime">hell Grün</option>
                    <option value="aqua">hell Blau</option>
                    <option value="orange">Orange</option>
                    <option value="fuchsia">Hell-Magenta</option>
                </select>
            </div>
        </td>
    </tr>
    </tbody>
    <tbody>
    <tr>
        <td colspan="5" style="text-align: center"><h4>Presets</h4></td>
    </tr>
    </tbody>
    <tbody id="presets"></tbody>

</table>

<script src="assets/js/notify.js"></script>