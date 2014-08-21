<?php 
$inner_menu = array(
	'orders'    => 'Сделки',
	'statistics' => 'Статистика',
	'favorites' => 'Избранные',
);
$inner_menu_html = '<div class="inner_menu"><ul>';
foreach ($inner_menu as $link => $item) {
	if ($link == $this->uri->segment(1) && !$this->uri->segment(2)) {
		$inner_menu_html .= '<li>'.$item.'</li>';
	} else {
		$inner_menu_html .= '<li><a href="'.site_url($link).'">'.$item.'</a></li>';
	}
}
$inner_menu_html .= '</ul><div class="clear"></div></div>';
$center_block = $inner_menu_html.$center_block;
?>
<div class="container">
	<?php echo get_alerts();?>
	<div class="row">
		<?php if (!empty($right_info)) {?>
		<div class="col-md-8">
			<?php echo $center_block?>
		</div>
		<div class="col-md-4">
			<div class="detail_block">
			<div class="title"><?php echo $right_info['title']?><?php echo isset($user_info['is_cleaner']) && $user_info['is_cleaner'] == 0 && $right_info['title'] != 'Детали заявки' ? '<a href="'.site_url('personal/edit_profile/'.$user_info['id']).'"><i class="icon-pencil"></i></a>' : ''?></div>
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
