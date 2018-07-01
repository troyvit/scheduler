<?php

//     public $config = '{"tables":{"class":{"key":"id","fields":{"name":{"type":"string"},"start":{"type":"date","name":"Start Date"},"end":{"type":"date","name":"End Date"}}},"event_type":{"key":"id","fields":{"et_code":{"type":"string","name":"Event Code"},"et_name":{"type":"string","name":"Event Name"},"et_activity_level":{"type":"int","name":"Group (1), Private (2), Disabled (0)"},"et_desc":{"type":"string","name":"Event Description"}}}}}';

$fields = $table_info['fields'];

$name = $table_info['name'];

$cf = count($fields);

// print_r($table_info);
?>

<table border=1 id = "table_<?php echo $table; ?>">
<tr>
<td id="mini_admin_<?php echo $table; ?>" colspan = "<?php echo $cf; ?>">
    <?php echo $name; ?>
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
foreach($table_data as $id => $data_arr) {
    echo '<tr id="td_row_'.$id.'">';
    foreach($fields as $field_name => $field_info) {
        // calling off of field_name to make extra sure that th eonly data we pull is data specified by the config.
        echo '<td>'.$data_arr[$field_name].'</td>';
    }
    echo '</tr>';
}
?>



</table>
