<?php
if (!empty($order_info)) {
	$pay_url = $this->order_model->make_payment_url($order_info);
}
?>
<div class="client_block">
	<div class="container">
		<?php if ($this->uri->segment(2) != 'detail' || $this->uri->segment(2) == 'edit_profile') {?>
		<div class="row">
			<div class="col-sm-12">
				<div class="add_title">Появилась пыль? Нужно навести порядок?</div>
				<div class="big_status">Закажите уборку Вашего дома прямо сейчас!</div>
				<br />
				<form method="post" action="<?php echo site_url('make_order')?>">
				<input type="hidden" name="zip" value="<?php echo trim($user_info['zip'], ',')?>" />
				<input type="submit" class="big_link" value="Оформить заявку на уборку" />
				</form>
			</div>
		</div>
		<?php } elseif (!empty($order_info)) {?>
		<div class="row">
			<div class="col-sm-12">
				<div class="big_status"><?php echo !empty($order_info['cleaner_id']) ? 'Чистотой Вашего дома сейчас занимается' : 'Ищем работника для Вас'?></div>
			</div>
			<div class="col-sm-5 text-right info_block">
				<?php /*if ($order_info['status'] == 0) {?>
					<div class="big_status">Ожидание оплаты</div>
					<div class="add_title"><?php echo $order_info['country'].', '.$order_info['city'].', '.$order_info['address']?></div>
				<?php } else*/
					if (in_array($order_info['status'], array(0,1,2))) {?>
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
					<?php } elseif (!$order_info['cleaner_id'] && $order_info['status'] == 2 && $order_info['start_date'] < time()) {?>
						<div class="add_title text-danger no_margin">
							Горничная не найдена<br />Сделка отменена!
						</div>
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
				<a data-toggle="modal" data-target="#ajaxModal" href="<?php echo site_url('personal/profile/'.$cleaner_info['id'])?>" data-original-title="" title="" class="label">Профиль</a>
				<?php } else {?>
				<img src="/img/no_photo.jpg" width="100" class="img-circle">
				<div class="name">Неизвестно</div>
				<?php }?>
			</div>
			<div class="col-sm-5 text-left info_block">
				<?php if (in_array($order_info['status'], array(0,1,2)) && $order_info['start_date'] > 86400 + time()) {?>
					<a data-toggle="modal" data-target="#ajaxModal" class="black_link no_margin" href="<?php echo site_url('orders/cancel/'.$order_info['id'])?>">Отказаться от сделки</a>
					<?php if (in_array($order_info['status'], array(0,1))) {?>
					<a target="_blank" href="<?php echo $pay_url?>" class="big_status no_margin">Оплатить сделку</a>
					<?php }?>
				<?php } elseif ($order_info['status'] == 2 && !empty($order_info['cleaner_id'])) {
					if (($order_info['start_date'] + (3600 * $order_info['duration']) + 1800) < time()) {?>
						<a data-toggle="modal" data-target="#ajaxModal" href="<?php echo site_url('orders/positive_mark/'.$order_info['id'])?>" class="btn btn-success">Уборкой доволен</a>
						<br>
						<br>
						<a data-toggle="modal" data-target="#ajaxModal" href="<?php echo site_url('orders/negative_mark/'.$order_info['id'])?>" class="btn btn-danger">Уборкой не доволен</a>
					<?php } elseif ($order_info['start_date'] > 3600 + time()) {?>
						<a data-toggle="modal" data-target="#ajaxModal" class="black_link no_margin" href="<?php echo site_url('orders/cancel/'.$order_info['id'])?>">Отказаться от сделки</a>
					<?php } else {?>
						<span class="black_link disabled">Отказаться от сделки</span>
					<?php }?>
				<?php } else {?>
					<span class="black_link disabled">Отказаться от сделки</span>
				<?php }?>
			</div>
		</div>

		<?php }?>
	</div>
</div>
