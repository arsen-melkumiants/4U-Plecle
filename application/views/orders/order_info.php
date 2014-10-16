<?php
$options = array();
if (!empty($order_info['need_ironing'])) {
	$options[] = 'требуется глажка';
}
if (!empty($order_info['have_pets'])) {
	$options[] = 'есть животные';
}
$options = implode(', ', $options) ?: '&nbsp;';

if (!empty($order_info['add_durations'])) {
	foreach (json_decode($order_info['add_durations'], true) as $item) {
		$add_options[] = $item['name'];
	}
	$add_options = implode(', ', $add_options);
}
?>
<h4 class="title">Подробности сделки</h4>
<dl class="dl-horizontal list">
	<dt>Частота потребности горничной</dt>
	<dd><?php echo $this->order_model->frequency[$order_info['frequency']]?></dd>
	<dt>Рабочее время</dt>
	<dd><?php echo isset($this->order_model->duration[$order_info['duration']]) ? $this->order_model->duration[$order_info['duration']] : $order_info['duration'].' часов'?></dd>
	<dt>Дата уборки</dt>
	<dd><?php echo date('d.m.Y с H:i', $order_info['start_date'])?></dd>
	<dt>Особые условия</dt>
	<dd><?php echo $options?></dd>
	<?php if (!empty($add_options)) {?>
	<dt>Дополнительные услуги</dt>
	<dd><?php echo $add_options?></dd>
	<?php }?>
	<dt>Место уборки</dt>
	<dd><?php echo $order_info['country'].', '.$order_info['city'].', '.$order_info['address']?></dd>
	<dt>Заметки клиента для Вас</dt>
	<dd><?php echo $order_info['comment']?></dd>
</dl>

<?php echo !empty($order_gallery) ? $order_gallery : false;?>

<h4 class="title">Финансы</h4>
<dl class="dl-horizontal list">
	<dt>Сумма за рабочие часы</dt>
	<?php if ($user_info['is_cleaner']) {?>
	<dd><?php echo $order_info['cleaner_price'] * $order_info['duration']?> рублей</dd>
	<?php } else {?>
	<dd><?php echo $order_info['price_per_hour'] * $order_info['duration']?> рублей</dd>
	<?php }?>
	<dt>Сумма за моющие средства</dt>
	<dd><?php echo $order_info['detergent_price'] * $order_info['need_detergents']?> рублей</dd>

	<?php if ($order_info['urgent_cleaning'] && $user_info['is_cleaner']) {?>
	<dt>Сумма за срочность</dt>
	<dd><?php echo floatval($order_info['urgent_cleaner_price'])?> рублей</dd>
	<?php } elseif ($order_info['urgent_cleaning'] && !$user_info['is_cleaner']) {?>
	<dt>Сумма за срочность</dt>
	<dd><?php echo floatval($order_info['urgent_price'])?> рублей</dd>
	<?php }?>

	<dt><b>Итого</b></dt>
	<?php if ($user_info['is_cleaner']) {?>
	<dd><b><?php echo floatval($order_info['total_cleaner_price'])?> рублей</b></dd>
	<?php } else {?>
	<dd><b><?php echo floatval($order_info['total_price'])?> рублей</b></dd>
	<?php }?>
</dl>

<?php if (!empty($payment_history)) {?>
<h4 class="title">История оплат</h4>
<?php echo $payment_history?>
<?php }?>
