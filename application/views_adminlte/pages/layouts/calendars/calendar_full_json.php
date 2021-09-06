<?php
// Map the calendar fields with the entity fields
$calendar_map = array();
foreach ($data['calendars_fields'] as $field) {
    $calendar_map[$field['calendars_fields_type']] = $field['fields_name'];
}

if (!isset($calendar_map['id']) || !$calendar_map['id']) {
    $calendar_map['id'] = $data['calendars']['entity_name'] . "_id";
}

$element_id = (isset($value_id) ? '/' . $value_id : NULL);
$calendarId = 'calendar' . $data['calendars']['calendars_id'];

$settings = $this->db->join('languages', 'languages_id = settings_default_language', 'LEFT')->get('settings')->row_array();

?>
<div <?php echo sprintf('id="%s"', $calendarId); ?> class="has-toolbar"></div>

<script>
    var updateCalendar = function(evt) {
        var fStart = moment(evt.event.start).format('DD/MM/YYYY HH:mm'); // formatted start
        var fEnd = moment(evt.event.end).format('DD/MM/YYYY HH:mm'); // formatted end
        var allDay = evt.event.allDay;
        var event_id = evt.event.id;

        var data = {
            [token_name]: token_hash,
            <?php echo json_encode($calendar_map['id']) . ' : event_id,' . json_encode($calendar_map['start']) . ' : fStart, ' . json_encode($calendar_map['end']) . ' : fEnd, ' . (isset($calendar_map['all_day']) ? json_encode($calendar_map['all_day']) . ' : allDay? "' . DB_BOOL_TRUE . '":"' . DB_BOOL_FALSE . '"' : ''); ?>
        };

        $.ajax({
            url: "<?php echo base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"); ?>",
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(data) {
                if (parseInt(data.status) < 1) {
                    alert(data.txt);
                }
            },
            error: function() {
                alert('There was an error while saving the event');
            },
        });
    }

    $(function() {
        'use strict';

        var sourceUrl = "<?php echo base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}" . $element_id); ?>";
        var minTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_min_time') ?: '06:00:00'); ?>;
        var maxTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_max_time') ?: '22:00:00'); ?>;

        var token = JSON.parse(atob($('body').data('csrf')));
        var token_name = token.name;
        var token_hash = token.hash;

        var calendarEl = document.getElementById('<?php echo $calendarId; ?>');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            editable: true,
            selectable: true,
            disableDragging: false,
            locale: '<?php echo (!empty($settings['languages_code'])) ? (explode('-', $settings['languages_code'])[0]) : 'en'; ?>',
            selectHelper: true,
            minTime: minTime,
            maxTime: maxTime,
            timeFormat: 'HH:mm',
            axisFormat: 'HH:mm',

            plugins: ['interaction', 'dayGrid', 'timeGrid'],
            defaultView: 'timeGridWeek',
            defaultDate: moment().format('YYYY-MM-DD HH:mm'),
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },

            select: function(date) {
                var fStart = moment(date.start).format('DD/MM/YYYY HH:mm'); // formatted start
                var fEnd = moment(date.end).format('DD/MM/YYYY HH:mm'); // formatted end
                var allDay = date.allDay;

                var data = {
                    [token_name]: token_hash,
                    <?php echo json_encode($calendar_map['start']) . ' : fStart, ' . json_encode($calendar_map['end']) . ' : fEnd, ' . (isset($calendar_map['all_day']) ? json_encode($calendar_map['all_day']) . ' : allDay? "' . DB_BOOL_TRUE . '":"' . DB_BOOL_FALSE . '"' : ''); ?>
                };
                loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['create_form']}")); ?>, data, function() {
                    calendar.refetchEvents();
                }, 'get');
            },

            eventClick: function(evt) {
                loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['update_form']}")); ?> + '/' + evt.event.id, {}, function() {
                    calendar.refetchEvents();
                });
                return false;
            },

            eventDrop: function(evt) {
                updateCalendar(evt);
            },

            eventResize: function(evt, delta, revertFunc) {
                updateCalendar(evt);
            },

            eventSources: [{
                events: function(fetchInfo, successCallback, failureCallback) {
                    $.ajax({
                        type: 'POST',
                        url: sourceUrl,
                        dataType: 'json',
                        data: {
                            [token_name]: token_hash,
                            "start": moment(fetchInfo.start).format('YYYY-MM-DD HH:mm'),
                            "end": moment(fetchInfo.end).format('YYYY-MM-DD HH:mm')
                        },
                        loading: function(bool) {
                            $('#loading').fadeTo(bool ? 1 : 0);
                        },
                        success: function(response) {
                            successCallback(response);
                        },
                        error: function(response) {
                            console.log(response);
                            failureCallback(response);
                        },
                    });
                },
                color: '#4B8DF8', // a non-ajax option
                textColor: 'white' // a non-ajax option
            }],
        });

        calendar.render();
    });
</script>