# Modelos de código

Nessa pasta estão alguns modelos de código de coisas úteis em desenvolvimento WordPress.

São exemplo de código para serem usado como referência. 

O que temos aqui: 

## metabox.php

Metaboxes são caixas que podem ser adicionadas nas páginas de edições de posts, normalmente usados como interface de inserção de metadados.

São alternativas a interface genérica do WordPress de edição de metadados. (Dica: se seu metadado começar com um "_" ele não aparecerá na interface generica do WP).

Veja a documentação completa:  https://developer.wordpress.org/reference/functions/add_meta_box/

## metabox_google_maps.php

Não está funcionando, porque o Google agora pede chave para API, mas serve como exemplo de um metabox mais elaborado, e com uma função para ser usado "Loop" do WP para exibição dos dados o front end.

## post_type_e_metaboxes.php

Exemplo de registro de post type com vários tipos de metados podendo ser acrescentados a ele.

## post_type_simples.php

Exemplo simples de registro de um post type

Documentação: https://developer.wordpress.org/reference/functions/register_post_type/

## taxonomy.php

Exemplo simples de registro de uma taxonomia

Documentação: https://developer.wordpress.org/reference/functions/register_taxonomy/

## theme-options.php

Exemplo de criação de uma página de configuração no administrador do WP.

Este exemplo usa parcialmente a Settings API, registrando apenas uma "setting" e criando todo o formuáario por conta própria, sem utilizar as funções para criar `sections`e `settings fields`.

## shortcode.php

Exemplo de criação de shortcodes

Um deles pega o conteúdo que vem dentro do shortcode entre [colunas] e [/colunas].

O segundo mostra como receber parametros no shortcode, por exemplo: [embed largura=200]

