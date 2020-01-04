<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace app\system;

/**
 * Class Controller
 * The base Controller object
 */
class Controller extends TopObject {

    public static $frameName = 'default';

    public function run($action) {
        $content = $this->{'action' . $action}();
        return $content;
    }

    protected function render($viewName, $params = []) {
        return View::render($viewName, $params);
    }

    public function error404() {
        header("HTTP/1.0 404 Not Found");
    }
}
