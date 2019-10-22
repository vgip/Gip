<?php

$tests = array(
  'simpleTest' => 'simple_test',
  'easy' => 'easy',
  'HTML' => 'html',
  'simpleXML' => 'simple_xml',
  'PDFLoad' => 'pdf_load',
  'startMIDDLELast' => 'start_middle_last',
  'AString' => 'a_string',
  'Some4Numbers234' => 'some4_numbers234',
  'TEST123String' => 'test123_string',
);

foreach ($tests as $test => $result) {
  $output = Str::convertAllCamelCaseToUnderscore($test);
  if ($output === $result) {
    echo "Pass: $test => $result\n";
  } else {
    echo "Fail: $test => $result [$output]\n";
  }
}

