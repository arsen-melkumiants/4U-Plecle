<script>
window.onload = function() {
	var click = false;
	$('.text_edit').attr('contenteditable', true);
	$('.text_edit').on('click', function() {
		$(this).next().show().next().show();
	}).on('blur', function() {
		var text = $(this);
		setTimeout(function() {
			if (!click) {
				text.next().fadeOut().next().fadeOut();
			}
		}, 100);
	});
	$('.text_save').on('click', function() {
		click = true;
		var btn  = $(this);
		var id   = btn.data('id');
		var text = btn.prev().html();
		btn.button('loading');
		btn.prev().attr('contenteditable', false);
		$.post('/favorites/edit/'+ id, {info : text}, function(data) {
			setTimeout(function() {
				btn.next().fadeOut();
				btn.fadeOut('medium', function() {
					btn.button('reset');
					btn.prev().attr('contenteditable', true);
					click = false;
				});
			}, 500);
			btn.prev().blur();
		});
	});
};
</script>
