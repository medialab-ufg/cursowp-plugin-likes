# cursowp-plugin-likes

Este plugin é um exemplo desenvolvido durante o curso WordPress para Desenvolvedores ministrado aqui no MediaLab.

Serve de referência para os participantes e também é base para o exercício de final de curso (ver a seção **Desafios** abaixo).

Ele mostra, na prática, a utilização de alguns recursos do WordPress, como:

* chamadas ajax
* uso da settings API para colocar uma configuração no admin
* uso de filtros para modificar o conteúdo do post
* Inserção de javascript e uso da função wp_localize_script para passar variáveis para o js

(Nota: Este plugin tem find didáticos apenas, não foi feito para ser usado em produção e nem está pronto pra isso)

Este repositório também traz [outros exemplos](modelos-de-codigo) de código com funções úteis no desenvolvimento WordPress. Eles estão na pasta [modelos-de-codigo](modelos-de-codigo).

## Desafios

A proposta é que os participantes façam um **Fork** desse repositório e enviem um **Pull Request** com alterações que resolvam um problema real.

Abaixo temos algumas ideias de desafios, mas outros podem ser feitos. (Aliás, podem mandar um Pull Request para esse repositório com edição neste README com sugestões de outros desafios).

Para quem ainda é novo no Github, vale ver esse post sobre como fazer Fork, Pull Request, etc: https://blog.da2k.com.br/2015/02/04/git-e-github-do-clone-ao-pull-request/


### Fáceis

* ~~Adicionar a possibilidade de Descurtir~~
* ~~Não permitir curtir os próprios posts~~

### Médios

* Colocar uma opção em cada post para habilitar se ele pode ser curtido ou não (igual vc habilita comentários ou não (usa metaboxes)
* Criar um Widget que liste os X posts mais votados, onde X é uma opção do Widget

### O mais legal

* Criar um post type novo e:
  * aplicar essa funcionalidade de curtir apenas a esse post type
  * Fazer a listagem deste post type ser ordenada por mais curtidos
  * Opcional: Criar um template no seu tema para listagem desse post type
