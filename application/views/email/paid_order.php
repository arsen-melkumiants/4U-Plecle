<html>
	<body>
		<h4>Оплата успешно произведена</h4>
		<p>Сделка номер #<?php echo $order_id;?> оплачена. Уборка состоится <?php echo $start_date?></p>
		<p>Подробности сделки доступны в вашем <a href="<?php echo site_url('orders/detail/'.$order_id)?>">личном кабинете</a></p>
		<br />
		<p>С уважением, Администрация сайта <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
	</body>
</html>
