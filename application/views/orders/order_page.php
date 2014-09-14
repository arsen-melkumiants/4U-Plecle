<?php
if (!empty($user_info['is_cleaner'])) {
	$inner_menu = array(
		'orders'    => 'Сделки',
		'reviews'   => 'Отзывы',
		'favorites' => 'Избранные',
	);
} else {
	$inner_menu = array(
		'orders'     => 'Сделки',
		'statistics' => 'Статистика',
		'favorites'  => 'Избранные',
	);
}
$inner_menu_html = '<div class="inner_menu"><ul>';
foreach ($inner_menu as $link => $item) {
	if ($link == $this->uri->segment(1) && !$this->uri->segment(2)) {
		$inner_menu_html .= '<li>'.$item.'</li>';
	} else {
		$inner_menu_html .= '<li><a href="'.site_url($link).'">'.$item.'</a></li>';
	}
}
$inner_menu_html .= '</ul><div class="clear"></div></div>';
if ($this->uri->segment(1) != 'make_order') {
	$center_block = $inner_menu_html.$center_block;
}
?>
<div class="container">
	<?php echo get_alerts();?>
	<div class="row">
		<?php if (!empty($right_info)) {?>
		<div class="col-md-8">
			<?php echo $center_block?>
		</div>
		<div class="col-md-4">
			<?php if (($this->uri->segment(1) != 'make_order') && !$user_info['is_cleaner']) {?>
			<div class="detail_block">
				<div class="title">Поиск работника</div>
				<div class="list">
				<form class="form-horizontal" method="post" action="<?php echo site_url('make_order')?>">
					<div class="form-group">
						<div class="col-md-12">
							<label class="sr-only">Введите свой индекс</label>
							<input type="text" name="zip" class="form-control" placeholder="Введите свой индекс">
						</div>
					</div>
					<div class="form-group">
						<div class="col-md-12">
							<button class="btn btn-primary btn-block" modal="" value="Вход" type="submit">Найти работника</button>
						</div>
					</div>
				</form>
				</div>
			</div>
			<?php }?>
			<div class="detail_block">
				<div class="title"><?php echo $right_info['title']?><?php echo isset($user_info['is_cleaner']) && $user_info['is_cleaner'] == 0 && $right_info['title'] != 'Детали заявки' ? '<a href="'.site_url('personal/edit_profile').'"><i class="icon-pencil"></i></a>' : ''?></div>
				<div class="list">
					<table class="table">
						<tbody>
							<?php foreach ($right_info['info_array'] as $key => $value) {
							if ($value === false) {
								continue;
							}?>
							<tr><td><?php echo $key?></td><td><?php echo $value?></td></tr>
							<?php }?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<?php } else {?>
		<div class="col-md-12"><?php echo $center_block?></div>
		<?php }?>
	</div>
</div>
