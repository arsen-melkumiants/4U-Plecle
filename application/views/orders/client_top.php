<div class="client_block">
	<div class="container">
		<?php if ($this->uri->segment(2) != 'detail') {?>
		<div class="row">
			<div class="col-sm-12">
				<div class="add_title">Появилась пыль? Нужно навести порядок?</div>
				<div class="big_status">Закажите уборку Вашего дома прямо сейчас!</div>
				<br />
				<a href="<?php echo site_url('make_order')?>" class="big_link">Оформить заявку на уборку</a>
			</div>
		</div>
		<?php } else {?>
		<div class="row">
			<div class="col-sm-12">
				<div class="big_status"><?php echo !empty($order_info['cleaner_id']) ? 'Чистотой Вашего дома сейчас занимается' : 'Ищем работника для Вас'?></div>
			</div>
			<div class="col-sm-5 text-right info_block">
				<?php if ($order_info['status'] == 0) {?>
					<div class="big_status">Ожидание оплаты</div>
					<div class="add_title"><?php echo $order_info['country'].', '.$order_info['city'].', '.$order_info['address']?></div>
				<?php } elseif ($order_info['status'] == 1 || $order_info['status'] == 2) {?>
					<div class="add_title">Начало уборки:</div>
					<div class="big_status"><?php echo date('d.m.Y в H:i', $order_info['start_date'])?></div>
					<?php if (in_array($order_info['status'], array(0,1)) && ($order_info['start_date'] - 86400 * 2) < time()) {
					$time_left = $order_info['start_date'] - 86400 - time();
						if ($time_left > 0) {?>
						<div class="black_link no_margin">
							<?php
							$hours = $time_left / 60 / 60;
							$minutes = ($hours - floor($hours)) * 60;
							?>
							Осталось <?php echo floor($hours)?> часа(ов) и <?php echo ceil($minutes)?> минут(ы) для оплаты заказа
						</div>
						<?php } else {?>
						<div class="add_title text-danger no_margin">
							Заказ не оплачен!<br />Сделка отменена!
						</div>
						<?php }?>
					<?php }?>
				<?php } elseif ($order_info['status'] == 3) {?>
					<div class="big_status">Уборка завершена</div>
					<div class="add_title"><?php echo date('В H:i d.m.Y', $order_info['start_date'] + (3600 * $order_info['duration']))?></div>
				<?php } elseif ($order_info['status'] > 3) {?>
					<div class="big_status">Сделка отменена</div>
					<div class="add_title"><?php echo date('В H:i d.m.Y', $order_info['cancel_date'])?></div>
				<?php }?>
			</div>
			<div class="col-sm-2 text-center">
				<?php if (!empty($cleaner_info)) {?>
				<img src="<?php echo !empty($cleaner_info['photo']) ? '/uploads/avatars/'.$cleaner_info['photo'] : '/img/no_photo.jpg'?>" width="100" alt="<?php echo $cleaner_info['first_name']?>" class="img-circle">
				<div class="name"><?php echo $cleaner_info['first_name']?></div>
				<a href="<?php echo site_url('profile/'.$cleaner_info['id'])?>" class="label">Профиль</a>
				<?php } else {?>
				<img src="<?php echo !empty($cleaner_info['photo']) ? '/uploads/avatars/'.$cleaner_info['photo'] : '/img/no_photo.jpg'?>" width="100" alt="<?php echo $cleaner_info['first_name']?>" class="img-circle">
				<div class="name">Неизвестно</div>
				<?php }?>
			</div>
			<div class="col-sm-5 text-left info_block">
				<?php if ($order_info['status'] == 0) {?>
					<a href="<?php echo site_url('orders/cancel/'.$order_info['id'])?>" class="black_link no_margin">Отказаться от сделки</a>
				<?php } elseif ($order_info['status'] == 1 && $order_info['start_date'] > 86400 + time()) {?>
					<a href="<?php echo site_url('orders/cancel/'.$order_info['id'])?>" class="black_link no_margin">Отказаться от сделки</a>
					<a href="<?php echo site_url('orders/pay/'.$order_info['id'])?>" class="big_status no_margin">Оплатить сделку</a>
				<?php } elseif ($order_info['status'] == 2) {
					if (($order_info['start_date'] + (3600 * $order_info['duration']) + 1800) < time()) {?>
						<a href="<?php echo site_url('orders/positive_mark/'.$order_info['id'])?>" class="btn btn-success">Уборкой доволен(а)</a>
						<br>
						<br>
						<a href="<?php echo site_url('orders/negative_mark/'.$order_info['id'])?>" class="btn btn-danger">Уборкой не доволен(а)</a>
					<?php } elseif ($order_info['start_date'] > 86400 + time()) {?>
						<a href="<?php echo site_url('orders/cancel/'.$order_info['id'])?>" class="black_link no_margin">Отказаться от сделки</a>
					<?php } else {?>
						<span class="black_link disabled">Отказаться от сделки</span>
					<?php }?>
				<?php }?>
			</div>
		</div>

		<?php }?>
	</div>
</div>
