<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace app\system;

/**
 * Class View
 * The base View object
 */
class View extends Component {

    static $_cssFiles = [];

    public function render($viewName, $params = []) {
        ob_start();
        ob_implicit_flush(false);

        foreach ($params as $param => $value) {
            $$param = $value;
        }

        require(Top::$app->basePath . "/views/$viewName.php");

        return ob_get_clean();
    }

    public function insertCssFile($file) {

    }
}
