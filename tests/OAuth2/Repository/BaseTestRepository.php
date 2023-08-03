<?php

declare(strict_types=1);

namespace Platine\Test\Framework\OAuth2\Repository;

use Platine\Dev\PlatineTestCase;
use Platine\Orm\Entity;
use Platine\Orm\EntityManager;
use Platine\Orm\Mapper\EntityMapper;
use Platine\Orm\Mapper\EntityMapperInterface;

/*
 * @group core
 * @group framework
 */
class BaseTestRepository extends PlatineTestCase
{
    protected function getEntityManager(array $mockMethods = [], array $excludes = []): EntityManager
    {
        $methods = $this->getClassMethodsToMock(EntityManager::class, $excludes);

        $em = $this->getMockBuilder(EntityManager::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods($methods)
                    ->getMock();

        foreach ($mockMethods as $method => $returnValue) {
            $em->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $em;
    }

    protected function getEntityInstance(
        array $columns = [],
        EntityManager $em = null,
        EntityMapper $mapper = null
    ): Entity {
        if (!$em) {
            $em = $this->getMockBuilder(EntityManager::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        }

        if (!$mapper) {
            $mapper = $this->getMockBuilder(EntityMapper::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        }
        return new class ($em, $mapper, $columns) extends Entity
        {
            public static function mapEntity(EntityMapperInterface $mapper): void
            {
            }
        };
    }

    protected function getEntityMapper(array $mockMethods = [], array $excludes = []): EntityMapper
    {
        $methods = $this->getClassMethodsToMock(EntityMapper::class, $excludes);

        $em = $this->getMockBuilder(EntityMapper::class)
                    ->disableOriginalConstructor()
                    ->onlyMethods($methods)
                    ->getMock();

        foreach ($mockMethods as $method => $returnValue) {
            $em->expects($this->any())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $em;
    }
}
