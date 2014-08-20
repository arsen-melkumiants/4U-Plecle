<h4 class="title">Выплаты</h4>
<dl class="dl-horizontal list">
	<dt>Выплачено за месяц</dt>
	<dd><?php echo $stats['month_payment']?> рублей</dd>
	<dt>Выплачено за год</dt>
	<dd><?php echo $stats['year_payment']?> рублей</dd>
	<dt>Выплачено за выбранный период</dt>
	<dd><?php echo 'awd'?> рублей</dd>
</dl>
<h4 class="title">Общее</h4>
<dl class="dl-horizontal list">
	<dt>Завершено сделок</dt>
	<dd><?php echo $stats['order_count']['total'].' (<span class="text-success">'.$stats['order_count']['success'].'</span> / <span class="text-danger">'.$stats['order_count']['fail'].'</span>)'?></dd>
	<dt>Написано отзывов</dt>
	<dd><?php echo 'в разработке'?></dd>
	<dt>На сайте</dt>
	<dd class="parse_time"><?php echo date('m-d-Y', $user_info['created_on'])?></dd>
</dl>
<?php after_load('js', '/dist/js/moment-with-langs.min.js')?>
<script>
window.onload = function() {
	moment.lang('ru');
	moment($('.parse_time').text());
	$('.parse_time').text(moment($('.parse_time').text()).fromNow(true));
};
</script>
