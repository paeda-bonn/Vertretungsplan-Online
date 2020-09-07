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

class Config
{
    public $url_web = "https://vplan.paeda.biz/";
    public $url_api = "https://vplan.paeda.biz/api/";
    public $api_secret = "75140035082837810714444522939082541179386996799142126159711853657541224";
    public $https = true;
    //Normal User
    private $user = "paedvert";
    private $pass = "";
    //Admin User
    private $userAdmin = "admin";
    private $passAdmin = "";
    private $link_DP = "https://www.otto-kuehne-schule.de/index.php?id=171";
    private $link_Impressum = "https://www.otto-kuehne-schule.de/index.php?id=16";

    function login($user, $pass)
    {
        if ($user == $this->user && $pass == $this->pass) {
            return true;
        } else {
            return false;
        }
    }

    function loginAdmin($user, $pass)
    {
        if ($user == $this->userAdmin && $pass == $this->passAdmin) {
            return true;
        } else {
            return false;
        }
    }
}

?>