<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\TreeHelper;

class TreeHelperTest extends PlatineTestCase
{
    public function testCreateTree(): void
    {
        $data = [
            ['id' => 1, 'parent_id' => 0, 'name' => 'Root'],
            ['id' => 2, 'parent_id' => 1, 'name' => 'Data'],
            ['id' => 3, 'parent_id' => 1, 'name' => 'Document'],
            ['id' => 4, 'parent_id' => 3, 'name' => 'PDF'],
        ];

        $tree = TreeHelper::createTree($data);
        $this->assertCount(1, $tree);
        $this->assertArrayHasKey('children', $tree[0]);
        $this->assertCount(2, $tree[0]['children']);
        $this->assertEquals(2, $tree[0]['children'][0]['id']);
    }
}
