<?php

namespace osoznan\patri;

/**
 * Class Router
 * Makes route from url to controller/action and params
 */
class Router extends Component {
    /** @var App */
    public $application;

    public $controller;
    public $action;

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

       // var_dump($urlPathParts); echo '<br>';

        $curPath = '';
        $basePath = $this->application->basePath . 'apps';

        $part = reset($urlPathParts);

        do {
            if (is_dir($basePath . '/' . $curPath)) {
                $nextPart = next($urlPathParts);

                // if there is module then get the controllers dir and find the target there
                if (is_file($basePath . '/' . $part . '/' . 'App.php')) {
                    $curPath .= ($part . '/controllers');

                    if (is_file($basePath . '/' . $curPath . '/' . ucfirst($nextPart) . 'Controller.php')) {
                        $fullClassName = 'app\\' . str_replace('/', '\\', $curPath) . '\\' . ucfirst($nextPart) . 'Controller';

                        if (class_exists($fullClassName)) {
                            $controllerInstance = new $fullClassName();

                            $action = next($urlPathParts);
                            $action = $action ? $action : 'index';
                            $action = str_replace('-', '_', $action);

                            if (method_exists($controllerInstance, "action" . ucfirst($action))) {
                                return [
                                    'controller' => $controllerInstance,
                                    'action' => $action,
                                    'path' => $basePath . '/' . $part
                                ];
                            } else {
                                throw new \Exception('wrong controller action: ' . $action);
                            }
                        } else {
                            throw new \Exception('wrong controller class: ' . $fullClassName);
                        }
                    }

                } else {
                    $curPath .= $part;
                }
            } else {
                throw new \Exception('wrong route: ' . $curPath);
            }
        } while ($part = next($urlPathParts));
    }

}
