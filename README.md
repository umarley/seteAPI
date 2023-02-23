<center>
<img src="https://files.cercomp.ufg.br/weby/up/767/o/setepretoPrancheta_1_4x.png" alt="SETE Logo" width="300">
<h1 align="center">Sistema Eletrônico de Gestão do Transporte Escolar - API</h1>
</center>

<h2 align="center"> 
	🚧  Em construção...  🚧
</h2>
</br>
<p align="center"> Esta API foi criada para alimentar o sistema de frontend do SETE, foi criada com PHP e utiliza o framework Laminas.
</p>

<br>

Sumário
=================
<!--ts-->
   * Sobre o SETE
   * Pré-Requisitos
   * Como executar a API
   * Como configurar o banco de dados
   * Como fazer as requisições
   * Tecnologias
   * Contribuidores
<!--te-->

</br>

# Sobre o SETE

<p>
O Sistema Eletrônico de Gestão do Transporte Escolar (SETE) é um software de <i>e-governança</i> desenvolvido pelo <a href='https://transportes.fct.ufg.br/p/31447-apresentacao-do-cecate-ufg'>CECATE UFG</a> voltado a auxiliar na gestão do transporte escolar dos municípios brasileiros considerando suas singularidades.  O sistema foi projeto com intuito de não depender de nenhum software proprietário, desta forma é possível utilizá-lo sem ter de licenciar programas dependentes.
</p>

</br>

# Pré-requisitos

Para utilizar a API do SETE é preciso instalar em sua máquina:
[Git](https://git-scm.com/download), [PHP v7.4](https://www.php.net/), [Composer](https://getcomposer.org/). Agora só precisa fazer algumas configurações.

</br>

# 💻Como executar a API

O primeiro passo é clonar o repositório.

Depois precisa liberar algumas extenções do PHP, para isso acesse o php.ini como administrador e descomente:
```bash
extension=openssl
extension=intl
extension=fileinfo
extension=gd2
```

Após isso, através do terminal, acesse a pasta do projeto e coloque o seguinte comando para instalar as depedências.


```bash
$ composer install
```

Por fim, execute o servidor com o seguinte comando:

```bash
$ php -S 0.0.0.0:8080 -t public/ public/index.php

# O servidor iniciará na porta:8080.
```

# 🎲 Como configurar o banco de dados

Para configurar o banco de dados é preciso ter instalado o [POSTGRESQL](https://www.postgresql.org/download/).

</br>

# ⚙️ Como fazer as requisições
Para fazer as requisições localmente é preciso instalar o [POSTMAN](https://www.postman.com/), depois precisamos configurar a collection. Passo a passo:
- Em authorization, selecione o "Type" como "API Key".
- Preencha "Key" com "Authorization"
- Em "Value" coloque sua {{API_KEY}}.
- Por fim, selecione "Header" em "add to".

Já na aba de "Variables" adicionaremos cinco em "variable":
- API_KEY: É preenchido com sua api_key em "inicial value" e "current value".
- USERNAME: É preenchido com seu e-mail em "inicial value" e "current value".
- PASSWORD: É preenchido com sua senha criptografada em "inicial value" e "current value".
- HTTP_URL: É preenchido com "https://localhost:8080" em "inicial value" e "current value".
- CIDADE: É preenchido com o código da sua cidade em "inicial value" e "current value".

Por fim, é preciso acessar a documentação do [SETE - API](https://app.swaggerhub.com/apis-docs/umarley/SistemaSETE/1.0.0#/) e seguir os formatos de requisições desejadas.

</br>

# 🛠 Tecnologias

Ferramentas utilizadas na construção do projeto:

- [PHP](https://www.php.net/)
- [Laminas](https://getlaminas.org/)
- [Composer](https://getcomposer.org/)
- [Visual Studio Code](https://code.visualstudio.com/)
- [Postman](https://www.postman.com/)
- [Swagger](https://swagger.io/)
- [Git](https://git-scm.com/download)
- [Postgresql](https://www.postgresql.org/download/)
- [DBeaver](https://dbeaver.io/download/)
- [Postman](https://www.postman.com/)

<br>

# 🤝 Contribuidores

<table>
  <tr>
    <td align="center">
<a href="https://github.com/umarley">
 <img style="border-radius: 50%;" src="https://avatars.githubusercontent.com/u/8119489?v=4" width="100px;" alt=""/>
 <br />
 <sub><b>Umarley Ricardo</b></sub></a> <a href="https://github.com/umarley"></a></td>
 
 <td align="center"><a href="https://github.com/pedsanches">
 <img style="border-radius: 50%;" src="https://avatars.githubusercontent.com/u/61986850?v=4" width="100px;" alt=""/>
 <br />
 <sub><b>Pedro Henrique
</b></sub></a> <a href="https://github.com/pedsanches"></a></td>

 <td align="center"><a href="https://github.com/nataliasou">
 <img style="border-radius: 50%;" src="https://avatars.githubusercontent.com/u/45390353?v=4" width="100px;" alt=""/>
 <br />
 <sub><b>Natália Souza</b></sub></a> <a href="https://github.com/nataliasou"></a></td>

<td align="center"><a href="https://github.com/JohnHeberty">
 <img style="border-radius: 50%;" src="https://avatars.githubusercontent.com/u/46422955?v=4" width="100px;" alt=""/>
 <br />
 <sub><b>John Heberty</b></sub></a> <a href="https://github.com/JohnHeberty"></a></td>
</tr>
</table>

</br>
