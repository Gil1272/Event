<?php

use SimpleSoftwareIO\QrCode\Facades\QrCode;


function displayTr($label, $value) {
    return "<tr><td> <strong>$label</strong> </td><td> $value </td></tr>";
}


function qrCodeImage(string $url,$size=500){
    // return (QrCode::format('svg')->size($size)->generate($url));
}
