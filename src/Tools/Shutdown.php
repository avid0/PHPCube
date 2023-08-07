<?php
namespace PHPCube\Tools;

class Shutdown {
    private static array $callables = [];

    private $callable;

    public function __construct(callable $callable){
        $this->callable = $callable;
    }

    public function __destruct(){
        if($this->callable){
            ($this->callable)();
        }
    }

    public function close(): void {
        $this->callable = null;
    }

    public static function register(callable $callable): void {
        $shutdown = new Shutdown($callable);
        self::$callables[] = $shutdown;
    }
}

?>