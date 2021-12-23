<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Pagination;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Pagination\BootstrapRenderer;
use Platine\Pagination\Page;
use Platine\Pagination\Pagination;

/*
 * @group core
 * @group framework
 */
class BootstrapRendererTest extends PlatineTestCase
{
    public function testRenderEmpty(): void
    {
        $pagination = $this->getMockInstance(Pagination::class, [
            'getTotalPages' => 1
        ]);
        $o = new BootstrapRenderer();
        $this->assertEquals('', $o->render($pagination));
    }

    public function testRenderAll(): void
    {
        $pages = [
            $this->getMockInstance(Page::class, [
                'getUrl' => 'page_1',
                'getNumber' => 1,
                'isCurrent' => false,
            ]),
            $this->getMockInstance(Page::class, [
                'getUrl' => null,
                'getNumber' => 2,
                'isCurrent' => true,
            ]),
            $this->getMockInstance(Page::class, [
                'getUrl' => 'page_3',
                'getNumber' => 3,
                'isCurrent' => false,
            ]),
        ];
        $pagination = $this->getMockInstance(Pagination::class, [
            'getTotalPages' => 10,
            'hasPreviousPage' => true,
            'getPreviousUrl' => 'previous_url',
            'getPreviousText' => 'Previous',
            'getPages' => $pages,
            'hasNextPage' => true,
            'getNextUrl' => 'next_url',
            'getNextText' => 'Next',
        ]);
        $o = new BootstrapRenderer();
        $expected = '<ul class = "pagination"><li class = "page-item"><a href = '
                . '"previous_url" class="page-link">&laquo; Previous</a></li>'
                . '<li class = "page-item"><a href = "page_1" class="page-link">1'
                . '</a></li><li class = "page-item disabled"><a href="#" '
                . 'class="page-link">2</a></li><li class = "page-item">'
                . '<a href = "page_3" class="page-link">3</a></li>'
                . '<li class = "page-item"><a href = "next_url" class="page-link">'
                . 'Next &raquo;</a></li></ul>';

        $this->assertEquals($expected, $o->render($pagination));
    }
}
