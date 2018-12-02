<?php
class Db_connect {
    public $mysqli;
    var $database_host;
    var $database_name;
    var $database_user;
    var $database_password;

    function __construct($db_con) {
        foreach($db_con as $var => $val) {
            $this->$var = $val;
        }
    }

    function Db_connect() {
        $this->mysqli = new mysqli($this->database_host, $this->database_user, $this-> database_password, $this -> database_name);
        if ($this->mysqli->connect_error) {
                die('Connect Error (' . $this->mysqli->connect_errno . ') '
                                . $this->mysqli->connect_error);
        }

        if (mysqli_connect_error()) {
                die('Connect Error (' . mysqli_connect_errno() . ') '
                                . mysqli_connect_error());
        }
        return $this->mysqli;
    }
}

$db_obj = new Db_connect($db_con);
$mydb = $db_obj -> Db_connect();
$db=$mydb;

class Data_Render {
    var $db;
    var $data_type;
    var $html_type;
    var $db_result;
}

class serialized_Render extends Data_Render {
    var $db_result;
    var $key_name;

    function render_assoc() {
        if($this->data_type=='array') {
            // return the data as an array
            return $this->render_array();
        }
        if($this->data_type='string') {
            // return the data as an string
            return $this->render_string();
        }
    }

    function render_array() {
        $key_name=$this->key_name;
        while ($row = $this->db_result->fetch_assoc()) {
            // debug // echo "I am totally extracting you \n";
            extract($row);
            $ret[$$key_name]=$row;
        }
        $this->db_result->close();
        return $ret;
    }

}
class html_Render extends Data_Render {
    var $data_type; // list, string, checkboxes
    var $selected; // default selected value or values
    var $sel_option; // for dropdowns - option name
    var $data_label; // for check and radio - checkbox name - should be merged with sel_option
    var $sel_value; // for dropdowns - option value
    var $db_result; // database result dropped in
    var $data_format; // array, db_result
    var $data_set; // list of data other than a db_result
                   // they should all be data_sets and I should get rid of db_result above
    // var $radio_value; // value for radio buttons // comes from the loop now
    // for radio buttons
    var $name_name;
    var $id_name;
    var $label_name;
    var $value_key;
    function render_assoc() {
        if($this->data_type=='list') {
            // we are rendering list items
            /* code fart! */
            return $this->render_list();
        }     
        if($this->data_type == 'string') {
            // debug // echo "data type is indeed string baby on line ".__LINE__."<br>";
            return $this->render_string();
        }
        if($this -> data_type == 'newcheckboxes') {
            return $this -> new_render_checkboxes();
        }
        if($this -> data_type == 'radio') {
            return $this -> new_render_radio();
        }

        if($this -> data_type == 'table') {
            return $this -> render_table_body();
        }

        // add more data types here
    }

    function render_checkboxes() {
        if( $this -> data_format == 'array' ) {
            $data_label = $this -> data_label;
            if( is_array ( $this -> data_set ) ) {
                $ds = $this -> data_set;
                $id_name = $this -> id_name;
                /*
                echo '<pre>';
                print_r($ds);
                echo '</pre>';
                 */
                foreach ( $ds as $var => $val ) {
                    extract( $val );
                    // debug // echo 'in here id is $field_id (field_id) and id_name is '.$this->id_name.'which makes id_name '.$$id_name.'<br>';
                    if( in_array( $id, $this -> selected ) ) {
                        $checked = ' CHECKED ';
                    } else {
                        $checked = '';
                    }
                    $ret.= '<input '.$checked.' type="checkbox" id="'.$$id_name.'" /><label for="'.$$id_name.'">'.$$data_label.'</label>';
                }
            }
        }
        return $ret;
    }

    function render_table_body() {
        // you are responsible for the table head so you can label your table, Able
        $ret='<tbody>';
        while($row = $this->db_result -> fetch_assoc()) {
        }
        // not tonight I'm sorry I'm too old
    }

    function new_render_checkboxes() {
        $id_name    = $this -> id_name;
        $name_name  = $this -> name_name;
        $label_name = $this -> label_name;
        $value_name = $this -> value_name;
        $selected   = $this -> selected; // needs to be an array
        if( $this -> data_format == 'array' ) {
            $data_label = $this -> data_label;
            if( is_array ( $this -> data_set ) ) {
                $ds = $this -> data_set;
                    $i=0; // sorry future-Troy
                    // doubly sorry in that it doesn't even work the way you think it does
                foreach($ds as $id => $arr) {
                    // whatever // echo "id is $id and i is $i <br>";
                    if(in_array($arr[$value_name],$selected)) {
                        $sel= ' checked ';
                    } else {
                        $sel = '';
                    }
                    $ret.= '<input '.$sel.' class="'.$this->input_class.'" value="'.$arr[$value_name].'" name="'.$arr[$name_name].'" type="checkbox" id="'.$arr[$id_name].'_'.$i.'"><label for="'.$arr[$id_name].'_'.$i.'">'.$arr[$label_name].'</label>';
                    $i++;
                }
            }
        }
        return $ret;
    }

    function new_render_radio() {
        $id_name    = $this -> id_name;
        $name_name  = $this -> name_name;
        $label_name = $this -> label_name;
        $value_name = $this -> value_name;
        $selected   = $this -> selected;
        $extra_attr = $this -> extra_attr;
        if( $this -> data_format == 'array' ) {
            $data_label = $this -> data_label;
            if( is_array ( $this -> data_set ) ) {
                $ds = $this -> data_set;
                $i=0; // sorry future-Troy
                /*
                echo 'count is ';
                echo count($ds);
                echo '<br>';
                echo "we are going to look for $value_name inside the array <br>";
                 */
                foreach($ds as $id => $arr) {
                    /*
                    echo '<pre>';
                    print_r($arr);
                    echo '</pre>';
                    echo "we are looking for $id_name <br>";
                    echo "input type equals 'raadio' id = ";
                    echo $arr[$id_name];
                     */
                    if($arr[$value_name]==$selected) {
                        $sel= ' checked ';
                    } else {
                        $sel = '';
                    }
                    $ret.= '<input '.$sel.' class="'.$this->input_class.'" value="'.$arr[$value_name].'" name="'.$arr[$name_name].'" type="radio" id="'.$arr[$id_name].'_'.$i.'" '.$extra_attr.' ><label for="'.$arr[$id_name].'_'.$i.'">'.$arr[$label_name].'</label>';
                    $i++;
                }
            }
        }
        return $ret;
    }

    function render_radio() {
        $val_str='';
        $id_str = '';
        if( $this -> data_format == 'array' ) {
            $data_label = $this -> data_label;
            if( is_array ( $this -> data_set ) ) {
                $ds = $this -> data_set;
                // print_r($ds);
                $id_name = $this -> id_name;
                $radio_value = $this -> radio_value;
                if(strlen($radio_value) > 0) {
                    $val_str=' value = "'.$radio_value.'" ';
                }
                print_r($ds);
                foreach ( $ds as $var => $val ) {
                    extract( $val );
                    if($this -> selected == $id) {
                        // test!!!! // really?!?
                        $checked = ' CHECKED ';
                    } else {
                        $checked = '';
                    }
                    echo "id name is $id_name <br>";
                    // $ret.= '<input '.$checked.' name = "'.$data_label.'" type="radio" '.$val_str.' id="'.$$id_name.'" /><label for="'.$$id_name.'">'.$$data_label.'</label>';
                    // you don't want duplicate ids if you use javascript
                    // and you don't even want it if you use html
                    //$ret.= '<input '.$checked.' name = "'.$id_name.'" type="radio" '.$val_str.' id="'.$id_name.'" /><label for="'.$id_name.'">'.$data_label.'</label>';
                // $ret.= "data_label is $data_label and id_name is $id_name and value is $radio_value <br>";
                    $ret.='<input '.$checked.' name = "'.$id_name.'" type= "radio" '.$val_str.' id="'.$id_name.'" /><label for="'.$id_name.'">'.$data_label.'</lable>';
                }
            }
        }
        return $ret;
    }


    function render_list () {
        while($row = $this->db_result -> fetch_assoc()) {
            // echo "we are dealing with ".$this->selected." for this one <br>";
            /* debug 
            echo '<pre>';
            print_r($row);
            echo '</pre>';
             */
            // extract($row);
            $val=$this->sel_value;
            $option=$this->sel_option;
            // debug // echo "val is $val and option is $option <br>"; echo "row[$option] is ".$row[$option].'<br>';
            /* you need to account for an option being an array of options for a more complex list */
            $ret.='<option value="'.$row[$val].'"';
            if($this->selected==$row[$val]) {
                $ret.= ' selected="selected" ';
            }
            $ret.= '>'.$row[$option].'</option>';
        }
        return $ret;
    }
}

class S_language {
    var $db;
    var $phrase_book;
    function get_phrases() {
        $query='SELECT phrase, LOWER(phrase_key) AS phrase_key from phrase WHERE lang="'.$_SESSION['lang'].'"';
        // debug // echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }

    function gp($phrase_key, $in_form=false) {
        // you seriously need to incorporate printf in this crud
        $phrase_key=strtolower(trim($phrase_key)); // just in case
        $phrase = $this -> phrase_book[$phrase_key]['phrase'];
        if(strlen($phrase) == 0) {
            $phrase=$phrase_key;
        }
        // why do I put spaces around this?!?
        if($this->edit_phrase == false) {
            if($in_form == false) {
                return '<span class="phrase">'.$phrase.'</span>'; // blarg this breaks for anything inside a form field
            } else {
                return $phrase;
            }
        } else {
            return '<input type="text" name="';
        }
    }

    function update_phrase($phrase_key, $phrase) {
        $query='UPDATE phrase
            SET phrase="'.$phrase.'"
            WHERE id='.$id;
        // debug // echo $query.'<br>';
        $result = $this->db->query($query);
        return $result;
    }
}

class S_class {
    var $db;
    var $class_id;
    /* manage classes */
    function get_class() {
        // wtf // $query='SELECT id, name, start, end FROM class'.$this->class_id;
        // dbl wtf in light of the above 
        $query='SELECT id, name, start, end FROM class WHERE id='.$this->class_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return 'dead jim';
        }
    }

