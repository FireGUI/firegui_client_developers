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
    $calendar_map['id'] = $data['calendars']['entity_name'] . "_id";
}

$element_id = (isset($value_id) ? $value_id : NULL);
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

?>
<div class="row">
    <div class="col-lg-2 col-md-3">
        <?php if ($data['calendars']['calendars_filter_entity_id']) : ?>
            <?php $entity = $this->datab->get_entity($data['calendars']['calendars_filter_entity_id']); ?>
            <h3><?php echo ucwords($entity['entity_name']); ?></h3>

            <?php
            $main_entity = $data['calendars']['entity_name'];
            $where = ($filterWhere) ? "WHERE $filterWhere" : '';
            foreach ($data['calendars_fields'] as $_field) {
                if ($_field['calendars_fields_type'] == 'filter') {
                    $field_filter = $_field['fields_name'];
                }
            }
            if (!empty($field_filter)) {
                if ($filterWhereFilter) {
                    $filterWhereFilter .= "AND {$entity['entity_name']}_id IN (SELECT $field_filter FROM $main_entity $where)";
                } else {
                    $filterWhereFilter = "{$entity['entity_name']}_id IN (SELECT $field_filter FROM $main_entity $where)";
                }
            }
            //debug($filterWhereFilter);
            $filter_data = $this->datab->get_entity_preview_by_name($entity['entity_name'], $filterWhereFilter);


            $detailsLink = $this->datab->get_detail_layout_link($data['calendars']['calendars_filter_entity_id']);

            natcasesort($filter_data);
            ?>
            <?php foreach ($filter_data as $id => $nome) : ?>
                <?php if ($detailsLink) : ?>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="cal_filter[]" class="js_check_filter" value="<?php echo $id; ?>" <?php if ($entity['entity_name'] == LOGIN_ENTITY && $id == $this->auth->get(LOGIN_ENTITY . "_id")) echo 'checked'; ?> />
                            <?php echo anchor($detailsLink . '/' . $id, $nome, ['data-toggle' => 'tooltip', 'title' => 'Visualizza ' . $nome]); ?>
                        </label>
                    </div>
                <?php else : ?>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="cal_filter[]" class="js_check_filter" value="<?php echo $id; ?>" <?php if ($entity['entity_name'] == LOGIN_ENTITY && $id == $this->auth->get(LOGIN_ENTITY . "_id")) echo 'checked'; ?> />
                            <?php echo $nome; ?>
                        </label>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="calendar_custom_area"></div>
    </div>

    <div class="col-lg-10 col-md-9">
        <div <?php echo sprintf('id="%s"', $calendarId); ?> class="has-toolbar"></div>
    </div>
</div>

<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.15.1/moment-with-locales.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.0.1/fullcalendar.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.0.1/locale-all.js"></script>-->

<script>
    var jqCalendarView;
    $(function() {

        var jqCalendar = $('#<?php echo $calendarId; ?>');
        var sourceUrl = "<?php echo base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}/{$element_id}"); ?>";
        var minTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_min_time') ?: '06:00:00'); ?>;
        var maxTime = <?php echo json_encode(array_get($data['calendars'], 'calendars_max_time') ?: '22:00:00'); ?>;
        // ============================

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
        jqCalendarView = jqCalendar.fullCalendar({
            defaultView: 'agendaWeek',
            editable: true,
            selectable: true,
            disableDragging: false,
            height: 'auto',
            header: h,
            //            locale: 'it',
            lang: 'it',
            timeFormat: 'H:mm',
            axisFormat: 'H:mm',
            monthNames: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
            monthNamesShort: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
            dayNames: ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'],
            dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'],
            firstDay: 1,
            buttonText: {
                today: 'Mostra oggi',
                month: 'Mese',
                week: 'Sett.',
                day: 'Giorno',
            },
            timeFormat: 'H:mm',
            columnFormat: {
                agendaWeek: 'ddd D MMMM'
            },
            axisFormat: 'H:mm',
            minTime: minTime,
            maxTime: maxTime,
            allDayHtml: "<i class='far fa-clock'></i>",
            eventRender: function(event, element) {
                element.attr('data-id', event.id).css({
                    'margin-bottom': '1px',
                    'border': '1px solid #aaa'
                });
            },
            selectHelper: true,
            select: function(start, end, allDay) {
                var fStart = formatDate(start.toDate()); // formatted start
                var fEnd = formatDate(end.toDate()); // formatted end
                var allDay = isAlldayEvent(fStart, fEnd, 'DD/MM/YYYY HH:mm');
                var data = {
                    <?php echo json_encode($calendar_map['start']) . ' : fStart, ' . json_encode($calendar_map['end']) . ' : fEnd, ' . (isset($calendar_map['all_day']) ? json_encode($calendar_map['all_day']) . ' : allDay? "' . DB_BOOL_TRUE . '":"' . DB_BOOL_FALSE . '"' : ''); ?>
                };
                loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['create_form']}")); ?>, data, function() {
                    jqCalendar.fullCalendar('refetchEvents');
                }, 'get');

                if (allDay) {
                    end.date(end.date() + 1);
                    end.minutes(end.minutes() - 1);
                }
            },
            eventClick: function(event, jsEvent, view) {
                loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['update_form']}")); ?> + '/' + event.id, {}, function() {
                    jqCalendar.fullCalendar('refetchEvents');
                });
                return false;
            },
            eventDrop: function(event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {
                var allDay = isAlldayEvent(event.start, event.end);
                var fStart = event.start.format('DD/MM/YYYY HH:mm'); // formatted start
                var fEnd = event.end.format('DD/MM/YYYY HH:mm'); // formatted end
                var data = {
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
            eventResize: function(event, dayDelta, minuteDelta, revertFunc) {
                var allDay = isAlldayEvent(event.start, event.end);
                var fStart = event.start.format('DD/MM/YYYY HH:mm'); // formatted start
                var fEnd = event.end.format('DD/MM/YYYY HH:mm'); // formatted end
                var data = {
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
                data: function() {
                    var values = [];
                    $('.js_check_filter').filter('[type=checkbox]:checked').each(function() {
                        values.push($(this).val());
                    });
                    return {
                        filters: values
                    };
                },
                error: function(error) {
                    console.log(error.responseText);
                },
                loading: function(bool) {
                    $('#loading').fadeTo(bool ? 1 : 0);
                },
                color: '#4B8DF8', // a non-ajax option
                textColor: 'white' // a non-ajax option
            }],
            viewRender: function(view) {
                window.sessionStorage.setItem(sessionStorageKey, JSON.stringify({
                    view: view.name,
                    date: jqCalendar.fullCalendar('getDate').toISOString()
                }));
            }
        });



        $('.js_check_filter').on('change', function() {
            jqCalendar.fullCalendar('refetchEvents');
        });


        // Ripristina sessione
        var sessionStorageKey = jqCalendar.attr('id');

        try {
            var calendarSession = JSON.parse(window.sessionStorage.getItem(sessionStorageKey));
            jqCalendar.fullCalendar('changeView', calendarSession.view);
            jqCalendar.fullCalendar('gotoDate', calendarSession.date);
        } catch (e) {
            // ... skip ...
        }
    });
</script>