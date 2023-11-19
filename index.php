<?php
require_once 'Autoloader.php';
Autoloader::register();
new Api();

/**
 * Simple API class
 */
class Api
{

    /**
     * DB object
     * @var PDO
     */
	private static PDO $db;

    /**
     * Keeps current request method and makes it available to other classes
     * @var string
     */
    public static string $requestMethod;

    /**
     * DB object setter
     * @return PDO
     */
	public static function getDb()
	{
		return self::$db;
	}

    /**
     * Entry point to the API
     */
	public function __construct()
	{
		self::$db = (new Database())->init();

        self::$requestMethod = $this->getRequestMethod();

        try{
            $this->response(
                $this->resolveRoute()
            );
        } catch (Exception $e) {
            $errors = json_decode($e->getMessage());

            if ($errors == null) {
                $errors = $e->getMessage();
            }

            $this->response(['errors' => $errors]);
        }
	}

    /**
     * Returns requested URI
     * @return string
     */
    private function getUri():string
    {
        return strtolower(trim((string)($_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI']), '/'));
    }

    /**
     * Returns request method
     * @return string
     */
    public function getRequestMethod():string
    {
        return isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';
    }

    /**
     * Returns routes
     * @return array[]
     */
    private function listRoutes():array
    {
        return [
            'get constructionStages' => [
                'class' => 'ConstructionStages',
                'method' => 'getAll',
            ],
            'get constructionStages/(:num)' => [
                'class' => 'ConstructionStages',
                'method' => 'getSingle',
            ],
            'post constructionStages' => [
                'class' => 'ConstructionStages',
                'method' => 'post',
                'bodyType' => 'ConstructionStagesData'
            ],
            'patch constructionStages/(:num)' => [
                'class' => 'ConstructionStages',
                'method' => 'update',
                'bodyType' => 'ConstructionStagesData'
            ],
            'delete constructionStages/(:num)' => [
                'class' => 'ConstructionStages',
                'method' => 'delete'
            ],
        ];
    }

    /**
     * Returns wildcards
     * @return string[]
     */
    private function listWildcards():array
    {
        return [
            ':any' => '[^/]+',
            ':num' => '[0-9]+',
        ];
    }

    /**
     * Resolving route
     * @return mixed
     * @throws Exception
     */
    private function resolveRoute():mixed
    {
        $uri = $this->getUri();
        $httpVerb = $this->getRequestMethod();
        $wildcards = $this->listWildcards();
        $routes = $this->listRoutes();

        if ($uri) {
            foreach ($routes as $pattern => $target) {
                $pattern = str_replace(array_keys($wildcards), array_values($wildcards), $pattern);
                if (preg_match('#^'.$pattern.'$#i', "{$httpVerb} {$uri}", $matches)) {
                    $params = [];
                    array_shift($matches);
                    if ($httpVerb === 'post' || $httpVerb === 'patch') {
                        $data = json_decode(file_get_contents('php://input'));

                        if ($data == null) {
                            throw new Exception('Input data is invalid or not an acceptable JSON format');
                        }

                        $params = [new $target['bodyType']($data)];
                    }
                    $params = array_merge($params, $matches);
                    return call_user_func_array([new $target['class'], $target['method']], $params);
                }
            }
        }

        throw new Exception("Route doesn't exist.");
    }

    /**
     * API response function
     * @param $response
     * @return void
     */
    private function response($response):void
    {
        if (php_sapi_name() != 'cli')
        {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($response, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }
}