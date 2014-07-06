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
				<div class="big_status"><?php echo !empty($order_info['status']) ? 'Чистотой Вашего дома сейчас занимается' : 'Ищем работника для Вас'?></div>
			</div>
			<div class="col-sm-5 text-right info_block">
				<?php if ($order_info['status'] == 0) {?>
					<div class="big_status">Ждем работника</div>
					<div class="add_title"><?php echo $order_info['country'].', '.$order_info['city'].', '.$order_info['address']?></div>
				<?php } elseif ($order_info['status'] == 1 || $order_info['status'] == 2) {?>
					<div class="add_title">Начало уборки:</div>
					<div class="big_status"><?php echo date('d.m.Y в h:i', $order_info['start_date'])?></div>
					<?php if ($order_info['status'] == 1 && ($order_info['start_date'] - 86400) < time()) {
					$time_left = $order_info['start_date'] - time();
						if ($time_left > 0) {?>
						<div class="black_link no_margin">
							Осталось <?php echo date('h часа(ов) и i минут(ы)')?> для оплаты заказа
						</div>
						<?php } else {?>
						<div class="add_title text-danger no_margin">
							Заказ не оплачен!<br />Сделка заморожена!
						</div>
						<?php }?>
					<?php }?>
				<?php } elseif ($order_info['status'] == 3) {?>
					<div class="big_status">Уборка завершена</div>
					<div class="add_title"><?php echo date('В h:i d.m.Y', $order_info['start_date'] + (3600 * $order_info['duration']))?></div>
				<?php }?>
			</div>
			<div class="col-sm-2 text-center">
				<img src="http://placehold.it/100x100" alt="..." class="img-circle">
				<div class="name">Наталья</div>
				<a href="#" class="label">Профиль</a>
			</div>
			<div class="col-sm-5 text-left info_block">
				<?php if ($order_info['status'] == 0) {?>
					<a href="#" class="black_link">Отказаться от сделки</a>
				<?php } elseif ($order_info['status'] == 1) {?>
					<a href="#" class="black_link no_margin">Отказаться от сделки</a>
					<a href="#" class="big_status no_margin">Оплатить сделку</a>
				<?php } elseif ($order_info['status'] == 2) {
					if (($order_info['start_date'] + (3600 * $order_info['duration']) + 1800) < time()) {?>
						<a href="#" class="btn btn-success">Уборкой доволен(а)</a>
						<br>
						<br>
						<a href="#" class="btn btn-danger">Уборкой не доволен(а)</a>
					<?php } else {?>
						<span class="black_link disabled">Отказаться от сделки</span>
					<?php }?>
				<?php }?>
			</div>
		</div>

		<?php }?>
	</div>
</div>
