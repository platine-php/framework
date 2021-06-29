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

    protected Lang $lang;

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
            //$this->variables = $this->variablesFromString($markup);
            //$this->name = "'" . implode('', $this->variables) . "'";
            $this->value = $lexer->getStringMatch(0);
        } else {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: cycle [name :] var [, var2, var3 ...]',
                'cycle'
            ));
        }
    }

    public function render(Context $context): string
    {
        return $this->lang->tr($this->value);
    }

    /**
     * Set the language instance
     * @param Lang $lang
     * @return $this
     */
    public function setLang(Lang $lang): self
    {
        $this->lang = $lang;

        return $this;
    }
}
