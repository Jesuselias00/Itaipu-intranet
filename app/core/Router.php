<?php
// app/core/Router.php

class Router {
    protected $routes = [];
    protected $basePath = '';

    public function __construct() {
        // Detecta o diretorio base de los controladores
        $this->basePath = dirname(__DIR__);
        
        // Registra el inicio del router
        file_put_contents(__DIR__ . '/router_debug.log', 
            date('c') . " | Router iniciado | REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n", FILE_APPEND);
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

        // Suporte a X-HTTP-Method-Override e _method para formulários multipart
        if ($method === 'POST') {
            if (!empty($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
                $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
            } elseif (isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
            }
        }

        // Log das informações recebidas
        file_put_contents(__DIR__ . '/router_debug.log', 
            date('c') . " | Router::dispatch | Método: $method | URI processada: $uri\n", FILE_APPEND);

        // Verifica as rotas registradas e executa a primeira correspondente
        $routeFound = false;
        $params = [];
        
        if (array_key_exists($method, $this->routes)) {
            // Log das rotas registradas para este método
            file_put_contents(__DIR__ . '/router_debug.log', 
                date('c') . " | Router::dispatch | Rotas registradas para $method: " . 
                json_encode(array_keys($this->routes[$method])) . "\n", FILE_APPEND);
                
            foreach ($this->routes[$method] as $route => $callback) {
                // Converte a rota em um padrão regex para buscar variáveis
                $pattern = $this->convertRouteToRegex($route);
                
                // Log do padrão sendo testado e da chave da rota
                file_put_contents(__DIR__ . '/router_debug.log', 
                    date('c') . " | Router::dispatch | Testando rota (chave): '" . $route . "' | Padrão: '" . $pattern . "' | Contra URI: '" . $uri . "'\n", FILE_APPEND);
                  if (preg_match($pattern, $uri, $matches)) {
                    // Remove o índice completo do match
                    array_shift($matches);
                    $params = $matches;

                    // Log dos parâmetros para depuração
                    file_put_contents(__DIR__ . '/router_debug.log', 
                        date('c') . " | Router::dispatch | ¡COINCIDENCIA ENCONTRADA! Rota correspondente: $route | Parâmetros: " . 
                        json_encode($params) . "\n", FILE_APPEND);
                    
                    if (is_callable($callback)) {
                        // Se for um closure, executa diretamente passando os parâmetros
                        file_put_contents(__DIR__ . '/router_debug.log', 
                            date('c') . " | Router::dispatch | Executando closure\n", FILE_APPEND);
                        call_user_func_array($callback, $params);
                    } else if (is_string($callback) && strpos($callback, '@') !== false) {
                        // Se for string no formato "Controller@method"
                        file_put_contents(__DIR__ . '/router_debug.log', 
                            date('c') . " | Router::dispatch | Chamando controller: $callback\n", FILE_APPEND);
                        $this->callControllerMethod($callback, $params);
                    }
                    
                    $routeFound = true;
                    break;
                } else {
                    file_put_contents(__DIR__ . '/router_debug.log', 
                        date('c') . " | Router::dispatch | Patrón '" . $pattern . "' NO coincide con URI: '" . $uri . "'\n", FILE_APPEND);
                }
            }
        }

        if (!$routeFound) {
            // Log de rota não encontrada para depuração
            file_put_contents(__DIR__ . '/router_debug.log', 
                date('c') . " | Router::dispatch | ERRO: Rota não encontrada para URI: $uri e método: $method\n", FILE_APPEND);
            $this->notFound();
        }
    }    // Converte padrões de rota como 'funcionarios/{id}' para regex de forma robusta
    private function convertRouteToRegex($route) {
        // Usa # como delimitador para não precisar escapar a barra '/'
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $route);
        $regex = '#^' . $pattern . '$#';

        // Log para depuração
        file_put_contents(__DIR__ . '/router_debug.log', 
            date('c') . " | Router::convertRouteToRegex | Rota: '$route' -> Regex: '$regex'\n", FILE_APPEND);
        
        return $regex;
    }

