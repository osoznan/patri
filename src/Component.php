<?php
/**
 * User: Zemlyansky Alexander <astrolog@online.ua>
 */

namespace osoznan\patri;

/**
 * Class Component
 * The base component object. It works with events and properties
 */
class Component extends TopObject {
    private $_events = [];

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        $this->init();
    }

    public function init() {}

    public static function setConfig($object, $params) {
        if (!$params) {
            return;
        }

        foreach ($params as $key => $data) {
            if (is_array($data)) {
                if (method_exists($object, $methodName = 'set' . ucfirst($key))) {
                    $object->$methodName($data);

                } elseif (isset($data['class'])) {
                    $newObject = new $data['class']();
                    unset($data['class']);

                    self::setConfig($newObject, $data);
                    $object->$key = $newObject;
                } else {
                    $object->{$key} = $data;
                }
            } else {
                $object->{$key} = $data;
            }
        }
    }

    public function on($name, $func, $data = null) {
        $this->_events[$name][] = [$func, $data];
    }

    public function off($name, $func = null) {
        if (!$func) {
            unset($this->_events[$name]);
        }

        $target = array_search($func, $this->_events);
        if ($target !== false) {
            unset($this->_events[$target]);
        }
    }

    public function trigger($name, $event = null) {
        if (isset($this->_events[$name])) {
            $handlers = $this->_events[$name];
        } else {
            return;
        }

        if ($event == null) {
            $event = new Event();
        }

        foreach ($handlers as $handler) {
            call_user_func($handler[0], $event);
        }
    }

    public function getHandler($name) {
        return $this->_events[$name];
    }

    public function setOn($events) {
        foreach ($events as $name => $event) {
            $this->on($name, $event);
        }
    }
}
