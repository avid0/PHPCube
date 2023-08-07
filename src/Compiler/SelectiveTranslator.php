<?php
namespace PHPCube\Compiler;

use PHPCube\Tokenizer\Scanner;

class SelectiveTranslator extends Translator {
    public Translator $translator;
    public string $selectPattern;

    public function __construct(Translator $translator, string $selectPattern, Scanner $scanner){
        $this->translator = $translator;
        $this->selectPattern = $selectPattern;
        $this->scanner = $scanner;
    }

    public function __destruct(){
        $this->translator->replace($this->selectPattern, $this->scanner->all());
    }
}

?>