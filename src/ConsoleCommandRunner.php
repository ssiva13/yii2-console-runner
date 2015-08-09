<?php

namespace tebazil\runner;

class ConsoleCommandRunner
{
    private $_outerApplication;
    private $_innerApplication;
    private $_output;
    private $_exitCode;

    public function __construct($config = false)
    {
        if (!$config) {
            $config = \Yii::getAlias('@app/config/console.php');
        }
        if (is_string($config)) {
            $config = require($config);
        }
        // fcgi doesn't have STDIN and STDOUT defined by default
        defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
        defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));
        $this->_outerApplication = \Yii::$app;
        $this->_innerApplication = new \yii\console\Application($config); //this changes \Yii::$app;
        \Yii::$app=$this->_outerApplication; //we set it back
    }

    public function run($route, array $params = [])
    {
        $this->_output = null;
        $this->_exitCode = null;
        \Yii::$app=$this->_innerApplication; //Yii::$app references to console application, while you are running your command
        ob_start();
        $this->_exitCode = $this->_innerApplication->runAction($route, $params);
        $this->_output = ob_get_clean();
        \Yii::$app=$this->_outerApplication; //now Yii::$app is outer application again (typically web application)
        return $this;
    }

    public function getOutput()
    {
        return $this->_output;
    }

    public function getExitCode()
    {
        return $this->_exitCode;
    }

}