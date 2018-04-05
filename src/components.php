<?php

use Snowdog\DevTest\Component\CommandRepository;
use Snowdog\DevTest\Component\Menu;
use Snowdog\DevTest\Component\RouteRepository;
use DevTest\Command\SitemapCommand;
use DevTest\Controller\SitemapAction;
use DevTest\Controller\ImportSitemapAction;
use DevTest\Menu\SitemapMenu;

Menu::register(SitemapMenu::class, 30);

RouteRepository::registerRoute('GET', '/sitemap', SitemapAction::class, 'execute');
RouteRepository::registerRoute('POST', '/sitemap', ImportSitemapAction::class, 'execute');

CommandRepository::registerCommand('import:sitemap [userLogin] [sitemapUrl]', SitemapCommand::class);
