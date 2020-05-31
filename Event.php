<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace app\system;

class Component extends TopObject {

    private $_events = [];

    public function on($name, $func, $data = null) {
        $this->_events[$name][] = [$func, $data];
    }

    public function trigger($name, $event = null) {
        $eventHandlers = $this->events['name'] ?? [];
        if (!empty($eventHandlers))) {
            if ($event == null) {
                $event = new Event();
            }
        }


    }
}
