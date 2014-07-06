<h4 class="title">Подробности сделки</h4>
<dl class="dl-horizontal list">
	<dt>Частота потребности горничной</dt>
	<dd><?php echo $this->order_model->frequency[$order_info['frequency']]?></dd>
	<dt>Рабочее время</dt>
	<dd><?php echo $this->order_model->duration[$order_info['duration']]?></dd>
	<dt>Дата уборки</dt>
	<dd><?php echo date('d.m.Y с h:i', $order_info['start_date'])?></dd>
	<dt>Особые условия</dt>
	<dd>Требуется глажка, есть животные</dd>
	<dt>Заметки клиента для Вас</dt>
	<dd><?php echo $order_info['comment']?></dd>
</dl>
<h4 class="title">Финансы</h4>
<dl class="dl-horizontal list">
	<dt>Сумма за рабочие часы</dt>
	<dd><?php echo $order_info['price_per_hour'] * $order_info['duration']?> рублей</dd>
	<dt>Сумма за моющие средства</dt>
	<dd><?php echo $order_info['detergent_price'] * $order_info['need_detergents']?> рублей</dd>
	<dt><b>Итого</b></dt>
	<dd><b><?php echo floatval($order_info['total_price'])?> рублей</b></dd>
</dl>

<?php if (!empty($payment_history)) {?>
<h4 class="title">История оплат</h4>
<?php echo $payment_history?>
<dl class="dl-horizontal list">
	<dt>25.05.2014</dt>
	<dd>1 200 рублей</dd>
</dl>
<?php }?>
