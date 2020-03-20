<?php
// Map the calendar fields with the entity fields
$calendar_map = array();
foreach ($data['calendars_fields'] as $field) {
    $calendar_map[$field['calendars_fields_type']] = $field['fields_name'];
}

if (!isset($calendar_map['id']) || !$calendar_map['id']) {
    $calendar_map['id'] = $data['calendars']['entity_name'] . "_id";
}

$element_id = (isset($value_id) ? $value_id : NULL);
$calendarId = 'calendar' . $data['calendars']['calendars_id'];

$settings = $this->db->join('languages', 'languages_id = settings_default_language', 'LEFT')->get('settings')->row_array();
//debug($settings, true);
?>
<div <?php echo sprintf('id="%s"', $calendarId); ?> class="has-toolbar"></div>
<script>
    $(function() {
        if (!jQuery().fullCalendar) {
            throw Error('Calendar not loaded');
        }

        var jqCalendar = $('#<?php echo $calendarId; ?>');
        var sourceUrl = "<?php echo base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}/{$element_id}"); ?>";
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
                right: 'title, prev, next',
                center: '',
                left: 'prev,next,today,month,agendaWeek,agendaBusinessWeek,agendaDay'
            };
        } else {
            jqCalendar.removeClass("mobile");

            h = {
                right: 'title',
                center: '',
                left: 'prev,next,today,month,agendaWeek,agendaBusinessWeek,agendaDay'
            };
        }

        jqCalendar.fullCalendar('destroy'); // destroy the calendar




        jqCalendar.fullCalendar({
            defaultView: 'agendaWeek',
            editable: true,
            selectable: true,
            disableDragging: false,
            header: h,
            lang: '<?php echo (!empty($settings['languages_code'])) ? (explode('-', $settings['languages_code'])[0]) : 'en'; ?>',
            selectHelper: true,
            minTime: minTime,
            maxTime: maxTime,
            timeFormat: 'H:mm',
            axisFormat: 'H:mm',
            // monthNames: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
            // monthNamesShort: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
            // dayNames: ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'],
            // dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'],
            // firstDay: 1,
            // buttonText: {
            //     today: 'Mostra oggi',
            //     month: 'Mese',
            //     week: 'Sett.',
            //     day: 'Giorno'
            // },

            select: function(start, end) {
                var fStart = formatDate(start.toDate()); // formatted start
                var fEnd = formatDate(end.toDate()); // formatted end
                var allDay = isAlldayEvent(fStart, fEnd, 'DD/MM/YYYY HH:mm');
                var data = {
                    [token_name]: token_hash,
                    <?php echo json_encode($calendar_map['start']) . ' : fStart, ' . json_encode($calendar_map['end']) . ' : fEnd, ' . (isset($calendar_map['all_day']) ? json_encode($calendar_map['all_day']) . ' : allDay? "' . DB_BOOL_TRUE . '":"' . DB_BOOL_FALSE . '"' : ''); ?>
                };
                loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['create_form']}")); ?>, data, function() {
                    jqCalendar.fullCalendar('refetchEvents');
                }, 'get');
            },
            eventClick: function(event, jsEvent, view) {
                loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['update_form']}")); ?> + '/' + event.id, {}, function() {
                    jqCalendar.fullCalendar('refetchEvents');
                });
                return false;
            },
            eventDrop: function(event) {
                var allDay = isAlldayEvent(event.start, event.end);
                var fStart = event.start.format('DD/MM/YYYY HH:mm'); // formatted start
                var fEnd = event.end.format('DD/MM/YYYY HH:mm'); // formatted end
                var data = {
                    [token_name]: token_hash,
                    <?php echo json_encode($calendar_map['id']) . ' : event.id,' . json_encode($calendar_map['start']) . ' : fStart, ' . json_encode($calendar_map['end']) . ' : fEnd, ' . (isset($calendar_map['all_day']) ? json_encode($calendar_map['all_day']) . ' : allDay? "' . DB_BOOL_TRUE . '":"' . DB_BOOL_FALSE . '"' : ''); ?>
                };

                $.ajax({
                    url: "<?php echo base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"); ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: data,
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
            eventResize: function(event, delta, revertFunc) {
                var allDay = isAlldayEvent(event.start, event.end);
                var fStart = event.start.format('DD/MM/YYYY HH:mm'); // formatted start
                var fEnd = event.end.format('DD/MM/YYYY HH:mm'); // formatted end
                var data = {
                    [token_name]: token_hash,
                    <?php echo json_encode($calendar_map['id']) . ' : event.id,' . json_encode($calendar_map['start']) . ' : fStart, ' . json_encode($calendar_map['end']) . ' : fEnd, ' . (isset($calendar_map['all_day']) ? json_encode($calendar_map['all_day']) . ' : allDay? "' . DB_BOOL_TRUE . '":"' . DB_BOOL_FALSE . '"' : ''); ?>
                };


                $.ajax({
                    url: "<?php echo base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"); ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: data,
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
                    [token_name]: token_hash
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