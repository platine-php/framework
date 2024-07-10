<?php

/**
 * Platine Framework
 *
 * Platine Framework is a lightweight, high-performance, simple and elegant
 * PHP Web framework
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Framework
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file InputClean.php
 *
 *  This class apply the clean (XSS, sanitize, ...) the request data
 *
 *  @package    Platine\Framework\Http
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Http;

use Platine\Stdlib\Helper\Str;

/**
 * @class InputClean
 * @package Platine\Framework\Http
 */
class InputClean
{
    /**
     * The list of invalid filename chars
     * @var array<string>
     */
    protected array $invalidFilenameChars = [
        '../', '<!--', '-->', '<', '>',
        '\'', '"', '&', '$', '#',
        '{', '}', '[', ']', '=',
        ';', '?', '%20', '%22',
        '%3c',      // <
        '%253c',    // <
        '%3e',      // >
        '%0e',      // >
        '%28',      // (
        '%29',      // )
        '%2528',    // (
        '%26',      // &
        '%24',      // $
        '%3f',      // ?
        '%3b',      // ;
        '%3d'       // =
    ];

    /**
     * The character set to use
     * @var string
     */
    protected string $charset = 'UTF-8';

    /**
     * The random generated XSS hash to protect URL
     * @var string
     */
    protected string $xssHash = '';

    /**
     * The list of forbidden strings
     * @var array<string, string>
     */
    protected array $forbiddenStrings = [
        'document.cookie' => '[removed]',
        'document.write'  => '[removed]',
        '.parentNode'     => '[removed]',
        '.innerHTML'      => '[removed]',
        '-moz-binding'    => '[removed]',
        '<!--'            => '&lt;!--',
        '-->'             => '--&gt;',
        '<![CDATA['       => '&lt;![CDATA[',
        '<comment>'   => '&lt;comment&gt;',
        '<%'              => '&lt;&#37;'
    ];

    /**
     * The list of forbidden strings patterns
     * @var array<string>
     */
    protected array $forbiddenStringPatterns = [
        'javascript\s*:',
        '(document|(document\.)?window)\.(location|on\w*)',
        'expression\s*(\(|&\#40;)', // CSS and IE
        'vbscript\s*:', // IE, surprise!
        'wscript\s*:', // IE
        'jscript\s*:', // IE
        'vbs\s*:', // IE
        'Redirect\s+30\d',
        "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
    ];

    /**
     * Create new instance
     * @param string $charset
     */
    public function __construct(string $charset = 'UTF-8')
    {
        $this->charset = $charset;
    }