    // Método separado para isolar a lógica de chamar um controlador
    private function callControllerMethod($callback, $params = []) {
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
            
            // Log dos parâmetros antes de chamar o método
            file_put_contents(__DIR__ . '/router_debug.log', 
                date('c') . " | Router::callControllerMethod | Chamando {$controllerName}@{$method} com parâmetros: " . 
                json_encode($params) . "\n", FILE_APPEND);
                
            // Chama o método do controlador passando os parâmetros extraídos da URL
            call_user_func_array([$controller, $method], $params);
            
        } catch (Exception $e) {
            // Log do erro para debug (pode ser removido ou ajustado em produção)
            file_put_contents(__DIR__ . '/router_debug.log', 
                date('c') . " | Router::callControllerMethod | ERRO: " . $e->getMessage() . 
                " | Pilha: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            $this->notFound();
        }
    }    // Obtém a URI limpa da requisição
    private function getUri() {
        // Obtém a URI original
        $originalUri = $_SERVER['REQUEST_URI'];
        
        // Log da URI original para depuração
        file_put_contents(__DIR__ . '/router_debug.log', 
            date('c') . " | Router::getUri | URI original: $originalUri\n", FILE_APPEND);
        
        // Remover parâmetros da query string (se houver)
        $uri = parse_url($originalUri, PHP_URL_PATH);
        $uri = trim($uri, '/');      // Enfoque mais direto para extraer a parte relevante de la URI
        if (strpos($uri, 'Itaipu-intranet/api/') === 0) {
            // Elimina "Itaipu-intranet/api/" del principio
            $uri = substr($uri, strlen('Itaipu-intranet/api/'));
            file_put_contents(__DIR__ . '/router_debug.log', 
                date('c') . " | Router::getUri | Detectado prefijo 'Itaipu-intranet/api/' -> $uri\n", FILE_APPEND);
        } else if (strpos($uri, 'api/') === 0) {
            // Elimina "api/" del principio
            $uri = substr($uri, strlen('api/'));
            file_put_contents(__DIR__ . '/router_debug.log', 
                date('c') . " | Router::getUri | Detectado prefijo 'api/' -> $uri\n", FILE_APPEND);
        } else if (strpos($uri, 'app/api.php/') !== false) {
            // Elimina todo hasta después de "app/api.php/"
            $uri = substr($uri, strpos($uri, 'app/api.php/') + strlen('app/api.php/'));
            file_put_contents(__DIR__ . '/router_debug.log', 
                date('c') . " | Router::getUri | Encontrado 'app/api.php/' en URI -> $uri\n", FILE_APPEND);
        } else if (strpos($uri, 'Itaipu-intranet/app/api.php/') !== false) {
            // Elimina todo hasta depois de "Itaipu-intranet/app/api.php/"
            $uri = substr($uri, strpos($uri, 'Itaipu-intranet/app/api.php/') + strlen('Itaipu-intranet/app/api.php/'));
            file_put_contents(__DIR__ . '/router_debug.log', 
                date('c') . " | Router::getUri | Encontrado 'Itaipu-intranet/app/api.php/' en URI -> $uri\n", FILE_APPEND);
        }
        
        // Normalizar: remover barra final, se houver
        $uri = rtrim($uri, '/');
        
        // Log da URI processada para depuração
        file_put_contents(__DIR__ . '/router_debug.log', 
            date('c') . " | Router::getUri | URI processada final: $uri\n", FILE_APPEND);
            
        return $uri;
    }

    // Retorna uma resposta 404 em formato JSON
    private function notFound() {
        // Garantir cabeçalho JSON e status 404
        header('Content-Type: application/json');
        http_response_code(404);
        
        echo json_encode([
            'status' => 'error', 
            'message' => 'Endpoint não encontrado.'
        ]);
        
        exit;
    }
    
    // Obtiene todas las rutas registradas
    public function getRegisteredRoutes() {
        $allRoutes = [];
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route => $callback) {
                $allRoutes[] = "$method:$route";
            }
        }
        return $allRoutes;
    }
    
    // Obtiene la ruta actual que se está solicitando
    public function getCurrentRoute() {
        return $this->getUri();
    }
}
