<div id="calendar"></div>
<?php after_load('css', '/dist/calendar/fullcalendar.min.css')?>
<?php after_load('js', '/dist/js/moment-with-langs.min.js')?>
<?php after_load('js', '/dist/calendar/fullcalendar.min.js')?>
<?php after_load('js', '/dist/calendar/lang-all.js')?>

<script>
window.onload = function() {
	var calendar = $('#calendar').fullCalendar({
		lang  : 'ru',
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay'
		},
		allDaySlot : false,
		axisFormat : 'HH:mm',
		events     : '/<?php echo $this->MAIN_URL?>/get_events',
	});

	$('#calendar').on('click', '.fc-day-header.fc-widget-header', function() {
		var selected = $(this).text();
		selected = moment(selected, 'dddd, D.MM', 'ru');
		calendar.fullCalendar('gotoDate', selected);
		calendar.fullCalendar('changeView', 'agendaDay');
	});
};
</script>
