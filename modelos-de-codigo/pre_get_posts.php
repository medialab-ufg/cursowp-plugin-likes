/*
 * Exemplos de uso da pre_get_posts
 */

add_action('pre_get_posts', function($query) {

  /**
   * Aqui temos que nos preocupar em aplicar ess filtro apenas na ocasião que queremos
   * Neste caso, na listagem do post type project e apenas na "main_query"
   * 
   * A main_query é a consulta padrão que o WordPress faz baseado na URL que você está visitando.
   * 
   * Se não tiver isso no IF, o filtro pode acabar sendo aplicado em qualquer outra subquery que tenha na página,
   * como a listagem de um widget por exemplo
   */ 
  
  if ($query->is_post_type_archive('project') && $query->is_main_query()) {
    
    
    /**
     * O método set seta o valor da propriedade da query que será feita por posts.
     * 
     * Aqui vale ver a documentação da classe WP_Query para saber todas as opções que existem:
     * 
     * https://codex.wordpress.org/Class_Reference/WP_Query
     */ 
    $query->set('orderby', 'meta_value_num');
    $query->set('meta_key', 'numero');
    $query->set('order', 'ASC');
  }
});
