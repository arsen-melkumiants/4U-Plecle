<?php
$options = array();
if (!empty($order_info['need_ironing'])) {
	$options[] = 'требуется глажка';
}
if (!empty($order_info['have_pets'])) {
	$options[] = 'есть животные';
}
$options = implode(', ', $options) ?: '&nbsp;';
?>
<h4 class="title">Подробности сделки</h4>
<dl class="dl-horizontal list">
	<dt>Частота потребности горничной</dt>
	<dd><?php echo $this->order_model->frequency[$order_info['frequency']]?></dd>
	<dt>Рабочее время</dt>
	<dd><?php echo $this->order_model->duration[$order_info['duration']]?></dd>
	<dt>Дата уборки</dt>
	<dd><?php echo date('d.m.Y с H:i', $order_info['start_date'])?></dd>
	<dt>Особые условия</dt>
	<dd><?php echo $options?></dd>
	<dt>Место уборки</dt>
	<dd><?php echo $order_info['country'].', '.$order_info['city'].', '.$order_info['address']?></dd>
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
	<?php if (!empty($order_info['fine_price'])) {?>
	<dt><b class="text-danger">Штраф</b></dt>
	<dd><b class="text-danger"><?php echo floatval($order_info['fine_price'])?> рублей</b></dd>
	<?php }?>
</dl>

<?php if (!empty($payment_history)) {?>
<h4 class="title">История оплат</h4>
<?php echo $payment_history?>
<?php }?>
