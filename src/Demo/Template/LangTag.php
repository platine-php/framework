<?php

namespace Platine\Framework\Demo\Template;

use Platine\Lang\Lang;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;

/**
 * Description of LangTag
 *
 * @author tony
 */
class LangTag extends AbstractTag
{

    /**
     * Value to debug
     * @var string
     */
    protected string $value;

    /**
    * {@inheritdoc}
    */
    public function __construct(string $markup, &$tokens, Parser $parser)
    {
        $lexer = new Lexer('/' . Token::QUOTED_FRAGMENT . '/');
        if ($lexer->match($markup)) {
            $this->value = $lexer->getStringMatch(0);
        } else {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: tr [name]',
                'tr'
            ));
        }
    }

    public function render(Context $context): string
    {
        if ($context->hasKey($this->value)) {
            $this->value = (string) $context->get($this->value);
        }
        /** @var Lang $lang */
        $lang = app(Lang::class);
        return $lang->tr($this->value);
    }
}
