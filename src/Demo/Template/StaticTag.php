<?php

namespace Platine\Framework\Demo\Template;

use Platine\Config\Config;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;

/**
 * Description of StaticTag
 *
 * @author tony
 */
class StaticTag extends AbstractTag
{

    /**
     * The static path
     * @var string
     */
    protected string $path;

    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        $lexer = new Lexer('/' . Token::QUOTED_FRAGMENT . '/');
        if ($lexer->match($markup)) {
            $this->path = $lexer->getStringMatch(0);
        } else {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: static [PATH]',
                'static'
            ));
        }
    }

    public function render(Context $context): string
    {
        /** @template T @var Config<T> $config */
        $config = app(Config::class);

        $baseUrl = $config->get('app.url');
        $baseUrl = rtrim($baseUrl, '/') . '/';
        $staticDir = $config->get('app.static_dir');
        $staticDir = rtrim($staticDir, '/') . '/';

        return (string) $baseUrl . $staticDir . $this->path;
    }
}
