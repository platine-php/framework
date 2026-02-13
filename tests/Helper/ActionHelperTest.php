<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Helper;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Framework\Audit\Auditor;
use Platine\Framework\Helper\ActionHelper;
use Platine\Framework\Helper\FileHelper;
use Platine\Framework\Helper\Flash;
use Platine\Framework\Helper\Sidebar;
use Platine\Framework\Helper\ViewContext;
use Platine\Framework\Http\RouteHelper;
use Platine\Lang\Lang;
use Platine\Logger\Logger;
use Platine\Pagination\Pagination;
use Platine\Template\Template;

class ActionHelperTest extends PlatineTestCase
{
    public function testAll(): void
    {
        $lang = $this->getMockInstance(Lang::class);
        $pagination = $this->getMockInstance(Pagination::class);
        $config = $this->getMockInstance(Config::class);
        $sidebar = $this->getMockInstance(Sidebar::class);
        $template = $this->getMockInstance(Template::class);
        $routeHelper = $this->getMockInstance(RouteHelper::class);
        $flash = $this->getMockInstance(Flash::class);
        $logger = $this->getMockInstance(Logger::class);
        $fileHelper = $this->getMockInstance(FileHelper::class);
        $auditor = $this->getMockInstance(Auditor::class);
        $context = $this->getMockInstance(ViewContext::class);
        $o = new ActionHelper(
            $pagination,
            $context,
            $routeHelper,
            $lang,
            $logger,
            $auditor,
            $fileHelper,
            $config,
            $sidebar,
            $template,
            $flash,
        );

        $this->assertInstanceOf(ActionHelper::class, $o);
        $this->assertInstanceOf(Pagination::class, $o->getPagination());
        $this->assertInstanceOf(Lang::class, $o->getLang());
        $this->assertInstanceOf(Config::class, $o->getConfig());
        $this->assertInstanceOf(Sidebar::class, $o->getSidebar());
        $this->assertInstanceOf(Template::class, $o->getTemplate());
        $this->assertInstanceOf(RouteHelper::class, $o->getRouteHelper());
        $this->assertInstanceOf(Flash::class, $o->getFlash());
        $this->assertInstanceOf(Logger::class, $o->getLogger());
        $this->assertInstanceOf(FileHelper::class, $o->getFileHelper());
        $this->assertInstanceOf(Auditor::class, $o->getAuditor());
        $this->assertInstanceOf(ViewContext::class, $o->getContext());
    }
}
