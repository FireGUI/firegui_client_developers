function initCalendars() {
    $(function() {
        'use strict';
        $('.calendar_full_json_sidebar').each(function() {
            var jqCalendarView;

            var jqCalendar = $(this);
            var calendar_id = jqCalendar.attr('id');
            var sourceUrl = $(this).data('sourceurl');
            var minTime = $(this).data('mintime');
            var maxTime = $(this).data('maxtime');
            var language = $(this).data('language');
            var startField = $(this).data('start');
            var endField = $(this).data('end');
            var allday = $(this).data('allday');
            var formurl = $(this).data('formurl');
            var formedit = $(this).data('formedit');
            var fieldid = $(this).data('fieldid');
            var updateurl = $(this).data('updateurl');
            var allow_create = $(this).data('allow_create');
            var allow_edit = $(this).data('allow_edit');
            var calendars_default_view = $(this).data('view');
            
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
                        success: function(data) {
                            if (parseInt(data.status) < 1) {
                                // revertFunc();
                                alert(data.txt);
                            }
                        },
                        error: function() {
                            // revertFunc();
                            alert('There was an error while saving the event');
                        },
                    });
                }
            }

            var calendarEl = document.getElementById(calendar_id);

            $('#' + calendar_id).html('');
            var defaultView = (typeof localStorage.getItem("fcDefaultView_"+calendar_id) !== 'undefined' && localStorage.getItem("fcDefaultView_"+calendar_id) !== null) ? localStorage.getItem("fcDefaultView_"+calendar_id) : calendars_default_view;

            var calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['interaction', 'dayGrid', 'timeGrid'],
                defaultView: defaultView,
                defaultDate: moment().format('YYYY-MM-DD HH:mm'),
                header: {
                    left: 'title',
                    right: 'prev,next,dayGridMonth,timeGridWeek,timeGridDay'
                },
                datesRender: function(info, el)
                {
                    localStorage.setItem("fcDefaultView_"+calendar_id, info.view.type);
                },
                editable: true,
                selectable: true,
                disableDragging: false,
                height: 'auto',
                locale: language,
                timeFormat: 'H:mm',
                axisFormat: 'H:mm',
                timeFormat: 'H:mm',
                forceEventDuration: true, // @links: https://github.com/fullcalendar/fullcalendar/issues/2655#issuecomment-223838926
                columnFormat: {
                    agendaWeek: 'ddd D MMMM'
                },
                axisFormat: 'H:mm',
                minTime: minTime,
                maxTime: maxTime,
                allDayHtml: "<i class='far fa-clock'></i>",
                // eventRender: function (event, element) {
                //     element.attr('data-id', event.id).css({
                //         'margin-bottom': '1px',
                //         'border': '1px solid #aaa'
                //     });
                // },
                selectHelper: true,
                select: function(date, allDay) {
                    if (allow_create) {
                        var fStart = moment(date.start).format('DD/MM/YYYY HH:mm'); // formatted start
                        var fEnd = moment(date.end).format('DD/MM/YYYY HH:mm'); // formatted end

                        var allDay = isAlldayEvent(fStart, fEnd, 'DD/MM/YYYY HH:mm');
                        var data = {
                            [token_name]: token_hash,
                            [startField]: fStart,
                            [endField]: fEnd
                                //TODO: manage all days events
                        };

                        loadModal(formurl, data, function() {
                            calendar.refetchEvents();
                        }, 'get');

                        if (allDay) {
                            end.date(end.date() + 1);
                            end.minutes(end.minutes() - 1);
                        }
                    }
                },
                eventClick: function (evt, jsEvent, view) {
                    if (allow_edit) {
                        loadModal(formedit + '/' + evt.event.id, {}, function() {
                            calendar.refetchEvents();
                        });
                    }

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
                        var values = [];
                        $('.js_check_filter').filter('[type=checkbox]:checked').each(function() {
                            if($(this).val()!= 0){
                                values.push($(this).val());
                            }
                        });
                        
                        $.ajax({
                            type: 'POST',
                            url: sourceUrl,
                            dataType: 'json',
                            data: {
                                filters: values,
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
                viewRender: function(view) {
                    // window.sessionStorage.setItem(sessionStorageKey, JSON.stringify({
                    //     view: view.name,
                    //     date: jqCalendar.fullCalendar('getDate').toISOString()
                    // }));
                }
            });

            calendar.render();

            $('.js_check_filter').on('change', function() {
                $('#select-all').click(function(event) {   
                    if(this.checked) {
                        // Iterate each checkbox
                        $(':checkbox').each(function() {
                            this.checked = true;                        
                        });
                    } else {
                        $(':checkbox').each(function() {
                            this.checked = false;                       
                        });
                    }
                }); 
                calendar.refetchEvents();
            });

            // Ripristina sessione
            var sessionStorageKey = calendar_id;

            // try {
            //     var calendarSession = JSON.parse(window.sessionStorage.getItem(sessionStorageKey));
            //     jqCalendar.fullCalendar('changeView', calendarSession.view);
            //     jqCalendar.fullCalendar('gotoDate', calendarSession.date);
            // } catch (e) {
            //     // ... skip ...
            // }

        });
    });
}
