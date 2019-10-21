<?php

use Vgip\Gip\Common\Str;

$testsAllCamelCaseToUnderscore = [
    'simpleTest'        => 'simple_test',
    'easy'              => 'easy',
    'HTML'              => 'html',
    'simpleXML'         => 'simple_xml',
    'PDFLoad'           => 'pdf_load',
    'startMIDDLELast'   => 'start_middle_last',
    'AString'           => 'a_string',
    'Some4Numbers234'   => 'some4_numbers234',
    'TEST123String'     => 'test123_string',
];

$testsUnderscoreToLowerCamelCase = [
    'simple_test'       => 'simpleTest',
    'easy'              => 'easy',
    'HTML'              => 'html',
    'simple_xml'        => 'simpleXml',
    'pdf_load'          => 'pdfLoad',
    'start_middle_last' => 'startMiddleLast',
    'a_string'          => 'aString',
    'some4_numbers234'  => 'some4Numbers234',
    'test123_string'    => 'test123String',
];

echo 'Test convertLowerCamelCaseToUnderscore() and convertUpperCamelCaseToUnderscore() <br><br>';
foreach ($testsAllCamelCaseToUnderscore AS $test => $result) {
    $output = Str::convertLowerCamelCaseToUnderscore($test);
    if ($output === $result) {
        $failIptput = '';
        $res = 'Pass';
    } else {
        $res = 'Fail';
        $failIptput = ' (fail result conversion: '.$output.')';
    }
    echo $res.': '.$test.'=>'.$result.$failIptput.'<br>';
}

echo '<br><br> Test convertUnderscoreToLowerCamelCase() <br><br>';
foreach ($testsUnderscoreToLowerCamelCase AS $test => $result) {
    $failIptput = '';
    $output = Str::convertUnderscoreToLowerCamelCase($test);
    if ($output === $result) {
        $res = 'Pass';
    } else {
        $res = 'Fail';
        $failIptput = ' (fail result conversion: '.$output.')';
    }
    echo $res.': '.$test.'=>'.$result.$failIptput.'<br>';
}
