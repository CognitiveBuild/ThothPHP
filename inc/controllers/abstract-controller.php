<?php
abstract class AbstractController {
    
    protected $container;
    protected $view;

    public abstract static function get($method);

    function __construct($container) {

        $this->container = $container;
        $this->view = $container->get('view');
    }
}