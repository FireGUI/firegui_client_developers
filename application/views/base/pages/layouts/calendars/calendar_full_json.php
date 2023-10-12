<?php

//calendars_where_filter

// Map the calendar fields with the entity fields
$calendar_map = [];
$filterWhere = $filterWhereFilter = null;
foreach ($data['calendars_fields'] as $field) {
    $calendar_map[$field['calendars_fields_type']] = $field['fields_name'];
    
    // Recupero il campo filtro per applicare la fields_select_where
    if ($field['calendars_fields_type'] == 'filter' && trim($field['fields_select_where'])) {
        $filterWhere = $this->datab->replace_superglobal_data($field['fields_select_where']);
    }
}

if (!isset($calendar_map['id']) || !$calendar_map['id']) {
    $calendar_map['id'] = $data['calendars']['entity_name'] . '_id';
}

$element_id = (isset($value_id) ? $value_id : null);
$calendarId = 'calendar' . $data['calendars']['calendars_id'];
$data['calendars']['calendars_where'] = trim($data['calendars']['calendars_where']);
if (!empty($data['calendars']['calendars_where'])) {
    $add_where = $this->datab->replace_superglobal_data($data['calendars']['calendars_where']);
    if (empty($filterWhere)) {
        $filterWhere = $add_where;
    } else {
        $filterWhere .= ' AND ' . $add_where;
    }
}

// TODO INSERITA CHIOCCIOLA PERCHé DAVA ERRORE E FORSE MANCA PROPRIO IL CAMPO calendars_where_filter SU DB !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

@$data['calendars']['calendars_where_filter'] = trim($data['calendars']['calendars_where_filter']);
if (!empty($data['calendars']['calendars_where_filter'])) {
    $add_where = $this->datab->replace_superglobal_data($data['calendars']['calendars_where_filter']);
    if (empty($filterWhereFilter)) {
        $filterWhereFilter = $add_where;
    } else {
        $filterWhereFilter .= ' AND ' . $add_where;
    }
}
$settings = $this->db->join('languages', 'languages_id = settings_default_language', 'LEFT')->get('settings')->row_array();
$create_permission = (!empty($data['create_form']) && $data['calendars']['calendars_allow_create'] == DB_BOOL_TRUE) ? DB_BOOL_TRUE : DB_BOOL_FALSE;
$edit_permission = (!empty($data['update_form']) && $data['calendars']['calendars_allow_edit'] == DB_BOOL_TRUE) ? DB_BOOL_TRUE : DB_BOOL_FALSE;
$calendars_default_view = (!empty($data['cal_layout']['calendars_default_view'])) ? $data['cal_layout']['calendars_default_view'] : 'timeGridWeek';
$filter_default_view = (!empty($data['cal_layout']) && $data['cal_layout']['calendars_default_sidebar_toggle_all_filters'] == DB_BOOL_TRUE) ? DB_BOOL_TRUE : DB_BOOL_FALSE;
$url_parameters = (!empty($data['create_form'])) ? $data['cal_layout']['calendars_link'] : '';

$attributes = [
    'id' => $calendarId,
    'data-calendar' => base64_encode(json_encode($data['calendars'])),
    'class' => 'has-toolbar calendar_full_json',
    'data-view' => $calendars_default_view,
    'data-allow_create' => $create_permission,
    'data-allow_edit' => $edit_permission,
    'data-sourceurl' => base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}/{$element_id}"),
    'data-mintime' => (array_get($data['calendars'], 'calendars_min_time') ?: '06:00:00'),
    'data-maxtime' => (array_get($data['calendars'], 'calendars_max_time') ?: '22:00:00'),
    'data-language' => (!empty($settings['languages_code'])) ? (explode('-', $settings['languages_code'])[0]) : 'en',
    'data-allday' => $calendar_map['all_day'] ?? '',
    'data-formurl' => base_url("get_ajax/modal_form/{$data['create_form']}"),
    'data-formedit' => base_url("get_ajax/modal_form/{$data['update_form']}"),
    'data-updateurl' => base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"),
    'data-fieldid' => $calendar_map['id'],
    'data-url-parameters' => $url_parameters,
];

