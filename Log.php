<?php

class Log {
    private string $log;
    public function Add(mixed $log): void
    {
        $this->log .= $log.PHP_EOL;
    }
    public function Echo(): void{
        echo $this->log;
    }
    private static ?Log $instance = null;
    public static function getInstance(): Log
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()    {
        $this->log = '';
    }
    private function __clone()    {
    }

}