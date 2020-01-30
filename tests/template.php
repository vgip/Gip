<?php

declare(strict_types=1);

use Vgip\Gip\Common\Arr;

$arr = new Arr();

$testArr = [
    'a_key' => 'a_val',
    'b_key' => 'b_val',
    'c_key' => 'c_val',
];

$arrNew = $arr->updateArrayKeyByString($testArr, 'b_key', 'bb_key');
print_r($arrNew);
