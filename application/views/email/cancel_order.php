<html>
	<body>
		<h4>Сделка отменена</h4>
		<p>Сделка номер #<?php echo $order_id;?> отменена.<?php echo $start_date?></p>
		<?php if ($paid) {?>
		<p>Оплата будет возвращена в ближайшее время.</p>
		<?php }?>
		<p>Подробности сделки доступны в вашем <a href="<?php echo site_url('orders/detail/'.$order_id)?>">личном кабинете</a></p>
		<br />
		<p>С уважением, Администрация сайта <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
	</body>
</html>
