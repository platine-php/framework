<?php

namespace Platine\Framework\Demo\Template;

use Platine\Session\Session;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;

/**
 * Description of SessionFlashTag
 *
 * @author tony
 */
class SessionFlashTag extends AbstractTag
{

    /**
     * The key of the session
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
        } else {
            throw new ParseException(sprintf(
                'Syntax Error in "%s" - Valid syntax: session [key]',
                'session'
            ));
        }
    }

    public function render(Context $context): string
    {
        /** @var Session $session */
        $session = app(Session::class);

        return (string) $session->getFlash($this->name);
    }
}
