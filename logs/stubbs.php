<?php
$str1='{"transactionid":"2088405940","amount":"75.00","GWUSID":null,"info":"{\"1813\":{\"line_item\":\"Group Classes Spring 2014\",\"pay\":\"25.00\",\"full_name\":\"Eli Stubbs\"},\"1955\":{\"line_item\":\"Group Classes Spring 2014\",\"pay\":\"25.00\",\"full_name\":\"Zac Stubbs\"},\"1774\":{\"line_item\":\"Group Classes Spring 2014\",\"pay\":\"25.00\",\"full_name\":\"Ava Stubbs\"}}"}';
$str2='{"transactionid":"2132709468","amount":"1374.00","GWUSID":null,"info":"{\"1813\":{\"line_item\":\"Beginning Stroke 1\",\"pay\":\"458.00\",\"full_name\":\"Eli Stubbs\"},\"1955\":{\"line_item\":\"Funday\",\"pay\":\"458.00\",\"full_name\":\"Zac Stubbs\"},\"2067\":{\"line_item\":\"\",\"pay\":\"458.00\",\"full_name\":\"Ava Stubbs\"}}"}';

$arr[]=json_decode($str1, true);

$arr[]=json_decode($str2, true);

foreach ($arr as $the_arrs) {
    $info=$the_arrs['info'];
    $info_arr = json_decode($info, true);
    print_r($info_arr);
}


