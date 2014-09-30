<div id="calendar"></div>
<script>
<?php after_load('css', '/dist/calendar/fullcalendar.min.css')?>
<?php after_load('js', '/dist/js/moment-with-langs.min.js')?>
<?php after_load('js', '/dist/calendar/fullcalendar.min.js')?>
<?php after_load('js', '/dist/calendar/lang-all.js')?>

<?php after_load('js', '/dist/js/jquery.cookie.js')?>

<?php after_load('js', '/dist/js/bootstrap-datetimepicker.min.js')?>
<?php after_load('js', '/dist/js/bootstrap-datetimepicker.ru.js')?>
<?php after_load('css', '/dist/css/bootstrap-datetimepicker.min.css')?>
window.onload = function() {
	var calendar = $('#calendar').fullCalendar({
		lang        : 'ru',
		titleFormat : {
			month : 'MMMM YYYY', // September 2009
			week  : 'D MMMM, YYYY', // Sep 13 2009
			day   : 'D MMMM, YYYY'  // September 8 2009
		},
		header      : {
			left   : '',
			center : 'prev, title, next',
			right  : 'agendaWeek, agendaDay'
		},
		defaultView : 'agendaWeek',
		columnFormat : {
			month : 'MMM',
			week : 'dddd, D',
			day : 'dddd, D.MM.YYYY'
		},
		allDaySlot : false,
		axisFormat : 'HH:mm',
		slotDuration : '00:30:01',
		selectable : true,
		events: function(start, end, timezone, callback) {
			$.ajax({
				url: '/calendar/events',
				dataType: 'json',
				data: {
					// our hypothetical feed requires UNIX timestamps
					start: start.unix(),
					end: end.unix()
				},
				success: function(data) {
					var events = [];
					for(var key in data) {
						events.push(data[key]);
						if (data[key].repeatable == '1') {
							var repeat_item = data[key];
							for(var i = 1;i <= 12;i++) {
								events.push({
									id       : data[key].id,
									color    : data[key].color,
									editable : data[key].editable,
									delete   : data[key].delete,
									title    : data[key].title,
									start    : moment(repeat_item.start).add(i, 'w').format('YYYY-MM-DD HH:mm'),
									end      : moment(repeat_item.end).add(i, 'w').format('YYYY-MM-DD HH:mm'),
								});
							}
						}
					}
					console.log(events);
					callback(events);
				}
			});
		},
		//events: '/calendar/events',
		droppable : true,
		unselectAuto : true,
		select: function(start, end, allDay) {
			$('#ajaxModal').modal({remote: '/calendar/add_event?start_date=' + start.local().unix() + '&end_date=' + end.local().unix(), refresh: true});
			calendar.fullCalendar('unselect');
		},
		eventClick: function(calEvent, jsEvent, view) {
			if (jsEvent.target.className == 'remove') {
				$.get('/calendar/delete_event/' + calEvent.id);
				calendar.fullCalendar('removeEvents', calEvent.id);
				return true;
			}
			if (calEvent.editable) {
				$('#ajaxModal').modal({remote: '/calendar/edit_event/'+calEvent.id, refresh: true});
				calendar.fullCalendar('unselect');
			}
		},
		eventResize: function(event, delta, revertFunc) {
			$.post('/calendar/edit_event/' + event.id, {start_date : event.start.local().format(), end_date : event.end.local().format()});
		},
		eventDrop: function(event, delta, revertFunc) {
			$.post('/calendar/edit_event/' + event.id, {start_date : event.start.local().format(), end_date : event.end.local().format()});
		},
		eventRender: function(event, element) {
			if (typeof event.delete !== 'undefined') {
				element.find('.fc-title').append('<a class="remove" ref="' + event.delete + '">x</a>');
			}
		}
	});

	calendar.fullCalendar('gotoDate', $.cookie('curr_d'));

	$('.fc-left').prepend('<h1>Мой календарь</h1>');

	$(document).on('loaded.bs.modal', function() {
		datepicker();
	});

	$('.fc-toolbar button').on('click', function() {
		$.cookie('curr_d', calendar.fullCalendar('getDate').format(), { expires: 7, path: '/' });
	});

	var datepicker = function() {
		$('.date_time').datetimepicker({
			language       : 'ru',
			minuteStepping : 30,
		});
	};
};
</script>