        /**
     * The main function to clean input
     * @param mixed $str
     * @param bool $isImage
     * @return mixed
     */
    public function clean($str, bool $isImage = false)
    {
        if (is_array($str)) {
            foreach ($str as $key => &$value) {
                $str[$key] = $this->clean($value);
            }

            return $str;
        }

        if ($str === '' || $str === null || is_bool($str) || ! $str || is_numeric($str)) {
            return $str;
        }

        // Remove Invisible Characters
        $str = $this->removeInvisibleCharacters($str);

        // URL Decode
        // Just in case stuff like this is submitted:
        // <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
        // Note: Use rawurldecode() so it does not remove plus signs
        if (stripos($str, '%') !== false) {
            do {
                $oldStr = $str;
                $rawStr = rawurldecode($str);
                $str = (string) preg_replace_callback(
                    '#%(?:\s*[0-9a-f]){2,}#i',
                    [$this, 'urlDecodeSpaces'],
                    $rawStr
                );
            } while ($oldStr !== $str);
            unset($oldStr);
        }

        /*
         * Convert character entities to ASCII
         *
         * This permits our tests below to work reliably.
         * We only convert entities that are within tags since
         * these are the ones that will pose security problems.
         */
        $str = (string) preg_replace_callback(
            "/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si",
            [$this, 'convertAttribute'],
            $str
        );

        $str = (string) preg_replace_callback(
            '/<\w+.*/si',
            [$this, 'decodeEntity'],
            $str
        );

        // Remove Invisible Characters Again!
        $str = $this->removeInvisibleCharacters($str);

        /*
         * Convert all tabs to spaces
         *
         * This prevents strings like this: ja  vascript
         * NOTE: we deal with spaces between characters later.
         * NOTE: preg_replace was found to be amazingly slow here on
         * large blocks of data, so we use str_replace.
         */
        $str = str_replace("\t", ' ', $str);

        // Capture converted string for later comparison
        $convertedString = $str;

        // Remove Strings that are never allowed
        $str = $this->removeForbiddenStrings($str);

        /*
         * Makes PHP tags safe
         * Note: XML tags are inadvertently replaced too:
         * <?xml
         *
         * But it doesn't seem to pose a problem.
         */
        if ($isImage) {
            // Images have a tendency to have the PHP short opening and
            // closing tags every so often so we skip those and only
            // do the long opening tags.
            $str = (string) preg_replace(
                '/<\?(php)/i',
                '&lt;?\\1',
                $str
            );
        } else {
            $str = str_replace(
                ['<?', '?' . '>'],
                ['&lt;?', '?&gt;'],
                $str
            );
        }

        /*
         * Compact any exploded words
         *
         * This corrects words like:  j a v a s c r i p t
         * These words are compacted back to their correct state.
         */
        $words = [
            'javascript', 'expression', 'vbscript', 'jscript', 'wscript',
            'vbs', 'script', 'base64', 'applet', 'alert', 'document',
            'write', 'cookie', 'window', 'confirm', 'prompt', 'eval'
        ];

        foreach ($words as $word) {
            $word = implode('\s*', str_split($word)) . '\s*';

            // We only want to do this when it is followed by a non-word character
            // That way valid stuff like "dealer to" does not become "dealerto"
            $str = (string) preg_replace_callback(
                '#(' . substr($word, 0, -3) . ')(\W)#is',
                [$this, 'compactExplodedWords'],
                $str
            );
        }

        /*
         * Remove disallowed Javascript in links or img tags
         * We used to do some version comparisons and use of stripos(),
         * but it is dog slow compared to these simplified non-capturing
         * preg_match(), especially if the pattern exists in the string
         *
         * Note: It was reported that not only space characters, but all in
         * the following pattern can be parsed as separators between a tag name
         * and its attributes: [\d\s"\'`;,\/\=\(\x00\x0B\x09\x0C]
         * ... however, remove invisible characters above already strips the
         * hex-encoded ones, so we'll skip them below.
         */
        do {
            $original = $str;

            if (preg_match('/<a/i', $str)) {
                $str = (string) preg_replace_callback(
                    '#<a(?:rea)?[^a-z0-9>]+([^>]*?)(?:>|$)#si',
                    [$this, 'removeJsLink'],
                    $str
                );
            }

            if (preg_match('/<img/i', $str)) {
                $str = (string) preg_replace_callback(
                    '#<img[^a-z0-9]+([^>]*?)(?:\s?/?>|$)#si',
                    [$this, 'removeJsImage'],
                    $str
                );
            }

            if (preg_match('/script|xss/i', $str)) {
                $str = (string) preg_replace(
                    '#</*(?:script|xss).*?>#si',
                    '[removed]',
                    $str
                );
            }
        } while ($original !== $str);
        unset($original);

        /*
         * Sanitize naughty HTML elements
         *
         * If a tag containing any of the words in the list
         * below is found, the tag gets converted to entities.
         *
         * So this: <blink>
         * Becomes: &lt;blink&gt;
         */
        $pattern = '#'
            . '<((?<slash>/*\s*)((?<tagName>[a-z0-9]+)(?=[^a-z0-9]|$)|.+)' // tag
            // start and name, followed by a non-tag character
            . '[^\s\042\047a-z0-9>/=]*' // a valid attribute character
            // immediately after the tag would count as a separator
            // optional attributes
            . '(?<attributes>(?:[\s\042\047/=]*' // non-attribute characters,
            // excluding > (tag close) for obvious reasons
            . '[^\s\042\047>/=]+' // attribute characters
            // optional attribute-value
                . '(?:\s*=' // attribute-value separator
                    . '(?:[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]'
                . '*\047|\s*(?U:[^\s\042\047=><`]*))' // single, double or non-quoted value
                . ')?' // end optional attribute-value group
            . ')*)' // end optional attributes group
            . '[^>]*)(?<closeTag>\>)?#isS';

        // Note: It would be nice to optimize this for speed, BUT
        // only matching the naughty elements here results in
        // false positives and in turn - vulnerabilities!
        do {
            $oldStr = $str;
            $str = (string) preg_replace_callback(
                $pattern,
                [$this, 'sanitizeNaughtyHtml'],
                $str
            );
        } while ($oldStr !== $str);
        unset($oldStr);

        /*
         * Sanitize naughty scripting elements
         *
         * Similar to above, only instead of looking for
         * tags it looks for PHP and JavaScript commands
         * that are disallowed. Rather than removing the
         * code, it simply converts the parenthesis to entities
         * rendering the code un-executable.
         *
         * For example: eval('some code')
         * Becomes: eval&#40;'some code'&#41;
         */
        $str = (string) preg_replace(
            '#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|'
                . 'fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si',
            '\\1\\2&#40;\\3&#41;',
            $str
        );

        // Final clean up
        // This adds a bit of extra precaution in case
        // something got through the above filters
        $str = $this->removeForbiddenStrings($str);

        /*
         * Images are Handled in a Special Way
         * - Essentially, we want to know that after all of the character
         * conversion is done whether any unwanted, likely XSS, code was found.
         * If not, we return TRUE, as the image is clean.
         * However, if the string post-conversion does not matched the
         * string post-removal of XSS, then it fails, as there was unwanted XSS
         * code found and removed/changed during processing.
         */
        if ($isImage) {
            return ($str === $convertedString);
        }

        return $str;
    }

