# pass-validator

O objetivo do projeto é ser uma api que recebe via post uma string e algumas regras para validar se a string recebida é uma senha valida com base nas regras também recebidas.

## Funcionamento

O formato do body que o cliente envia e a api aceita é apenas json.

O único endpoint possível é <http://localhost/api/verify>;

E o único método possível esperado pela api para o endpoint é POST.

## Requisitos mínimos

- docker: 20.10.22;

- docker-compose: 2.12.2;

- composer: 2.0.9;

## Como executar a aplicação

1. Abra o terminal e acesse a pasta desse projeto

2. Para montar e subir os containers do projeto rode o comando:

    `docker-compose up -d`

3. Ainda no terminal avance um diretório até a pasta "pass-validator" com o comando:

    `cd pass-validator`

4. Por último e ainda no terminal, após rodar o comando do passo 3, carregue as dependências do projeto com o comando:

    `composer update`

Pronto! Agora é só usufruir da api validadora de senhas.

### OBS

Por mais que não estejam muito bem documentadas as classes "Request" e "Router" elas foram completamente desenvolvidas por mim e fazem parte de um mini framework que estou desenvolvendo. <https://github.com/ronei-kunkel/minifram-php>
