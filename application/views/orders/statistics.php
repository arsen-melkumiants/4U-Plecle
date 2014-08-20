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
	<dd><?php echo 'awd'?></dd>
	<dt>Написано отзывов</dt>
	<dd><?php echo 'awd'?></dd>
	<dt>На сайте</dt>
	<dd><?php echo 'awd'?></dd>
</dl>

<?php if (!empty($payment_history)) {?>
<h4 class="title">История оплат</h4>
<?php echo $payment_history?>
<?php }?>
