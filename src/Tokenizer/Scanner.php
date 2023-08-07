<?php
namespace PHPCube\Tokenizer;

class Scanner implements \Iterator, \Countable, \ArrayAccess {
    private array $tokens;
    private array $aliases = [];
    public int $offset = 0;

    public function __construct(string|array $subject){
        $this->all($subject);
    }

    public function all(string|array $tokens = null): array {
        if($tokens === null){
            return $this->tokens;
        }
        if(is_string($tokens)){
            $tokens = Tokenizer::all($tokens);
        }
        $this->tokens = $tokens;
        return $tokens;
    }

    public function aliases(array $aliases = null): array {
        if($aliases === null){
            return $this->aliases;
        }
        $this->aliases = $aliases;
        return $aliases;
    }

    public function alias(string $key, array $slice): void {
        $this->aliases[$key] = $slice;
    }

    public function name(string $key): Scanner|null {
        $slice = $this->aliases[$key] ?? null;
        if(!$slice || !isset($slice[1])){
            return null;
        }
        $tokens = array_slice($this->tokens, $slice[0], $slice[1]);
        $scanner = new Scanner($tokens);
        return $scanner;
    }

    public function has(string $key): bool {
        return isset($this->aliases[$key]);
    }

    public function length(int $offset = null): int {
        $tokens = $this->tokens;
        if($offset === null){
            $offset = $this->offset;
        }
        $length = 0;
        for($i = 0; $i < $offset; ++$i){
            if(is_array($tokens[$i])){
                $length += strlen($tokens[$i][1]);
            }else{
                $length += strlen($tokens[$i]);
            }
        }
        return $length;
    }

    public function offset(int $offset = null): int {
        if($offset === null){
            return $this->offset;
        }else{
            $this->offset = $offset;
            return $offset;
        }
    }

    public function current(): array|string {
        return $this->tokens[$this->offset];
    }

    public function key(): int {
        return $this->offset;
    }

    public function next(): void {
        ++$this->offset;
    }

    public function valid(): bool {
        return isset($this->tokens[$this->offset]);
    }

