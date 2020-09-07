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

$menuonline =
    "<ul id=\"main-menu\" class=\"sm sm-blue\">";
if (substr($subsite, 0, 5) == "admin") {
    $menuonline .= "<li><a href=\"?subsite=adminVert\">Vertretungen Import</a></li>";
    $menuonline .= "<li><a href=\"?subsite=adminUntis\">Untis</a></li>";
    $menuonline .= "<li><a href=\"?subsite=adminKlausuren\">Klausuren Import</a></li>";
    $menuonline .= "<li><a href=\"?subsite=adminAushang\">Aushang Admin</a></li>";
    $menuonline .= "<li><a href=\"?subsite=vertretungsplan\">Administration Verlassen</a></li>";
} else {
    $menuonline .= "<li><a href=\"?subsite=vertretungsplan\">Vertretungsplan</a></li>";
    $menuonline .= "<li><a href=\"?subsite=klausuren\">n&auml;chste Klausuren</a></li>";
    $menuonline .= "<li><a href=\"?subsite=aushang\">Aushang</a></li>";
    $menuonline .= "<li><a href=\"?subsite=admin\">Admin</a></li>";
}

$menuonline .=
    "<li>
		<a href=>Impressum</a>
	</li>
	<li>
		<a href=>Datenschutz</a>
	</li>	
</ul>";
echo $menuonline;
?>