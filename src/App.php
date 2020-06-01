<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace osoznan\patri;

class App extends Component {
    const EVENT_AFTER_RUN = 'after-run';

    public $defaultControllerName = 'site';

    public $config;

    /** @var Controller */
    public $controller;

    public $basePath;

    public $components = [];

    public $action;

    /** @var Db */
    public $db;

    public $csrfToken;

    public function init() {
        $this->db = $this->get('db');
    }

    public function getConfig($selector) {
        $keys = explode('.', $selector);

        $config = [$this->config];
        foreach ($keys as $key) {
            $curr = end($config);
            if (isset($curr[$key])) {
                $config[] = $curr[$key];
            } else {
                return null;
            }
        }
        return end($config);
    }

    /**
     * Launches the framework itself
     * @param $url
     */
    public function run($url) {
        if ($mapResult = $this->resolveUrlByControllerMap($url)) {
            $url = $mapResult;
        }

        try {
            $router = new Router(['application' => $this]);
            $routeInfo = $router->getData($url);

            $this->controller = $routeInfo['controller'];
            $this->action = $routeInfo['action'];
            $this->basePath = $routeInfo['path'];

        } catch (\Exception $e) {
            // $this->controller = new SiteController();
            $this->action = 'error';
        }

        try {
            // here goes all!
            if (!Request::isAjax()) {
                ob_start();
                ob_implicit_flush(false);

                $controllerOutput = $this->controller->run($this->action);
                $out = ob_get_clean() . $controllerOutput;

                $view = new View();
                $totalOutput = $view->render('_frames/' . $this->controller::$frameName, [
                    'content' => $out
                ]);

            } else {
                echo $this->controller->run($this->action);
            }

            // ЗДЕСЬ ВСЁ И ВЫВОДИТСЯ!!
            echo $totalOutput ?? null;

            $this->trigger(self::EVENT_AFTER_RUN);

        } catch (\Exception $e) {
            $this->controller->error404();
            echo $e->getMessage();
        }
    }

    protected function resolveUrlByControllerMap($url) {
        $map = $this->getConfig('controllerMap');

        foreach ($map as $pattern => $replace) {
            $result = preg_replace('/' . $pattern . '/', $replace, $url, 1, $count);
            if ($count) {
                return $result;
            }
        }

        return false;
    }

    public function get($name) {
        if (!isset($this->components[$name]) || is_array($this->components[$name])) {
            if (!($conf = $this->getConfig("components.$name"))) {
                return null;
            }

            $class = $conf['class'];
            $component = new $class();

            $this->setConfig($component, $conf);

            $this->components[$name] = $component;
        }

        return $this->components[$name];
    }

    public function getCsrfToken() {
        if (!isset($this->csrfToken)) {
            $this->csrfToken = md5(time() . rand(0, 1000000));
            $_SESSION['_csrfToken'] = $this->csrfToken;
        }

        return $this->csrfToken;
    }

    public static function checkCsrfToken() {
        if (count(Request::post())) {
            if (Request::post('_csrf') != $_SESSION['_csrfToken']) {
                return false;
            }
        }

        return true;
    }
}