    public function count(): int {
        return count($this->tokens);
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->tokens[$offset]) || isset($this->aliases[$offset]);
    }

    public function offsetGet(mixed $offset): string|array {
        return $this->tokens[$offset] ?? $this->name($offset);
    }

    public function offsetSet(mixed $key, mixed $value): void {
        $this->aliases[$key] = $value;
    }

    public function offsetUnset(mixed $key): void {
        if(isset($this->aliases[$key])){
            unset($this->aliases[$key]);
        }
    }

    public function token(int &$offset = null): array|string|bool {
        $tokens = $this->tokens;
        if($offset === null){
            $offset = &$this->offset;
        }
        if(!isset($tokens[$offset])){
            return false;
        }
        return $tokens[$offset++];
    }

    public function readLength(int $length, int &$offset = null): array {
        $tokens = $this->tokens;
        if($offset === null){
            $offset = &$this->offset;
        }
        $read = [];
        $readed = 0;
        while(true){
            if(!isset($tokens[$offset])){
                break;
            }
            if($readed >= $offset){
                break;
            }
            $read[] = $tokens[$offset];
            if(is_array($tokens[$offset])){
                $readed += strlen($tokens[$offset][1]);
            }else{
                $readed += strlen($tokens[$offset]);
            }
            ++$offset;
        }
        return $read;
    }
    
    public function merge(int &$offset = null): string {
        $tokens = $this->tokens;
        if($offset === null){
            $offset = &$this->offset;
        }
        $code = '';
        for(; isset($tokens[$offset]); ++$offset){
            $token = $tokens[$offset];
            if(is_array($token)){
                $code .= $token[1];
            }else{
                $code .= $token;
            }
        }
        return $code;
    }

    public function reverse(int $offset = null): void {
        if($offset === null){
            $offset = $this->offset;
        }
        $reverse = array_reverse(array_slice($this->tokens, 0, $offset));
        $this->tokens = array_merge(array_slice($this->tokens, $offset), $reverse);
    }

    public function scan(string $pattern, int &$offset = null): Scanner|bool {
        $tokens = $this->tokens;
        if($offset === null){
            $offset = &$this->offset;
        }
        if(empty($pattern)){
            return false;
        }
        $recursive_regex = "(?<a>(?<!\\\\)\((?:\\\\\\\\|\\\\\\(|\\\\\\)|\g<a>|[^()])*\))|(?<b>(?<!\\\\)\\[(?:\\\\\\\\|\\\\\\[|\\\\\\]|\g<b>|[^[\]])*\\])|(?<c>(?<!\\\\)\\{(?:\\\\\\\\|\\\\\\{|\\\\\\}|\g<c>|[^{}])*\\})|(?<d>(?<!\\\\)<(?:\\\\\\\\|\\\\<|\\\\>|\g<d>|[^<>])*>)";
        $aliases = PatternAlias::$aliases;
        $shorts = PatternAlias::$shorts;
        preg_match_all("/(?:$recursive_regex|\\\\\\\\|(?<!\\\\)\\\\\||[^|])+/", $pattern, $paths);
        $paths = $paths[0];
        for($j = 0; isset($paths[$j]); ++$j){
            $path = $paths[$j];
            if(empty($path)){
                continue;
            }
            $path = preg_replace('/(?<!\\\\)\\\\\\|/', '|', $path);
            preg_match_all("/(?:=[^=]+=){0,1}(?:$recursive_regex|\\\\\\\\|\\\\\/|[^\/])+/", $path, $patterns);
            $patterns = $patterns[0];
            $read = [];
            $readNames = [];
            $lock = $offset;
            $idx = $offset;
            for($i = 0; isset($patterns[$i]) && isset($tokens[$idx]); ++$i){
                $pattern = $patterns[$i];
                $pattern = preg_replace('/(?<!\\\\)\\\\\\//', '/', $pattern);
                $readName = false;
                if(strlen($pattern) > 1 && $pattern[0] == '='){
                    $endOfName = strpos($pattern, '=', 1);
                    if($endOfName !== false){
                        $readName = substr($pattern, 1, $endOfName - 1);
                        $pattern = substr($pattern, $endOfName + 1);
                    }
                }
                if(strlen($pattern) > 1 && $pattern[0] == '?'){
                    $optional = true;
                    $pattern = substr($pattern, 1);
                }else{
                    $optional = false;
                }
                $nameCommand = strlen($pattern) > 1 ? $pattern[0] : false;
                $params = explode(':', $pattern, 2);
                $name = $params[0] ?: ':';
                $param = $params[1] ?? false;
                if($name == '**'){
                    $nameCommand = '**';
                }elseif($name == '#'){
                    $nameCommand = '#';
                }
                if($nameCommand == '['){
                    $pattern = preg_replace('/(?<!\\\\)\\\\([[\]\\\\])/', '$1', $pattern);
                    $subpattern = substr($pattern, 1, -1);
                    $subreads = [];
                    $aliases = [];
                    if(!$optional){
                        $subread = $this->scan($subpattern, $idx);
                        if(!$subread){
                            continue 2;
                        }else{
                            $subreads[] = $subread->all();
                            $aliases[] = $subread->aliases();
                        }
                    }
                    while($subread = $this->scan($subpattern, $idx)){
                        $subreads[] = $subread->all();
                        $aliases[] = $subread->aliases();
                    }
                    foreach($aliases as $item){
                        $shiftAlias = count($read);
                        foreach($item as $key => $alias){
                            $alias[0] += $shiftAlias;
                            $readNames[$key] = $alias;
                        }
                    }
                    if($readName){
                        $readNames[$readName] = [count($read), array_sum(array_map('count', $subreads))];
                    }
                    $read = array_merge($read, ...$subreads);
                }elseif($nameCommand == '<'){
                    $pattern = preg_replace('/(?<!\\\\)\\\\([<>\\\\])/', '$1', $pattern);
                    $subpattern = substr($pattern, 1, -1);
                    $reverseOffset = $idx;
                    $this->reverse($reverseOffset);
                    $subread = $this->scan($subpattern, $idx);
                    if($subread){
                        $shiftAlias = count($read);
                        foreach($subread->aliases() as $key => $alias){
                            $alias[0] += $shiftAlias;
                            $readNames[$key] = $alias;
                        }
                        if($readName){
                            $readNames[$readName] = [count($read), count($subread)];
                        }
                        $read = array_merge($read, $subread->all());
                    }elseif(!$optional){
                        continue 2;
                    }
                    $this->reverse($reverseOffset);
                    $idx = 2 * $reverseOffset - $idx;
                }elseif($nameCommand == '@'){
                    $pattern = preg_replace('/(?<!\\\\)\\\\([@\\\\])/', '$1', $pattern);
                    $subpattern = substr($pattern, 1);
                    $subread = $this->scan($subpattern, $idx);
                    if(!$subread && !$optional){
                        continue 2;
                    }
                }elseif($nameCommand == '('){
                    $pattern = preg_replace('/(?<!\\\\)\\\\([()\\\\])/', '$1', $pattern);
                    $rpos = strrpos($pattern, ')');
                    $subpattern = substr($pattern, 1, $rpos - 1);
                    $count = substr($pattern, $rpos + 1);
                    $count = $count && is_numeric($count) ? max(floor($count), 0) : 1;
                    for($repeat = 0; $repeat < $count; ++$repeat){
                        $subread = $this->scan($subpattern, $idx);
                        if($subread){
                            $shiftAlias = count($read);
                            foreach($subread->aliases() as $key => $alias){
                                $alias[0] += $shiftAlias;
                                $readNames[$key] = $alias;
                            }
                            if($readName){
                                $readNames[$readName] = [count($read), count($subread)];
                            }
                            $read = array_merge($read, $subread->all());
                        }elseif(!$optional){
                            continue 3;
                        }
                    }
                }elseif($nameCommand == '%'){
                    $subpattern = substr($pattern, 1);
                    preg_match_all("/(?:$recursive_regex|\\\\\\\\|\\\\%|[^%])+/", $subpattern, $scopes);
                    $search = $scopes[0][0];
                    $except = $scopes[0][1] ?? false;
                    $subread = $this->find($search, $except, $idx, true);
                    if($subread){
                        if($readName){
                            $readNames[$readName] = [count($read), count($subread)];
                        }
                        $read = array_merge($read, $subread);
                    }elseif(!$optional){
                        continue 2;
                    }
                }elseif($nameCommand == '^'){
                    $idxBefore = $idx;
                    $subpattern = substr($pattern, 1);
                    $subread = $this->scan($subpattern, $idx);
                    if(!$subread){
                        $idx = $idxBefore + 1;
                    }elseif(!$optional){
                        continue 2;
                    }
                }elseif($nameCommand == '$'){
                    $subpattern = substr($pattern, 1);
                    $code = $this->merge($idx);
                    if(preg_match($subpattern, $code, $match)){
                        $length = strlen($match[0]);
                        $subread = $this->readLength($length, $idx);
                        if($readName){
                            $readNames[$readName] = [count($read), count($subread)];
                        }
                        $read = array_merge($read, $subread);
                    }elseif(!$optional){
                        continue 2;
                    }
                }elseif($nameCommand == '**'){
                    if($readName){
                        $readNames[$readName] = [count($read), 1];
                    }
                    $read[] = $tokens[$idx++];
                }elseif($nameCommand == '#'){
                }elseif($nameCommand == ':'){
                    $subpattern = substr($pattern, 1);
                    preg_match_all("/(?:$recursive_regex|\\\\\\\\|\\\\:|[^:])+/", $subpattern, $scopes);
                    $scopes = $scopes[0];
                    if(count($scopes) < 2){
                        $scopes = explode(':', $scopes[0], 2);
                        if(count($scopes) < 2){
                            continue;
                        }
                    }
                    $depth = 0;
                    $subreads = [];
                    if(isset($subreads[2]) && $subreads[2] == '#'){
                        $subreads[2] = '';
                    }
                    $idxBefore = $idx;
                    do {
                        $singleRead = false;
                        if($singleRead = $this->scan($scopes[0], $idx)){
                            ++$depth;
                        }elseif($singleRead = $this->scan($scopes[1], $idx)){
                            --$depth;
                        }elseif(isset($scopes[2]) && $scopes[2] && ($singleRead = $this->scan($scopes[2], $idx)));
                        elseif(isset($scopes[3]) && $scopes[3] && ($singleRead = $this->scan($scopes[3], $idx))){
                            $depth = 0;
                        }elseif($depth <= 0){
                            break;
                        }
                        if(!$singleRead){
                            if(isset($tokens[$idx])){
                                $singleRead = [$tokens[$idx++]];
                            }
                        }
                        if($singleRead instanceof Scanner){
                            $singleRead = $singleRead->all();
                        }
                        $subreads[] = $singleRead;
                    }while($depth > 0 && isset($tokens[$idx]));
                    if(($subreads === [] || !isset($tokens[$idx])) && !$optional){
                        $idx = $idxBefore;
                        continue 2;
                    }else{
                        if($readName){
                            $readNames[$readName] = [count($read), array_sum(array_map('count', $subreads))];
                        }
                        $read = array_merge($read, ...$subreads);
                    }
                }else{
                    $name = preg_replace('/(?<!\\\\)\\\\([:()<>{}[\]\\\\])/', '$1', $name);
                    $name = str_replace('\\\\', '\\', $name);
                    $constantName = strtoupper($name);
                    $constantName = str_replace('-', '_', $constantName);
                    if(isset($shorts[$constantName])){
                        $constantName = $shorts[$constantName];
                    }
                    if(isset($aliases[$constantName])){
                        $alias = $aliases[$constantName];
                        $subread = $this->scan($alias, $idx);
                        if($subread){
                            $shiftAlias = count($read);
                            foreach($subread->aliases() as $key => $alias){
                                $alias[0] += $shiftAlias;
                                $readNames[$key] = $alias;
                            }
                            if($readName){
                                $readNames[$readName] = [count($read), count($subread)];
                            }
                            $read = array_merge($read, $subread->all());
                        }elseif(!$optional){
                            continue 2;
                        }
                    }elseif($constantName == 'next'){
                        ++$idx;
                    }elseif($constantName == 'previous'){
                        --$idx;
                    }elseif($constantName == 'lock'){
                        $lock = $idx;
                    }elseif($constantName == 'unlock'){
                        $idx = $lock;
                    }elseif($constantName == 'reverse'){
                        $this->reverse($idx);
                    }elseif($constantName == 'rewind'){
                        $idx = 0;
                    }else{
                        if(defined("T_$constantName")){
                            $tokenId = constant("T_$constantName");
                        }elseif(defined("TC_$constantName")){
                            $tokenId = constant("TC_$constantName");
                        }else{
                            $tokenId = $name;
                        }
                        if((!$tokenId && $param) || $tokens[$idx] == $tokenId || (is_array($tokens[$idx]) && $tokens[$idx][0] == $tokenId)){
                            if($param && is_array($tokens)){
                                $paramCommand = strlen($param) > 1 ? $param[0] : false;
                                if($paramCommand == '!'){
                                    $compare = strtolower(substr($param, 1)) == strtolower($tokens[$idx][1]);
                                }elseif($paramCommand == '$'){
                                    $compare = preg_match(substr($param, 1), $tokens[$idx][1]);
                                }elseif($paramCommand == '~'){
                                    $compare = strpos($tokens[$idx][1], substr($param, 1)) !== false;
                                }elseif($paramCommand == '>'){
                                    $compare = strpos($tokens[$idx][1], substr($param, 1)) === 0;
                                }elseif($paramCommand == '^'){
                                    $compare = $param != $tokens[$idx][1];
                                }else{
                                    $compare = $param == $tokens[$idx][1];
                                }
                                if($compare){
                                    if($readName){
                                        $readNames[$readName] = [count($read), 1];
                                    }
                                    $read[] = $tokens[$idx++];
                                }elseif(!$optional){
                                    continue 2;
                                }
                            }else{
                                if($readName){
                                    $readNames[$readName] = [count($read), 1];
                                }
                                $read[] = $tokens[$idx++];
                            }
                        }elseif(!$optional){
                            continue 2;
                        }
                    }
                }
            }
            if($read === []){
                return false;
            }
            $offset = $idx;
            $resultScanner = new Scanner($read);
            $resultScanner->aliases($readNames);
            return $resultScanner;
        }
        return false;
    }

    public function rewind(): void {
        $this->offset = 0;
    }

    public function loop(callable $func): void {
        $tokens = $this->tokens;
        $offset = &$this->offset;
        while(isset($tokens[$offset])){
            $func($this);
        }
    }

    public function map(callable $func): void {
        $tokens = $this->tokens;
        $offset = &$this->offset;
        while(isset($tokens[$offset])){
            $token = $tokens[$offset++];
            $func($token);
        }
    }

    public function find(string $pattern, string|bool $except = false, int &$offset = null, bool $returnAll = false): array|bool {
        $tokens = $this->tokens;
        if($offset === null){
            $offset = &$this->offset;
        }
        $beforeOffset = $offset;
        $reads = [];
        while(isset($tokens[$offset])){
            if($except){
                $exceptScan = $this->scan($except, $offset);
                if($exceptScan){
                    if($returnAll){
                        $reads = array_merge($reads, $exceptScan->all());
                    }
                    continue;
                }
            }
            $scan = $this->scan($pattern, $offset);
            if($scan){
                $reads = array_merge($reads, $scan->all());
                return $reads;
            }else{
                if($returnAll){
                    $reads[] = $tokens[$offset];
                }
                ++$offset;
            }
        }
        $offset = $beforeOffset;
        return false;
    }

    public function indexOf(string $pattern, int &$offset = null): int|bool {
        return $this->find($pattern, $offset) ? $this->offset : false;
    }

    public function end(){
        $this->offset = count($this->tokens);
    }

    public function __toString(): string {
        return Tokenizer::merge($this->tokens);
    }
}

?>