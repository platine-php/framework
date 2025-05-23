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
 *  @file Auditor.php
 *
 *  The Auditor class
 *
 *  @package    Platine\Framework\Audit
 *  @author Platine Developers team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Framework\Audit;

use DateTime;
use Platine\Container\ContainerInterface;
use Platine\Framework\Audit\Model\AuditRepository;
use Platine\Framework\Auth\Repository\UserRepository;
use Platine\Http\ServerRequestInterface;
use Platine\Stdlib\Helper\Str;
use Platine\UserAgent\UserAgent;

/**
 * @class Auditor
 * @package Platine\Framework\Audit
 */
class Auditor
{
    /**
     * The audit details
     * @var string
     */
    protected string $detail = '';

    /**
     * The audit event
     * @var string
     */
    protected string $event = '';

    /**
     * The audits tags
     * @var array<string>
     */
    protected array $tags = [];

    /**
     * Create new instance
     * @param AuditRepository $repository
     * @param ContainerInterface $container
     * @param UserAgent $userAgent
     * @param AuditUserInterface $auditUser
     * @param UserRepository $userRepository
     */
    public function __construct(
        protected AuditRepository $repository,
        protected ContainerInterface $container,
        protected UserAgent $userAgent,
        protected AuditUserInterface $auditUser,
        protected UserRepository $userRepository
    ) {
    }

    /**
     * Return the audit repository instance
     * @return AuditRepository
     */
    public function getRepository(): AuditRepository
    {
        return $this->repository;
    }

    /**
     * Save the audits information's
     * @return bool
     */
    public function save(): bool
    {
        $userAgent = '';
        $url = '';
        if ($this->container->has(ServerRequestInterface::class)) {
            /** @var ServerRequestInterface $request */
            $request = $this->container->get(ServerRequestInterface::class);
            $url = $request->getUri()->getPath();

            $userAgentStr = $request->getHeaderLine('User-Agent');
            $ua = $this->userAgent->parse($userAgentStr);
            $userAgent = sprintf(
                '%s %s - %s %s',
                $ua->os()->getName(),
                $ua->os()->getVersion(),
                $ua->browser()->getName(),
                $ua->browser()->getVersion()
            );
        }

        $entity = $this->repository->create([
            'event' => $this->event,
            'detail' => $this->detail,
            'user_agent' => $userAgent,
            'tags' => implode(', ', $this->tags),
            'date' => new DateTime('now'),
            'ip' => Str::ip(),
            'user_id' => $this->auditUser->getUserId(),
            'url' => $url,
        ]);

        return (bool) $this->repository->save($entity);
    }

    /**
     *
     * @param string $detail
     * @return $this
     */
    public function setDetail(string $detail): self
    {
        $this->detail = $detail;
        return $this;
    }

    /**
     *
     * @param string $event
     * @return $this
     */
    public function setEvent(string $event): self
    {
        $this->event = $event;
        return $this;
    }

    /**
     *
     * @param array<string> $tags
     * @return $this
     */
    public function setTags(array $tags): self
    {
        $this->tags = $tags;
        return $this;
    }
}