    function class_by_date($date) {
        // wtf // $query='SELECT id, name, start, end FROM class'.$this->class_id;
        // dbl wtf in light of the above 
        $query='SELECT id, name, start, end FROM class WHERE start < "'.$date.'" AND end > "'.$date.'"';
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            $row = $result->fetch_assoc();
            return $row;
        } else {
            return false;
        }
    }

    function get_class_set ($ids, $order_by='') {
        // get a group of classes and order them by something
        foreach($ids as $id) {
            if($id*1 > 0) {
                $nids[]=$id;
            }
        }
        $id_str=implode(",", $nids);
        $query='SELECT id, name, start, end FROM class WHERE id in ('.$id_str.')';
        if(strlen($order_by > 0)) {
            $query.=' ORDER BY '.$order_by;
        }
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return 'dead jim';
        }
    }

    function get_all_classes($orderby='') {
        // wtf // $query='SELECT id, name, start, end FROM class'.$this->class_id;
        $query='SELECT id, name, start, end FROM class '.$orderby;
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }
    function get_all_locations() {
        $query='SELECT concat("location_", id) as location_id, id, "location" as name_name, location FROM location';
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function get_all_leaders($activity_level=1) {
        $clause='';
        if($activity_level*1 > 0) {
            $clause = ' WHERE activity_level = '.$activity_level;
        }
        $query='SELECT concat("leader_", id) as leader_id, id, concat(fname, " ", lname) as leader, email FROM leader'.$clause;;
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function S_class($class_id) {
        $this->set_class($class_id);
    }

    function set_class($class_id) {
        $this->class_id=$class_id;
    }

    function add_class_events($data) {
        // why the shit is this in class and not event?
        // I bet I called it add_class_events instead of add_events to try to feel 
        // less stupid about doing that
        // debug // print_r($data);
        extract($data);
        /* 
         * get the start date from the class id
         *
         */
        // echo "timestamp is $timestamp \n";
        $event_date = date('Y-m-d', $timestamp);
        $event_time = date('H:i:s', $timestamp);
        $num_day_of_week = date('w', $timestamp);
        // debug // echo "event date is $event_date and end_date is $end_date <br>";
        if($end == '') {
            // no end date. must be a group class
            // getting the end date from the class instead of any input
            $result=$this->get_class();
            $row = $result->fetch_assoc();
            $end_date=$row['end'];
            $start_date=$row['start'];
        } else {
            $start_date=$data['start'];
            $end_date=$data['end'];
            $event_date = $start;
        }
        // insert into the main events table
        $current_day_number=date('j', $timestamp);
        // debug // echo "current_day_number is now $current_day_number <br>";
        $this_year = date('Y', $timestamp);
        // deduecho "this_year is now $this_year <br>";
        $this_month = date('n', $timestamp);
        // echo "this_month is now $this_month <br>";
        $event_time= date('H:i', $timestamp);
        // echo "event_time is now $event_time <br>";
        $e_datetime=$event_date.' '.$event_time;
        // echo "e_datetime is $e_datetime <br>";
        // I'm glad I don't pull class_id from the object because I sometimes need a different class id
        echo "so everything should be ok here for the insert into event \n";
        $ins='INSERT INTO event(class_id, start, end, location_id, leader_id, number_participants, et_id, duration)
                VALUES ('.$class_id.', "'.$start_date.'", "'.$end_date.'", '.$location_id.', '.$leader_id.', '.$number_participants.', '.$et_id.', '.$duration.')';
        // debug // 
        echo $ins."\n"; echo "and that was the insert into event \n"; // die();
        $result = $this->db->query($ins);
        $event_id=$this->db->insert_id;
        echo "event id is $event_id \n";
        echo "working with $event_id <br>";
        while($event_date <= $end_date) {
            $toinsert = true;
            $inc_num=$this->increment_number($occurance_rate);
            if($inc_num == 1) {
                // daily class, assume it's a private
                $dsp_arr = config::config_option('default_selected_privates');
                $default_selected_privates = json_decode($dsp_arr['option_value']);
                print_r($default_selected_privates);
                echo "num_day_of_week is $num_day_of_week \n";
                if(!in_array($num_day_of_week, $default_selected_privates)) {
                    echo "toinsert is false\n";
                    $toinsert = false;
                }
            }
            // do all your date shit here
            // '9999-12-31 23:59:59' 
            $ta=explode('-', $event_date);
            $current_day_number=$ta[2];
            $this_year=$ta[0];
            $this_month=$ta[1];
            $e_datetime=$event_date.' '.$event_time;
            $ins='INSERT INTO event_daytime(event_id, daytime)
                VALUES ('.$event_id.', "'.$e_datetime.'")';
            if($toinsert === true) {
                // debug // 
                echo $ins."\n";
                $result = $this->db->query($ins);
            }
            $event_date      = date('Y-m-d', mktime(0,0,0,$this_month, $current_day_number+$inc_num, $this_year));
            $num_day_of_week = date('w', mktime(0,0,0,$this_month, $current_day_number+$inc_num, $this_year));
        }
        // full of confidence
        // misplaced it turns out
        return $event_id;
    }

    function test_func() {
        // test getting all config options from another class
        /*
        $config_stuff = config::my_config();
        return $config_stuff;
         */

        $val = config::config_option('default_selected_privates');
        return $val;
    }

    function increment_number($occurance_rate) {
        // maybe this needs to be in config
        // yeah like the new config table I just built
        $day_inc=array('daily'=>1,'weekly'=>7,'every other day'=>2);
        return $day_inc[$occurance_rate];
    }

    function class_week_dates($row, $days) {
        // take the data from a class and set up the class week from it
        // days usually comes from the config
        extract($row);
        $sa=explode('-', $start);
        // 2012-09-04
        $this_month=date('n', mktime(0,0,0,$sa[1], $sa[2], $sa[0]));
        $this_year=date('Y', mktime(0,0,0,$sa[1], $sa[2], $sa[0]));
        $today=date('w', mktime(0,0,0,$sa[1], $sa[2], $sa[0]));
        $dom=date('j', mktime(0,0,0,$sa[1], $sa[2], $sa[0]));
        $current_day_number = $dom-$today;
        while($m < $days) {
            $newdate= date('j', mktime(0,0,0,$this_month, $dom+$m, $this_year));
            $newday= date('w', mktime(0,0,0,$this_month, $dom+$m, $this_year));
            $week[$newday]=$newdate;
            // debug // echo $newdate .'<br>';
            $m++;
        }
        ksort($week);
        return $week;
    }

    function get_first_event_date_by_day($day) {
        $query= 'SELECT min(daytime) AS start_date, event_id, id, time_format(daytime, "%H") as start_hour, time_format(daytime, "%i") as start_minute, date_format(daytime, "%W") as start_date_name
            FROM event_daytime 
            WHERE daytime > (SELECT start FROM class WHERE id='.$this->class_id.') 
            AND date_format(daytime, "%W") = "'.$day.'"';
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function get_all_events_by_day($day)  {
        // consider making one of these that doesn't depend on class_id
        $query = 'SELECT event.*, event_daytime.*, 
            event_daytime.id as edt_id,
            DAYNAME(event_daytime.daytime) AS event_day,
            DATE_FORMAT(event_daytime.daytime, "%l:%i %p") as event_time
            FROM event, event_daytime 
            WHERE event_daytime.daytime like "'.$day.'%" 
            AND event_daytime.event_id=event.id and event.class_id in (0, '.$this->class_id.')
            ORDER BY event_daytime.daytime';
        // debug // echo $query; die();

        if ($result = $this->db->query($query)) {
            return $result;
        }
    }


    function get_all_events_by_day_with_class($day)  {
        // consider making one of these that doesn't depend on class_id
        $query = 'SELECT event.*, event_daytime.*, 
            event_daytime.id as edt_id,
            DAYNAME(event_daytime.daytime) AS event_day,
            DATE_FORMAT(event_daytime.daytime, "%l:%i %p") as event_time
            FROM event, event_daytime 
            WHERE event_daytime.daytime like "'.$day.'%" 
            AND event_daytime.event_id=event.id and event.class_id in (0, '.$this->class_id.')
            ORDER BY event_daytime.daytime';
        // debug // echo $query; die();

        if ($result = $this->db->query($query)) {
            return $result;
        }
    }
}

class S_event {
    /* deals with events and their ilk. */
    var $db;
    public $date_range; // range of dates used to populate a group of events. set up by s_class
    public $class_id;
    /* manage events */

    /*
     * keep
    function S_event($class_id, $location_id) {
        $this->set_class($class_id);
        $this->set_location($location_id);
    }
    */

    function get_events_by_class_id() {
        /* get all the events happening in a class. */
    }

    function sayClass() {
        /* test function */
        return $this->class_id;
    }

    function get_events_in_date_range($start_date, $days) {
        $query='select event_type.et_name, event_type.et_code, event_daytime.id as edt_id, event_daytime.daytime, location.location, leader.fname as fname, CONCAT (leader.fname, " ", leader.lname) as leader, event.id, event.duration 
            from event left join leader on event.leader_id=leader.id 
            left join event_daytime on event.id=event_daytime.event_id 
            left join location on event.location_id=location.id 
            left join event_type on event.et_id = event_type.id 
            where event_daytime.daytime between "'.$start_date.'" and date_add("'.$start_date.'", interval '.$days.' day)';
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo "arg!"; die();
        }
    }

    function get_surrounding_private_events($private_event_id, $private_event_daytime_id, $datetime) {
        // I can use this to color my events too
        $query = 'SELECT private_event_daytime.id, private_event_daytime.daytime as dt, private_event.duration AS dur
            FROM private_event_daytime, private_event
            WHERE private_event_daytime.daytime <= "'.$datetime.'"
            AND DATE_ADD(private_event_daytime.daytime, INTERVAL private_event.duration MINUTE) >="'.$datetime.'"
            AND private_event_daytime.private_event_id = private_event.id';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo "arg!"; die();
        }
    }

    function get_private_events_in_moment($private_event_id, $private_event_daytime_id, $datetime) {
        // not used but cool
        $query='SELECT private_event_daytime.id, private_event_daytime.daytime as dt, private_event.duration AS dur
            FROM private_event_daytime, private_event
            WHERE private_event_daytime.daytime = "'.$datetime.'"
            AND private_event_daytime.private_event_id = private_event.id';
        // debug // 
        echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo "arg!"; die();
        }
    }

    function same_time_events($event_daytime_id) {
        $query='SELECT a.* FROM event_daytime a
            LEFT JOIN event_daytime b ON (a.daytime=b.daytime)
            WHERE a.id='.$event_daytime_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $this->db->error;
            echo "arg!"; die();
        }
    }

    function get_events_in_moment($start_moment, $units, $increment) {
        $query='';
        // debug // 
        echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo "arg!"; die();
        }
    }

    function get_event($event_id) {
        $query='SELECT 
            event.id as event_id, event.start as start, event.end as end, event.et_id as et_id, event.class_id, event.location_id, event.leader_id, event.number_participants, event.et_id, event.duration, DAYNAME(min(event_daytime.daytime)) 
            AS event_day, 
            DATE_FORMAT(min(event_daytime.daytime), "%l:%i %p") as event_time
            FROM event LEFT JOIN event_daytime on event.id=event_daytime.event_id 
            WHERE event.id='.$event_id.' 
            GROUP BY event.id';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    function better_get_event($event_id) {
        

        $query='select event_type.et_name as et_name, event_type.et_code as et_code,
           DAYNAME(min(event_daytime.daytime)) 
            AS event_day, 
            DATE_FORMAT(min(event_daytime.daytime), "%l:%i %p") as event_time,
            event_daytime.daytime, event.class_id, event.location_id, event.leader_id, event.et_id, location.location, leader.fname, event.id, event.duration, event.number_participants
            from event left join leader on event.leader_id=leader.id 
            left join event_daytime on event.id=event_daytime.event_id 
            left join location on event.location_id=location.id 
            left join event_type on event.et_id = event_type.id 
            where event.id='.$event_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.'<br>';
            echo "arg!"; die();
        }
    }

    function get_event_by_type($et_id) {
        // get all the events of a certain type
        // this has apparently never worked.
        /*
        $query='SELECT 
            event.id as event_id, event.class_id, event.location_id, event.leader_id, event.number_participants, event.et_id, event.duration, DAYNAME(min(event_daytime.daytime)) 
            AS event_day, 
            DATE_FORMAT(min(event_daytime.daytime), "%l:%i %p") as event_time,
            min(event_daytime.daytime) AS day_order 
            FROM event LEFT JOIN event_daytime on event.id=event_daytime.event_id 
            WHERE event.et_id='.$et_id.' 
            AND event.class_id='.$this->class_id.' 
            ORDER BY event_day_order 
            GROUP BY event.id';
         */
        // the above left as a testament to madness
        $query='SELECT 
            event.id as event_id, event.class_id, event.location_id, event.leader_id, event.number_participants, event.et_id, event.duration, DAYNAME(min(event_daytime.daytime)) 
            AS event_day, 
            DATE_FORMAT(min(event_daytime.daytime), "%l:%i %p") as event_time,
            DATE_FORMAT(min(event_daytime.daytime), "%w") as event_sort,
            min(event_daytime.daytime) AS day_order 
            FROM event LEFT JOIN event_daytime on event.id=event_daytime.event_id 
            WHERE event.et_id='.$et_id.' 
            AND event.class_id='.$this->class_id.' 
            GROUP BY event.id
            ORDER BY event_sort, event_daytime.daytime
            ';


        // debug // echo $query .'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    function event_by_participant($participant_id) {
        $query = 'SELECT event_participant.id as event_participant_id, status_id, event_id FROM event_participant 
            LEFT JOIN event on event_participant.event_id = event.id
            WHERE event.class_id = '.$this->class_id.' 
            AND event_participant.participant_id='.$participant_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query;
            return false;
        }
    }

    function class_by_ep_id ($event_participant_id) {
        $query = 'SELECT class.*, event.* FROM event_participant, event, class
            WHERE event_participant.id='.$event_participant_id.'
            AND event_participant.event_id=event.id
            AND event.class_id = class.id';
        // debug // echo $query.'<br>';
            if ($result = $this->db->query($query)) {
                $arr=$result -> fetch_assoc();
                return($arr);
                /*
                extract($arr);
                $ret[$class_id]=$arr;
                return $ret;
                 */
        } else {
            echo $query;
            return false;
        }
    }

    function orphan_event_by_ep_id ($event_participant_id) {
        $query = 'SELECT event_participant.id as event_participant_id, status_id, event_id FROM event_participant 
            WHERE event_participant.id='.$event_participant_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query;
            return false;
        }
    }

    function event_by_ep_id($event_participant_id) {
        $query = 'SELECT event_participant.id as event_participant_id, status_id, event_id FROM event_participant 
            LEFT JOIN event on event_participant.event_id = event.id
            WHERE event.class_id = '.$this->class_id.' 
            AND event_participant.id='.$event_participant_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query;
            return false;
        }
    }

    function event_name_by_event_participant_id($event_participant_id) {
        // just get the damn name of an event
        $query='select event_type.et_name  as et_name from event_type LEFT JOIN event on event.et_id=event_type.id LEFT JOIN event_participant on event_participant.event_id = event.id where event_participant.id='.$event_participant_id;
        // 
        if($event_participant_id == 3819 || $event_participant_id == 4335) {
            echo $query;
            die();
        }
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            // echo $query;
            return false;
        }
    }

    function get_event_participant ($event_participant_id) {
        $query = 'SELECT * FROM event_participant WHERE id='.$event_participant_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
        // get event data based on event_participant_id
    }

    function event_by_location ($location_id) {
        $query = 'SELECT * FROM event WHERE class_id = '.$this->class_id.' AND location_id = '.$location_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    function delete_event() {
        $query='delete from event where id='.$this->event_id;
        $result = $this->db->query($query);
        $query='delete from event_daytime where event_id='.$this->event_id;
        $result = $this->db->query($query);
        $query='delete from event_participant where event_id='.$this->event_id;
        // you need to delete from event_participant_billing and its line items as well
	// get all event_participant_ids from event_participant based on this-> event_id
	// look on line 2026: s_participant -> participants_in_event($event_id).
	// it gets you the event_participant_ids. You can delete from event_participant_billing based on those.
	// then build a delete with an in() to make it a little faster.
        // you need to learn foreign keys buddy
        $result = $this->db->query($query);
        return $result;
    }

    function edit_event($data) {
        extract($data);
        $query='UPDATE event set 
            class_id='.$class_id.', 
            location_id='.$location_id.',
            leader_id='.$leader_id.',
            et_id='.$et_id.',
            duration='.$duration.',
            number_participants='.$number_participants.'
            WHERE id='.$this->event_id;
        // debug // 
        echo $query.'<br>';

        $result = $this->db->query($query);
        return $result;
    }

    function insert_event ($e_name, $class_id) {
        /* insert an event */
        /* you need the first date of the event
         * and you need how often the event repeats (weekly right now)
         * then you need some maths to set up the dates that you insert into
         * event. */
        /* for some insane reason I put this in s_class */
        echo "$e_name : $class_id \n";
    }

    function set_location($location_id) {
        $this->location_id=$location_id;
    }

    function get_all_event_types($et_activity_level='') {
        if(strlen($et_activity_level) > 0) {
            $clause = ' WHERE et_activity_level = '.$et_activity_level.' ';
        } else {
            $clause = ' WHERE et_activity_level > 0 ';
        }
        $query='SELECT id, et_code, et_activity_level, if(et_desc !="", concat(et_name, " (", et_desc, ")"), et_name) as event from event_type '.$clause.' order by et_name';
        // debug // echo $query."<br>";
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;

    }

    function get_event_type_by_id($id) {
        $query='SELECT * FROM event_type WHERE id='.$id;
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }

    function update_participant_status_hard_way ($event_id, $participant_id, $status_id) {
        $query='UPDATE event_participant
            SET status_id='.$status_id.'
            WHERE participant_id='.$participant_id.' 
            AND event_id='.$event_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.'<br>';
            echo "arg!"; die();
        }
    }

    function update_participant_status ($event_participant_id, $status_id) {
        $query='UPDATE event_participant
            SET status_id='.$status_id.'
            WHERE id='.$event_participant_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.'<br>';
            echo "arg!"; die();
        }
    }

    function get_event_daytime($event_daytime_id) {
        // get one day's instance of an event based on its unique id
        $query='SELECT * FROM event_daytime WHERE id='.$event_daytime_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }

    function update_event_daytime_meta ($edt_id, $edt_meta) {
        $edt_meta = $this->db->real_escape_string($edt_meta);
        $query='UPDATE event_daytime
            SET edt_meta="'.$edt_meta.'"
            WHERE id='.$edt_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.'<br>';
            echo "arg!"; die();
        }
    }

    function get_event_by_et_id ($et_id) {
        $query = 'SELECT event.* FROM event, event_daytime
            WHERE event_daytime.id = '.$et_id.'
            AND event_daytime.event_id = event.id';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.'<br>';
            echo "arg!"; die();
        }
    }

    function get_events_at_time ($daytime) {
        $query = 'SELECT *
           FROM event_daytime 
           WHERE daytime="'.$daytime.'"
           ORDER BY event_id';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }
}

