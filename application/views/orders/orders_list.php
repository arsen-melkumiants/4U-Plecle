<?php echo (!empty($created_orders) ? $created_orders: '')."\n";?>
<?php echo (!empty($active_orders) ? $active_orders: '')."\n";?>

<?php $unread_invites = $this->order_model->get_unread_invite_count($user_info['id']);
$unread_invites = !empty($unread_invites) ? '<div class="count invite_count">'.$unread_invites.'</div>' : '';
?>
<?php if (!empty($request_orders) && !empty($invite_orders)) {?>
<h4 class="title">
	<a href="#request_orders" role="tab" data-toggle="tab">Эти сделки должны Вас заинтересовать</a>
	<a href="#invite_orders"  role="tab" data-toggle="tab" class="invite_link">Вам предлагают<?php echo $unread_invites?></a>
</h4>
<div id="myTabContent" class="tab-content">
	<div class="tab-pane active in" id="request_orders">
		<?php echo $request_orders?>
	</div>
	<div class="tab-pane" id="invite_orders">
		<?php echo $invite_orders?>
	</div>
</div>
<?php } else {
echo (!empty($request_orders) ? '<h4 class="title">Эти сделки должны Вас заинтересовать</h4>'.$request_orders : '')."\n";

echo (!empty($invite_orders) ? '<h4 class="title">Вам предлагают</h4>'.$invite_orders: '')."\n";
}?>

<?php echo (!empty($completed_orders) ? $completed_orders: '')."\n"?>




<?php after_load('js', '/dist/js/jquery.cookie.js')?>
<script>
window.onload = function () {
	var cur_hash = window.location.hash;
	var read_invites = function () {
		$.get('/orders/read_invites');
		setTimeout(function() {
			$('.invite_count').fadeOut('slow');
		}, 1000);
	};
	if(typeof cur_hash !== 'undefined') {
		$('a[href="' + cur_hash + '"]').tab('show');
		if (cur_hash == '#invite_orders') {
			read_invites();
		}
	}

	$('a[data-toggle="tab"]').on('shown.bs.tab', function () {
		$.cookie('ol_tab', $(this).attr('href').substring(1));
		var scrollmem = $('body').scrollTop();
		window.location.hash = this.hash;
		$('html,body').scrollTop(scrollmem);
		if (this.hash == '#invite_orders') {
			read_invites();
		}
	})
}
</script>
