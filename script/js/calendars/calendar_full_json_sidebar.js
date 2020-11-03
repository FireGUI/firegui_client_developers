$(function () {
    'use strict';
    $('.calendar_full_json_sidebar').each(function () {
        var jqCalendarView;


        var jqCalendar = $(this);
        var sourceUrl = $(this).data('sourceurl');
        var minTime = $(this).data('mintime');
        var maxTime = $(this).data('maxtime');
        var language = $(this).data('language');
        var startField = $(this).data('start');
        var endField = $(this).data('end');
        var allday = $(this).data('allday');
        var formurl = $(this).data('formurl');
        var formedit = $(this).data('formedit');
        var fieldid = $(this).data('formedit');
        var updateurl = $(this).data('updateurl');
        // ============================

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
        jqCalendarView = jqCalendar.fullCalendar({
            defaultView: 'agendaWeek',
            editable: true,
            selectable: true,
            disableDragging: false,
            height: 'auto',
            header: h,
            //            locale: 'it',
            lang: language,
            timeFormat: 'H:mm',
            axisFormat: 'H:mm',
            timeFormat: 'H:mm',
            columnFormat: {
                agendaWeek: 'ddd D MMMM'
            },
            axisFormat: 'H:mm',
            minTime: minTime,
            maxTime: maxTime,
            allDayHtml: "<i class='far fa-clock'></i>",
            eventRender: function (event, element) {
                element.attr('data-id', event.id).css({
                    'margin-bottom': '1px',
                    'border': '1px solid #aaa'
                });
            },
            selectHelper: true,
            select: function (start, end, allDay) {
                var fStart = formatDate(start.toDate()); // formatted start
                var fEnd = formatDate(end.toDate()); // formatted end
                var allDay = isAlldayEvent(fStart, fEnd, 'DD/MM/YYYY HH:mm');
                var data = {
                    [token_name]: token_hash,
                    [startField]: fStart,
                    [endField]: fEnd
                    //TODO: manage all days events
                };

                loadModal(formurl, data, function () {
                    jqCalendar.fullCalendar('refetchEvents');
                }, 'get');

                if (allDay) {
                    end.date(end.date() + 1);
                    end.minutes(end.minutes() - 1);
                }
            },
            eventClick: function (event, jsEvent, view) {
                loadModal(formedit + '/' + event.id, {}, function () {
                    jqCalendar.fullCalendar('refetchEvents');
                });
                return false;
            },
            eventDrop: function (event, dayDelta, minuteDelta, allDay, revertFunc, jsEvent, ui, view) {
                var allDay = isAlldayEvent(event.start, event.end);
                var fStart = event.start.format('DD/MM/YYYY HH:mm'); // formatted start
                var fEnd = event.end.format('DD/MM/YYYY HH:mm'); // formatted end
                var data = {
                    [token_name]: token_hash,
                    [fieldid]: event.id,
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
                            revertFunc();
                            alert(data.txt);
                        }
                    },
                    error: function () {
                        revertFunc();
                        alert('There was an error while saving the event');
                    },
                });
            },
            eventResize: function (event, dayDelta, minuteDelta, revertFunc) {
                var allDay = isAlldayEvent(event.start, event.end);
                var fStart = event.start.format('DD/MM/YYYY HH:mm'); // formatted start
                var fEnd = event.end.format('DD/MM/YYYY HH:mm'); // formatted end
                var data = {
                    [token_name]: token_hash,
                    [fieldid]: event.id,
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
                            revertFunc();
                            alert(data.txt);
                        }
                    },
                    error: function () {
                        revertFunc();
                        alert('There was an error while saving the event');
                    },
                });
            },
            eventSources: [{
                url: sourceUrl,
                type: 'POST',
                data: function () {
                    var values = [];
                    $('.js_check_filter').filter('[type=checkbox]:checked').each(function () {
                        values.push($(this).val());
                    });
                    return {
                        filters: values,
                        [token_name]: token_hash
                    };
                },
                error: function (error) {
                    console.log(error.responseText);
                },
                loading: function (bool) {
                    $('#loading').fadeTo(bool ? 1 : 0);
                },
                color: '#4B8DF8', // a non-ajax option
                textColor: 'white' // a non-ajax option
            }],
            viewRender: function (view) {
                window.sessionStorage.setItem(sessionStorageKey, JSON.stringify({
                    view: view.name,
                    date: jqCalendar.fullCalendar('getDate').toISOString()
                }));
            }
        });



        $('.js_check_filter').on('change', function () {
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
});