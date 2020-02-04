<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace app\system;

use mysql_xdevapi\Exception;

class App {

    public static $defaultControllerName = 'site';

    protected static $config;

    public static function getConfig($param) {
        if (!static::$config) {
            $config = require('config/config.php');
            return $config[$param];
        }

        return static::$config;
    }

    /**
     * Convert url to framework router params.
     * Just over-simple thing, no special checks made (BeeJee task doesn't wait too long)
     *
     * @param $url string Url to show
     * @return array
     */
    private function urlPartsToRouteElements($url) {
        $parts = explode('?', $url);
        $urlPath = trim($parts[0], '/');

        // if query string exist, set GET data properly
        if (isset($parts[1])) {
            foreach (explode('&', $parts[1]) as $pair) {
                $elem = explode('=', $pair);
                $_GET[$elem[0]] = $elem[1];
            }
        }

        $urlPathParts = explode('/', $urlPath);

        $curPath = '';
        $basePath = self::basePath() . '/controllers';
        for ($i = 0; $i < count($urlPathParts); $i++) {
            $part = $urlPathParts[$i];
            if (empty($part)) {
                $part = 'site';
            }

            $nextProbablyDir = $basePath . '/' . $curPath . '/' . $part;
            if (is_dir($nextProbablyDir)) {
                $curPath .= '/' . $part;
            } elseif (is_file($basePath . '/' . $curPath . '/' . ucfirst($part) . 'Controller.php')) {

                $fullClassName = 'app\\controllers' . str_replace('/', '\\', $curPath) . '\\' . ucfirst($part) . 'Controller';

                if (class_exists($fullClassName)) {
                    $controllerInstance = new $fullClassName();
                    $action = $urlPathParts[$i + 1] ?? 'index';
                    $action = str_replace('-', '_', $action);

                    if (method_exists($controllerInstance, "action" . ucfirst($action))) {
                        return [
                            'controller' => $controllerInstance,
                            'action' => $action
                        ];
                    } else {
                       throw new \Exception('wrong controller action: ' . $action);
                    }
                } else {
                    throw new \Exception('wrong controller class: ' . $fullClassName);
                }
            }
        }
    }

    /**
     * Launches the framework itself
     *
     * @param $url
     */
    public function run($url) {
        if ($mapResult = $this->resolveUrlByControllerMap($url)) {
            $url = $mapResult;
        }

        $routeInfo = $this->urlPartsToRouteElements($url);

        $controller = $routeInfo['controller'];
        $action = $routeInfo['action'];

        try {
            // here goes all!
            if (!isset($_POST['ajax'])) {
                ob_start();
                ob_implicit_flush(false);

                $controllerOutput = $controller->run($action);

                $out = ob_get_clean() . $controllerOutput;

                $totalOutput = View::render('frames/' . $controller::$frameName, [
                    'content' => $out
                ]);
            } else {
                echo $controller->run($action);
            }

            // ЗДЕСЬ ВСЁ И ВЫВОДИТСЯ!!
            echo $totalOutput ?? null;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    protected function resolveUrlByControllerMap($url) {
        $map = App::getConfig('controllerMap');

        foreach ($map as $pattern => $replace) {
            $result = preg_replace('/' . $pattern . '/', $replace, $url, 1, $count);
            if ($count) {
                return $result;
            }
        }

        return false;
    }

    public static function basePath() {
        return $_SERVER['DOCUMENT_ROOT'];
    }

}
