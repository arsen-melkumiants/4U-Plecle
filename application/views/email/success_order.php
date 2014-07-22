<html>
	<body>
		<h4>Сделка успешно выполнена</h4>
		<p>Поздравляем! Сделка номер #<?php echo $order_id;?> успешно выполнена.</p>
		<p>Подробности сделки доступны в вашем <a href="<?php echo site_url('orders/detail/'.$order_id)?>">личном кабинете</a></p>
		<br />
		<p>С уважением, Администрация сайта <a href="<?php echo base_url()?>"><?php echo SITE_NAME?></a></p>
	</body>
</html>