class S_private_event {
    /* why extend event? It really has no relation */
    /* yeah but it would be how its supposed to be done.
     * you just don't understand why yet */

    function get_unique_current_events() {
        // get any event that hasn't expired yet and send along a unique field based on start and end
        $query='SELECT concat(DATE_FORMAT(start, "%Y-%m-%d"), ":",DATE_FORMAT(end,"%Y-%m-%d")) as start_end, DATE_FORMAT(start, "%M %d %Y") as start, DATE_FORMAT(end, "%M %d %Y") AS end FROM private_event WHERE end >= now()';
        // this one gets past events too
        // $query='SELECT concat(DATE_FORMAT(start, "%Y-%m-%d"), ":",DATE_FORMAT(end,"%Y-%m-%d")) as start_end, DATE_FORMAT(start, "%M %d %Y") as start, DATE_FORMAT(end, "%M %d %Y") AS end FROM private_event';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function get_private_event($private_event_id) {
        $query='SELECT *, DATE_FORMAT(start, "%H:%i:%s") as pe_start FROM private_event WHERE id='.$private_event_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.'<br>';
            echo "error!"; die();
        }
    }

    function get_private_event_from_daytime($private_event_daytime_id) {
        // get all of a private event's info from one daytime.
        // that totally won't make sense later
    }

    function private_event_by_participant ($participant_id, $end_date='') {
        // get private event by participant. I stuck that end date in 
        // there in case a participant is in more than one private class
        // and you wanna try to limit which one. which is starting to outline
        // a limitation to what I'm trying to do: namely I can't call 
        // specific private events since they aren't attached to any class
    }

    function private_event_by_id ($id_type, $id) {
        // search for private events based on location or leader,
    }

    function delete_private_event ($private_event_id) {
        $query = 'DELETE FROM private_event WHERE id='.$private_event_id;
        // debug // echo $query.'<br>';
        $result = $this->db->query($query);
        if(is_object($result)) {
            $query = 'DELETE FROM private_event_daytime WHERE private_event_id='.$private_event_id;
            // debug // echo $query.'<br>';
            $result = $this->db->query($query);
        }
        return $result;
    }

    function delete_private_event_daytime ($private_event_daytime_id) {
        $query = 'DELETE FROM private_event_daytime WHERE id='.$private_event_daytime_id;
        // debug // echo $query.'<br>';
        $result = $this->db->query($query);
    }

    function update_private_event_daytime_meta ($ped_id, $ped_meta) {
        $ped_meta=$this->db->real_escape_string($ped_meta);
        $query = 'UPDATE private_event_daytime SET ped_meta="'.$ped_meta.'"
            WHERE id='.$ped_id;
        // debug // echo $query."<br>";
        $result = $this->db->query($query);
        return $result;
    }

    function update_private_event ($data, $full=false) {
        // this just updates private_event, not private_event_daytime
        // and it is apparently unused
        extract($data);
        // $this->delete_private_event($private_event_id);
        if($full == true) {
            $query = 'UPDATE private_event
                SET start="'.$start.'",
                end="'.$end.'"
                duration='.$duration.', 
                location_id='.$location_id.',
                leader_id='.$leader_id.',
                participant_id='.$participant_id.' 
                WHERE id='.$private_event_id;
        } else {
            $query = 'UPDATE private_event
                SET 
                duration='.$duration.', 
                location_id='.$location_id.',
                leader_id='.$leader_id.',
                participant_id='.$participant_id.' 
                WHERE id='.$private_event_id;
        }
        $result = $this->db->query($query);
    }

    function edit_private_event_daytime($data) {
    }

    function add_private_event ($start, $end, $selected_days, $private_event_time, $duration, $location_id, $leader_id, $participant_id, $ped_meta, $status_id=1) {
        // I am calling wtf on this. according to this function I never included a time in my private event insert and that is not true because I SEE the times in it from previous inserts. It must have been lost when I copied. I hate git.
        $ped_meta=$this->db->real_escape_string($ped_meta);
        $query ='INSERT INTO private_event 
            (start, end, duration, location_id, leader_id, participant_id, status_id)
            VALUES ("'.$start.' '.$private_event_time.'", "'.$end.' '.$private_event_time.'", '.$duration.', '.$location_id.', '.$leader_id.', '.$participant_id.', '.$status_id.')';
        echo $query."<br>";
        $result = $this->db->query($query);
        /*
        echo "result is \n";
        print_r($result);
         */
        if($result == true) {
            $private_id=$this->db->insert_id;
        } else {
            $private_id=false;
        }
        $ret['private_id']=$private_id;
        // now we do the private_event_daytime
        $start_ds=strtotime($start.' '.$private_event_time);
        $end_ds=strtotime($end.' '.$private_event_time);
        /*
        echo "start_ds is $start_ds and end_ds is $end_ds <br>";
        echo $end_ds-$start_ds.'<br>';
         */
        $i=0;
        $inc_num=1;
        $one_single_day=86400;
        while($start_ds <= $end_ds) {
            /*
            echo '<br><br>';
            echo "start_ds:<br>$start_ds and end_ds is <br>$end_ds ";
            echo "and the next one will be <br>";
            echo $start_ds+$one_single_day;
            */
            // do all your date stuff here
            // '9999-12-31 23:59:59' 
            $day_number=date('w', $start_ds);
            // echo "day_number is $day_number <br>";
            if(in_array($day_number, $selected_days)) {
                $ins_date=date('Y-m-d H:i', $start_ds);
                $debug_date=date('l', $start_ds);
                // echo "inserting a $debug_date <br>";
                $ped_id=$this -> insert_private_event_daytime ($ins_date, $private_id, $participant_id, $ped_meta);
            }
            $start_ds=$start_ds+$one_single_day;
        }
    }

    function insert_private_event_daytime($daytime, $event_id, $participant_id, $ped_meta='') {
        // I'm not inserting ped_meta here.
        $query = 'INSERT INTO private_event_daytime 
            (daytime, private_event_id, participant_id, ped_meta) VALUES 
            ("'.$daytime.'", '.$event_id.', '.$participant_id.', "'.$ped_meta.'")';
        // debug // echo "&nbsp; &nbsp; &nbsp;". $query.'<br>';
        $result = $this->db->query($query);
        if($result == true) {
            $ped_id=$this->db->insert_id;
        } else {
            $ped_id=false;
        }
        return $ped_id;
    }

    function get_private_events_in_date_range ($start, $end) {
        // this is fast becoming a hilarious favorite function of mine
        // It only gets blocks of events with a solid start and a solid end.
        // if you want weekly classes you need a different table 
        $query='SELECT 
            private_event.start AS start, 
            private_event.participant_id AS participant_id, 
            DATE_FORMAT(private_event.start, "%h:%i") AS start_time, 
            private_event.id AS private_event_id, 
            location.location, 
            leader.fname, 
            CONCAT(participant.fname, " ", participant.lname) AS participant_fullname,
            participant.dob AS participant_dob,
            period_diff(DATE_FORMAT(now(), "%Y%m"), DATE_FORMAT(participant.dob, "%Y%m") ) as participant_age_months,
            DATEDIFF("'.$end.'", "'.$start.'") as num_days,
            private_event.duration 
            FROM private_event 
            LEFT JOIN leader ON private_event.leader_id=leader.id 
            LEFT JOIN location ON private_event.location_id=location.id 
            LEFT JOIN participant ON private_event.participant_id=participant.id 
            WHERE DATE_FORMAT (private_event.start, "%Y-%m-%d") ="'.$start.'" 
            AND DATE_FORMAT(private_event.end, "%Y-%m-%d") = "'.$end.'" ORDER BY start';
        // wow past-troy you were a crazy bastard
        // debug // echo '<pre>'.$query.'</pre>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo "arg!<br>"; 
            echo $query;die();
        }
    }

    function same_time_private_events($private_event_daytime_id) {
        $query='
            SELECT a.* FROM private_event_daytime a
            LEFT JOIN private_event_daytime b ON (a.daytime=b.daytime)
            WHERE a.id='.$private_event_daytime_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $this->db->error;
            echo "arg!"; die();
        }
    }

    function get_private_event_daytime ($private_event_daytime_id) {
        $query = 'SELECT * FROM private_event_daytime
            WHERE id='.$private_event_daytime_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo "arg!<br>"; 
            echo $query;die();
        }
    }

    function ped_by_pe ($private_event_id, $daytime) {
        // private event daytime by private event id and the time it happend
        $query = 'SELECT * 
            FROM private_event_daytime
            WHERE private_event_id='.$private_event_id.'
            AND daytime="'.$daytime.'"';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo "arg!<br>"; 
            echo $query;die();
        }
    }

    function get_min_max_private_events($private_event_daytime_id) {
        $query = 'SELECT min(daytime) AS pe_start, max(daytime) AS pe_end 
            FROM private_event_daytime
            WHERE id='.$private_event_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo "arg!<br>"; 
            echo $query;die();
        }
    }

    function get_private_event_daytime_age($private_event_daytime_id) {
        // not used
        $query = 'SELECT DATEDIFF(private_event_daytime.daytime, private_event.start) 
            AS days_different 
            FROM private_event_daytime, private_event 
            WHERE private_event_daytime.id='.$private_event_daytime_id.'
            AND private_event_daytime.private_event_id = private_event.id';
        if ($result = $this->db->query($query)) {
            $arr=$result -> fetch_assoc();
            return $arr['days_different'];
        } else {
            echo "arg!<br>"; 
            echo $query;die();
        }
    }
    function get_private_events_from_start ($start_date, $days) {
        // num_days was broken in the sql because it relied on a start and end 
        // for it to work. I just changed it to represent the number of days
        // passed to the function
        $query='select private_event_daytime.daytime, 
            private_event_daytime.id as ped_id, 
            private_event_daytime.id as private_event_daytime_id, 
            private_event_daytime.ped_meta as ped_meta, 
            location.location, 
            location.id as location_id, 
            leader.fname as fname, 
            CONCAT (leader.fname, " ", leader.lname) as leader,
            leader.id as leader_id, 
            private_event.id,
            DATEDIFF(private_event_daytime.daytime, private_event.start) AS days_different, 
            concat(participant.fname, " ", participant.lname) as participant_fullname,
            participant.dob AS participant_dob,
            participant.id AS participant_id,
            period_diff(DATE_FORMAT(now(), "%Y%m"), DATE_FORMAT(participant.dob, "%Y%m") ) as participant_age_months,
            "'.$days.'" as num_days,
            private_event.duration 
            from private_event 
            left join leader on private_event.leader_id=leader.id 
            left join private_event_daytime on private_event.id=private_event_daytime.private_event_id 
            left join location on private_event.location_id=location.id 
            left join participant on private_event.participant_id=participant.id 
            where private_event_daytime.daytime between "'.$start_date.'" and date_add("'.$start_date.'", interval '.$days.' day)';
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo "arg!<br>"; 
            echo $query;die();
        }
    }

    function get_private_events_at_time ($daytime) {
        $query = 'SELECT *
           FROM private_event_daytime 
           WHERE daytime="'.$daytime.'"
           ORDER by private_event_id';
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }
}

