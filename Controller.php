<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace app\system;

/**
 * Class Controller
 * The base Controller object
 */
class Controller extends Component {

    public static $frameName = 'default';

    public function run($action) {
        $this->beforeAction($action);
        $content = $this->{'action' . $action}();
        $this->afterAction($action);
        return $content;
    }

    public function beforeAction($action) {}

    public function afterAction($action) {}

    protected function render($viewName, $params = []) {
        $view = new View();
        return $view->render($viewName, $params);
    }

    public function getViewPath() {
        $a = get_called_class();
        $namespace = explode('\\', $a);

        $parts = array_slice($namespace, 2);
        $lastPart = end($parts);
        $lastPart = lcfirst(substr($lastPart, 0, strlen($lastPart) - 10 ));

        return 'views/' . $parts[0] . '/' . $lastPart . '/';
    }

    public function jsonRender($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    public function error404() {
        header("HTTP/1.0 404 Not Found");
    }
}
