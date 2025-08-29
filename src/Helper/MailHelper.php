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

namespace Platine\Framework\Helper;

use Platine\Config\Config;
use Platine\Mail\Mailer;
use Platine\Mail\Message;
use Platine\Mail\Transport\TransportInterface;
use Platine\Stdlib\Helper\Arr;
use Platine\Template\Template;

/**
 * @class MailHelper
 * @package Platine\Framework\Helper
 * @template T
 */
class MailHelper
{
    /**
     * Create new instance
     * @param Template $template
     * @param TransportInterface $transport
     * @param PrintHelper<T> $printHelper
     * @param Config<T> $config
     */
    public function __construct(
        protected Template $template,
        protected TransportInterface $transport,
        protected PrintHelper $printHelper,
        protected Config $config,
    ) {
    }

    /**
     * Send the mail using report content
     * @param int|string $reportId
     * @param string $object
     * @param string|array<string> $receiverAddress
     * @param array<string, mixed> $data
     * @param array<int|string, string> $attachments
     * @param string $senderAddress
     * @param string $senderName
     * @return bool
     */
    public function sendReportMail(
        int|string $reportId,
        string $object,
        string|array $receiverAddress,
        array $data = [],
        array $attachments = [],
        string $senderAddress = '',
        string $senderName = ''
    ): bool {
        $content = $this->printHelper->getReportContent($reportId);

        return $this->sendMail(
            $reportId,
            $content,
            $object,
            $receiverAddress,
            $data,
            $attachments,
            $senderAddress,
            $senderName
        );
    }


    /**
     * Main function to send the mail
     * @param int|string $reportId this parameter is used only for
     * debug of report content
     * @param string $content
     * @param string $object
     * @param string|array<string> $receiverAddress
     * @param array<string, mixed> $data
     * @param array<int|string, string> $attachments
     * @param string $senderAddress
     * @param string $senderName
     * @return bool
     */
    public function sendMail(
        int|string $reportId,
        string $content,
        string $object,
        string|array $receiverAddress,
        array $data = [],
        array $attachments = [],
        string $senderAddress = '',
        string $senderName = ''
    ): bool {
        // @codeCoverageIgnoreStart
        if ($this->isEnabled() === false) {
            return true;
        }
        // @codeCoverageIgnoreEnd

        if (empty($receiverAddress)) {
            return false;
        }

        $mainInformations = $this->printHelper->getMainData();
        if (empty($senderAddress)) {
            $senderAddress = $this->getSenderEmail();
        }

        if (empty($senderName)) {
            $senderName = $this->config->get('app.name');
        }

        $reportData = $data + $mainInformations;

        // If need debug
        $this->printHelper->debugReport($reportId, $reportData);

        $receivers = Arr::wrap($receiverAddress);
        $mailBody = $this->template->renderString($content, $reportData);

        $mailer = new Mailer($this->transport);

        foreach ($receivers as $receiver) {
            $message = new Message();
            $message->setFrom($senderAddress, $senderName)
                    ->setTo($receiver)
                    ->setSubject($object)
                    ->setBody($mailBody)
                    ->setHtml();

            foreach ($attachments as $name => $path) {
                if (is_string($name)) {
                    $message->addAttachment($path, $name);
                } else {
                    $message->addAttachment($path);
                }
            }

            if ($mailer->send($message) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the sending mail feature is enabled or not
     * @return bool
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * Return the sender email address
     * @return string
     */
    protected function getSenderEmail(): string
    {
        return '';
    }
}
