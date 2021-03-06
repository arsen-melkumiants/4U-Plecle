<div class="cleaner_block">
	<div class="container">
		<div class="row">
			<div class="col-sm-2">
				<img src="<?php echo !empty($user_info['photo']) ? '/uploads/avatars/'.$user_info['photo'] : '/img/no_photo.jpg'?>" width="100" alt="<?php echo $user_info['first_name']?>" class="img-circle">
			</div>
		<?php if ($this->uri->segment(2) != 'detail') {?>
			<div class="col-sm-6" style="margin-left: -25px;">
				<div class="title">Здравствуйте, <?php echo $user_info['first_name']?></div>
				<div class="add_title">Скорее выбирайте заказ и за работу!</div>
			</div>
		<?php } else {?>
			<?php $active_deal = $order_info['status'] == 2 || ($order_info['status'] == 1 && $order_info['start_date'] > time() + 86400); ?>
			<div class="col-sm-6">
				<?php if ($active_deal && $order_info['cleaner_id'] == $user_info['id']) {?>
				<div class="big_status"><?php echo $order_info['status'] != 2 ? 'Оплата не совершена' : 'Сделка Ваша!'?></div>
				<?php } else {?>
				<br>
				<br>
				<?php }?>
				<div class="title<?php echo $active_deal ? ' no_margin' : ''?>">Адрес клиента</div>
				<div class="add_title"><?php echo $order_info['country'].', '.$order_info['city'].', '.$order_info['address']?></div>
			</div>

			<div class="col-sm-4 text-right">
				<?php if (!$order_info['cleaner_id'] && (($order_info['status'] == 2 && $order_info['start_date'] > time()))) {
					if ($order_info['invite_status'] === '0') {?>
					<div class="invite_action_block">
						<a href="<?php echo site_url('orders/accept/'.$order_info['id'])?>" class="big_link">Взяться!</a>
						или
						<a href="<?php echo site_url('orders/reject_invite/'.$order_info['id'])?>" class="big_link">Отказаться</a>
					</div>
					<?php } else {?>
					<a href="<?php echo site_url('orders/accept/'.$order_info['id'])?>" class="big_link">Взяться!</a>
					<?php }?>
				<?php } elseif ($order_info['status'] == 2 && time() > $order_info['start_date'] && $order_info['start_date'] + (3600 * $order_info['duration']) > time()) {?>
					<div class="big_status">Уборка идет</div>
					<div class="add_title">Конец <?php echo date('d.m.Y в H:i', $order_info['start_date'] + (3600 * $order_info['duration']))?></div>
				<?php } elseif ($order_info['status'] == 2 && $order_info['start_date'] + (3600 * $order_info['duration']) < time()) {?>
					<div class="big_status">Ожидание оценки уборки</div>
					<div class="add_title">Конец <?php echo date('d.m.Y в H:i', $order_info['start_date'] + (3600 * $order_info['duration']))?></div>
				<?php } elseif ($active_deal) {?>
					<div class="add_title">Начало уборки:</div>
					<div class="big_status"><?php echo date('d.m.Y в H:i', $order_info['start_date'])?></div>
					<a data-toggle="modal" data-target="#ajaxModal" class="black_link" href="<?php echo site_url('orders/cancel/'.$order_info['id'])?>">Отказаться от сделки</a>
				<?php } elseif (in_array($order_info['status'], array(0,1)) && $order_info['start_date'] < time() + 86400) {?>
					<div class="big_status">Сделка не состоялась</div>
					<div class="add_title text-danger"><?php echo date('В H:i d.m.Y', $order_info['start_date'])?></div>
				<?php } elseif ($order_info['status'] == 3) {?>
					<div class="big_status">Уборка завершена</div>
					<div class="add_title"><?php echo date('В H:i d.m.Y', $order_info['start_date'] + (3600 * $order_info['duration']))?></div>
				<?php } elseif ($order_info['status'] > 3) {?>
					<div class="big_status">Сделка отменена</div>
					<div class="add_title text-danger"><?php echo date('В H:i d.m.Y', $order_info['cancel_date'])?></div>
				<?php }?>
			</div>
		<?php }?>
		</div>
	</div>
</div>
