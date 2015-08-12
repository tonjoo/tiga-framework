<?php

class StreamWriter
{
    public $buffer = array();
    public $close;

    public function __construct()
    {
        $this->close = false;
    }

    public function write($data)
    {
        if ($this->close) {
            new Exception('tried to write to closed stream');
        }
        $this->buffer[] = $data;
    }

    public function close()
    {
        $this->close = true;

        return implode('', $this->buffer);
    }
}

class Evaluator
{
    public static function gt($l, $r)
    {
        return $l > $r;
    }
    public static function ge($l, $r)
    {
        return $l >= $r;
    }

    public static function lt($l, $r)
    {
        return $l < $r;
    }
    public static function le($l, $r)
    {
        return $l <= $r;
    }

    public static function eq($l, $r)
    {
        return $l == $r;
    }
    public static function ne($l, $r)
    {
        return $l != $r;
    }

    public static function not_($bool)
    {
        return !$bool;
    }
    public static function and_($l, $r)
    {
        return ($l && $r);
    }
    public static function or_($l, $r)
    {
        return ($l && $r);
    }

    # Currently only support single expression with no preceddence ,no boolean expression
    #    [expression] =  [optional binary] ? operant [ optional compare operant]
    #    [operant] = variable|string|numeric|boolean
    #    [compare] = > | < | == | >= | <=
    #    [binary]    = not | !
    public static function exec($args, $context)
    {
        $argc = count($args);
        $first = array_shift($args);
        $first = $context->resolve($first);
        switch ($argc) {
            case 1 :
                return $first;
            case 2 :
                if (is_array($first) && isset($first['operator']) && $first['operator'] == 'not') {
                    $operant = array_shift($args);
                    $operant = $context->resolve($operant);

                    return !($operant);
                }
            case 3 :
                list($op, $right) = $args;
                $right = $context->resolve($right);

                return call_user_func(array('Evaluator', $op['operator']), $first, $right);
            default:
                return false;
        }
    }
}

/**
 * $type of token, Block | Variable.
 */
class H2o_Token
{
    public function __construct($type, $content, $position)
    {
        $this->type = $type;
        $this->content = $content;
        $this->result = '';
        $this->position = $position;
    }

    public function write($content)
    {
        $this->result = $content;
    }
}

/**
 * a token stream.
 */
class TokenStream
{
    public $pushed;
    public $stream;
    public $closed;
    public $c;

    public function __construct()
    {
        $this->pushed = array();
        $this->stream = array();
        $this->closed = false;
    }

    public function pop()
    {
        if (count($this->pushed)) {
            return array_pop($this->pushed);
        }

        return array_pop($this->stream);
    }

    public function feed($type, $contents, $position)
    {
        if ($this->closed) {
            throw new Exception('cannot feed closed stream');
        }
        $this->stream[] = new H2o_Token($type, $contents, $position);
    }

    public function push($token)
    {
        if (is_null($token)) {
            throw new Exception('cannot push NULL');
        }
        if ($this->closed) {
            $this->pushed[] = $token;
        } else {
            $this->stream[] = $token;
        }
    }

    public function close()
    {
        if ($this->closed) {
            new Exception('cannot close already closed stream');
        }
        $this->closed = true;
        $this->stream = array_reverse($this->stream);
    }

    public function isClosed()
    {
        return $this->closed;
    }

    public function current()
    {
        return $this->c;
    }

    public function next()
    {
        return $this->c = $this->pop();
    }
}

class H2o_Info
{
    public $h2o_safe = array('filters', 'extensions', 'tags');
    public $name = 'H2o Template engine';
    public $description = 'Django inspired template system';
    public $version = H2O_VERSION;

    public function filters()
    {
        return array_keys(h2o::$filters);
    }

    public function tags()
    {
        return array_keys(h2o::$tags);
    }

    public function extensions()
    {
        return array_keys(h2o::$extensions);
    }
}

/**
 * Functions.
 */
function sym_to_str($string)
{
    return substr($string, 1);
}

function is_sym($string)
{
    return isset($string[0]) && $string[0] === ':';
}

function symbol($string)
{
    return ':'.$string;
}

function strip_regex($regex, $delimiter = '/')
{
    return substr($regex, 1, strrpos($regex, $delimiter) - 1);
}
