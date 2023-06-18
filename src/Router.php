<?php

// src/Router.php

namespace PedroQuezado\Code\Router;

class Router {
    private $routes = []; // Armazena as rotas definidas
    private $domain; // Domínio do projeto
    private $separator; // Separador de callback (padrão: "::")
    private $namespace = ''; // Namespace para as chamadas de métodos de classe
    private $routeGroups = []; // Grupos de rota
    private $middlewares = []; // Middlewares das rotas
    private $namedRoutes = []; // Nomes de rota
    private $cachedRoutes; // Rotas em cache

    /**
     * Construtor da classe Router.
     *
     * @param string $domain Domínio do projeto
     * @param string $separator Separador de callback (padrão: "::")
     */
    public function __construct(string $domain, string $separator = "::")
    {
        $this->domain = rtrim($domain, "/"); // Remove a barra final do domínio, se houver
        $this->separator = $separator;
    }

    /**
     * Define o namespace para as chamadas de métodos de classe.
     *
     * @param string $namespace O namespace a ser definido
     * @return void
     */
    public function namespace(string $namespace): void
    {
        $this->namespace = $namespace;
    }

    /**
     * Define um grupo de rota.
     *
     * @param array $attributes Atributos do grupo de rota
     * @param \Closure $callback Função de callback para definir as rotas do grupo
     * @return void
     */
    public function group(array $attributes, \Closure $callback): void
    {
        $this->routeGroups[] = $attributes;
        $callback();
        array_pop($this->routeGroups);
    }

    /**
     * Define um middleware para as rotas.
     *
     * @param mixed $middleware Middleware a ser aplicado (função ou classe de middleware)
     * @return void
     */
    public function middleware($middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    /**
     * Define uma rota HTTP GET.
     *
     * @param string $path Caminho da rota
     * @param mixed $callback Callback da rota (função ou método de classe)
     * @return void
     */
    public function get(string $path, $callback): void
    {
        $this->addRoute('GET', $path, $callback);
    }

    /**
     * Define uma rota HTTP POST.
     *
     * @param string $path Caminho da rota
     * @param mixed $callback Callback da rota (função ou método de classe)
     * @return void
     */
    public function post(string $path, $callback): void
    {
        $this->addRoute('POST', $path, $callback);
    }

    /**
     * Define uma rota HTTP PUT.
     *
     * @param string $path Caminho da rota
     * @param mixed $callback Callback da rota (função ou método de classe)
     * @return void
     */
    public function put(string $path, $callback): void
    {
        $this->addRoute('PUT', $path, $callback);
    }

    /**
     * Define uma rota HTTP PATCH.
     *
     * @param string $path Caminho da rota
     * @param mixed $callback Callback da rota (função ou método de classe)
     * @return void
     */
    public function patch(string $path, $callback): void
    {
        $this->addRoute('PATCH', $path, $callback);
    }

    /**
     * Define uma rota HTTP DELETE.
     *
     * @param string $path Caminho da rota
     * @param mixed $callback Callback da rota (função ou método de classe)
     * @return void
     */
    public function delete(string $path, $callback): void
    {
        $this->addRoute('DELETE', $path, $callback);
    }

    /**
     * Adiciona uma nova rota.
     *
     * @param string $method Método HTTP da rota (GET, POST, etc.)
     * @param string $path Caminho da rota
     * @param mixed $callback Callback da rota (função ou método de classe)
     * @return void
     */
    private function addRoute(string $method, string $path, $callback): void
    {
        $route = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback,
            'middlewares' => $this->middlewares,
            'groupName' => end($this->routeGroups)['name'] ?? null
        ];

        $this->routes[] = $route;

        if (isset($route['name'])) {
            $this->namedRoutes[$route['name']] = $route;
        }

        $this->middlewares = [];
    }

    /**
     * Executa o roteamento das requisições.
     *
     * @return void
     */
    public function run(): void
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $matchedRoute = $this->findMatchingRoute($requestMethod, $requestPath);

