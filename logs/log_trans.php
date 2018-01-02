<?php

$str[]='{"transactionid":"2085680211","amount":"75.00","GWUSID":null,"info":"{\"1957\":{\"line_item\":\"Group Classes Spring 2014\",\"pay\":\"25.00\",\"full_name\":\"Garrett Beshore\"},\"1836\":{\"line_item\":\"Group Classes Spring 2014\",\"pay\":\"25.00\",\"full_name\":\"Connor Beshore\"},\"1907\":{\"line_item\":\"Group Classes Spring 2014\",\"pay\":\"25.00\",\"full_name\":\"Katie Beshore\"}}"}';
$str[]='{"transactionid":"2112207376","amount":"687.00","GWUSID":null,"info":"{\"2043\":{\"line_item\":\"Intermediate Stroke\",\"pay\":\"458.00\",\"full_name\":\"Garrett Beshore\"},\"1836\":{\"line_item\":\"Beginning Stroke 1\",\"pay\":\"229\",\"full_name\":\"Connor Beshore\"},\"1907\":{\"line_item\":\"Funday\",\"pay\":\"0\",\"full_name\":\"Katie Beshore\"}}"}';
$str[]='{"transactionid":"2154373210","amount":"687.00","GWUSID":null,"info":"{\"2043\":{\"line_item\":\"Intermediate Stroke\",\"pay\":\"0.00\",\"full_name\":\"Garrett Beshore\"},\"2075\":{\"line_item\":\"Beginning Stroke 1\",\"pay\":\"229.00\",\"full_name\":\"Connor Beshore\"},\"1907\":{\"line_item\":\"Funday\",\"pay\":\"458.00\",\"full_name\":\"Katie Beshore\"}}"}';

foreach($str as $string) {

    $arr[]=json_decode($string, true);
}

foreach ($arr as $the_arrs) {
    $info=$the_arrs['info'];
    $info_arr = json_decode($info, true);
    print_r($info_arr);
}


