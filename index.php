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

header('Content-Type: text/html; charset=UTF-8');
require_once("config.php");
require_once("dependencies/lang.php");
require_once('dependencies/SimpleXLSX.php');

$config = new Config;

if ($config->https) {
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off") {
        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirect);
        exit();
    }
}

if (isset($_SERVER['PHP_AUTH_USER'])) {
    $user = $_SERVER['PHP_AUTH_USER'];
    $pass = $_SERVER['PHP_AUTH_PW'];
    if ($config->loginAdmin($user, $pass)) {
        $admin = true;
    } elseif ($config->login($user, $pass)) {
        $admin = false;
    } else {
        header('WWW-Authenticate: Basic realm="Vertretungsplan"');
        header('HTTP/1.0 401 Unauthorized');
        die ("Not authorized");
    }
} else {
    header('WWW-Authenticate: Basic realm="Vertretungsplan"');
    header('HTTP/1.0 401 Unauthorized');
    die ("Not authorized");
}

if (isset($_GET["subsite"])) {
    $subsite = htmlspecialchars($_GET["subsite"]);
    if ($subsite == "") {
        $subsite = "vertretungsplan";
    }
} else {
    $subsite = "vertretungsplan";
}

if (substr($subsite, 0, 5) == "admin" and !$admin) {
    header('WWW-Authenticate: Basic realm="Vertretungsplan"');
    header('HTTP/1.0 401 Unauthorized');
    die();
}

?>
<!doctype html>
<html lang="de">
<meta charset="utf-8">
<head>
    <link href="assets/css/sm-core-css.css" rel="stylesheet" type="text/css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
    <link href="assets/css/<?php echo $subsite ?>.css" rel="stylesheet" type="text/css">
    <link href="assets/css/sm-blue.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="assets/css/vertretungsplan.css">
    <title>
        <?php echo $langhtmlonliverttitle ?>
    </title>
</head>
<body>
<?php
require_once('dependencies/menus.php');
require_once('subsites/' . $subsite . '.php');
?>
<div style="height: 10px;">
    <space></space>
</div>
<h4 style="position: fixed;bottom: 0;margin: 0 auto;background-color: white;">
    <a href="https://github.com/paeda-bonn/vplan-web">Vertretungsplan</a> - © Copyright 2017 - 2020 Nils Witt
</h4>
</body>
</html>