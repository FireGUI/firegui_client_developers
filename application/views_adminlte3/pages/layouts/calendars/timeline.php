<link href="<?php echo base_url_scripts("script/timeline/css/timelineScheduler.css"); ?>" rel="stylesheet" />
<link href="<?php echo base_url_scripts("script/timeline/css/timelineScheduler.styling.css"); ?>" rel="stylesheet" />
<link href="<?php echo base_url_scripts("script/timeline/css/calendar.css"); ?>" rel="stylesheet" />
<script src="<?php echo base_url_scripts("script/timeline/js/moment.min.js"); ?>"></script>
<script src="<?php echo base_url_scripts("script/timeline/js/timelineScheduler.js"); ?>"></script>


<div class="calendar"></div>
<div class="realtime-info"></div>


<?php
$where = $this->datab->generate_where("calendars", $data['calendars']['calendars_id'], $value_id, $data['calendars']['calendars_where']);
$data_entity = $this->datab->get_data_entity($data['calendars']['calendars_entity_id'], 0, $where);
?>

<script>
    var today = moment().startOf('day');

    var Calendar = {
        Periods: [{
                Name: '3 days',
                Label: '3 days',
                TimeframePeriod: (60 * 3),
                TimeframeOverall: (60 * 24 * 3),
                TimeframeHeaders: [
                    'Do MMM',
                    'HH'
                ]
            },
            {
                Name: '1 week',
                Label: '1 week',
                TimeframePeriod: (60 * 24),
                TimeframeOverall: (60 * 24 * 7),
                TimeframeHeaders: [
                    'MMM',
                    'Do'
                ]
            },
            {
                Name: '1 month',
                Label: '1 month',
                TimeframePeriod: (60 * 24),
                TimeframeOverall: (60 * 24 * 31),
                TimeframeHeaders: [
                    'MMM',
                    'Do'
                ]
            },
            {
                Name: '6 month',
                Label: '6 month',
                TimeframePeriod: (60 * 24 * 7),
                TimeframeOverall: (60 * 24 * 180),
                TimeframeHeaders: [
                    'MMM',
                    'Do'
                ]
            },
        ],
        Sections: [{
            id: 1,
            name: '<?php echo ucfirst($data_entity['entity']['entity_name']); ?>'
        }, ],
        Items: [
            <?php
            $previews = array();
            $conversion = array(
                "title" => "name"
            );

            foreach ($data['calendars_fields'] as $field) {
                if ($field['fields_ref']) {
                    $ids = array_map(function ($dato) use ($field) {
                        return $dato[$field['fields_name']];
                    }, $data_entity['data']);
                    if (empty($ids)) {
                        $previews[$field['fields_ref']] = array();
                    } else {
                        $previews[$field['fields_ref']] = $this->datab->get_entity_preview_by_name($field['fields_ref'], "{$field['fields_ref']}_id IN (" . implode(',', $ids) . ")");
                    }
                }
            }
            $x = 0;
            foreach ($data_entity['data'] as $event) {
                $x++;
                echo '{';
                echo 'id: ' . $x . ', ';
                echo 'sectionID: 1, ';
                echo "classes: 'item-status-none', ";

                // Aggiungo campi dinamici e range 
                foreach ($data['calendars_fields'] as $field) {
                    if ($field['fields_ref'] && isset($previews[$field['fields_ref']][$event[$field['fields_name']]]) && in_array($field['calendars_fields_type'], array('title', 'description'))) {
                        echo "{$conversion[$field['calendars_fields_type']]}: '{$previews[$field['fields_ref']][$event[$field['fields_name']]]}', ";
                    } elseif ($field['calendars_fields_type'] == 'date_range') {
                        $date = explode(',', $event[$field['fields_name']]);
                        echo "start: moment('" . trim($date[0], '[') . "', 'YYYY-MM-DD hh:mm:ss'), ";
                        echo "end: moment('" . trim($date[1], ')') . "', 'YYYY-MM-DD hh:mm:ss'), ";
                    } else {
                        echo "{$conversion[$field['calendars_fields_type']]}: '{$event[$field['fields_name']]}', ";
                    }
                }
                echo '},';
            }
            ?>
        ],
        Init: function() {
            TimeScheduler.Options.GetSections = Calendar.GetSections;
            TimeScheduler.Options.GetSchedule = Calendar.GetSchedule;
            TimeScheduler.Options.Start = today;
            TimeScheduler.Options.Periods = Calendar.Periods;
            TimeScheduler.Options.SelectedPeriod = '1 week';
            TimeScheduler.Options.Element = $('.calendar');

            TimeScheduler.Options.AllowDragging = true;
            TimeScheduler.Options.AllowResizing = true;

            TimeScheduler.Options.Events.ItemClicked = Calendar.Item_Clicked;
            TimeScheduler.Options.Events.ItemDropped = Calendar.Item_Dragged;
            TimeScheduler.Options.Events.ItemResized = Calendar.Item_Resized;

            TimeScheduler.Options.Events.ItemMovement = Calendar.Item_Movement;
            TimeScheduler.Options.Events.ItemMovementStart = Calendar.Item_MovementStart;
            TimeScheduler.Options.Events.ItemMovementEnd = Calendar.Item_MovementEnd;

            TimeScheduler.Options.Text.NextButton = '&nbsp;';
            TimeScheduler.Options.Text.PrevButton = '&nbsp;';

            TimeScheduler.Init();
        },
        GetSections: function(callback) {
            callback(Calendar.Sections);
        },
        GetSchedule: function(callback, start, end) {
            callback(Calendar.Items);
        },
        Item_Clicked: function(item) {
            console.log(item);
        },
        Item_Dragged: function(item, sectionID, start, end) {
            var foundItem;

            console.log(item);
            console.log(sectionID);
            console.log(start);
            console.log(end);

            for (var i = 0; i < Calendar.Items.length; i++) {
                foundItem = Calendar.Items[i];

                if (foundItem.id === item.id) {
                    foundItem.sectionID = sectionID;
                    foundItem.start = start;
                    foundItem.end = end;

                    Calendar.Items[i] = foundItem;
                }
            }

            TimeScheduler.Init();
        },
        Item_Resized: function(item, start, end) {
            var foundItem;

            console.log(item);
            console.log(start);
            console.log(end);

            for (var i = 0; i < Calendar.Items.length; i++) {
                foundItem = Calendar.Items[i];

                if (foundItem.id === item.id) {
                    foundItem.start = start;
                    foundItem.end = end;

                    Calendar.Items[i] = foundItem;
                }
            }

            TimeScheduler.Init();
        },
        Item_Movement: function(item, start, end) {
            var html;

            html = '<div>';
            html += '   <div>';
            html += '       Start: ' + start.format('Do MMM YYYY HH:mm');
            html += '   </div>';
            html += '   <div>';
            html += '       End: ' + end.format('Do MMM YYYY HH:mm');
            html += '   </div>';
            html += '</div>';

            $('.realtime-info').empty().append(html);
        },
        Item_MovementStart: function() {
            $('.realtime-info').show();
        },
        Item_MovementEnd: function() {
            $('.realtime-info').hide();
        }
    };

    $(document).ready(Calendar.Init);
</script>