if (array_key_exists('start', $calendar_map)) {
    $attributes['data-start'] = $calendar_map['start'];
    $attributes['data-start-is-datetime'] = true;
} elseif (array_key_exists('date_start', $calendar_map)) {
    $attributes['data-start'] = $calendar_map['date_start'];
    $attributes['data-start-is-datetime'] = false;
}

if (array_key_exists('end', $calendar_map)) {
    $attributes['data-end'] = $calendar_map['end'];
    $attributes['data-end-is-datetime'] = true;
} elseif (array_key_exists('date_end', $calendar_map)) {
    $attributes['data-end'] = $calendar_map['date_end'];
    $attributes['data-end-is-datetime'] = false;
}

$attributesString = '';
foreach ($attributes as $key => $value) {
    $attributesString .= sprintf('%s="%s" ', $key, $value);
}

?>

<style>
    .scrollable {
        max-height: 600px;
        overflow-y: scroll;
    }
</style>

<style>
    .fc-scroller {
        height: 100% !important;
    }
    
    .total-label {
        font-size: 1.2rem;
        font-weight: bold;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <div <?php echo $attributesString; ?>></div>
    </div>
</div>

<script>
    function initFullCalendarJson() {
        $('.calendar_full_json').each(function () {
            var jqCalendar = $(this);
            var calendar_data = JSON.parse(atob($(this).data('calendar')));
            var calendar_id = jqCalendar.attr('id');
            var sourceUrl = $(this).data('sourceurl');
            var minTime = $(this).data('mintime');
            var maxTime = $(this).data('maxtime');
            var language = $(this).data('language');
            var startField = $(this).data('start');
            var endField = $(this).data('end');
            var alldayfield = $(this).data('allday');
            var formurl = $(this).data('formurl');
            var url_parameters = $(this).data('url-parameters');
            var formedit = $(this).data('formedit');
            var fieldid = $(this).data('fieldid');
            var updateurl = $(this).data('updateurl');
            var allow_create = $(this).data('allow_create');
            var allow_edit = $(this).data('allow_edit');
            var calendars_default_view = $(this).data('view');
            var isStartDateTime = $(this).data('start-is-datetime')
            var isEndDateTime = $(this).data('end-is-datetime')
            // ============================
        
            var token = JSON.parse(atob($('body').data('csrf')));
            var token_name = token.name;
            var token_hash = token.hash;
        
            var date = new Date();
            var d = date.getDate();
            var m = date.getMonth();
            var y = date.getFullYear();
        
            var updateCalendar = function (evt) {
                if (allow_edit) {
                    var allDay = isAlldayEvent(evt.event.start, evt.event.end);
                    var fStart = moment(evt.event.start).format('DD/MM/YYYY HH:mm'); // formatted start
                    var fEnd = moment(evt.event.end).format('DD/MM/YYYY HH:mm'); // formatted end
                    var data = {
                        [token_name]: token_hash,
                        [fieldid]: evt.event.id,
                        [startField]: fStart,
                        [endField]: fEnd
                        //TODO: manage all days events
                    };
                
                    $.ajax({
                        url: updateurl,
                        type: 'POST',
                        dataType: 'json',
                        data: data,
                        success: function (data) {
                            if (parseInt(data.status) < 1) {
                                // revertFunc();
                                alert(data.txt);
                            }
                        },
                        error: function () {
                            // revertFunc();
                            alert('There was an error while saving the event');
                        },
                    });
                }
            }
        
            var calendarEl = document.getElementById(calendar_id);
        
            $('#' + calendar_id).html('');
            var defaultView = (typeof localStorage.getItem('fcDefaultView_' + calendar_id) !== 'undefined' && localStorage.getItem('fcDefaultView_' + calendar_id) !== null) ? localStorage.getItem('fcDefaultView_' + calendar_id) : calendars_default_view;
        
            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['interaction', 'dayGrid', 'timeGrid'],
                defaultView: defaultView,
                defaultDate: moment().format('YYYY-MM-DD HH:mm'),
                header: {
                    left: 'title',
                    right: 'prev,next,dayGridMonth,timeGridWeek,timeGridDay'
                },
                datesRender: function (info, el) {
                    localStorage.setItem('fcDefaultView_' + calendar_id, info.view.type);
                },
                editable: true,
                selectable: true,
                disableDragging: false,
                weekNumbers: true,
                height: 'auto',
                locale: language,
                timeFormat: 'H:mm',
                axisFormat: 'H:mm',
                forceEventDuration: true, // @links: https://github.com/fullcalendar/fullcalendar/issues/2655#issuecomment-223838926
                columnFormat: {
                    agendaWeek: 'ddd D MMMM'
                },
                minTime: minTime,
                maxTime: maxTime,
                allDayHtml: "<i class='far fa-clock'></i>",
                selectHelper: true,
                select: function (date) {
                    if (allow_create) {
                        var fStart = moment(date.start); // formatted start
                        var fEnd = moment(date.end);
        
                        if (date.allDay) {
                            fEnd = moment(date.start).add(1, 'hours');
                        }
        
                        if (isStartDateTime) {
                            fStart = fStart.format('DD/MM/YYYY HH:mm');
                        } else {
                            fStart = fStart.format('DD/MM/YYYY');
                        }
        
                        if (isEndDateTime) {
                            fEnd = fEnd.format('DD/MM/YYYY HH:mm');
                        } else {
                            fEnd = fEnd.format('DD/MM/YYYY');
                        }
        
                        var data = {};
        
                        data[startField] = fStart;
        
                        if (endField) {
                            data[endField] = fEnd;
                        }
        
                        if (alldayfield && typeof date.allDay !== 'undefined') {
                            data[alldayfield] = date.allDay;
                        }
        
                        loadModal(formurl + url_parameters, data, function () {
                            calendar.refetchEvents();
                        }, 'get');
                    }
                },
                eventClick: function (evt, jsEvent, view) {
                    if (calendar_data.calendars_event_click === 'form' && allow_edit) {
                        loadModal(formedit + '/' + evt.event.id, {}, function () {
                            calendar.refetchEvents();
                        });
                    } else if (calendar_data.calendars_event_click === 'layout' && calendar_data.calendars_layout_id.length > 0) {
                        if (calendar_data.calendars_layout_modal == true) {
                            loadModal(base_url + 'get_ajax/layout_modal/' + calendar_data.calendars_layout_id + '/' + evt.event.id, {}, function () {
                                calendar.refetchEvents();
                            });
                        } else {
                            window.location.href = base_url + 'main/layout/' + calendar_data.calendars_layout_id + '/' + evt.event.id;
                        }
                    } else if (calendar_data.calendars_event_click === 'link' && calendar_data.calendars_link.length > 0) {
                        var link = calendar_data.calendars_link;
                    
                        var link = link.replace('{base_url}/', base_url);
                        var link = link.replace('{value_id}', evt.event.id);
                    
                        window.location.href = link;
                    }
                
                    return false;
                },
            
                eventDrop: function (evt) {
                    updateCalendar(evt);
                },
            
                eventResize: function (evt, delta, revertFunc) {
                    updateCalendar(evt);
                },
                eventSources: [{
                    events: function (fetchInfo, successCallback, failureCallback) {
                        $.ajax({
                            type: 'POST',
                            url: sourceUrl,
                            dataType: 'json',
                            data: {
                                [token_name]: token_hash,
                                'start': moment(fetchInfo.start).format('YYYY-MM-DD HH:mm'),
                                'end': moment(fetchInfo.end).format('YYYY-MM-DD HH:mm')
                            },
                            loading: function (bool) {
                                $('#loading').fadeTo(bool ? 1 : 0);
                            },
                            success: function (response) {
                                successCallback(response);
                            },
                            error: function (response) {
                                console.log(response);
                                failureCallback(response);
                            },
                        });
                    },
                    color: '#4B8DF8', // a non-ajax option
                    textColor: 'white' // a non-ajax option
                }],
                eventRender: function (info) {
                    if (typeof info.event.extendedProps.description !== 'undefined' && info.event.extendedProps.description) {
                        $(info.el).popover({
                            title: info.event.title,
                            content: info.event.extendedProps.description,
                            placement: 'top',
                            container: 'body',
                            trigger: 'hover',
                            html: true
                        });
                    }
                },
            });
        
            calendar.render();
        });
    }
    
    $(function() {
        initFullCalendarJson();
    })
</script>
