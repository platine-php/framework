<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Http\RateLimit\Exception;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Http\RateLimit\Exception\LimitExceededException;
use Platine\Framework\Http\RateLimit\Rate;

/*
 * @group core
 * @group framework
 */
class LimitExceededExceptionTest extends PlatineTestCase
{
    public function testCreate(): void
    {
        $ex = LimitExceededException::create('api', Rate::perHour(100));

        $this->assertEquals('api', $ex->getIdentifier());
        $this->assertEquals(100, $ex->getRate()->getQuota());
        $this->assertEquals(3600, $ex->getRate()->getInterval());
    }
}
