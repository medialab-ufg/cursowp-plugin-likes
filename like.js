(function($){
    $(document).ready(function() {
        
        $('.cursowp_like').css('cursor', 'pointer').click(function() {
            var post_id = $(this).data('post_id');
            $('#cursowp_like_'+post_id).html('Processando...').load(
                cursowp_like.ajaxurl, 
                {
                    action: 'cursowp_like', 
                    post_id: post_id
                }
            );
        });
        
    });
})(jQuery);
