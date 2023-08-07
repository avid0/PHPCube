<?php
namespace PHPCube\Compiler;

use PHPCube\Tokenizer\Scanner;
use PHPCube\Tokenizer\Tokenizer;

class Translator {
    public Scanner $scanner;

    public function __construct(array|string|Scanner $subject){
        if($subject instanceof Scanner){
            $this->scanner = $subject;
        }else{
            $this->scanner = new Scanner($subject);
        }
    }

    public function translate(callable $func): void {
        $translated = '';
        $this->scanner->loop(function($scanner)use(&$translated, $func){
            $result = $func($scanner);
            if(is_array($result)){
                $result = Tokenizer::merge($result);
            }
            $translated .= $result;
        });
        $this->scanner->all($translated);
    }

    public function replace(array|string $search, string|callable|array $replace): void {
        $translated = '';
        if(is_array($search)){
            foreach($search as $offset => $item){
                if(is_array($item)){
                    $search[$offset] = Tokenizer::merge($item);
                }
            }
            $this->scanner->loop(function($scanner)use(&$translated, $search, $replace){
                foreach($search as $offset => $item){
                    $scan = $scanner->scan($item);
                    if($scan){
                        $to = is_array($replace) ? $replace[$offset] : $replace;
                        if(is_callable($to)){
                            $result = $to($scan, $scanner);
                            if(is_array($result)){
                                $result = Tokenizer::merge($result);
                            }
                        }else{
                            $result = $to;
                        }
                        $translated .= $result;
                        return;
                    }
                }
                $result = $scanner->token();
                if(is_array($result)){
                    $result = $result[1];
                }
                $translated .= $result;
            });
        }else{
            if(is_array($replace)){
                $replace = Tokenizer::merge($replace);
            }
            $this->scanner->loop(function($scanner)use(&$translated, $search, $replace){
                $scan = $scanner->scan($search);
                if($scan){
                    if(is_callable($replace)){
                        $result = $replace($scan, $scanner);
                        if(is_array($result)){
                            $result = Tokenizer::merge($result);
                        }
                    }else{
                        $result = $replace;
                    }
                    $translated .= $result;
                }else{
                    $result = $scanner->token();
                    if(is_array($result)){
                        $result = $result[1];
                    }
                    $translated .= $result;
                }
            });
        }
        $this->scanner->all($translated);
    }

    public function replaceList(array $list): void {
        $search = [];
        $replace = [];
        foreach($list as $searchItem => $replaceItem){
            $search[] = $searchItem;
            $replace[] = $replaceItem;
        }
        $this->replace($search, $replace);
    }

    public function select(string $pattern): SelectiveTranslator {
        $scanner = $this->scanner->scan($pattern);
        return new SelectiveTranslator($this, $pattern, $scanner);
    }

    public function __toString(): string {
        return Tokenizer::merge($this->scanner->all());
    }
}

?>