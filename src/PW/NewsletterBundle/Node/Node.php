<?php

namespace PW\NewsletterBundle\Node;

/**
 * Represents a cache node.
 */
class Node extends \Twig_Node
{
    public function __construct(\Twig_NodeInterface $body, $attributes, $lineno, $tag = 'cache')
    {
        parent::__construct(array('body' => $body), $attributes, $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        // Check if cachehandler is enabled, if not just compile without the cache functions
        $cache = $compiler->getEnvironment()->getExtension('cache');
        if (!$cache->isEnabled()) {
            return $compiler->subcompile($this->getNode('body'));
        }

        // Get the cache key
        $cacheKey = $this->getAttribute('cacheKey');

        $compiler
            ->addDebugInfo($this)
            ->write("if (\$this->env->getExtension('cache')->cacheExists(")
            ->subcompile($cacheKey)
            ->write(")) {\n")
                ->indent()
                ->write("echo \$this->env->getExtension('cache')->cacheGet(")
                ->subcompile($cacheKey)
                ->write(");\n")
                ->outdent()
            ->write("} else {\n")
                ->indent()
                ->write("ob_start();\n")
                ->subcompile($this->getNode('body'))
                ->write("\$body = ob_get_clean();\n")
                ->write("\$this->env->getExtension('cache')->cacheSet(")
                ->subcompile($cacheKey)
                ->write(", \$body);\n")
                ->write("echo \$body;\n")
                ->outdent()
            ->write("}\n")
            ;
    }
}
