(function($) {
    $.fn.weekly_schedule = function(callerSettings) {

        var settings = $.extend({
            days: ["sun", "mon", "tue", "wed", "thu", "fri", "sat"], // Days displayed
            hours: "7:00-22:00", // Hours displayed
            fontFamily: "Montserrat", // Font used in the component
            fontColor: "black", // Font colot used in the component
            fontWeight: "100", // Font weight used in the component
            fontSize: "0.8em", // Font size used in the component
            hoverColor: "#727bad", // Background color when hovered
            selectionColor: "#9aa7ee", // Background color when selected
            headerBackgroundColor: "transparent", // Background color of headers
            onSelected: function(){}, // handler called after selection
            onRemoved: function(){} // handler called after removal
        }, callerSettings||{});

        /* set range here! */
        settings.hoursParsed = parseHours("7:00-22:00");

        var schedule = this;

        if (typeof callerSettings == 'string') {
            switch (callerSettings) {
                case 'getSelectedHour':
                    return getSelectedHour();
                default:
                    throw 'Weekly schedule method unrecognized!'
            }
        }
        // options is an object, initialize!
        else {
            var days = settings.days; // option
            var hours = settings.hoursParsed; // option

            $(schedule).addClass('schedule');

            // Create Header
            var dayHeaderContainer = $('<div></div>', {
                class: "header"
            });

            var align_block = $('<div></div>', {
                class: "align-block"
            });

            dayHeaderContainer.append(align_block);

            // Insert header items
            for (var i = 0; i < days.length; ++i) {
                var day_header = $('<div></div>', {
                    class: "header-item " + days[i] + "-header"
                });
                var header_title = $('<h3>' + capitalize(days[i]) + '</h3>')

                day_header.append(header_title);
                dayHeaderContainer.append(day_header);
            }


            var days_wrapper = $('<div></div>', {
                class: 'days-wrapper'
            });

            var hourHeaderContainer = $('<div></div>', {
                class: 'hour-header'
            });

            days_wrapper.append(hourHeaderContainer);

            for (var i = 0; i < hours.length; i++) {
                var hour_header_item = $('<div></div>', {
                    class: 'hour-header-item ' + hours[i]
                });

                var header_title = $('<h5>' + hours[i] +'</h5>');

                hour_header_item.append(header_title);
                hourHeaderContainer.append(hour_header_item);
            }



            for(var i = 0; i < days.length; i++) {
                var day = $('<div></div>', {
                    class: "day " + days[i]
                });

                for(var j = 0; j < hours.length; j++) {
                    var hour = $('<div></div>', {
                        class: "hour " + hours[j],
                        id: days[i] + hours[j].split(':')[0] + hours[j].split(':')[1]
                    });

                    day.append(hour);
                }

                days_wrapper.append(day);
            }

            /*
             * Inject objects
             */

            $(schedule).append(dayHeaderContainer);
            $(schedule).append(days_wrapper);


            /*
             *  Style Everything
             */
            $('.schedule').css({
                width: "100%",
                display: "flex",
                flexDirection: "column",
                justifyContent: "flex-start"
            });

            $('.header').css({
                height: "30px",
                width: "90%",
                background: settings.headerBackgroundColor,
                marginBottom: "5px",
                display: "flex",
                flexDirection: "row"
            });
            $('.align-block').css({
                width: "100%",
                height: "100%",
                background: settings.headerBackgroundColor,
                margin: "3px"
            });

            // Style Header Items
            $('.header-item').css({
                width: '100%',
                height: '100%',
                margin: '2px' // option
            });
            $('.header-item h3').css({
                margin: 0,
                textAlign: 'center',
                verticalAlign: 'middle',
                position: "relative",
                top: "50%",
                color: settings.fontColor,
                fontFamily: settings.fontFamily,
                fontSize: settings.fontSize,
                fontWeight: settings.fontWeight,
                transform: "translateY(-50%)",
                userSelect: "none"
            });

            $('.hour-header').css({
                display: 'flex',
                flexDirection: 'column',
                margin: '2px', // option
                marginRight: '1px',
                background: settings.headerBackgroundColor,
                width: '100%'
            });

            $('.days-wrapper').css({
                width: "90%",
                height: "100%",
                background: "transparent", //option
                display: "flex",
                flexDirection: "row",
                justifyContent: "flex-start",
                position: "relative"
            });

            $('.hour-header-item').css({
                width: "100%",
                height: "100%",
                border: "none", // option
                borderColor: "none", // option
                borderStyle: "none" // option
            });
            $('.hour-header-item h5').css({
                color: settings.fontColor,
                margin: "0", // option
                textAlign: "right",
                verticalAlign: "middle",
                position: "relative",
                fontFamily: settings.fontFamily,
                fontSize: settings.fontSize,
                fontWeight: settings.fontWeight,
                paddingRight: "10%",
                userSelect: "none",
                paddingTop: "8px",
            });

            $('.day').css({
                display: "flex",
                flexDirection: "column",
                marginRight: "1px", // option
                marginBottom: "1px",
                background: "transparent", // option
                width: "100%"
            });
            $('.hour').css({
                background: "#dddddd", // option
                marginBottom: "1px", // option
                width: "100%",
                height: "35px",
                userSelect: "none"
            });

            /*
             * Hook eventlisteners
             */
            $("<style type='text/css' scoped> .hover { background: "
                + settings.hoverColor +
                " !important;} .selected { background: "
                + settings.selectionColor +
                " !important; } .disabled { pointer-events: none !important; opacity: 0.3 !important; box-shadow: none !important; } " +
                ".selected-now { background: #9dff7c !important;} " +
                ".selected-mine {background: #FF0015 !important;}</style>")
                .appendTo(schedule);

            // Prevent Right Click
            $('.schedule').on('contextmenu', function() {
                return false;
            });
        }

        function parseHours(string) {
            var output = [];

            var split = string.toUpperCase().split("-");
            var startInt = parseInt(split[0].split(":")[0]);
            var endInt = parseInt(split[1].split(":")[0]);

            var startHour = split[0].includes("PM") ? startInt + 12 : startInt;
            var endHour = split[1].includes("PM") ? endInt + 12 : endInt;

            var curHour = startHour;

            for (curHour; curHour <= endHour; curHour++) {
                var parsedStr = "";

                parsedStr += curHour.toString() + ":00";

                output.push(parsedStr);
            }

            return output;
        }

        function capitalize(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

    };
}(jQuery));

function getSelectedHour() {
    let dayContainer = $('.day');
    let output = {};
    for (let i = 0; i < dayContainer.length; i++) {
        let children = $(dayContainer[i]).children();

        let hoursSelected = [];
        for (let j = 0; j < children.length; j++) {
            if ($(children[j]).hasClass('selected-now')) {
                hoursSelected.push(children[j].getAttribute('class').split(' ')[1]);
            }
        }
        output[i] = hoursSelected;
    }
    return output;
}

let outputArray = [];
let index = [];
$(document).ready(function () {

    initSchedule(window.countRooms, window.schedules);


    $('#frm-courseForm-room').on('change', function () {
        let values = [];
        $('#frm-courseForm-room option:selected').each(function(i, sel){
            values.push($(sel).val());
        });

        $.nette.ajax({
            type: 'GET',
            url: window.changeRoom,
            data: {
                'roomIds': values
            },
            success: function () {
                setTimeout(function(){
                    index = [];
                    initSchedule(window.countRooms, window.schedules);
                    }, 1);
            },
        });

    });

    $('#saveCourse').click(function() {
        console.log(index);
        $.nette.ajax({
            type: 'POST',
            url: window.selectHours,
            data: {
                'hours': outputArray,
                'roomIds' : index,
            },
        });
    });
});

function changeSchedule(scheduleId, schedule) {
    if (typeof(schedule) != "undefined" && schedule !== null && schedule !== "") {
        schedule = JSON.parse(schedule);
        // MONDAY
        if (typeof(schedule[0]) != "undefined" && schedule[0] !== null) {
            let len = schedule[0].length;
            for (let i = 0; i < len; i++) {
                let hours = schedule[0][i].split(':');
                $(scheduleId + " #Po" + (hours[0]) + (hours[1])).addClass('selected');
            }
        }

        // TUESDAY
        if (typeof(schedule[1]) != "undefined" && schedule[1] !== null) {
            let len = schedule[1].length;
            for (let i = 0; i < len; i++) {
                let hours = schedule[1][i].split(':');
                $(scheduleId + " #Ut" + (hours[0]) + (hours[1])).addClass('selected');
            }
        }

        // WEDNESDAY
        if (typeof(schedule[2]) != "undefined" && schedule[2] !== null) {
            let len = schedule[2].length;
            for (let i = 0; i < len; i++) {
                let hours = schedule[2][i].split(':');
                $(scheduleId + " #St" + (hours[0]) + (hours[1])).addClass('selected');
            }
        }

        // THURSDAY
        if (typeof(schedule[3]) != "undefined" && schedule[3] !== null) {
            let len = schedule[3].length;
            for (let i = 0; i < len; i++) {
                let hours = schedule[3][i].split(':');
                $(scheduleId + " #Št" + (hours[0]) + (hours[1])).addClass('selected');
            }
        }

        // FRIDAY
        if (typeof(schedule[4]) != "undefined" && schedule[4] !== null) {
            let len = schedule[4].length;
            for (let i = 0; i < len; i++) {
                let hours = schedule[4][i].split(':');
                $(scheduleId + " #Pia" + (hours[0]) + (hours[1])).addClass('selected');
            }
        }
    }
}

function initSchedule(countRooms, schedules)
{
    let value;
    for (let i = 1; i < countRooms + 1; i++) {
        if (typeof(schedules[i]) != "undefined" && schedules[i] !== null) {
            index.push(i);
            $('#mySchedule_'+i).weekly_schedule({
                days: ["Po", "Ut", "St", "Št", "Pia"],
                fontColor: "black",
                fontWeight: "100",
                fontSize: "0.8em",
                hoverColor: "#0091bf",
                selectionColor: "#00A9E4",
                headerBackgroundColor: "transparent",
            });
            value = $('#hours_'+i).text();
            changeSchedule('#mySchedule_'+i, value);
        }
    }

    let mousedown = false;
    let devarionMode = false;
    let id;

    $('#mySchedule_' + index[0] + ' .hour').on('mouseenter', function() {
        id = $(this).attr('id');
        if (!mousedown) {
            $(this).addClass('hover');
            for (let i = 1; i < index.length; i++) {
                $('#mySchedule_'+ index[i] + ' #' + id).addClass('hover');
            }
        }
        else {
            if (devarionMode) {
                $(this).removeClass('selected-now');
                for (let i = 1; i < index.length; i++) {
                    if (!$('#mySchedule_'+ index[i] + ' #' + id).hasClass('selected')) {
                        $('#mySchedule_'+ index[i] + ' #' + id).removeClass('selected-now');
                    }
                }
                outputArray = getSelectedHour();
            }
            else {
                $(this).addClass('selected-now');
                for (let i = 1; i < index.length; i++) {
                    if ($('#mySchedule_'+ index[i] + ' #' + id).hasClass('selected')) {
                        $(this).removeClass('selected-now');
                        alert('Nie je možné vybrať miestnosť v tejto hodine.')
                    } else {
                        $('#mySchedule_'+ index[i] + ' #' + id).addClass('selected-now');
                    }
                }
                outputArray = getSelectedHour();
            }
        }
    }).on('mousedown', function() {
        mousedown = true;

        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected-now');
            for (let i = 1; i < index.length; i++) {
                if (!$('#mySchedule_'+ index[i] + ' #' + id).hasClass('selected')) {
                    $('#mySchedule_'+ index[i] + ' #' + id).removeClass('selected-now');
                }
            }
            devarionMode = true;
            outputArray = getSelectedHour();
        }
        else {
            $(this).addClass('selected-now');
            for (let i = 1; i < index.length; i++) {
                if ($('#mySchedule_'+ index[i] + ' #' + id).hasClass('selected')) {
                    $(this).removeClass('selected-now');
                    alert('Nie je možné vybrať miestnosť v tejto hodine.')
                } else {
                    $('#mySchedule_'+ index[i] + ' #' + id).addClass('selected-now');
                }
            }
            outputArray = getSelectedHour();
        }
        $(this).removeClass('hover');
    }).on('mouseup', function() {
        devarionMode = false;
        mousedown = false;
    }).on('mouseleave', function () {
        if (!mousedown) {
            $(this).removeClass('hover');
            for (let i = 1; i < index.length; i++) {
                $('#mySchedule_'+ index[i] + ' #' + id).removeClass('hover');
            }
        }
    });
}



