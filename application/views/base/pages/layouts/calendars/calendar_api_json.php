<?php
// Map the calendar fields with the entity fields
$calendar_map = array();
foreach ($data['calendars_fields'] as $field) {
    $calendar_map[$field['calendars_fields_type']] = $field['fields_name'];
}

if (!isset($calendar_map['id']) || !$calendar_map['id']) {
    $calendar_map['id'] = $data['calendars']['entity_name'] . "_id";
}
// Fix per quando usao campo daterange piuttosto di start ed end
if (isset($calendar_map['date_range'])) {
    $calendar_map['start'] = $calendar_map['date_range'] . "_start";
    $calendar_map['end'] = $calendar_map['date_range'] . "_end";
}
$element_id = (isset($value_id) ? $value_id : NULL);
$calendarId = 'calendar' . $data['calendars']['calendars_id'];
$settings = $this->db->join('languages', 'languages_id = settings_default_language', 'LEFT')->get('settings')->row_array();
?>
<div <?php echo sprintf('id="%s"', $calendarId); ?> class="has-toolbar"></div>
<script>
    $(function() {
        'use strict';

        if (!jQuery().fullCalendar) {
            throw Error('Calendar not loaded');
        }

        var jqCalendar = $('#<?php echo $calendarId; ?>');
        var sourceUrl = <?php echo json_encode(base_url("api/search/{$data['calendars']['entity_name']}")); ?>;
        var minTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_min_time') ?: '06:00:00'); ?>;
        var maxTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_max_time') ?: '22:00:00'); ?>;
        var token = JSON.parse(atob($('body').data('csrf')));
        var token_name = token.name;
        var token_hash = token.hash;
        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();
        var h = {};
        if (jqCalendar.width() <= 400) {
            jqCalendar.addClass("mobile");
            h = {
                left: 'title, prev, next',
                center: '',
                right: 'today,month,agendaWeek,agendaDay'
            };
        } else {
            jqCalendar.removeClass("mobile");
        }

        jqCalendar.fullCalendar('destroy'); // destroy the calendar
        jqCalendar.fullCalendar({
            defaultView: 'agendaWeek',
            editable: true,
            selectable: true,
            disableDragging: false,
            header: h,
            minTime: minTime,
            maxTime: maxTime,
            lang: '<?php echo (!empty($settings['languages_code'])) ? (explode('-', $settings['languages_code'])[0]) : 'en'; ?>',

            selectHelper: true,
            select: function(start, end, allDay) {

                if (allDay) {
                    end.date(end.date() + 1);
                    end.minutes(end.minutes() - 1);
                }

                data = {
                    [token_name]: token_hash,
                    <?php echo json_encode($calendar_map['start']); ?>: formatDate(start),
                    <?php echo json_encode($calendar_map['end']); ?>: formatDate(end),
                    <?php if (isset($calendar_map['all_day'])) : ?> <?php echo json_encode($calendar_map['all_day']); ?>: (allDay ? 1 : 0),
                    <?php endif; ?>
                };

                loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['create_form']}")); ?>, data, function() {
                    jqCalendar.fullCalendar('refetchEvents');
                });

                return;
            },
            eventClick: function(event, jsEvent, view) {
                loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['update_form']}")); ?> + '/' + event.id, {}, function() {
                    jqCalendar.fullCalendar('refetchEvents');
                });
                return false;
            },
            eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {

                var oStart = new Date(event.start);
                var oEnd = new Date(event.end);


                if (allDay) {
                    oStart.setDate(event.start.getDate());
                    oStart.setMinutes(0);
                    oStart.setHours(0);

                    oEnd.setDate(event.start.getDate());
                    oEnd.setHours(23);
                    oEnd.setMinutes(59);
                }


                $.ajax({
                    url: "<?php echo base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"); ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        [token_name]: token_hash,
                        <?php echo $calendar_map['id'] ?>: event.id,
                        <?php echo json_encode($calendar_map['start']); ?>: formatDate(oStart),
                        <?php echo json_encode($calendar_map['end']); ?>: formatDate(oEnd),
                        <?php if (isset($calendar_map['all_day'])) : ?> <?php echo json_encode($calendar_map['all_day']); ?>: (allDay ? 1 : 0),
                        <?php endif; ?>
                    },
                    success: function(data) {
                        if (parseInt(data.status) < 1) {
                            revertFunc();
                            alert(data.txt);
                        }
                    },
                    error: function() {
                        revertFunc();
                        alert('There was an error while saving the event');
                    },
                });
            },
            eventResize: function(event, dayDelta, minuteDelta, revertFunc) {

                $.ajax({
                    url: "<?php echo base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"); ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        [token_name]: token_hash,
                        <?php echo $calendar_map['id'] ?>: event.id,
                        <?php echo json_encode($calendar_map['start']); ?>: formatDate(event.start),
                        <?php echo json_encode($calendar_map['end']); ?>: formatDate(event.end),
                        <?php if (isset($calendar_map['all_day'])) : ?> <?php echo json_encode($calendar_map['all_day']); ?>: 0,
                        <?php endif; ?>
                    },
                    success: function(data) {
                        if (parseInt(data.status) < 1) {
                            revertFunc();
                            alert(data.txt);
                        }
                    },
                    error: function() {
                        revertFunc();
                        alert('There was an error while saving the event');
                    },
                });
            },
            eventSources: [{
                url: sourceUrl,
                type: 'POST',
                data: {
                    [token_name]: token_hash,
                },
                error: function(error) {
                    console.log(error.responseText);
                },
                loading: function(bool) {
                    $('#loading').fadeTo(bool ? 1 : 0);
                },
                color: '#4B8DF8', // a non-ajax option
                textColor: 'white' // a non-ajax option
            }]
        });
    });
</script>