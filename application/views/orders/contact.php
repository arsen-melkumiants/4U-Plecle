<div class="contact_info">
	<div class="contact_info_inner" data-title="Контакты сделки">
		<img class="group-google-maps-preview" src="http://maps.googleapis.com/maps/api/staticmap?center=<?php echo urlencode($address)?>&size=700x500&sensor=true&markers=color:red|<?php echo urlencode($address)?>">
		<div class="phone">Телефон клиента: <?php echo $this->ion_auth->user($order_info['client_id'])->row()->phone?></div>
	</div>
	<div class="btn_block">
		<button class="btn btn-primary btn-block" data-dismiss="modal">Закрыть</button>
	</div>
	<div class="print" onclick="print_div('.contact_info_inner')"><i class="icon-print"></i></div>
</div>
<style>
	.print_area img {width: 700px;}
</style>
<script>
	var print_div = function(div_id) {
		var print_div   = $(div_id);
		var print_html  = print_div.html();
		var print_class = print_div.removeClass('no_print').attr('class');
		var print_title = print_div.data('title');
		$('.modal').css('overflow-y', 'auto');
		$('body *').addClass('no_print');
		$('body').append('<div class="print_area">' + print_html + '</div>');
		$('.print_area').addClass(print_class);
		$('.print_area *').removeClass('no_print');
		var old_title = $('title').text();
		$('title').text(print_title);

		window.print();

		$('body *').removeClass('no_print');
		$('.print_area').remove();
		$('title').text(old_title);
	}
</script>
