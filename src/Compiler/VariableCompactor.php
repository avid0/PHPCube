<?php
namespace PHPCube\Compiler;

use PHPCube\Script;

class VariableCompactor {
    public string $parent;
    public string $compactor;
    public string $extractor;
    public string $remover;

    public static function name(): string {
        $name = bin2hex(random_bytes(Script::RANDOM_LENGTH));
        $name = str_replace(['+', '/', '='], ['_', '_', ''], base64_encode($name));
        return "_CUBE_$name";
    }

    public static function generate(): VariableCompactor {
        $var1 = self::name();
        $var2 = self::name();
        $var3 = self::name();
        $object = new VariableCompactor;
        $object->parent = "$$var3";
        $object->remover = "unset($$var1,$$var2,$$var3);";
        $object->compactor = "$$var3=[];" .
            "foreach(get_defined_vars() as $$var1=>$$var2)" .
            "if(!in_array($$var1,[\"$var1\",\"$var2\",\"$var3\",\"GLOBALS\"]))" .
            "\${$var3}[$$var1]=&$$$var1;";
        $object->extractor = "foreach($$var3 as $$var1=>$$var2)" .
            "$$$var1=&\${$var3}[$$var1];{$object->remover}";
        return $object;
    }
}

?>