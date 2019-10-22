<?php

use Vgip\Gip\Common\Str;

$pathAutolad = join(DIRECTORY_SEPARATOR, [dirname(dirname(__DIR__)), 'vendor', 'autoload.php']);
require $pathAutolad;


$testTask = [];

$testTask['all_camel_case_to_lower_snake_case'] = [
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

$testTask['lower_snake_case_to_lower_camel_case'] = [
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

$testResult = [];

$testResult['all_camel_case_to_lower_snake_case'] = [];
foreach ($testTask['all_camel_case_to_lower_snake_case'] AS $test => $result) {
    $output = Str::convertLowerCamelCaseToLowerSnakeCase($test);
    if ($output === $result) {
        $failIptput = '';
        $res = 'Pass';
    } else {
        $res = 'Fail';
        $failIptput = ' (fail result conversion: '.$output.')';
    }
    $testResult['all_camel_case_to_lower_snake_case'][] = $res.': '.$test.'=>'.$result.$failIptput.'';
}

$testResult['lower_snake_case_to_lower_camel_case'] = [];
foreach ($testTask['lower_snake_case_to_lower_camel_case'] AS $test => $result) {
    $failIptput = '';
    $output = Str::convertLowerSnakeCaseToLowerCamelCase($test);
    if ($output === $result) {
        $res = 'Pass';
    } else {
        $res = 'Fail';
        $failIptput = ' (fail result conversion: '.$output.')';
    }
    $testResult['lower_snake_case_to_lower_camel_case'][] = $res.': '.$test.'=>'.$result.$failIptput;
}

echo '<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>Test PHP string xCase converter</title>

</head>

<body>
  <h1>Test PHP string xCase converter</h1>
  
  <div><strong>LowerCamelCase:</strong> lowerCamelCase, backColor, color</div>
  <div><strong>UpperCamelCase:</strong> UpperCamelCase, BackColor, Color</div>
  <div><strong>LowerSnakeCase:</strong> lower_snake_case, back_color, color</div>
  
  <h2>Test convert AllCamelCase to LowerSnakeCase: convertLowerCamelCaseToLowerSnakeCase()</h2>
  <div>'.join('<br>', $testResult['all_camel_case_to_lower_snake_case']).'</div>
      
  <h2>Test convert LowerSnakeCase to LowerCamelCase: convertLowerSnakeCaseToLowerCamelCase()</h2>
  <div>'.join('<br>', $testResult['lower_snake_case_to_lower_camel_case']).'</div>
</body>
</html>';