    /**
     * Generate the XSS hash if not yet generated
     * and return it
     * @return string
     */
    public function getXssHash(): string
    {
        if (empty($this->xssHash)) {
            $this->xssHash = Str::random(16);
        }

        return $this->xssHash;
    }


    /**
     * Return the character set
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Set the character set
     * @param string $charset
     * @return $this
     */
    public function setCharset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Sanitize the filename
     * @param string $str
     * @param bool $isRelativePath whether to preserve path
     * @return string
     */
    public function sanitizeFilename(string $str, bool $isRelativePath = false): string
    {
        $invalids = $this->invalidFilenameChars;
        if ($isRelativePath === false) {
            $invalids[] = './';
            $invalids[] = '/';
        }

        $cleanStr = $this->removeInvisibleCharacters($str, false);
        do {
            $old = $cleanStr;
            $cleanStr = str_replace($invalids, '', $cleanStr);
        } while ($old !== $cleanStr);

        return stripslashes($cleanStr);
    }

    /**
     * Remove the "img" tags
     * @param string $str
     * @return string
     */
    public function stripImageTags(string $str): string
    {
        return (string) preg_replace(
            [
                '#<img[\s/]+.*?src\s*=\s*(["\'])([^\\1]+?)\\1.*?\>#i',
            '#<img[\s/]+.*?src\s*=\s*?(([^\s"\'=<>`]+)).*?\>#i',
            ],
            '\\2',
            $str
        );
    }

    /**
     * HTML Entities Decode
     * A replacement for html_entity_decode()
     *
     * The reason we are not using html_entity_decode() by itself is because
     * while it is not technically correct to leave out the semicolon
     * at the end of an entity most browsers will still interpret the entity
     * correctly. html_entity_decode() does not convert entities without
     * semicolons, so we are left with our own little solution here. Bummer.
     *
     * @param string $str
     * @param string|null $charset the custom character set if not will use the current one
     * @return string
     */
    protected function htmlEntityDecode(string $str, ?string $charset = null): string
    {
        if (strpos($str, '&') === false) {
            return $str;
        }

        static $entities;

        if ($charset === null) {
            $charset = $this->charset;
        }

        $flag = ENT_COMPAT | ENT_HTML5;

        if (! isset($entities)) {
            $entities = array_map(
                'strtolower',
                get_html_translation_table(HTML_ENTITIES, $flag, $charset)
            );
        }

        do {
            $strCompare = $str;

            // Decode standard entities, avoiding false positives
            $matches = [];
            if (preg_match_all('/&[a-z]{2,}(?![a-z;])/i', $str, $matches) > 0) {
                $replace = [];
                $matches = array_unique(array_map('strtolower', $matches[0]));
                foreach ($matches as &$match) {
                    if (($char = array_search($match . ';', $entities, true)) !== false) {
                        $replace[$match] = $char;
                    }
                }

                $strReplace = str_replace(array_keys($replace), array_values($replace), $str);

                // Decode numeric & UTF16 two byte entities
                $str = html_entity_decode(
                    (string) preg_replace(
                        '/(&#(?:x0*[0-9a-f]{2,5}(?![0-9a-f;])|(?:0*\d{2,4}(?![0-9;]))))/iS',
                        '$1;',
                        $strReplace
                    ),
                    $flag,
                    $charset
                );
            }
        } while ($strCompare !== $str);

        return $str;
    }



