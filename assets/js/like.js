(function($){
  $(document).ready(function() {

    $('.cursowp_like, .cursowp_unlike').live('click',function() {
      var post_id = $(this).data('post_id');
      var link_class = $(this).attr('class');

      if(link_class == 'cursowp_like'){
        $('#cursowp_like_'+post_id).html('Processando...').load(
          cursowp_like.ajaxurl,
          {
            action: 'cursowp_like',
            post_id: post_id
          }
        );
      }
      else {
        $('#cursowp_like_'+post_id).html('Processando...').load(
          cursowp_like.ajaxurl, {
            action: 'cursowp_unlike',
            post_id: post_id,
          }
        );
      }
    });

  });
})(jQuery);
