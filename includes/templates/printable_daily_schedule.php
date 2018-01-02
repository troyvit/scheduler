<a id="closehds">X</a>
<select style="margin: 2em;" id="filter_leader">
     <option value=""><?php echo $sl->gp('Select an Instructor to filter'); ?></option>
    <?php echo $leader_list; ?>
</select>

<h2><?php echo $day; ?></h2>
<?php if(strlen($leader_header) > 0) { 
    echo '<h3>'.$leader_header.'</h3>';
}
echo $table_data;
?>
