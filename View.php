<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace app\system;

/**
 * Class View
 * The base View object
 */
class View extends TopObject {

    public static function render($viewName, $params = []) {
        ob_start();
        ob_implicit_flush(false);

        foreach ($params as $param => $value) {
            $$param = $value;
        }

        $a = require("views/$viewName.php");

        return ob_get_clean();
    }
}
