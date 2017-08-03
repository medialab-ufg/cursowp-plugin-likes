(function($){
	
	function bind_like() {
		$('.cursowp_like').css('cursor', 'pointer').off('click').on('click', links_like);
		$('.cursowp_dislike').css('cursor', 'pointer').off('click').on('click', links_dislike);
	}
	
	function links_like() {
		var post_id = $(this).data('post_id');
		$('#cursowp_like_'+post_id).html('Processando...').load(
			cursowp_like.ajaxurl, 
			{
				action: 'cursowp_like', 
				post_id: post_id
			},
			bind_like
		);
		
		
	}
	
	function links_dislike() {
		
		var post_id = $(this).data('post_id');
		$('#cursowp_like_'+post_id).html('Processando...').load(
			cursowp_like.ajaxurl, 
			{
				action: 'cursowp_dislike', 
				post_id: post_id
			},
			bind_like
		);
	}
	
	
    $(document).ready(function() {
        bind_like();
    });
    

})(jQuery);