    /**
     * The URL decode taking space into account
     * @param array<int, string> $matches
     * @return string
     */
    protected function urlDecodeSpaces(array $matches): string
    {
        $input = $matches[0];
        $noSpace = (string) preg_replace('#\s+#', '', $input);

        return $noSpace === $input
                ? $input
                : rawurldecode($noSpace);
    }

    /**
     * Compact exploded words (remove white space from string like 'j a v a s c r i p t')
     * @param array<int, string> $matches
     * @return string
     */
    protected function compactExplodedWords(array $matches): string
    {
        return (string) preg_replace('/\s+/s', '', $matches[1]) . $matches[2];
    }

    /**
     * Sanitize the string to remove naughty HTML elements
     * @param array<int|string, string> $matches
     * @return string
     */
    protected function sanitizeNaughtyHtml(array $matches): string
    {
        static $naughtyTags = [
            'alert', 'area', 'prompt', 'confirm', 'applet', 'audio', 'basefont',
            'base', 'behavior', 'bgsound', 'blink', 'body', 'embed', 'expression',
            'form', 'frameset', 'frame', 'head', 'html', 'ilayer','iframe', 'input',
            'button', 'select', 'isindex', 'layer', 'link', 'meta', 'keygen', 'object',
            'plaintext', 'style', 'script', 'textarea', 'title', 'math', 'video', 'svg',
            'xml', 'xss'
        ];

        static $evilAttributes = [
            'on\w+', 'style', 'xmlns', 'formaction',
            'form', 'xlink:href', 'FSCommand', 'seekSegmentTime'
        ];

        // First, escape unclosed tags
        if (empty($matches['closeTag'])) {
            return '&lt;' . $matches[1];
        } elseif (in_array(strtolower($matches['tagName']), $naughtyTags, true)) {
            // Is the element that we caught naughty? If so, escape it
            return '&lt;' . $matches[1] . '&gt;';
        } elseif (isset($matches['attributes'])) {
            // For other tags, see if their attributes are "evil" and strip those
            // We'll store the already fitlered attributes here
            $attributes = [];

            $attributesPattern = '#'
                    . '(?<name>[^\s\042\047>/=]+)' // attribute characters
                    // optional attribute-value
                    . '(?:\s*=(?<value>[^\s\042\047=><`]+|\s*\042[^\042]*\042|'
                    . '\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*)))'
                    // attribute-value separator;
                    . '#i';

            // Blacklist pattern for evil attribute names
            $isEvilPattern = '#^(' . implode('|', $evilAttributes) . ')$#i';
            // Each iteration filters a single attribute
            do {
                // Strip any non-alpha characters that may precede an attribute.
                // Browsers often parse these incorrectly and that has been a
                // of numerous XSS issues we've had.
                $matches['attributes'] = (string) preg_replace(
                    '#^[^a-z]+#i',
                    '',
                    $matches['attributes']
                );
                $attribute = [];
                if (
                    ! preg_match(
                        $attributesPattern,
                        $matches['attributes'],
                        $attribute,
                        PREG_OFFSET_CAPTURE
                    )
                ) {
                    // No (valid) attribute found? Discard everything else inside the tag
                    break;
                }

                if (
                    // Is it indeed an "evil" attribute?
                    preg_match($isEvilPattern, $attribute['name'][0]) ||
                    // Or does it have an equals sign, but no value and not quoted? Strip that too!
                    trim($attribute['value'][0]) === ''
                ) {
                    $attributes[] = 'xss=removed';
                } else {
                    $attributes[] = $attribute[0][0];
                }

                $matches['attributes']  = (string) substr(
                    $matches['attributes'],
                    $attribute[0][1] + strlen($attribute[0][0])
                );
            } while ($matches['attributes'] !== '');

            $result = count($attributes) > 0
                    ? ''
                    : ' ' . implode(' ', $attributes);

            return '<' . $matches['slash'] . $matches['tagName'] . $result . '>';
        }

        return $matches[0];
    }

