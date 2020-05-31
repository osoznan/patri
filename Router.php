<?php

namespace app\system;

class Router extends Component {

    public $url;

    public $controller;
    public $action;


    protected function getController($controllerPath) {

    }


    /**
     * Convert url to framework router params.
     * @param $url string Url to show
     * @return array
     */
    public function getData($url) {
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

        var_dump($urlPathParts); echo '<br>';

        $curPath = '';
        $basePath = self::basePath() . 'modules';
        foreach ($urlPathParts as $part) {
            if (empty($part)) {
                $part = $this->defaultControllerName;
            }

            echo $curPath. '<br>';

            echo $basePath . '/' . $curPath . '/' . ucfirst($part) . 'Controller.php' . '<br>';

            if (is_dir($basePath . '/' . $curPath)) {
                if (is_file($basePath . '/' . $curPath . '/' . 'Module.php')) {
                    $curPath .= '/controllers/' . $part;
                } else {
                    $curPath .= $part;
                }

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
            } else {
                throw new \Exception('wrong route: ' . $curPath);
            }
        }
    }

}
