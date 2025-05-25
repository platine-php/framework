<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper\Mermaid\Graph;

use Platine\Dev\PlatineTestCase;
use Platine\Framework\Helper\Mermaid\Graph\Graph;
use Platine\Framework\Helper\Mermaid\Graph\Link;
use Platine\Framework\Helper\Mermaid\Graph\Node;

/**
 * Graph class tests
 *
 * @group core
 * @group graph
 */
class GraphTest extends PlatineTestCase
{
    public function testSetGet(): void
    {
        $o = new Graph([]);
        $o->addNode(new Node('foo'));
        $o->addLink(new Link(new Node('A'), new Node('B')));
        $o->addStyle('mystyle');

        $o->addSubGraph(new Graph([
            'title' => 'My Sub Graph'
        ]));

        $this->assertCount(1, $o->getLinks());
        $this->assertCount(1, $o->getNodes());
        $this->assertCount(1, $o->getStyles());
        $this->assertCount(1, $o->getSubGraphs());
        $this->assertCount(2, $o->getParams());
    }

    public function testAll(): void
    {
        $o = new Graph([]);

        $nodeA = new Node('A');
        $nodeB = new Node('B');
        $nodeC = new Node('C');
        $nodeD = new Node('D');
        $nodeE = new Node('E');
        $nodeE->setTitle('');

        $o->addNode($nodeA);
        $o->addNode($nodeB);
        $o->addNode($nodeE);
        $o->addLink(new Link($nodeA, $nodeB, 'A to B'));
        $o->addLink(new Link($nodeA, $nodeE, 'A to E'));
        $o->addStyle('mainGraphStyle');

        $sg = new Graph([
            'title' => 'My Sub Graph'
        ]);
        $sg->addNode($nodeC);
        $sg->addNode($nodeD);
        $link = new Link($nodeC, $nodeD, 'C to D');
        $link->setStyle(Link::THICK);
        $sg->addLink($link);

        $o->addSubGraph($sg);

        $expected = 'graph LR;
    7fc56270e7a70fa81a5935b72eacbe29("A");
    9d5ed678fe57bcca610140957afab571("B");
    3a3ea00cfc35332cedf6e5e9a32e94da;

    7fc56270e7a70fa81a5935b72eacbe29-->|"A to B"|9d5ed678fe57bcca610140957afab571;
    7fc56270e7a70fa81a5935b72eacbe29-->|"A to E"|3a3ea00cfc35332cedf6e5e9a32e94da;

    subgraph "My Sub Graph"
        0d61f8370cad1d412f80b84d143e1257("C");
        f623e75af30e62bbd73d6df5b50bb7b5("D");
        0d61f8370cad1d412f80b84d143e1257 == "C to D" ==> f623e75af30e62bbd73d6df5b50bb7b5;
    end
mainGraphStyle;';
        $this->assertEquals($o->__toString(), $expected);
    }
}
