<?php
// app/core/Router.php

class Router {
    protected $routes = [];

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

        if (array_key_exists($method, $this->routes) && array_key_exists($uri, $this->routes[$method])) {
            $callback = $this->routes[$method][$uri];

            if (is_callable($callback)) {
                call_user_func($callback);
            } else {
                // Se o callback for uma string no formato "Controller@method"
                list($controller, $method) = explode('@', $callback);
                $controller = 'Controllers\\' . $controller; // Adiciona o namespace se estiver usando

                if (class_exists($controller) && method_exists(new $controller(), $method)) {
                    $controllerInstance = new $controller();
                    call_user_func([$controllerInstance, $method]);
                } else {
                    $this->notFound();
                }
            }
        } else {
            $this->notFound();
        }
    }

    // Obtém a URI limpa da requisição
    private function getUri() {
        // Pega a URI completa e remove a parte base do diretório se estiver em um subdiretório do localhost
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if (strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
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
