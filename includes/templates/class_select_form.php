<div id="classSelectTabs">
    <ul>
    <li><a href="#dayFilter"><?php echo $sl->gp('Classes by day'); ?></a></li>
    <li><a href="#event_typeFilter"><?php echo $sl->gp('Classes by type'); ?></a></li>
    </ul>
    <div id="event_typeFilter">
        <form id="event_typeFilterForm" name="event_typeFilterForm">
            <select id="classSelect" name="classSelect">
                <?php echo $class_list;  ?>
            </select>
            <select name="event_type" id="event_type">
                <?php echo $event_list;  ?>
            </select>
            <input class="classLoadButton" type="button" id="get_events_by_type" value="Go">
        </form>
    </div>
    <div id="dayFilter">
        <form id="dayFilterForm" name="dayFilterForm">
            <select id="classSelect" name="classSelect">
                <?php echo $class_list;  ?>
            </select>
            <select id="day_select" name="day_select">
            <?php echo $day_list; ?>
            </select>
            <input class="classLoadButton" type="button" id="get_events_by_day" value="Go">
        </form>
    </div>
<?php
if(strlen($location_boxes) > 0) { ?>
    <div id="location_boxes">
    <p><?php echo $sl->gp('To limit by location select the location below that you would like to see.'); ?></p>
        <?php echo $location_boxes; ?>
        <input type="button" id="filter_reset" value="Reset"/>
    </div>
<?php
}
?>
</div>
    <input type="button" id="printButton" value="Print">
</div>
