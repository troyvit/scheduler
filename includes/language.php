<?php

$sl= new S_language;
$sl-> db = $db;
$ph_res = $sl-> get_phrases();
$ph_arr=result_as_array(new serialized_Render(), $ph_res, 'phrase_key');
$sl-> phrase_book = $ph_arr;
/*
echo '<pre>';
print_r($sl->phrase_book);
 */
// crap. I need the id in here if I'm going to make these modifiable

?>
