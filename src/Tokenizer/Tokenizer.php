<?php
namespace PHPCube\Tokenizer;

define('TC_ASYNC', 1001); // async
define('TC_AWAIT', 1002); // await
define('TC_DEFER', 1003); // defer

class Tokenizer {
    public static function name(int $id): string {
        switch($id){
            case 1001: return TC_ASYNC;
            case 1002: return TC_AWAIT;
            case 1003: return TC_DEFER;
        }
        return token_name($id);
    }

    public static function all(string $code): array {
        $tokens = token_get_all($code);
        for($i = 0; isset($tokens[$i]); ++$i){
            $token = &$tokens[$i];
            if(is_array($token)){
                $tokenId = $token[0];
                $tokenCode = strtolower($token[1]);
                if($tokenId == T_STRING){
                    if($tokenCode == 'async'){
                        $token[0] = TC_ASYNC;
                    }elseif($tokenCode == 'await'){
                        $token[0] = TC_AWAIT;
                    }elseif($tokenCode == 'defer'){
                        $token[0] = TC_DEFER;
                    }
                }
            }
        }
        return $tokens;
    }

    public static function merge(array|Scanner $tokens): string {
        $code = '';
        for($offset = 0; isset($tokens[$offset]); ++$offset){
            $token = $tokens[$offset];
            if(is_array($token)){
                $code .= $token[1];
            }else{
                $code .= $token;
            }
        }
        return $code;
    }

    public static function withName(array|Scanner $tokens): array {
        if($tokens instanceof Scanner){
            $tokens = $tokens->all();
        }
        for($i = 0; isset($tokens[$i]); ++$i){
            $token = $tokens[$i];
            if(is_array($token) && !isset($token[3])){
                $tokens[$i][3] = self::name($token[0]);
            }
        }
        return $tokens;
    }
}

?>