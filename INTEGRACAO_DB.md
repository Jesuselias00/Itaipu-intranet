# Instruções de Integração com Banco de Dados

Este guia explica como configurar e integrar o sistema com o banco de dados MariaDB/MySQL.

## 1. Criar Banco de Dados

Primeiramente, você precisa criar o banco de dados. Abra o phpMyAdmin (geralmente em http://localhost/phpmyadmin) e execute:

```sql
CREATE DATABASE IF NOT EXISTS intranet_itaipu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ou execute o script `sql/create_database.sql` através do phpMyAdmin.

## 2. Criar as Tabelas

Após criar o banco de dados, selecione o banco `intranet_itaipu` e execute o script SQL de criação das tabelas:

```sql
-- Execute o conteúdo do arquivo sql/schema.sql
```

## 3. Inserir Dados Iniciais

Para ter dados iniciais de teste, execute:

```sql
-- Execute o conteúdo do arquivo sql/insert_initial_data.sql
```

## 4. Configuração da Conexão

Verifique o arquivo `app/config/database.php` para garantir que as credenciais de conexão estão corretas:

```php
return [
    'DB_HOST' => 'localhost', // Ou o IP do seu servidor de banco de dados
    'DB_NAME' => 'intranet_itaipu', // Nome do banco de dados
    'DB_USER' => 'root',      // Usuário do banco de dados
    'DB_PASS' => '',          // Senha do usuário (padrão do XAMPP é vazia)
    'DB_CHARSET' => 'utf8mb4' // Conjunto de caracteres recomendado
];
```

## 5. Teste a Conexão

Para testar a conexão com o banco de dados, acesse:

```
http://localhost/Itaipu-intranet/app/api.php/test-router
```

Se tudo estiver funcionando corretamente, você deverá ver uma resposta JSON indicando sucesso.

## 6. Teste a API de Funcionários

Para testar o endpoint de listagem de funcionários:

```
http://localhost/Itaipu-intranet/app/api.php/funcionarios
```

## 7. Credenciais de Acesso para Teste

Para fazer login no sistema, use:

- Email: admin@itaipu.com
- Senha: admin123

## 8. Atualização do Frontend

O frontend está configurado para fazer requisições à API automaticamente. 
Se você precisar ajustar o URL base da API, verifique o início do arquivo em:
`public/assets/js/funcionarios.js`

## 9. Resolução de Problemas

### Erros de Conexão com o Banco de Dados

1. Verifique se o serviço MySQL/MariaDB está rodando no XAMPP
2. Confira as credenciais no arquivo `app/config/database.php`
3. Verifique se o banco de dados `intranet_itaipu` foi criado corretamente

### Erros 404 na API

1. Verifique se o arquivo `.htaccess` está presente na raiz do projeto
2. Certifique-se de que o mod_rewrite está habilitado no Apache

### Logs de Erro

Para verificar logs de erro e diagnosticar problemas, confira:
- `app/core/router_debug.log` - Contém logs de execução da API
- Logs de erro do Apache no XAMPP: `xampp/apache/logs/error.log`

## 10. Otimizações para Fotos

As fotos dos funcionários são armazenadas em formato binário (BLOB) no banco de dados.
Para melhor desempenho em ambientes de produção, considere:

1. Redimensionar as imagens antes de enviar para o servidor
2. Implementar compressão de imagem
3. Em ambientes maiores, considere armazenar os arquivos em disco com apenas os caminhos no banco de dados
