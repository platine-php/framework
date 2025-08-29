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

declare(strict_types=1);

namespace Platine\Framework\Template\Tag;

use Platine\Config\Config;
use Platine\Filesystem\FileInterface;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Helper\FileHelper;
use Platine\Template\Exception\ParseException;
use Platine\Template\Parser\AbstractTag;
use Platine\Template\Parser\Context;
use Platine\Template\Parser\Lexer;
use Platine\Template\Parser\Parser;
use Platine\Template\Parser\Token;

/**
 * @class ImageTag
 * @package Platine\Framework\Template\Tag
 */
class ImageTag extends AbstractTag
{
    /**
     * The name of the tag
     * @var string
     */
    protected string $tagName = 'image';

    /**
     * The image name
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
                'Syntax Error in "%s" - Valid syntax: %s [name]',
                $this->tagName,
                $this->tagName
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(Context $context): string
    {
        /** @template T @var Config<T> $config */
        $config = app(Config::class);

        /** @var Filesystem $filesystem */
        $filesystem = app(Filesystem::class);

        /** @var FileHelper $fileHelper */
        $fileHelper = app(FileHelper::class);

        $filename = $this->name;
        if ($context->hasKey($filename)) {
            $filename = $context->get($filename);
        }

        if (empty($filename)) {
            return '';
        }

        $width = $this->attributes['width'] ?? 50;
        $height = $this->attributes['height'] ?? 50;
        $useRootAttr = $this->attributes['root'] ?? 'true';
        $useRoot = true;
        if ($useRootAttr === 'false') {
            $useRoot = false;
        }
        $configPath = $fileHelper->getRootPath(
            $config->get($this->getConfigName()),
            $useRoot
        );

        $path = $configPath . '/' . $filename;
        $file = $filesystem->file($path);
        if ($file->exists() === false) {
            return '';
        }

        $image = $this->getImageContent($file, $path, $config);

        return sprintf(
            '<img src="%s" width="%s" height="%s"/>',
            $image,
            $width,
            $height
        );
    }

    /**
     * Return the name of the configuration
     * @return string
     */
    protected function getConfigName(): string
    {
        return 'platform.data_image_path';
    }

    /**
     * Return the image content
     * @param FileInterface $file
     * @param string $path
     * @param Config $config
     * @return string
     */
    protected function getImageContent(
        FileInterface $file,
        string $path,
        Config $config
    ): string {
        $extension = $file->getExtension();
        $image = sprintf(
            'data:image/%s;base64,%s',
            $extension,
            base64_encode($file->read())
        );

        return $image;
    }
}
