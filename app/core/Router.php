<?php
// app/core/Router.php

class Router {
    protected $routes = [];
    protected $basePath = '';

    public function __construct() {
        // Detecta el directorio base de los controladores
        $this->basePath = dirname(__DIR__);
    }

    // Adiciona uma rota GET
    public function get($uri, $callback) {
        $this->routes['GET'][$uri] = $callback;
    }

    // Adiciona uma rota POST
    public function post($uri, $callback) {
        $this->routes['POST'][$uri] = $callback;
    }

    // Adiciona uma rota PUT
    public function put($uri, $callback) {
        $this->routes['PUT'][$uri] = $callback;
    }

    // Adiciona uma rota DELETE
    public function delete($uri, $callback) {
        $this->routes['DELETE'][$uri] = $callback;
    }

    // Despacha a requisição para a função/método correto
    public function dispatch() {
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];

        // Log temporário mais detalhado para depuração (pode ser removido em produção)
        file_put_contents(__DIR__ . '/router_debug.log', date('c') . " | Método: $method | URI recebida: $uri\n", FILE_APPEND);

        if (array_key_exists($method, $this->routes) && array_key_exists($uri, $this->routes[$method])) {
            $callback = $this->routes[$method][$uri];

            if (is_callable($callback)) {
                // Se for um closure ou função anônima, executa diretamente
                call_user_func($callback);
            } else if (is_string($callback) && strpos($callback, '@') !== false) {
                // Se for string no formato "Controller@method"
                $this->callControllerMethod($callback);
            } else {
                $this->notFound();
            }
        } else {
            $this->notFound();
        }
    }

    // Método separado para isolar a lógica de chamar um controlador
    private function callControllerMethod($callback) {
        try {
            list($controllerName, $method) = explode('@', $callback);
            
            // Caminho completo para o arquivo do controlador
            $controllerFile = $this->basePath . "/controllers/{$controllerName}.php";
            
            // Verifica se o arquivo do controlador existe
            if (!file_exists($controllerFile)) {
                throw new Exception("Controlador não encontrado: {$controllerName}");
            }
            
            // Se o arquivo existe mas ainda não foi incluído, faz o require
            if (!class_exists($controllerName)) {
                require_once $controllerFile;
            }
            
            // Instancia o controlador e chama o método
            $controller = new $controllerName();
            
            if (!method_exists($controller, $method)) {
                throw new Exception("Método {$method} não encontrado no controlador {$controllerName}");
            }
            
            call_user_func([$controller, $method]);
            
        } catch (Exception $e) {
            // Log do erro para debug (pode ser removido ou ajustado em produção)
            file_put_contents(__DIR__ . '/router_debug.log', 
                date('c') . " | ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
            $this->notFound();
        }
    }

    // Obtém a URI limpa da requisição
    private function getUri() {
        // 1. Tenta usar PATH_INFO (mais robusto)
        if (isset($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] !== '') {
            $uri = trim($_SERVER['PATH_INFO'], '/');
            return $uri;
        }
        // 2. Fallback para lógica personalizada
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        // Remove todos os diretórios antes de 'api/'
        $apiPos = strpos($uri, 'api/');
        if ($apiPos !== false) {
            $uri = substr($uri, $apiPos + 4); // Pega só o que vem depois de 'api/'
            $uri = trim($uri, '/');
        }
        return $uri;
    }

    private function notFound() {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Endpoint não encontrado.']);
        exit;
    }
}
