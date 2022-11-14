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
$calendars_default_view = (!empty($data['calendars']['calendars_default_view'])) ? $data['calendars']['calendars_default_view'] : 'timeGridWeek';

$settings = $this->db->join('languages', 'languages_id = settings_default_language', 'LEFT')->get('settings')->row_array();

?>
<div <?php echo sprintf('id="%s"', $calendarId); ?> class="has-toolbar"></div>

<style>
    .fc-scroller {
        height: 100% !important;
    }

    .total-label {
        font-size: 1.2rem;
        font-weight: bold;
    }
</style>

<script>
    var updateCalendar = function(evt) {
        var fStart = moment(evt.event.start).format('DD/MM/YYYY HH:mm'); // formatted start
        var fEnd = moment(evt.event.end).format('DD/MM/YYYY HH:mm'); // formatted end
    
        var fDateStart = moment(evt.event.start).format('DD/MM/YYYY'); // formatted date start
        var fDateEnd = moment(evt.event.end).format('DD/MM/YYYY'); // formatted date end
    
        var fTimeStart = moment(evt.event.start).format('HH:mm'); // formatted date start
        var fTimeEnd = moment(evt.event.end).format('HH:mm'); // formatted date end
        
        var allDay = evt.event.allDay;
        var event_id = evt.event.id;

        var data = {
            [token_name]: token_hash,
            <?php echo json_encode($calendar_map['id']) . ' : event_id,' . json_encode($calendar_map['start']) . ' : fStart, ' . json_encode($calendar_map['end']) . ' : fEnd, ' . (isset($calendar_map['all_day']) ? json_encode($calendar_map['all_day']) . ' : allDay? "' . DB_BOOL_TRUE . '":"' . DB_BOOL_FALSE . '"' : ''); ?>,
        
            date_start: fDateStart,
            date_end: fDateEnd,
            time_start: fTimeStart,
            time_end: fTimeEnd,
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
        var id_calendario = "<?php echo $element_id; ?>";
        var calendars_default_view = "<?php echo $calendars_default_view; ?>";
        var sourceUrl = "<?php echo base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}" . $element_id); ?>";
        var minTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_min_time') ?: '06:00:00'); ?>;
        var maxTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_max_time') ?: '22:00:00'); ?>;

        var calendar_data = <?php echo json_encode($data['calendars']) ?>;
        
        var token = JSON.parse(atob($('body').data('csrf')));
        var token_name = token.name;
        var token_hash = token.hash;

        var calendarEl = document.getElementById('<?php echo $calendarId; ?>');
        var defaultView = (typeof localStorage.getItem("fcDefaultView_"+id_calendario) !== 'undefined' && localStorage.getItem("fcDefaultView_"+id_calendario) !== null) ? localStorage.getItem("fcDefaultView_"+id_calendario) : calendars_default_view;
    
        var calendar = new FullCalendar.Calendar(calendarEl, {
            editable: true,
            selectable: true,
            disableDragging: false,
            locale: '<?php echo (!empty($settings['languages_code'])) ? (explode('-', $settings['languages_code'])[0]) : 'en'; ?>',
            selectHelper: true,
            minTime: minTime,
            forceEventDuration: true, // @links: https://github.com/fullcalendar/fullcalendar/issues/2655#issuecomment-223838926
            maxTime: maxTime,
            timeFormat: 'HH:mm',
            axisFormat: 'HH:mm',
        
            plugins: ['interaction', 'dayGrid', 'timeGrid'],
            defaultView: defaultView,
            defaultDate: moment().format('YYYY-MM-DD HH:mm'),
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
        
            datesRender: function(info, el)
            {
                localStorage.setItem("fcDefaultView_"+id_calendario, info.view.type);
            },
        
            select: function(date) {
                <?php if (!empty($data['create_form']) && $data['calendars']['calendars_allow_create'] == DB_BOOL_TRUE) : ?>
                    var fStart = moment(date.start).format('DD/MM/YYYY HH:mm'); // formatted start
                    var fEnd = moment(date.end).format('DD/MM/YYYY HH:mm'); // formatted end
                    var allDay = date.allDay;
    
                    var fDateStart = moment(date.start).format('DD/MM/YYYY'); // formatted date start
                    var fDateEnd = moment(date.end).format('DD/MM/YYYY'); // formatted date end
        
                    var fTimeStart = moment(date.start).format('HH:mm'); // formatted time start
                    var fTimeEnd = moment(date.end).format('HH:mm'); // formatted time end
                    
                    var data = {
                        [token_name]: token_hash,
                        <?php echo json_encode($calendar_map['start']) . ' : fStart, ' . json_encode($calendar_map['end']) . ' : fEnd, ' . (isset($calendar_map['all_day']) ? json_encode($calendar_map['all_day']) . ' : allDay? "' . DB_BOOL_TRUE . '":"' . DB_BOOL_FALSE . '"' : ''); ?>,
    
                        date_start: fDateStart,
                        date_end: fDateEnd,
                        time_start: fTimeStart,
                        time_end: fTimeEnd,
                    };
                    loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['create_form']}")); ?>, data, function() {
                        calendar.refetchEvents();
                    }, 'get');
                <?php endif; ?>
                return false;
            },

            eventClick: function(evt) {
                <?php if ($data['calendars']['calendars_event_click'] === 'form' && !empty($data['update_form']) && $data['calendars']['calendars_allow_edit'] == DB_BOOL_TRUE) : ?>
                    loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['update_form']}")); ?> + '/' + evt.event.id, {}, function() {
                        calendar.refetchEvents();
                    });
                <?php elseif($data['calendars']['calendars_event_click'] === 'layout' && !empty($data['calendars']['calendars_layout_id'])): ?>
                    <?php if($data['calendars']['calendars_layout_modal'] === DB_BOOL_TRUE): ?>
                        loadModal(<?php echo json_encode(base_url("get_ajax/layout_modal/{$data['calendars']['calendars_layout_id']}")); ?> + '/' + evt.event.id, {}, function() {
                            calendar.refetchEvents();
                        });
                    <?php else: ?>
                        window.location.href = <?php echo json_encode(base_url("main/layout/{$data['calendars']['calendars_layout_id']}")); ?> + '/' + evt.event.id
                    <?php endif; ?>
                <?php elseif($data['calendars']['calendars_event_click'] === 'link' && !empty($data['calendars']['calendars_link'])): ?>
                    var link = '<?php echo $data['calendars']['calendars_link']; ?>';
    
                    var link = link.replace('{base_url}', '<?php echo base_url(); ?>');
                    var link = link.replace('{value_id}', evt.event.id);
                    
                    window.location.href = link;
                <?php endif; ?>
                
                return false;
            },

            eventDrop: function(evt) {
                <?php if (!empty($data['update_form']) && $data['calendars']['calendars_allow_edit'] == DB_BOOL_TRUE) : ?>
                    updateCalendar(evt);
                <?php endif; ?>
                return false;
            },

            eventResize: function(evt, delta, revertFunc) {
                <?php if (!empty($data['update_form']) && $data['calendars']['calendars_allow_edit'] == DB_BOOL_TRUE) : ?>
                    updateCalendar(evt);
                <?php endif; ?>
                return false;
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
