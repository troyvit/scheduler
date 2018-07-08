<?php

//     public $config = '{"tables":{"class":{"key":"id","fields":{"name":{"type":"string"},"start":{"type":"date","name":"Start Date"},"end":{"type":"date","name":"End Date"}}},"event_type":{"key":"id","fields":{"et_code":{"type":"string","name":"Event Code"},"et_name":{"type":"string","name":"Event Name"},"et_activity_level":{"type":"int","name":"Group (1), Private (2), Disabled (0)"},"et_desc":{"type":"string","name":"Event Description"}}}}}';

$fields = $table_info['fields'];

$name = $table_info['name'];

$cf = count($fields);

// print_r($table_info);
?>


<table border=1 id = "table_<?php echo $table; ?>">
<tr>
<td class="what_tr_is_for" id="mini_admin_<?php echo $table; ?>" colspan = "<?php echo $cf; ?>">
    <?php echo $name; ?>
    <a name="<?php echo "link_".$table; ?>" id="<?php echo "link_".$table; ?>"></a>
</td>
</tr>

<tr class="col_names">

    <?php
    foreach($fields as $field_name => $field_info) {
        echo "<td>".$field_info['name']."</td>";
    }
    ?>
</tr>

<?php 
$update['table'] = $table;

foreach($table_data as $id => $data_arr) {
    echo '<tr id="td_row_'.$id.'">';
    foreach($fields as $field_name => $field_info) {
    echo '<td><!--';
        print_r($field_info);
        print_r($data_arr);
        $html_id['table'] = $table; 
        $html_id['field_name'] = $field_name;
        $html_id['id'] = $id;
        $html_id_complete = base64_encode(json_encode($html_id));
        // calling off of field_name to make extra sure that th eonly data we pull is data specified by the config.
        
        echo '--><input type="text" class = "config_edit" name = "" id="'.$html_id_complete.'" value="'.$data_arr[$field_name].'"><!--<br>'.$html_id_complete.'--></td>';
    }
    echo '</tr>';
}
?>

<tr>
<td colspan = "<?php echo $cf; ?>">
<a href="#top">Return to top</a><br>
</td>
</tr>


</table>
