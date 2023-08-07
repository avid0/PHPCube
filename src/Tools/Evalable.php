<?php
namespace PHPCube\Tools;

class Evalable {
    private string $code;

    public function __construct(string $code){
        $this->code = $code;
    }

    public function close(){
        $this->code = null;
    }

    public function __destruct(){
        if($this->code){
            eval($this->code);
        }
    }

    public function code(){
        return $this->code;
    }

    public function __toString(): string {
        if($this->code){
            $code = $this->code;
            $this->code = '';
            return $code;
        }else{
            return ';';
        }
    }
}

?>