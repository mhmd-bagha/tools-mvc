<?php

class Controller
{
    function __construct() {}

    function view($view, $header = 'require', $footer = 'require')
    {
        if ($header == 'require') {require('header.php');}
        require('views/' . $view . '.php');
        if ($footer == 'require') {require('footer.php');}
    }


    function model($modelUrl)
    {
        require('models/model_' . $modelUrl . '.php');
        $classname = 'model_' . $modelUrl;
        $this->model = new $classname;
    }

}