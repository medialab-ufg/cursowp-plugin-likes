// LOOP PERSONALIZADO

/**
 * Primeiro fazemos a query por posts
 * A classe WP_Query é uma das principais queries do WordPress
 * Ela te permite fazer consultas por posts passando uma infinidade de parâmetros de filtro e ordenação.
 * 
 * Você pode por exemplo buscar posts que estejam numa categoria, que tenham um metadado, que sejam de um autor, etc...
 * 
 * Para ver todas as possibilidades de busca, veja a documentação completa em:
 * https://codex.wordpress.org/Class_Reference/WP_Query
 */ 
$sections = new WP_Query(
    array(
        'post_type' => 'filmes',
        'post_parent' => get_the_ID(),
        'ignore_sticky_posts' => true,
        'showposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    )
);
?>
    <?php
    /**
     * Agora fazemos um Loop do WordPress
     * é um The Loop normal, a única diferença é que chamamos os metodos
     * have_posts() e the_post() a partir do nosso objeto.
     * 
     * O método the_post() seta as variáveis globais e, depois dela,
     * podemos usar as templates tags como the_title() e the_content
     */ 
    <?php if ($sections->have_posts()): ?>
        <?php while ($sections->have_posts()): $sections->the_post(); ?>
            <?php the_title(); ?>
            ...
        <?php endwhile; ?>
    <?php endif; ?>

<?php
/**
 * Ao final do loop, resetamos as variáveis globais para a query principal
 */ 
?>
<?php wp_reset_query(); ?>