    /**
     * Remove the JS link from the string
     * @param array<int, string> $matches
     * @return string
     */
    protected function removeJsLink(array $matches): string
    {
        return str_replace(
            $matches[1],
            (string) preg_replace(
                '#href=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;)|javascript:'
                    . '|livescript:|mocha:|charset=|window\.|document\.|\.cookie'
                    . '|<script|<xss|d\s*a\s*t\s*a\s*:)#si',
                '',
                $this->filterAttributes($matches[1])
            ),
            $matches[0]
        );
    }

    /**
     * Remove the JS from image tags
     * @param array<int, string> $matches
     * @return string
     */
    protected function removeJsImage(array $matches): string
    {
        return str_replace(
            $matches[1],
            (string) preg_replace(
                '#src=.*?(?:(?:alert|prompt|confirm|eval)(?:\(|&\#40;)|javascript:'
                    . '|livescript:|mocha:|charset=|window\.|document\.|\.cooki'
                    . 'e|<script|<xss|base64\s*,)#si',
                '',
                $this->filterAttributes($matches[1])
            ),
            $matches[0]
        );
    }

    /**
     * The HTML entities decode callback
     * @param array<int, string> $matches
     * @return string
     */
    protected function decodeEntity(array $matches): string
    {
        // Protect GET variables in URLs like 901119URL5918AMP18930PROTECT8198
        $str = (string) preg_replace(
            '|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-/]+)|i',
            $this->getXssHash() . '\\1=\\2',
            $matches[0]
        );
        // Decode, then un-protect URL GET vars
        return str_replace(
            $this->getXssHash(),
            '&',
            $this->htmlEntityDecode($str, $this->charset)
        );
    }

    /**
     * Convert the attribute
     * @param array<int, mixed> $matches
     * @return string
     */
    protected function convertAttribute(array $matches): string
    {
        return str_replace(
            ['>', '<', '\\'],
            ['&gt;', '&lt;', '\\\\'],
            $matches[0]
        );
    }

    /**
     *  Filter tag attributes for consistency and safety.
     * @param string $str
     * @return string
     */
    protected function filterAttributes(string $str): string
    {
        $result = '';
        $matches = [];
        if (
            preg_match_all(
                '#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is',
                $str,
                $matches
            ) > 0
        ) {
            foreach ($matches[0] as $match) {
                $result .= (string) preg_replace('#/\*.*?\*/#s', '', $match);
            }
        }

        return $result;
    }

    /**
     * Remove the forbidden strings
     * @param string $str
     * @return string
     */
    protected function removeForbiddenStrings(string $str): string
    {
        $keys = array_keys($this->forbiddenStrings);
        $values = array_values($this->forbiddenStrings);

        $cleanStr = str_replace($keys, $values, $str);
        foreach ($this->forbiddenStringPatterns as $regex) {
            $cleanStr = (string) preg_replace('#' . $regex . '#is', '[removed]', $cleanStr);
        }

        return $cleanStr;
    }

    /**
     * Remove invisible characters
     * This prevents sandwiching null characters
     * between ASCII characters, like Java\0script.
     *
     * @param string $str
     * @param bool $urlEncode
     * @return string
     */
    protected function removeInvisibleCharacters(string $str, bool $urlEncode = true): string
    {
        $nonDisplayables = [];

        /* Every control character except newline (dec 10),
     carriage return (dec 13) and horizontal tab (dec 09)
        */

        if ($urlEncode) {
            $nonDisplayables[] = '/%0[0-8bcef]/i';  // URL encoded 00-08, 11, 12, 14, 15
            $nonDisplayables[] = '/%1[0-9a-f]/i';   // URL encoded 16-31
            $nonDisplayables[] = '/%7f/i';      // URL encoded 127
        }

        $nonDisplayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-08, 11, 12, 14-31, 127
        $count = 0;
        do {
            $str = (string) preg_replace($nonDisplayables, '', $str, -1, $count);
        } while ($count > 0);

        return $str;
    }
}
