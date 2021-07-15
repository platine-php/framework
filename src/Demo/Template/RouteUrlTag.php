<?php

namespace Platine\Framework\Demo\Template;

use Platine\Framework\Http\RouteHelper;
use Platine\Lang\Lang;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;

/**
 * Description of RouteUrlTag
 *
 * @author tony
 */
class RouteUrlTag extends AbstractTag
{

    /**
     * The name of the route
     * @var string
     */
    protected string $name;

    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        $lexer = new Lexer('/' . Token::QUOTED_FRAGMENT . '/');
        if ($lexer->match($markup)) {
            $this->name = $lexer->getStringMatch(0);
            $this->extractAttributes($markup);
        } else {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: rurl [name] [PARAMETERS ...]',
                'rurl'
            ));
        }
    }

    public function render(Context $context): string
    {
        $parameters = [];
        foreach ($this->attributes as $key => $value) {
            if ($context->hasKey($value)) {
                $value = (string) $context->get($value);
                $parameters[$key] = $value;
            }
        }
        /** @var RouteHelper $helper */
        $helper = app(RouteHelper::class);
        return $helper->generateUrl($this->name, $parameters);
    }
}
