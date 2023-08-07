<?php
namespace PHPCube\Tokenizer;

class PatternAlias {
    public static $aliases = [
        'ENCAPSED_STRING'       => 'constant-encapsed-string|"/[encapsed-and-whitespace|variable|dollar-open-curly-braces|:\{\\\|curly-open:\}|num-string|\[|string|\]|object-operator|nullsafe-object-operator]/"',
        'SINGLE_QUOTES_STRING'  => 'constant-encapsed-string:>\'',
        'DOUBLE_QUOTES_STRING'  => 'constant-encapsed-string:>"|"/[encapsed-and-whitespace|variable|dollar-open-curly-braces|:\{\\\|curly-open:\}|num-string|\[|string|\]|object-operator|nullsafe-object-operator]/"',
        'HEREDOC'               => 'start-heredoc/[encapsed-and-whitespace|variable|dollar-open-curly-braces|:\{\\\|curly-open:\}|num-string|\[|string|\]|object-operator|nullsafe-object-operator]/end-heredoc',
        'SINGLE_QUOTES_HEREDOC' => 'start-heredoc:><<<\'/[encapsed-and-whitespace|variable|dollar-open-curly-braces|:\{\\\|curly-open:\}|num-string|\[|string|\]|object-operator|nullsafe-object-operator]/end-heredoc',
        'BACKTICKS_SHELL'       => '`/[encapsed-and-whitespace|variable|dollar-open-curly-braces|:\{\\\|curly-open:\}|num-string|\[|string|\]|object-operator|nullsafe-object-operator]/`',
        'STRING_AND_HEREDOC'    => 'encapsed-string|heredoc',
        'NUMBER'                => 'dnumber|lnumber',
        'COMPLETE_ARRAY'        => 'array/?space/:(:)|:[:]',
        'ALL_OBJECT_OPERATOR'   => 'object-operator|nullsafe-object-operator',
        'EMPTY'                 => 'constant-encapsed-string:\'\'|constant-encapsed-string:""',
        'TRUE'                  => 'string:!true',
        'FALSE'                 => 'string:!false',
        'BOOLEAN'               => 'string:!true|string:!false',
        'NULL'                  => 'string:!null',
        'INTERSECTION_TYPE'     => 'string/[?space/(\||&)/string]',
        'FUNCTION_DEFINATION'   => 'function/space/string/?space/:(:)/?space/?(:/?space/intersection-type/?space)/:{:}',
        'CLASS_DEFINATION'      => '?final/?abstract/class/space/string/?space/(extends/space/string|implements/space/string/?[?space/,/?space/string])2/?space/:{:}',
        'ANONYMOUS_FUNCTION'    => 'function/space/:(:)/?space/?(use/?space/:(:)/?space)/?(:/?space/intersection-type/?space)/:{:}',
        'ANONYMOUS_CLASS'       => 'new/space/class/?space/(extends/space/string|implements/space/string/?[?space/,/?space/string])2/?space/:{:}',
        'TRAIT_DEFINATION'      => 'trait/space/string/?space/:{:}',
        'INTERFACE_DEFINATION'  => 'interface/space/string/?space/:{:}',
        'IF_ELSE_EXPRESSION'    => 'if/?space/:(:)/?space/(:{:}|:/%endif%if-else-expression|expression)/?[(elseif|else/space/if)/?space/(:{:}|:/%endif%if-else-expression|expression)]/?(else/?space/(:{:}|:/%endif%if-else-expression|expression))',
        'WHILE_EXPRESSION'      => 'while/?space/:(:)/?space/(:{:}|:/%endwhile%while-expression|expression)',
        'FOR_EXPRESSION'        => 'for/?space/:(:)/?space/(:{:}|:/%endfor%for-expression|expression)',
        'DO_WHILE_EXPRESSION'   => 'do/?space/:{:}/?space/while/:(:)/;',
        'FOREACH_EXPRESSION'    => 'foreach/?space/:(:)/?space/(:{:}/:/%endforeach%foreach-expression|expression)',
        'NAMESPACE_EXPRESSION'  => 'namespace/space/name-qualified/?space/(;|:{:})',
        'DECLARE_EXPRESSION'    => 'declare/?space/:(:)/?space/(;|:{:})',
        'SWITCH_EXPRESSION'     => 'switch/?space/:(:)/?space/(:{:}|:/%endswitch%switch-expression)',
        'MATCH_EXPRESSION'      => 'match/?space/:(:)/?space',
        'FN_EXPRESSION'         => 'fn/?space/?&/?space/:(:)/?space/?(:/?space/intersection-type/?space)/double-arrow/single-expression',
        'SINGLE_EXPRESSION'     => '%(;|,|]|\)|})%(string-and-heredoc|anonymous-function|anonymous-class|backticks-shell)',
        'EXPRESSION'            => 'function-defination|class-defination|trait-defination|interface-defination|if-else-expression|while-expression|for-expression|foreach-expression|do-while-expression|switch-expression|namespace-expression|declare-expression|comment|doc-comment|single-expression',
        'PHP_TAG'               => '(open-tag|open-tag-with-echo)/%close-tag',
        'VARIABLE_EXPRESSION'   => '?[$]/variable|[$]/:{:}',
        'CUBE_ASYNC_EXPRESSION' => 'async/?space/(:{:}|single-expression)',
        'CUBE_AWAIT_EXPRESSION' => 'await/?space/single-expression',
        'CUBE_DEFER_EXPRESSION' => 'defer/?space/(:{:}|single-expression)',
        'CUBE_FN_EXPRESSION'    => 'fn/?space/?&/?space/:(:)/?space/?(:/?space/intersection-type/?space)/double-arrow/?space/(:{:}|single-expression)',
        'INCLUDE_EXPRESSION'    => '(include|include_once)/?space/single-expression',
        'REQUIRE_EXPRESSION'    => '(require|require_once)/?space/single-expression',
    ];

    public static $shorts = [
        'SPACE' => 'WHITESPACE',
    ];
}

?>