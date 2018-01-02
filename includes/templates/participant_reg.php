<?php
// I personally beleive templates shouldn't rely on objects created outside of them
// but this is a horrible template anyway.
    $t=0; // counts text boxes
    $r=0; // counts radio buttons (not sure either is used)

    echo '<h3>'.$name.'</h3>'."\n";
    echo '<p> DOB: '.$dob.'</p>';
    while($sections = $s_res->fetch_assoc()) {
        // debug // print_r($sections);
        $section_id=$sections['id'];
        $section_name=$sections['section_name'];
        echo '<div class="reg_section_'.$section_id.'" id="reg_section_'.$participant_id.'_'.$section_id.'">'."\n";
        echo '<h3>'.$section_name.'</h3>'."\n";
        $q_res = $s_reg -> questions_by_section($section_id);
        while($questions = $q_res -> fetch_assoc()) {
            $question        = $questions['question'];
            $question_id     = $questions['id'];
            $question_name   = $questions['question_name'];
            $answer_group_id = $questions['answer_group_id'];
            $is_required     = $questions['is_required'];

            // load up any answers you have for this question
            // you shouldn't even look for this unless you know they have a waiver
            $qa_res = $s_participant_reg -> get_p_reg_answer($participant_id, $question_id);
            if($qa_res -> num_rows != 0) {
                $qa_arr = $qa_res->fetch_assoc();
                $answer=htmlentities($qa_arr['answer']);
            } else {
                $answer='';
            }

            if($answer_group_id * 1 > 0) {
                // we have an answer group, which means raaaadios
                // ... probably or textareas or checkboxes
                // debug // $q_line.= "comparing $r to $radio_in_a_row <br>";
                // but apparently here I just assume radio and say fukit.
                if($r >= $radio_in_a_row) {
                    $break_row=" break_form_line ";
                    $q_line.='<div class="break_row"></div>'."\n";
                    $r=0;
                } else {
                    $break_row="";
                }
                $t=0; // reset the text boxes
                $r++; // increment the number of radios we have
                $extra_class=" div_radio $break_row";

                if($g_res = $s_reg -> get_answer_group($answer_group_id, $question_name, $question_id)) {
                    $g_arr = result_as_array(new serialized_Render(), $g_res, 'id');
                    foreach($g_arr as $gvar => $gval) {
                        foreach($gval as $mygkey => $myname)  {
                            // echo "key is $mygkey and val is $myname<br>";
                            if($mygkey=='question_name') {
                                $g_arr[$gvar]['simple_q']=$myname;
                                $killme=explode('|', $myname);
                                $myname=$killme[0].'['.$participant_id.']['.$killme[1].']';
                                $myname='p_reg_answer|answer|pqa|'.$participant_id.','.$question_id;
                                $g_arr[$gvar][$mygkey]=$myname;
                            }
                            if($mygkey=='field_id') {
                                $g_arr[$gvar][$mygkey]=$myname.'_'.$participant_id;
                            }
                        }
                    }
                    $preload_res = $s_reg -> get_preload_data($answer_group_id);
                    // and I was just saying how I had written some good code
                    $preload_arr = $preload_res -> fetch_assoc();
                    $answer_type = $preload_arr['answer_type'];
                    $preload_id  = $preload_arr['id'];
                    $field_data['name_name']   = 'question_name';
                    $field_data['id_name']     = 'field_id'; 
                    $field_data['label_name']  = 'answer';
                    $field_data['value_key']   = 'id';
                    $field_data['value_name']  = 'answer';
                    $field_data['input_class'] = 'editable';
                    $field_data['extra_attr']  = is_required( $is_required );  

                    // I love you past-Troy
                    if(strlen($answer) > 0) {
                        $selected=$answer;
                    } else {
                        $selected='';
                    }
                    if($answer_type == 'radio') {
                        $preload_buttons = new_as_html_radio_buttons(new html_Render(), $field_data, $g_arr, $selected);
                        $q_form = '<div class="register_radio">'.$preload_buttons.'</div>'."\n";
                    }
                    if($answer_type == 'checkbox') {
                        // untested.
                        $preload_checkboxen=array_as_html_checkboxes(new html_Render(), $field_data, $g_arr, $selected);
                        $q_form = '<div class="register_checkboxes">'.$preload_checkboxen.'</div>'."\n";
                    }
                    // I do not remember why this is ... here
                    // because I have answer_type in preload
                    // ... for some reason
                    // so I needed to differentiate textareas
                    // from text fields and that's how I did it.
                    // I might add "text" to preload too. so have that
                    // and I just added "checkbox" to preload, so more to eat.
                    if($answer_type == 'textarea') {
                        $extra_class="editable div_textarea $break_row";
                        $field_id=$question_name.'_'.$participant_id;
                        // $question_name=$g_arr[$preload_id]['question_name'];
                        // debug // $stuff=print_r($g_arr, true);
                        // $question='<label for="'.$question_name.'['.$question_id.']">'.$question.'</label>'."\n";
                        $question='<label for="'.$field_id.'">'.$question.'</label>'."\n";
                        $myname='p_reg_answer|answer|pqa|'.$participant_id.','.$question_id;
                        $q_form = '<div class="register_textarea div_'.$question_name.'">
                            <textarea class="editable textarea_'.$question_id.' '.$question_name.'-'.$question_id.'" name="'.$myname.'" id="'.$field_id.'">'.$answer.'</textarea></div>'."\n";
                    }
                }
            } else {
                $t++; // increment the number of text boxes we have
                $r=0; // reset the radio buttons
                if($t >= $text_in_a_row) {
                    $break_row=" break_form_line ";
                    $q_line.='<div class="break_row"></div>';
                    $t=0;
                } else {
                    $break_row="";
                }
                $extra_class=" div_text $break_row";
                $question='<label for="'.$question_name.'">'.$question.'</label>';
                $myname='p_reg_answer|answer|pqa|'.$participant_id.','.$question_id;
                $extra_attr = is_required($is_required);
                $q_form = '<input class="editable text_'.$section_id.' text_'.$question_name.'" type="text" name="'.$myname.'" id="'.$question_name.'_'.$question_id.'_'.$participant_id.'" value="'.$answer.'" '.$extra_attr.' >';
            }
            $q_line.= '<div class="div_'.$question_name.' reg_question question_'.$section_id.$extra_class.'"><span class="reg_question_text_container">'.$question .'</span>'. $q_form. '</div><!-- end question div -->';
        }
        echo $q_line;
        $q_line='';
        echo '</div><!-- end reg section -->';
        echo '<div class="clearfix"></div>';
    }
?>
