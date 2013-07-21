<?php

namespace PW\NewsletterBundle\TokenParser;

use PW\NewsletterBundle\Node\Node as NodeCache;

class Cache extends \Twig_TokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Twig_Token $token A Twig_Token instance
     *
     * @return Twig_NodeInterface A Twig_NodeInterface instance
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        if (!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            $cacheKey = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $file = $stream->getFilename();
            $cacheKey = $file . ':' . $lineno;
            $cacheKey = new \Twig_Node_Expression_Constant($cacheKey, $lineno);
        }

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new NodeCache(
            $body,
            array(
                'cacheKey' => $cacheKey
            ),
            $lineno,
            $this->getTag()
        );
    }

    public function decideBlockEnd(\Twig_Token $token)
    {
        return $token->test('endcache');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @param string The tag name
     */
    public function getTag()
    {
        return 'cache';
    }
}