        if ($matchedRoute) {
            $this->executeRoute($matchedRoute);
        } else {
            $this->renderErrorPage(404, 'Page not found');
        }
    }

    /**
     * Encontra a rota correspondente à requisição atual.
     *
     * @param string $requestMethod Método da requisição atual
     * @param string $requestPath Caminho da requisição atual
     * @return array|null Rota correspondente ou null se nenhuma rota corresponder
     */
    private function findMatchingRoute(string $requestMethod, string $requestPath): ?array
    {
        if ($this->cachedRoutes === null) {
            $this->cachedRoutes = $this->cacheRoutes();
        }

        foreach ($this->cachedRoutes as $route) {
            if ($this->matchRoute($route, $requestMethod, $requestPath)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Armazena em cache as rotas definidas.
     *
     * @return array Rotas em cache
     */
    private function cacheRoutes(): array
    {
        $cachedRoutes = [];

        foreach ($this->routes as $route) {
            $pattern = $this->convertPathToRegex($route['path']);
            $route['pattern'] = $pattern;
            $cachedRoutes[] = $route;
        }

        return $cachedRoutes;
    }

    /**
     * Verifica se a rota atual corresponde à rota definida.
     *
     * @param array $route Rota definida
     * @param string $requestMethod Método da requisição atual
     * @param string $requestPath Caminho da requisição atual
     * @return bool true se a rota corresponder, false caso contrário
     */
    private function matchRoute(array $route, string $requestMethod, string $requestPath): bool
    {
        if ($route['method'] !== $requestMethod) {
            return false;
        }

        $pattern = $route['pattern'];

        // Substitui os grupos de rota na expressão regular
        $pattern = $this->replaceRouteGroups($pattern);

        // Verifica se a rota corresponde ao padrão
        if (!preg_match($pattern, $requestPath, $matches)) {
            return false;
        }

        $route['matches'] = $matches;
        return true;
    }

    /**
     * Executa a rota correspondente à requisição atual.
     *
     * @param array $route Rota correspondente
     * @return void
     */
    private function executeRoute(array $route): void
    {
        $callback = $route['callback'];
        $matches = $route['matches'];

        // Verifica se a rota pertence a um grupo
        if (!empty($route['groupName'])) {
            $group = $this->getRouteGroupByName($route['groupName']);
            $callback = $this->wrapMiddleware($callback, $group['middlewares']);
        }

        // Executa o callback da rota
        if (is_callable($callback)) {
            call_user_func_array($callback, array_slice($matches, 1));
        } else {
            $this->executeControllerMethod($callback, $matches);
        }
    }

    /**
     * Executa o método de um controlador.
     *
     * @param string $callback Callback do método do controlador (ex: "Controller@method")
     * @param array $matches Parâmetros capturados na rota
     * @return void
     */
    private function executeControllerMethod(string $callback, array $matches): void
    {
        list($controller, $method) = explode($this->separator, $callback);
        $controller = $this->namespace . '\\' . $controller;

        if (class_exists($controller)) {
            $controllerInstance = new $controller();

            if (method_exists($controllerInstance, $method)) {
                call_user_func_array([$controllerInstance, $method], array_slice($matches, 1));
            } else {
                $this->renderErrorPage(500, 'Internal Server Error');
            }
        } else {
            $this->renderErrorPage(500, 'Internal Server Error');
        }
    }

    /**
     * Substitui os grupos de rota na expressão regular.
     *
     * @param string $pattern Expressão regular da rota
     * @return string Expressão regular modificada
     */
    private function replaceRouteGroups(string $pattern): string
    {
        foreach ($this->routeGroups as $group) {
            $prefix = isset($group['prefix']) ? $group['prefix'] : '';
            $pattern = str_replace('{' . $group['name'] . '}', $prefix, $pattern);
        }

        return $pattern;
    }

    /**
     * Retorna um grupo de rota pelo nome.
     *
     * @param string $name Nome do grupo de rota
     * @return array|null Grupo de rota correspondente ou null se não encontrado
     */
    private function getRouteGroupByName(string $name): ?array
    {
        foreach ($this->routeGroups as $group) {
            if ($group['name'] === $name) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Retorna o caminho de uma rota pelo nome.
     *
     * @param string $name Nome da rota
     * @param array $parameters Parâmetros da rota
     * @return string|null Caminho da rota correspondente ou null se não encontrado
     */
    public function route(string $name, array $parameters = []): ?string
    {
        if (isset($this->namedRoutes[$name])) {
            $route = $this->namedRoutes[$name];
            $path = $route['path'];

            // Substitui os parâmetros na rota
            foreach ($parameters as $key => $value) {
                $path = str_replace('{' . $key . '}', $value, $path);
            }

            return $this->domain . $path;
        }

        return null;
    }

    /**
     * Converte um caminho de rota em uma expressão regular.
     *
     * @param string $path Caminho da rota
     * @return string Expressão regular correspondente
     */
    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_quote($path, '/');

        // Substitui os parâmetros da rota
        $pattern = preg_replace('/\\\{([A-Za-z0-9_]+)\\\}/', '([^\/]+)', $pattern);

        return '/^' . $pattern . '$/';
    }

    /**
     * Renderiza uma página de erro.
     *
     * @param int $statusCode Código de status HTTP
     * @param string $message Mensagem de erro
     * @return void
     */
    private function renderErrorPage(int $statusCode, string $message): void
    {
        http_response_code($statusCode);
        echo "<h1>Error $statusCode</h1>";
        echo "<p>$message</p>";
        exit;
    }
    
    /**
     * Retorna as informações de depuração para var_dump.
     *
     * @return array Informações de depuração
     */
    public function __debugInfo(): array
    {
        return [
            'routes' => $this->routes,
            'domain' => $this->domain,
            'separator' => $this->separator,
            'namespace' => $this->namespace,
        ];
    }
}
