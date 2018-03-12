<?php 
// Map the calendar fields with the entity fields
$calendar_map = array();
$filterWhere = null;
foreach ($data['calendars_fields'] as $field) {
    $calendar_map[$field['calendars_fields_type']] = $field['fields_name'];
    
    // Recupero il campo filtro per applicare la fields_select_where
    if ($field['calendars_fields_type'] == 'filter' && trim($field['fields_select_where'])) {
        $filterWhere = $this->datab->replace_superglobal_data($field['fields_select_where']);
    }
}

if(!isset($calendar_map['id']) || !$calendar_map['id']) {
    $calendar_map['id'] = $data['calendars']['entity_name']."_id";
}

$element_id = (isset($value_id)? $value_id: NULL);
$calendarId = 'calendar' . $data['calendars']['calendars_id'];
?>
<div class="row">
    <div class="col-md-3">
        <?php if($data['calendars']['calendars_filter_entity_id']): ?>
            <?php $entity = $this->datab->get_entity($data['calendars']['calendars_filter_entity_id']); ?>
            <h3><?php echo ucwords($entity['entity_name']); ?></h3>
            
            <?php $filter_data = $this->datab->get_entity_preview_by_name($entity['entity_name'], $filterWhere); ?>
            <?php foreach($filter_data as $id => $nome): ?>
            <label class="checkbox">
                <input type="checkbox" name="cal_filter[]" class="js_check_filter" value="<?php echo $id; ?>" <?php if($entity['entity_name']==LOGIN_ENTITY && $id == $this->auth->get(LOGIN_ENTITY."_id")) echo 'checked'; ?> />
                <?php echo $nome; ?>
            </label>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="col-md-12 calendar_custom_area"></div>
    </div>
    
    <div class="col-md-9">
        <div <?php echo sprintf('id="%s"', $calendarId); ?> class="has-toolbar"></div>
    </div>
</div>
<script>
    
    $(function () {
        if (!jQuery().fullCalendar) {
            throw Error('Calendar not loaded');
        }
        
        var jqCalendar = $('#<?php echo $calendarId; ?>');
        var sourceUrl = "<?php echo base_url("get_ajax/get_calendar_events/{$data['calendars']['calendars_id']}/{$element_id}"); ?>";

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
            if (Metronic.isRTL()) {
                h = {
                    right: 'title',
                    center: '',
                    left: 'prev,next,today,month,agendaWeek,agendaDay'
                };
            } else {
                h = {
                    left: 'title',
                    center: '',
                    right: 'prev,next,today,month,agendaWeek,agendaDay'
                };
            }
        }

        jqCalendar.fullCalendar('destroy'); // destroy the calendar
        jqCalendar.fullCalendar({
            defaultView: 'agendaWeek',
            editable: true,
            selectable: true,
            disableDragging: false,
            header: h,
            
            monthNames: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
            monthNamesShort: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
            dayNames: ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'],
            dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'],
            firstDay: 1,
            buttonText: {
                today:    'Mostra oggi',
                month:    'Mese',
                week:     'Sett.',
                day:      'Giorno'
            },
            
            selectHelper: true,
            select: function(start, end, allDay) {
                var fStart = formatDate(start.toDate());    // formatted start
                var fEnd = formatDate(end.toDate());        // formatted end
                var allDay = isAlldayEvent(fStart, fEnd, 'DD/MM/YYYY HH:mm');
                var data = {<?php echo json_encode($calendar_map['start']) . ' : fStart, ' . json_encode($calendar_map['end']) . ' : fEnd, ' . (isset($calendar_map['all_day']) ? json_encode($calendar_map['all_day']) . ' : allDay? "t":"f"': ''); ?>};
                loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['create_form']}")); ?>, data, function() {
                    jqCalendar.fullCalendar('refetchEvents');
                }, 'get');
                
                if (allDay) {
                    end.date(end.date() + 1);
                    end.minutes(end.minutes() - 1);
                }
            },
            eventClick: function( event, jsEvent, view ) {
                loadModal(<?php echo json_encode(base_url("get_ajax/modal_form/{$data['update_form']}")); ?>+ '/' + event.id, {}, function() {
                    jqCalendar.fullCalendar('refetchEvents');
                });
                return false;
            },
            eventDrop: function( event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view ) {
                var allDay = isAlldayEvent(event.start, event.end);
                var fStart = event.start.format('DD/MM/YYYY HH:mm');    // formatted start
                var fEnd = event.end.format('DD/MM/YYYY HH:mm');        // formatted end
                var data = {<?php echo json_encode($calendar_map['id']).' : event.id,' . json_encode($calendar_map['start']).' : fStart, ' . json_encode($calendar_map['end']).' : fEnd, ' . (isset($calendar_map['all_day']) ? json_encode($calendar_map['all_day']).' : allDay? "t":"f"': ''); ?>};

                $.ajax({
                    url: "<?php echo base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"); ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function(data) {
                        if(parseInt(data.status) < 1) {
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
            eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
                var allDay = isAlldayEvent(event.start, event.end);
                var fStart = event.start.format('DD/MM/YYYY HH:mm');    // formatted start
                var fEnd = event.end.format('DD/MM/YYYY HH:mm');        // formatted end
                var data = {<?php echo json_encode($calendar_map['id']).' : event.id,' . json_encode($calendar_map['start']).' : fStart, ' . json_encode($calendar_map['end']).' : fEnd, ' . (isset($calendar_map['all_day']) ? json_encode($calendar_map['all_day']).' : allDay? "t":"f"': ''); ?>};

                
                $.ajax({
                    url: "<?php echo base_url("db_ajax/update_calendar_event/{$data['calendars']['calendars_id']}"); ?>",
                    type: 'POST',
                    dataType: 'json',
                    data: data,
                    success: function(data) {
                        if(parseInt(data.status) < 1) {
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
            eventSources: [
                {
                    url: sourceUrl,
                    type: 'POST',
                    data: {filters: [<?php echo $this->auth->get(LOGIN_ENTITY."_id"); ?>]},
                    error: function(error) {
                        console.log(error.responseText);
                    },
                    loading: function(bool) {
                        $('#loading').fadeTo(bool? 1: 0);
                    },
                    color: '#4B8DF8', // a non-ajax option
                    textColor: 'white' // a non-ajax option
                }
            ]
        });
        
        
        
        $('.js_check_filter').on('change', function() {
            var checkboxes = $('.js_check_filter').filter('[type=checkbox]');
            
            var values = [];
            checkboxes.each(function() {
                if($(this).is(':checked')) {
                    values.push($(this).val());
                }
            });
            
            jqCalendar.fullCalendar('removeEventSource', sourceUrl);
            jqCalendar.fullCalendar('addEventSource', {
                url: sourceUrl,
                type: 'POST',
                data: {filters: values},
                error: function(error) {
                    console.log(error.responseText);
                },
                loading: function(bool) {
                    $('#loading').fadeTo(bool? 1: 0);
                },
                color: '#4B8DF8', // a non-ajax option
                textColor: 'white' // a non-ajax option
            });
        });
    });
    
</script>