class S_login {
    /* manage logins */
    var $log_level;
    var $db;
    var $db_logout_params;
    var $perm_true=array();

    function get_all_login_levels($orderby='') {
        if(strlen($orderby) > 0) {
            $orderby=' ORDER BY '.$orderby;
        }
        $query='SELECT * from login_level '.$orderby;
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function get_all_logins($orderby='') {
        if(strlen($orderby) > 0) {
            $orderby=' ORDER BY '.$orderby;
        }
        $query='SELECT * from login'.$orderby;
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function test_for_login($fname, $lname, $email) {
        $query='SELECT id FROM login WHERE email="'.$email.'" AND fname="'.$fname.'" AND lname="'.$lname.'"';
        // debug // echo $query."\n";
        if ($result = $this->db->query($query)) {
            $login_arr=$result -> fetch_assoc();
            if(is_array($login_arr)) {
                extract($login_arr);
                return $id;
            }
        }
        return false;
    }

    function get_login_from_id($id) {
        $query='SELECT * FROM login WHERE id='.$id;
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function get_login_from_email($email) {
        $query='SELECT * FROM login WHERE email="'.$email.'"';
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function get_login($email, $password) {
        $query='SELECT * FROM login WHERE LOWER(email) = LOWER("'.$email.'") AND password="'.$password.'"';
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            if($result -> num_rows > 0) {
                return $result;
            }
        }
        return false;
    }

    function encrypt_password($password) {
        return md5($password);
    }

    function log_me_in($email, $password) {
        $login = $this -> get_login($email,$password);
        if($login != false) {
            $login_arr=$login -> fetch_assoc();
            $r['login_id']  = $login_arr['id'];
            $r['log_level'] = $login_arr['log_level'];
            // $r['login_hash'] = $login_arr['login_hash'];
            $r['fname'] = $login_arr['fname'];
            $r['lname'] = $login_arr['lname'];
            // valid email / password
            $login_hash = $this -> make_hash();
            $result = $this -> update_session_hash($r['login_id'], $login_hash);
            $r['login_hash'] = $login_hash;
            return $r;
        }
        return false;
    }

    function update_session_hash ($login_id, $login_hash) {
        $query = 'update login SET login_hash = "'.$login_hash.'",
            login_session = "'.session_id().'"
            WHERE id = '.$login_id;
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }

    function make_hash() {
        $ua=str_replace(' ', '', strtolower($_SERVER['HTTP_USER_AGENT']));
        $ri=$_SERVER['REMOTE_ADDR'];
        $rand = bin2hex(openssl_random_pseudo_bytes (rand(5,20)));
        $hash=$ua.$rand.$ri;
        return substr($hash,0, 255);
    }

    function make_pass($length=8) {
        // generate a random password
        // all lower case for now
        // avoid letters that look like numbers and vise versa
        // like o, 0, 1 and that.
        $pool['v']='aeiuy';
        $pool['c']='bcdfghjklmnpqrstvwxz';
        // add numbers some day
        // $pool['n']='23456789';
        $max_length['v']=strlen($pool['v']);
        $max_length['c']=strlen($pool['c']);
        $i=0;
        $lp='c';
        $password='';
        while($i < $length) {
            $max_length = strlen($pool[$lp]);
            $char = substr($pool[$lp], mt_rand(0, $max_length-1), 1);
            $password .= $char;
            $i++;
            if($lp=='c') {
                $lp='v';
            } else {
                $lp='c';
            }
        }
        // debug // echo "returning /$password/ \n";
        return $password;
    }
    function login_by_session($login_session, $login_hash) {
        // $login_hash = $this -> make_hash();
        // echo "has is $login_hash <br>";
        // echo "you are doing the login \n";
        $query = 'SELECT * FROM login WHERE login_session = "'.session_id().'" AND login_hash="'.$login_hash.'" AND last_log > NOW() - INTERVAL '.$this -> db_logout_params;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }

    function update_last_log() {
        // $query = 'UPDATE login SET last_log = NOW() WHERE login_session = "'.session_id().'" AND login_hash="'.$this -> make_hash().'"';
        $query = 'UPDATE login SET last_log = NOW() WHERE login_session = "'.session_id().'" AND login_hash="'.$_SESSION['login_hash'].'"';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            // echo "no result updating log";
            echo "update failed: ".$query;
            return false;
        }
        // echo "above the end of update_last_log <br>";
        return true;
    }

    function update_password($email, $password) {
        // this is for people who update their own passwords
        // you could just update the password by id if you check it somewhere else you know
        $password = $this -> encrypt($password);
        $login_hash = $this -> make_hash();
        $query='UPDATE login SET password="'.$password.'" WHERE email="'.$email.'" AND login_hash = "'.$login_hash.'"';
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function new_password($email, $login_id) {
        // create a new password and insert it into the database based on login id
        $password = $this -> make_pass();
        $en_pass = $this -> encrypt_password($password);
        $query='UPDATE login SET password="'.$en_pass.'" WHERE id='.$login_id;
        // debug // echo $query .'<br>';
        if ($result = $this->db->query($query)) {
            return $password;
        }
        return false;
    }

    function insert_login ($fname, $lname, $email, $password, $log_level=1) {
        $query = 'INSERT INTO login (fname, lname, email, password, log_level) 
            VALUES ("'.$fname.'", "'.$lname.'", "'.$email.'", "'.$password.'", '.$log_level.')';
        // debug // echo $query; echo "<br>";
        $result = $this->db->query($query);
        if($result == true) {
            $login_id=$this->db->insert_id;
        } else {
            $login_id=false;
        }
        return $login_id;
    }

    function update_login ($field_name, $field_val, $login_id) {
        $allowed_fields=array('fname','lname','email','password','log_level');
        if(in_array($field_name, $allowed_fields)) {
            if($field_name == 'password') {
                $field_val = $this -> encrypt_password($field_val);
            }
            $query = 'UPDATE login set '.$field_name.'="'.$field_val.'" WHERE id = '.$login_id;
            $result = $this->db->query($query);
            if($result == true) {
                return true;
            }
            return false;
        } else {
            return false;
        }
    }

    function send_password($login_data) {
        extract($login_data);
        $from_email='admin@infantaquatics.com';
        $headers = "From: $from_email";
        $headers .= "\nBcc: troy@troyvit.com\n";
        $subject = "Your new password for Infant Aquatics";
        $to=$email;
        $body="$fname,

Thanks for contacting us. Here is the password for your email address:

$password

If you have any problems please contact us at $from_email.

Thanks,

Infant Aquatics";
        mail($to, $subject, $body, $headers, '-f'.$from_email);
        // mail('troy@troyvit.com', $subject, $body, $headers, '-f'.$from_email);
    }
}

class S_login_reg extends S_login {
    var $db;
    function insert_login_address ($data) {
        extract($data);
        if($is_primary=='') {
            $is_primary=0;
        }
        $query = 'INSERT INTO login_address (login_id, address_type_id, is_primary, fname, lname, address_1, address_2, city, state, zip, country, phone_h, phone_c, phone_w, email) VALUES (
'.$login_id.', '.$address_type_id.', '.$is_primary.', "'.$fname.'", "'.$lname.'", "'.$address_1.'", "'.$address_2.'", "'.$city.'", "'.$state.'", "'.$zip.'", "'.$country.'", "'.$phone_h.'", "'.$phone_c.'", "'.$phone_w.'", "'.$email.'")';
echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    
    }

    function update_reg_login_item ($login_id, $reg_id, $field_name, $field_val) {
        // this should be using the id that belongs to reg_login
        $query='UPDATE reg_login SET '.$field_name.' = "'.$field_val.'"
            WHERE login_id='.$login_id.' AND reg_id='.$reg_id;
        // debug // echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function update_login_address_item($login_id, $address_type_id, $field_name, $field_val ) {
        // also used for inserts where it's just a single column
        if($field_val=='is_primary' || $field_val=='reg_status') {
            // we are adding an integer
            // echo "$field_val is clearly a number \n<br>";
            $field_val=$field_val;
        } else {
            // echo "we are escaping $field_val <br>\n";
            $field_val='"'.$this->db->real_escape_string($field_val).'"';
            // echo "and now is $field_val <br>\n";
        }
        $query='UPDATE login_address SET '.$field_name.' = '.$field_val.' WHERE login_id='.$login_id.' AND address_type_id='.$address_type_id;
        // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    function insert_login_address_item($login_id, $address_type_id, $field_name, $field_value) {
        if($field_value*1 > 0) {
            // we are adding an integer
            $field_value=$field_value;
        } else {
            $field_value='"'.$this->db->real_escape_string($field_value).'"';
        }
        $query='INSERT INTO login_address(login_id, address_type_id, '.$field_name.') VALUES ('.$login_id.', '.$address_type_id.', '.$field_value.')';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function login_address_types () {
        $query = 'SELECT * FROM login_address_type';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function address_by_login_type ($login_id, $address_type_id) {
        $query = 'SELECT * FROM login_address
            WHERE login_id='.$login_id.' 
            AND address_type_id='.$address_type_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function reg_login_insert($login_id, $reg_id) {
        $query = 'INSERT INTO reg_login (login_id, reg_id) VALUES ('.$login_id.', '.$reg_id.')';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function reg_login_status_update ($reg_login_id, $reg_status) {
        $query = 'UPDATE reg_login SET reg_status='.$reg_status.' 
            WHERE id = '.$reg_login_id;
        // debug // echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function get_all_reg_logins() {
        $query = 'SELECT * FROM reg_login';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function get_reg_login ($login_id, $reg_id) {
        // see if a registration exists for a login
        $query = 'SELECT * FROM reg_login WHERE login_id='.$login_id.' 
            AND reg_id= '.$reg_id;
        // debug // echo $query."<br>";
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function get_l_reg_answer($login_id, $question_id) {
        $query='SELECT * FROM l_reg_answer WHERE login_id='.$login_id.' AND question_id='.$question_id;
        // debug  // echo '<br>'.$query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } 
        return false;
    }

    function update_l_reg_answer($id, $answer) {
        $query='UPDATE l_reg_answer SET answer="'.$answer.'" 
            WHERE id='.$id;
        // debug  // echo '<br>'.$query.'<br>';
        if($result = $this->db->query($query)) {
            return true;
        }
        return false;
    }

    function insert_l_reg_answer($login_id, $question_id, $answer) {
        $query='INSERT INTO l_reg_answer(login_id, question_id, answer)
            VALUES ('.$login_id.', '.$question_id.', "'.$answer.'")';
        // debug  // echo '<br>'.$query.'<br>';
        if ($result = $this->db->query($query)) {
            $id=$this->db->insert_id;
            return $id;
        }
        return false;
    }

    function delete_l_reg_answer($login_id, $question_id) {
        $query='DELETE FROM l_reg_answer WHERE login_id='.$login_id.' AND question_id='.$question_id;
        // debug  // echo '<br>'.$query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } 
        return false;
    }

}

class S_reg {
    var $db;

    function get_reg($id) {
        $query='SELECT * FROM reg WHERE id='.$id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    function get_registration_document ( $document_type_id ) {
        $query='SELECT * FROM registration_document WHERE document_type_id='.$document_type_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } 
        return false;
    }

    function sections_by_reg($reg_id, $section_type) {
        $query='SELECT * FROM reg_section WHERE reg_id='.$reg_id.' AND section_type="'.$section_type.'"
            ORDER BY order_id';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    function sections_by_ids($ids) {
        // grab sections based on a comma-sep list of ids
        // long story
        $query='SELECT * FROM reg_section WHERE id in ('.$ids.') 
            ORDER BY order_id';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.'<br>';
            return false;
        }
    }

    function questions_by_section ($section_id) {
        $query='SELECT * FROM reg_question WHERE section_id='.$section_id.'
            ORDER BY order_id';
        // debug // echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    function get_answer_group($answer_group_id, $question_name, $question_id) {
        // new contestent in my "most shittily named table contest"
        /* sorry. I have to get stupider 
        $query='SELECT id, "'.$question_name.'['.$question_id.']" 
            AS question_name, concat("'.$question_name.'", "_", id) as field_id, answer_group_id, answer_type, answer 
            FROM reg_question_preload WHERE answer_group_id='.$answer_group_id;
         */
        $query='SELECT id, "'.$question_name.'|'.$question_id.'" 
            AS question_name, concat("'.$question_name.'", "_", id) as field_id, answer_group_id, answer_type, answer 
            FROM reg_question_preload WHERE answer_group_id='.$answer_group_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } 
        return false;
    }

    function get_preload_data ($answer_group_id) {
        // I have created a logic bug
        // thanks for that note past-Troy. Next time add a little more detail.
        $query = 'SELECT id, answer_type FROM reg_question_preload WHERE answer_group_id='.$answer_group_id.' LIMIT 1';
        if ($result = $this->db->query($query)) {
            return $result;
            /*
            $arr = $result -> fetch_assoc();
            return $arr['answer_type'];
             */
        }
        return false;
    }

}

class S_section_group {
    var $db;
    function get_participant_section_group ($participant_id, $reg_login_id, $section_group_id) {
        $query = 'SELECT * FROM participant_section_group
            WHERE participant_id='.$participant_id.' 
            AND reg_login_id='.$reg_login_id.'
        AND section_group_id='.$section_group_id;
        // debug  // echo '<br>'.$query.'<br>';
        if ($result = $this->db->query($query)) {
            $arr = $result -> fetch_assoc();
            $id = $arr['id'];
            return $id; // I know I know but i'm in a hurry. Sorry Future Troy
        }
        return false;
    }

    function reset_participant_section_groups($participant_id) {
        // set all participant_section_groups to 0 for a participant in prep for adding a new participant_section_group
        $query='UPDATE participant_section_group SET active_section=0 WHERE participant_id='.$participant_id;
    //     echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
        return false;
    }

    function activate_participant_section_group ($participant_id, $section_group_id) {
        // turn on a section_group for a participant
        $query='UPDATE participant_section_group SET active_section=1 WHERE participant_id='.$participant_id.'
            AND section_group_id='.$section_group_id;
    //     echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
        return false;
    }

    function update_participant_section_group($participant_id, $section_group_id, $reg_login_id, $active_section=1) {
        // update a participant_section_group
        $success=$this -> reset_participant_section_groups($participant_id);
        if($success !==false) {
            // see if the group exists
            $id = $this -> get_participant_section_group ($participant_id, $reg_login_id, $section_group_id);
            if($id == false ) {
                echo "id is false so we are inserting  using $participant_id and $reg_login_id and $section_group_id<br>";
                // insert the new section group
                $id = $this -> insert_participant_section_group($participant_id, $reg_login_id, $section_group_id);
                return $id;
            } else {
                echo "id is true so we are updating <br>";
                // activate the new section group
                $this->activate_participant_section_group ($participant_id, $section_group_id);
                // get the id
                $id_res = $this -> p_section_group_by_sg_id($participant_id, $section_group_id);
                $id_arr  = $id_res ->fetch_assoc();
                $id=$id_arr['id'];
                return $id; 
            }
        } else {
            echo "resetting failed<br>";
            return false;
        }
    }

    function get_participant_sections ($participant_id, $reg_login_id, $active_section=1) {
        // ok I should go from participant_section_group, through section_group_member to reg_section. CAN HE DO IT? HE CAN DO IT.
        // NO HE CAN'T. but I can go 2.
        // so I am going to go from participant_section_group to section_group_member.
        $query = 'SELECT section_group_member.*, participant_section_group.*
            FROM section_group_member 
            LEFT JOIN participant_section_group
            ON section_group_member.section_group_id=participant_section_group.section_group_id
            WHERE participant_section_group.participant_id='.$participant_id.' 
            AND participant_section_group.reg_login_id='.$reg_login_id.'
            AND participant_section_group.active_section='.$active_section;
        // debug  // echo '<br>'.$query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } 
        return false;
    }

    function get_all_section_groups() {
        $query='SELECT * from section_group';
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function insert_participant_section_group($participant_id, $reg_login_id, $section_group_id) {
        $query='INSERT INTO participant_section_group(participant_id, reg_login_id, section_group_id)
            VALUES ('.$participant_id.', '.$reg_login_id.', '.$section_group_id.')';
        if ($result = $this->db->query($query)) {
            $id=$this->db->insert_id;
            return $id;
        }
        return false;
    }

    function participant_section_group_by_id($participant_section_group_id) {
        $query='SELECT * FROM participant_section_group
            WHERE id='.$participant_section_group_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }

    function p_section_group_by_sg_id($participant_id, $section_group_id) {
        // this gets a participant's section group not by the key but by the section group id
        $query='SELECT * FROM participant_section_group
            WHERE participant_id='.$participant_id.'
            AND section_group_id='.$section_group_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }
}

class S_participant_reg extends S_login_reg {
    var $db;
    function get_p_reg_answer($participant_id, $question_id) {
        $query='SELECT * FROM p_reg_answer WHERE participant_id='.$participant_id.' AND question_id='.$question_id;
        // debug  // echo '<br>'.$query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } 
        return false;
    }

    function update_p_reg_answer($id, $answer) {
        $query='UPDATE p_reg_answer SET answer="'.$answer.'" 
            WHERE id='.$id;
        // debug  // echo '<br>'.$query.'<br>';
        if($result = $this->db->query($query)) {
            return true;
        }
        return false;
    }

    function insert_p_reg_answer($participant_id, $question_id, $answer) {
        $query='INSERT INTO p_reg_answer(participant_id, question_id, answer)
            VALUES ('.$participant_id.', '.$question_id.', "'.$answer.'")';
        // debug  // echo '<br>'.$query.'<br>';
        if ($result = $this->db->query($query)) {
            $id=$this->db->insert_id;
            return $id;
        }
        return false;
    }

    function delete_participant_answers($participant_id) {
        $query='DELETE FROM p_reg_answer WHERE participant_id='.$participant_id;
        if ($result = $this->db->query($query)) {
            $id=$this->db->insert_id;
            return $id;
        }
        return false;
    }

    function participant_waiver_by_id ($participant_waiver_id) {
        $query = 'SELECT * FROM participant_waiver 
            WHERE id = '.$participant_waiver_id;
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }
    function all_participant_waiver ($participant_id) {
        // get all the waivers belonging to a participant
        $query='SELECT p.id AS participant_waiver_id,
		p.amount_due AS amount_due,
		p.signed_name,
		w.status AS waiver_status,
		p.waiver_status AS ws_id,
		p.id,
		p.waiver,
		p.signature,
		p.reg_id as reg_id,
		p.participant_section_group_id as participant_section_group_id,
		DATE_FORMAT(p.signature_date, "%Y-%m-%d") AS signature_date 
        FROM participant_waiver p, participant_waiver_status w 
        WHERE p.participant_id='.$participant_id.' AND p.waiver_status = w.id';
        // debug // echo '<pre>'.$query.'</pre>';
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }

    function waiver_by_participant ($participant_id, $reg_id) {
        // get any participant waivers on file for this registration
        $query='SELECT id, waiver_status, amount_due, waiver, signature, signed_name, DATE_FORMAT(signature_date, "%Y-%m-%d") AS signature_date FROM participant_waiver WHERE participant_id='.$participant_id.'
            AND reg_id='.$reg_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }

    function delete_waiver_by_participant ($participant_id) {
        $query='DELETE FROM participant_waiver WHERE participant_id='.$participant_id;
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }

    function delete_waiver ($waiver_id) {
        $query='DELETE FROM participant_waiver WHERE id='.$id;
        if ($result = $this->db->query($query)) {
            return $result;
        }
        return false;
    }

    function insert_participant_waiver_item($participant_id, $reg_id, $field_name, $field_val) {
        // what a weird function and just the one I need
        // terrible name tho 

        $query='INSERT INTO participant_waiver(participant_id, reg_id, '.$field_name.') VALUES ('.$participant_id.', '.$reg_id.', "'.$field_val.'")';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function mark_waiver_begun ($waiver_id) {
        // debug // echo "we are updating participant_waiver <br>";
        $ret = $this->update_participant_waiver_item($waiver_id, 'waiver_status', 3);
    }

    function update_participant_waiver_item ($waiver_id, $field_name, $field_val) {
        $query='UPDATE participant_waiver SET '.$field_name.' = "'.$field_val.'" WHERE id='.$waiver_id;
        if($field_name == 'signature_date') {
            // ya could just make it a timestamp you know
            $query='UPDATE participant_waiver SET '.$field_name.' = '.$field_val.' WHERE id='.$waiver_id;
        }
        // debug // echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function change_participant_waiver_status ($participant_id, $reg_id, $waiver_status) {
        $query = 'UPDATE participant_waiver SET waiver_status='.$waiver_status.'
            WHERE participant_id='.$participant_id.' AND reg_id = '.$reg_id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }
}

class S_participant extends S_login {
    // hope this is a good idea. participant extends login in php same as it does in the db
    var $id;
    var $login_id; // not perfect. I'm not doing what I want
    var $orderby; // this should be in the db thing and then all our shit extends it? or just db::orderby
    function get_participant($id) {
        $query = 'SELECT * FROM participant WHERE id='.$id;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            // echo $query.' on line '.__LINE__.'<br>';
            return false;
        }
    }

    function get_participantby_name($fname, $lname) {
        $query = 'SELECT * FROM participant WHERE fname="'.$fname.'"
            AND lname="'.$lname.'"';
            echo $query."\n";
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
            return false;
        }
    }

    function get_all_participants($orderby='') {
        if(strlen($orderby) > 0) {
            $orderby=' ORDER BY '.$orderby;
        }
        $query='SELECT id, id as participant_id, fname, lname, dob, p_meta FROM participant '.$orderby;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo '<br><br><br>arg';
            echo $query.'<br>';
        }
    }

    function get_participants_by_id($arr) {
        $ids=implode(',', $arr);
        $query='SELECT id, fname, lname, pmeta FROM participant WHERE id in('.$ids.')';
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function get_participants_by_login($login_id) {
        // I should join this against event and class to see if the student is in a class but for now ... no
        $query='SELECT 
            login_participant.login_id, 
            participant.id, 
            participant.fname, 
            participant.lname, 
            participant.p_meta,
            participant.dob
            FROM login_participant, participant
            WHERE login_participant.login_id='.$login_id.' 
            AND login_participant.participant_id=participant.id';
        // debug // echo $query .'<br>';
            if ($result = $this->db->query($query)) {
                return $result;
            }
    }

    function get_logins_by_participant ($participant_id) {
        $query = 'SELECT login.* FROM login, login_participant 
            WHERE login_participant.participant_id = '.$participant_id.' 
            AND login_participant.login_id=login.id';
        // debug // echo $query.'<br>';
            if ($result = $this->db->query($query)) {
                return $result;
            }
    }

    function is_participant_in_class($participant_id, $class_id) {
        // not used
        $query='SELECT count(event.class_id) as classcount FROM event 
            LEFT JOIN event on event_participant.event_id = event.id 
            WHERE participant.participant_id='.$participant_id.' AND 
            where event.class_id='.$class_id;
        // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    function participant_in_class_events($participant_id, $class_id) {
        // get all the events of a class that a participant is in 
        $query= 'SELECT event_participant.id, event_participant.event_id FROM event_participant
            LEFT JOIN event on event_participant.event_id = event.id 
            WHERE event_participant.participant_id='.$participant_id.' AND
            event.class_id='.$class_id;
        // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    function get_private_participants() {
        $query = 'SELECT event_participant.*, 
            event_participant.id as ep_id
            FROM event_participant
            LEFT JOIN event ON event.id = event_participant.event_id
            LEFT JOIN event_type ON event.et_id = event_type.id
            WHERE event_type.et_activity_level=2';
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }
    
    function get_orphan_participants() {
        // get all orphans
        $query='SELECT event_participant.*,
            event_participant.id as ep_id
            FROM event_participant
            WHERE event_participant.event_id=0';
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    function participants_in_class($class_id, $status_id='') {
        $join = ' WHERE ';
        if($status_id*1 > 0) {
            $join = ' AND ';
            $stat_clause = ' WHERE event_participant.status_id='.$status_id;
        }
        $query='SELECT event_participant.*,
            event_participant.id as ep_id
            FROM event_participant
            LEFT JOIN event ON event.id=event_participant.event_id
            LEFT JOIN class on event.class_id=class.id '.$stat_clause.' 
            '.$join.' class.id='.$class_id;
        // debug // echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }


    function insert_event_participant($event_id, $participant_id) {
        $query='INSERT INTO event_participant(event_id, participant_id) 
            VALUES ('.$event_id.', '.$participant_id.')';
        // debug // echo $query.'<br>';
        $result = $this->db->query($query);
        if($result==true) {
            $event_participant_id=$this->db->insert_id;
        } else {
            echo $query."\n";
            echo $this->db->error;
            $event_participant_id=false;
        }
        return $event_participant_id;
    }

    function remove_event_participant ($event_participant_id) {
        $query='DELETE FROM event_participant WHERE id = '.$event_participant_id;
        // debug // echo $query.'<br>';
        $result = $this->db->query($query);
        /* 
        $query='DELETE FROM event_participant_billing WHERE event_participant_id = '.$event_participant_id;
        // debug // echo $query.'<br>';
        $result = $this->db->query($query);
         */
        // ^^ that is called separately from the rpc page
        // 
        if(is_object($result)) {
            return true;
        } else {
            echo $query;
        }
    }

    function update_event_participant ($field_name, $field_val, $ep_id) {
        $allowed_fields=array('event_id','status_id');
        if(in_array($field_name, $allowed_fields)) {
            $query = 'UPDATE event_participant SET '.$field_name.' = "'.$field_val.'" 
                WHERE id='.$ep_id;
            // debug // echo $query."\n";
            if ($result = $this->db->query($query)) {
                $participant_id=$this->db->insert_id;
                return $participant_id;
            }
        }
        return false;
    }

    // works but not used
    function append_ep_metadata ($ep_id, $ep_meta) {
            $query = 'UPDATE event_participant SET ep_meta = CONCAT ( ep_meta, "\r\n", "'.$ep_meta.'") 
                WHERE id='.$ep_id;
            // debug // 
            echo $query."\n";
            if ($result = $this->db->query($query)) {
                return true;
            }
        return false;
    }

    function get_status_from_id($status_id) {
        $query='SELECT * FROM event_participant_status WHERE id='.$status_id;
        $result = $this->db->query($query);
        if(is_object($result)) {
            return $result;
        }
    }

    function get_all_status() {
        if($_SESSION['log_level'] *1 > 0) {
            // only show the status that the user is authorized to see
            // btw this is what I wanted to avoid
            $clause=' WHERE status_display_level <='.$_SESSION['log_level'];
        } else {
            $clause='';
        }
        $query='SELECT * FROM event_participant_status'.$clause;
        // debug // echo $query.'<br>';
        $result = $this->db->query($query);
        if(is_object($result)) {
            return $result;
        }
    }

    function participants_in_event($event_id) {
        $query='SELECT event_participant.id AS event_participant_id, event_participant.*, participant.*,
            period_diff(DATE_FORMAT(now(), "%Y%m"), DATE_FORMAT(participant.dob, "%Y%m") ) as participant_age_months
           from event_participant
            LEFT JOIN participant on participant.id = event_participant.participant_id 
            WHERE event_participant.event_id = '.$event_id;
        // debug // echo '<pre>'.$query.'<br>';
            if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }

    function insert_participant($fname, $lname) {
        // helper function because some names are missing
        $query='INSERT INTO participant (fname, lname) VALUES ("'.$fname.'", "'.$lname.'")';
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            $participant_id=$this->db->insert_id;
            return $participant_id;
        }
    }

    function new_insert_participant($fname, $lname, $dob) {
        // helper function because some names are missing
        $query='INSERT INTO participant (fname, lname, dob) VALUES ("'.$fname.'", "'.$lname.'", "'.$dob.'")'; 
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            $participant_id=$this->db->insert_id;
            return $participant_id;
        } else {
            echo "we had a big ol error";
        }
    }

    function update_participant($field_name, $field_val, $participant_id) {
        $query = 'UPDATE participant SET '.$field_name.' = "'.$field_val.'" 
            WHERE id='.$participant_id;
        echo $query."\n";
        if ($result = $this->db->query($query)) {
            return true;
        }
        return false;
    }

    function check_participant($fname, $lname, $dob) {
        $query = 'SELECT id FROM participant WHERE fname="'.$fname.'" AND lname="'.$lname.'"';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query.' on line '.__LINE__.'<br>';
        }
    }

    function insert_login_participant($login_id, $participant_id) {
        $query = 'INSERT INTO login_participant (login_id, participant_id) VALUES ('.$login_id.', '.$participant_id.')';
        if($result = $this->db->query($query)) {
            return true;
        }
        return 'ah no sorry brah.';
    }

    function remove_login_participant($login_id, $participant_id) {
        $query = 'DELETE FROM login_participant WHERE login_id= '.$login_id.' AND participant_id= '.$participant_id;
        if($result = $this->db->query($query)) {
            return true;
        }
        return $query;
    }

    function remove_participant($participant_id) {
        $query = 'DELETE FROM participant WHERE id = '.$participant_id;
        // echo $query.'<br>';
        if($result = $this->db->query($query)) {
            return true;
        }
        return $query;
    }

    function remove_participant_from_all_events($participant_id) {
        $query='DELETE FROM event_participant WHERE participant_id='.$participant_id;
        if($result = $this->db->query($query)) {
            // return true;
        }
        return $query;
    }

    function compare_participant($existing_participants, $fname, $lname) {
        // compare fname and lname to the array of existing participants that belong to this login_id
        // debug // print_r($existing_participants);
        foreach($existing_participants as $id => $ep_array) {
            $matches = 0;
            foreach ($ep_array as $var => $val) {
                // debug // echo 'val is '.$val.' and $$var ('.$var.') is '.$$var."\n";
                if($val == $$var) {
                    $matches++;
                }
                // echo "var is $var and $val is $val and ...\n".$$var." is ".$$val."\n";
            }
            /* 
            echo "\n\n\n";
            echo "for this one we got $matches \n";
            echo "and we need ";
            echo count($ep_array);
            echo "\n";
            echo "we found $matches, and so ... ";
             */
            if($matches == count($ep_array)) {
                /*
                echo "Match!";
                echo "\n\n\n";
                 */
                return true;
            } 
        }
        return false;
    }
}

class S_billing {
    function event_billing_by_participant($participant_id) {
        // this has to be quick and dirty if I'm going to make it
        // we don't use the line item anymore. I'm keeping this because someday we will.
        /*
        $query='SELECT event_participant_billing.*, 
            event_line_item.line_item 
            FROM event_participant_billing, event_line_item
            WHERE event_participant_billing.participant_id='.$participant_id.' 
            AND event_participant_billing.event_line_item_id = event_line_item.id
            AND event_participant_billing.amount_due > 0';
         */
        // so ... we no longer use event_line_item. We will want to though
        $query = 'SELECT * from event_participant_billing
            WHERE participant_id='.$participant_id.' AND amount_due > amount_paid';
        // echo $query;

        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function class_name_from_event_participant_billing_id ($id) {
        // YUP
        $query='select class.name as class_name from class, event, event_participant, event_participant_billing where class.id=event.class_id and event.id=event_participant.event_id and event_participant.id=event_participant_billing.event_participant_id and event_participant_billing.id='.$id;
        // echo $query.';<br>';
        if ($result = $this->db->query($query)) {
            $arr = $result -> fetch_assoc();
            $class_name = $arr['class_name'];
            return $class_name;
        } else {
            echo mysql_error();
        }
    }

    function is_ep_paid ($event_participant_id) {
        // find out if the participant has paid fully for this event
        $eb_res = $this -> event_billing_by_epid ($event_participant_id);
        $eb_arr = $eb_res->fetch_assoc();
        $amount_due  = $eb_arr['amount_due'];
        $amount_paid = $eb_arr['amount_paid'];
        if($amount_due == 0) {
            return 'paid';
        }
        if($amount_paid == 0) {
            // debug // return $amount_due;
            return 'unpaid';
        }
        $amount_due = $amount_due - $amount_paid;
        if($amount_due <=0) {
            return 'paid';
        }
        return 'partially_paid';
    }

    function event_billing_by_epid($event_participant_id) {
        // this has to be quick and dirty if I'm going to make it
        $query='SELECT event_participant_billing.*, event_line_item.line_item 
            FROM event_participant_billing, event_line_item
            WHERE event_participant_billing.event_participant_id='.$event_participant_id.' 
            AND event_participant_billing.event_line_item_id = event_line_item.id';
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function get_event_line_item ($id) {
        // I am tired
        $query='SELECT * FROM event_line_item WHERE id='.$id;
        // echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function get_all_event_billing() {
        $query='SELECT * FROM event_participant_billing';
        // echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function get_event_billing ($id) {
        // I am tired 
        $query='SELECT * FROM event_participant_billing WHERE id='.$id;
        // echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

    function billing_by_ep_id ($event_participant_id) {
        // I am tired
        $query='SELECT * FROM event_participant_billing WHERE event_participant_id='.$event_participant_id;
        // echo $query;
        if ($result = $this->db->query($query)) {
            // echo "we are returning a result <br>";
            return $result;
        }
        return false;
    }

    function insert_event_billing ($event_line_item_id, $event_line_item, $participant_id, $event_participant_id, $amount_due, $amount_paid, $ep_b_meta='') {
        $query='INSERT INTO event_participant_billing (event_line_item_id, event_line_item, participant_id, event_participant_id, amount_due, amount_paid, ep_b_meta)
            VALUES ('.$event_line_item_id.', "'.$event_line_item.'", '.$participant_id.', '.$event_participant_id.', '.$amount_due.', '.$amount_paid.', "'.$ep_b_meta.'")';
        // debug // echo $query;
        if($result = $this->db->query($query)) {
            return true;
        }
            echo "error! <br>";
            echo $this->db->error;
        return false;
    }

    function update_event_billing ($pb_id, $amount, $addsub='+') {
        // this just updates the amount. leaves everything else alone
        // which makes it a poorly named function eh? EH? EH!?!?!?
        // apparently it's not used though so I turned it off
        // which was REALLY STUPID because process_payment uses it
        $query='UPDATE event_participant_billing set 
            amount_paid=amount_paid'.$addsub.$amount.'
            WHERE id='.$pb_id;
        // debug // echo $query.'<br>';
        if($result = $this->db->query($query)) {
            return true;
        }
        return false;
    }

    function update_event_participant_billing($field_name, $field_val, $pb_id) {
        $query = 'UPDATE event_participant_billing SET '.$field_name.' = "'.$field_val.'" 
            WHERE id='.$pb_id;
        // debug // echo $query."\n";
        if ($result = $this->db->query($query)) {
            $participant_id=$this->db->insert_id;
            return $participant_id;
        }
        return false;
    }

    function append_ep_billing_metadata ($event_participant_id, $ep_b_meta) {
            $query = 'UPDATE event_participant_billing SET ep_b_meta = CONCAT ( ep_b_meta, "\r\n", "'.$ep_b_meta.'") 
                WHERE event_participant_id='.$event_participant_id;
            // debug // echo $query."\n";
            if ($result = $this->db->query($query)) {
                return true;
            }
        return false;
    }

    function remove_event_participant_billing ($event_participant_id) {
        // this blows up if you have the same event_participant_id in 2 places
        // which sounds totally insane
        $query='DELETE FROM event_participant_billing WHERE event_participant_id = '.$event_participant_id;
        // debug // echo $query.'<br>';
        $result = $this->db->query($query);
        if(is_object($result)) {
            return true;
        } else {
            echo $query;
        }
    }

    function remove_all_event_participant_billing ($participant_id) {
        // this doesn't distinguish by class.
        $query='DELETE FROM event_participant_billing WHERE participant_id = '.$participant_id;
        // debug // echo $query.'<br>';
        $result = $this->db->query($query);
        if(is_object($result)) {
            return true;
        } else {
            echo $query;
        }
    }

    function event_billing_by_class($class_id) {
        $query = 'SELECT event_participant_billing.*, event_participant.event_id
            FROM event_participant_billing
            LEFT JOIN event_participant on event_participant_billing.event_participant_id = event_participant.id  
            LEFT JOIN event on event_participant.event_id = event.id
            WHERE event.class_id = '.$class_id;

        /*
        $query = 'SELECT event_participant_billing.*
            FROM event_participant_billing, event_participant, event
            WHERE event_participant_billing.event_participant_id=event_participant.id
            AND event_participant.event_id=event.id
            AND event.class_id='.$class_id;
         */

        /* both of those work and they both come up 49 shy from where you
         * would be if you grabbed all the billing rows. What's more 
         * checking by different class ids doesn't make a difference.
         */
        /* turns out you have 49 event_participant_ids in event_participant_billing that are not attached
         * to real events. I wonder if they were deleted somehow. Judy must have removed them from their events
         */

        echo $query;
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            return false;
        }
    }
}

class S_logging {
    var $db;
    function insert_item($data) {
        extract($data);
        $query='INSERT INTO billing_log (id_type, logged_id, log)
            VALUES ("'.$id_type.'", '.$logged_id.', "'.$log.'")';
            // echo $query;
        if($result = $this->db->query($query)) {
            return true;
        }
        return false;
    }
}

class S_private {
    var $db;
}

class S_location {
    var $db;
    function get_location_by_id($location_id) {
        $query='SELECT *
            FROM location WHERE id='.$location_id;
        // debug // echo $query.'<br>';
        if ($result = $this->db->query($query)) {
            return $result;
        } else {
            echo $query;
        }
    }

    function get_all_locations() {
        $query='SELECT concat("location_", id) as location_id, id, "location" as name_name, location FROM location';
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }

}

class S_leader {
    var $db;

    function get_leader_by_id($leader_id) {
        $query='SELECT id, fname, lname, email
            FROM leader WHERE id='.$leader_id;
        if ($result = $this->db->query($query)) {
            return $result;
        }
    }
}

class config  {
    var $db;
    function my_config() {
        $query = 'SELECT * FROM config';
        if ($result = $this->db->query($query)) {
            while($arr = $result -> fetch_assoc()) {
                extract($arr);
                $ret[$option_name]=$arr;
            }
        }
        return $ret;
    }

    function config_option ($option_name) {
        $query = 'SELECT * FROM config WHERE option_name="'.$option_name.'"';
        // debug // echo $query."<br>";
        if ($result = $this->db->query($query)) {
            $arr = $result -> fetch_assoc();
            return $arr;
        }
        return false; // yeah this isn't how we do the other sql queries but guess what WE DO THOSE WRONG.
    }

}

class mini_admin {
    var $db;
    public $config;

    function unpack_admin () {
        $json_arr = json_decode($this -> config, true);
        return $json_arr;
    }

    function table_update ($table, $field_name, $up_key, $value) {
        // unpack the config

        $config_arr = $this -> unpack_admin(); 

        $field = $config_arr['tables'][$table]['fields'][$field_name];
        $type = $field['type'];
        $name = $field['name'];
        $key = $config_arr['tables'][$table]['key'];

        $value = $this -> process_value($table, $value, $type);

        $query = "UPDATE $table  SET $field_name = $value WHERE $key = $up_key";

        // debug // echo $query."\n";
        if ($result = $this->db->query($query)) {
            $ret['success'] = "Updated $name to $value";
        } else {
            $mysql_error = $this->db->error;
            $ret['error'] = "Failed to update $name to $value, $mysql_error";
        }
        return $ret;
    }

    function table_insert ($table, $fields) {
        foreach($fields as $raw_field_name) {

        }
    }

    function get_tables () {
        $config_arr = $this -> unpack_admin(); 
        $tables = $config_arr['tables'];
        return $tables;
    }

    function get_table_data($table, $table_info) {
        $fields = $table_info['fields'];
        $key    = $table_info['key'];
        foreach($fields as $field_name => $field_data) {
            $fieldlist[] = $field_name;
        }
        $fieldlist = implode(',', $fieldlist);

        $query = "SELECT $key,$fieldlist FROM $table ORDER BY $key";
        // debug // echo $query."\n";;
        if ($result = $this->db->query($query)) {
            while($arr = $result -> fetch_assoc()) {
                // I don't think I need this $$key = $arr[$key]; //
                // reason is, we're assuming that the key is already unique, so we
                // don't need to name its field name. it's just a marker for a 
                // unique array. When we turn around and use this data as a way
                // to update we'll refer to the config for the key name.
                $id = $arr[$key];
                $ret[$id] = $arr;
            }
        } else {
            $mysql_error = $this->db->error;
            echo $mysql_error."\n";
            $ret['error'] = "Failed to get data for $table";
        }

        return $ret;

        // get the fields
    }

    function process_value($table, $value, $type) {
        // table is here in case someday we want table specific process options someday
        switch($type) {
        case "string":
            $formatted_value="'$value'";
            break;
        case "int":
            $formatted_value = $value;
            break;
        default: 
            $formatted_value = $value;
            break;
        }
        return $formatted_value;
    }
}

function result_as_json($result) {
    // you need to get this into your class
    // I'm going to regret this
    // I regret it
    // requires a column name called id
    while ($row = $result->fetch_assoc()) {
        // debug // echo "I am totally extracting you \n";
        extract($row);
        $ret[$id]=$row;
    }
    $result->close();
    // debug //print_r($ret);
    $ret=json_encode($ret);
    return $ret;
}

function result_as_array($renderer, $db_result, $key_name) {
    if($renderer instanceOf Data_Render) {
        $renderer->db_result=$db_result;
        $renderer->key_name=$key_name;
        $renderer->data_type='array';
        $ret=$renderer->render_assoc();
        return $ret;
    } else {
        // punt
        /*
        while ($row = $db_result->fetch_assoc()) {
            // debug // echo "I am totally extracting you \n";
            extract($row);
            $ret[$$keyname]=$row;
        }
        $result->close();
             */
        return $ret;
    }
}

function result_as_html_table($renderer, $db_result) {
    if($renderer instanceOf Data_Render) {
        $renderer->db_result=$db_result;
    }
}

function result_as_html_list($renderer, $db_result, $value, $option, $selected='') {
    // debug // echo "selected is $selected <br>";
    /*
    while ($row = $db_result->fetch_assoc()) {
        // debug // echo "I am totally extracting you \n";
        print_r($row);
        echo '<br>';
    }
     */
    // you need to pass db_result in somehow
    if($renderer instanceOf Data_Render) {
        $renderer->db_result=$db_result;
        // actually I think the function should process the returned array to find the selected element.
        $renderer->sel_option=$option;
        $renderer->sel_value=$value;
        $renderer->selected=$selected;
        $renderer->data_type='list';
        $ret=$renderer->render_assoc();
        return $ret;
    }
}

function array_as_html_radio_buttons ($renderer, $array, $id_name, $data_label, $value='', $selected='') {
    // grab an array of ids and values and return them as radio buttons with labels
    // debug // echo "in the function value is $value <br>";
    if($renderer instanceOf Data_Render) {
        $renderer -> data_type   = 'radio';
        $renderer -> data_format = 'array';
        $renderer -> data_set    = $array; # array is your list of values
        $renderer -> data_label  = $data_label;
        $renderer -> selected    = $selected;
        $renderer -> id_name     = $id_name;
        $renderer -> radio_value = $value;
        $ret = $renderer -> render_assoc();
        return $ret;
    }
}

function new_as_html_radio_buttons ($renderer, $field_data, $values, $selected=array()) {
    // when you are old and it's a choice between this and trimming your lawn with nail clippers you can come back to this and merge new_as_html_radio_buttons with new_as_html_check_boxes and then show all your old friends.
    if($renderer instanceOf Data_Render) {
        extract($field_data);
        extract($values);
        $renderer -> data_type   = 'radio';
        $renderer -> data_format = 'array';
        $renderer -> data_set    = $values; # array is your list of values
        $renderer -> selected    = $selected;
        $renderer -> name_name   = $field_data['name_name'];
        $renderer -> id_name     = $field_data['id_name'];
        $renderer -> label_name  = $field_data['label_name'];
        $renderer -> value_key   = $field_data['value_key'];
        $renderer -> value_name  = $field_data['value_name'];
        $renderer -> extra_attr  = $field_data['extra_attr'];
        $renderer -> input_class = $input_class;
        $ret = $renderer -> render_assoc();
        return $ret;
    }
}

function new_as_html_check_boxes ($renderer, $field_data, $values, $selected=array()) {
    if($renderer instanceOf Data_Render) {
        $renderer -> data_type   = 'newcheckboxes';
        $renderer -> data_format = 'array';
        $renderer -> data_set    = $values; # array is your list of values
        $renderer -> selected    = $selected;
        $renderer -> name_name   = $field_data['name_name'];
        $renderer -> id_name     = $field_data['id_name'];
        $renderer -> label_name  = $field_data['label_name'];
        $renderer -> value_key   = $field_data['value_key'];
        $renderer -> value_name  = $field_data['value_name'];
        $renderer -> extra_attr  = $field_data['extra_attr']; // not pushed to the class yet because I don't know what I'd do with a mandatory checkbox
        $renderer -> input_class = $field_data['input_class'];
        $ret = $renderer -> render_assoc();
        return $ret;
    }
}

function array_as_html_checkboxes($renderer, $array, $label, $selected=array(), $id_name) {
    // grab an array of ids and values and return them as checkboxes with labels
    if($renderer instanceOf Data_Render) {
        $renderer -> data_type   = 'checkboxes';
        $renderer -> data_format = 'array';
        $renderer -> data_set    = $array;
        $renderer -> data_label  = $label;
        $renderer -> selected    = $selected;
        $renderer -> id_name     = $id_name;
        $ret = $renderer -> render_assoc();
        return $ret;
    }
}

function generate_payment ($processor, $data, $preview=false) {
    // this should wrap around a set of classes like the html renderer does
    // where each class in the end represents a payment gateway
    // one problem is that due to the nature of the registration form at least
    // I can't keep contact info fields in here. That stuff is populated by javaScript.

    if($preview == true) {
        $input_type = 'text';
    } else {
        $input_type = 'hidden';
    }

    $order_description = $data['order_description'];
    $key      = $data['key'];
    $pd_arr   = $data['pd_arr']; // product description list
    $pay      = $data['pay']; // array of prices that matches product description
    $amount   = $data['amount']; // array of prices that matches product description
    foreach($pd_arr as $pd_id => $pd_val) {
        gw_printField($key, "product_description_$pd_id", $pd_val);
        gw_printField($key, "product_amount_$pd_id", $pay[$pd_id]);
    }
?>
    <input type="<?php echo $input_type; ?>" name="key_id" value="<?php echo $data['key_id']; ?>" />
    <input type="<?php echo $input_type; ?>" name="username" value="<?php echo $data['username']; ?>" />
    <input type="<?php echo $input_type; ?>" name="language" value="en" />
    <input type="<?php echo $input_type; ?>" name="line_item_field" value="<?php echo $data['line_item_field']; ?>" />
    <input type="<?php echo $input_type; ?>" name="return_method" value="redirect" />
    <input type="<?php echo $input_type; ?>" name="return_link" value="<?php echo $data['return_link']; ?>" />
    <input type="<?php echo $input_type; ?>" name="customer_receipt" value="false" />
<?php
    gw_printField($key, "action", "process_fixed");
    gw_printField($key, "order_description", $order_description );
    gw_printField($key, "amount",number_format($amount, 2,'.','') );
    // gw_printField($key, "surcharge", '0' ); 
    gw_printField($key, "hash", ''); 

}

function gw_printField($key, $name, $value = "") {
    $gw_merchantKeyText=$key;
    static $fields;
    // Generate the hash
    if($name == "hash") {
        $stringToHash = implode('|', array_values($fields)) .
            "|" . $gw_merchantKeyText;
        $value = implode("|", array_keys($fields)) . "|" . md5($stringToHash);
    } else {
        $fields[$name] = $value;
    }
    echo '<input type="hidden" name="'.$name.'" value="'.$value.'">'."\n";
}

function sigJsonToImage ($json, $options = array()) {
  $defaultOptions = array(
    'imageSize' => array(198, 55)
    ,'bgColour' => array(0xff, 0xff, 0xff)
    ,'penWidth' => 2
    ,'penColour' => array(0x14, 0x53, 0x94)
    ,'drawMultiplier'=> 12
  );

  $options = array_merge($defaultOptions, $options);


  $img = imagecreatetruecolor($options['imageSize'][0] * $options['drawMultiplier'], $options['imageSize'][1] * $options['drawMultiplier']);

  if ($options['bgColour'] == 'transparent') {
    imagesavealpha($img, true);
    $bg = imagecolorallocatealpha($img, 0, 0, 0, 127);
  } else {
    $bg = imagecolorallocate($img, $options['bgColour'][0], $options['bgColour'][1], $options['bgColour'][2]);
  }

  $pen = imagecolorallocate($img, $options['penColour'][0], $options['penColour'][1], $options['penColour'][2]);
  imagefill($img, 0, 0, $bg);

  if (is_string($json))
    $json = json_decode(stripslashes($json));

  if(is_array($json)) {
      foreach ($json as $v) {
        drawThickLine($img, $v->lx * $options['drawMultiplier'], $v->ly * $options['drawMultiplier'], $v->mx * $options['drawMultiplier'], $v->my * $options['drawMultiplier'], $pen, $options['penWidth'] * ($options['drawMultiplier'] / 2));

      }
  }
  $imgDest = imagecreatetruecolor($options['imageSize'][0], $options['imageSize'][1]);

  if ($options['bgColour'] == 'transparent') {
    imagealphablending($imgDest, false);
    imagesavealpha($imgDest, true);
  }

  imagecopyresampled($imgDest, $img, 0, 0, 0, 0, $options['imageSize'][0], $options['imageSize'][0], $options['imageSize'][0] * $options['drawMultiplier'], $options['imageSize'][0] * $options['drawMultiplier']);
  imagedestroy($img);

  return $imgDest;
}

function drawThickLine ($img, $startX, $startY, $endX, $endY, $colour, $thickness) {
  $angle = (atan2(($startY - $endY), ($endX - $startX)));

  $dist_x = $thickness * (sin($angle));
  $dist_y = $thickness * (cos($angle));

  $p1x = ceil(($startX + $dist_x));
  $p1y = ceil(($startY + $dist_y));
  $p2x = ceil(($endX + $dist_x));
  $p2y = ceil(($endY + $dist_y));
  $p3x = ceil(($endX - $dist_x));
  $p3y = ceil(($endY - $dist_y));
  $p4x = ceil(($startX - $dist_x));
  $p4y = ceil(($startY - $dist_y));

  $array = array(0=>$p1x, $p1y, $p2x, $p2y, $p3x, $p3y, $p4x, $p4y);
  imagefilledpolygon($img, $array, (count($array)/2), $colour);
}

function ca($action) {
    if(strlen($_REQUEST['action']) > 0) {
        $action=$_REQUEST['action'];
    } 
    return $action;
}

function is_logged_in() {
    if(strlen($_SESSION['login_hash']) > 0) {
        return true;
    }
    return false;
}

function is_admin() {
    if($_SESSION['log_level'] > 1) {
        return true;
    }
    return false;
}

function clean_string($string) {
    // this is how you know Troy wrote this
    $garbage[]="'";
    $garbage[]='"';
    $garbage[]=';';
    $garbage[]='%';
    $garbage[]='\\';
    $string=trim(str_replace($garbage, '', $string));
    return $string;
}

function is_required( $id ) {
    if($id == 0 || strlen(trim($id)) == 0 ) {
        // validarium // return 'data-rules-required="false"';
        return 'meh';
    } 
    // validarium return 'data-rules-required="true"';
    return 'required';
}

function datediffInWeeks($date1, $date2) {
    if($date1 > $date2) return datediffInWeeks($date2, $date1);
    $first = DateTime::createFromFormat('Y-m-d H:i:s', $date1);
    $second = DateTime::createFromFormat('Y-m-d H:i:s', $date2);
    $ret['days']  = $first->diff($second)->days;
    $ret['weeks'] = ceil($first->diff($second)->days/7);
    return $ret['weeks'];
}

?>
