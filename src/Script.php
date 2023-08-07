<?php
namespace PHPCube;

use PHPCube\Compiler\Translator;
use PHPCube\Tokenizer\Scanner;
use PHPCube\Compiler\VariableCompactor;
use PHPCube\Tools\Evalable;

class Script {
    const RANDOM_LENGTH = 4;

    public Translator $translator;
    public Scanner $scanner;

    public function __construct(array|string $subject){
        $this->translator = new Translator($subject);
        $this->scanner = $this->translator->scanner;
    }

    public static function file(string $file): Script|bool {
        if(file_exists($file)){
            return new Script(file_get_contents($file));
        }else{
            return false;
        }
    }

    public function compile(): bool {
        $this->translator->replaceList([
            'async/?space/=code=(:{:}|single-expression)' => function($scan){
                $code = (string)$scan->name('code');
                if($code[0] == '{'){
                    $code = substr($code, 1, -1);
                }
                $compactor = VariableCompactor::generate();
                $callable = "function()use(&{$compactor->parent}){{$compactor->extractor}{$code}}";
                $singleLine = "[eval('{$compactor->compactor}'),async({$callable}),eval('$compactor->remover')][1];";
                return $singleLine;
            },
            'await/?space/=code=single-expression' => function($scan){
                $code = (string)$scan->name('code');
                $code = substr($code, 0, -1);
                return "{$code}->await();";
            },
            'defer/?space/=code=(:{:}|single-expression)' => function($scan){
                $code = (string)$scan->name('code');
                if($code[0] == '{'){
                    $code = substr($code, 1, -1);
                }
                $compactor = VariableCompactor::generate();
                $shutdownVar = VariableCompactor::name();
                $callable = "function()use(&{$compactor->parent}){{$compactor->extractor}{$code}}";
                $singleLine = "($$shutdownVar=[eval('{$compactor->compactor}'),new \PHPCube\Tools\Shutdown({$callable}),eval('$compactor->remover')][1]);";
                return $singleLine;
            },
            '=cosection=(fn/?space/?&/?space/:(:)/?space/?(:/?space/intersection-type/?space)/double-arrow/?space)/=code=:{:}' => function($scan){
                $cosection = (string)$scan->name('cosection');
                $code = (string)$scan->name('code');
                $code = substr($code, 1, -1);
                $compactor = VariableCompactor::generate();
                $callable = "function()use(&{$compactor->parent}){{$compactor->extractor}{$code}}";
                $singleLine = "[eval('{$compactor->compactor}'),$cosection({$callable})(),eval('$compactor->remover')][1]";
                return "$singleLine";
            },
            '=a=number/?space/:/?space/=b=number/?(?space/:/?space/=c=number)' => function($scan){
                $a = (string)$scan->name('a');
                $b = (string)$scan->name('b');
                if($scan->has('c')){
                    $c = (string)$scan->name('c');
                    return "range($a,$b,$c)";
                }
                return "range($a,$b)";
            },
        ]);
        return true;
    }

    public function script(): string {
        return (string)$this->scanner;
    }

    public function evalable(): Evalable {
        return new Evalable("?>" . (string)$this->scanner . "<?php");
    }

    public function __toString(): string {
        return (string)$this->scanner;
    }
}

?>