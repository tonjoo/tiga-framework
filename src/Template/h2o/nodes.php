<?php

/*
        Nodes
*/

class H2o_Node
{
    public $position;
    public function __construct($argstring)
    {
    }

    public function render($context, $stream)
    {
    }
}

class NodeList extends H2o_Node implements IteratorAggregate
{
    public $parser;
    public $list;

    public function __construct(&$parser, $initial = null, $position = 0)
    {
        $this->parser = $parser;
        if (is_null($initial)) {
            $initial = array();
        }
        $this->list = $initial;
        $this->position = $position;
    }

    public function render($context, $stream)
    {
        foreach ($this->list as $node) {
            $node->render($context, $stream);
        }
    }

    public function append($node)
    {
        array_push($this->list, $node);
    }

    public function extend($nodes)
    {
        array_merge($this->list, $nodes);
    }

    public function getLength()
    {
        return count($this->list);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->list);
    }
}

class VariableNode extends H2o_Node
{
    private $filters = array();
    public $variable;

    public function __construct($variable, $filters, $position = 0)
    {
        if (!empty($filters)) {
            $this->filters = $filters;
        }
        $this->variable = $variable;
    }

    public function render($context, $stream)
    {
        $value = $context->resolve($this->variable);
        $value = $context->escape($value, $this->variable);
        $stream->write($value);
    }
}

class CommentNode extends H2o_Node
{
}

class TextNode extends H2o_Node
{
    public $content;
    public function __construct($content, $position = 0)
    {
        $this->content = $content;
        $this->position = $position;
    }

    public function render($context, $stream)
    {
        $stream->write($this->content);
    }

    public function is_blank()
    {
        return strlen(trim($this->content));
    }
}
