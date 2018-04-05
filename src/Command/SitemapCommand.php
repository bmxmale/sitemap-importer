<?php

namespace DevTest\Command;

use DevTest\Core\SitemapImporter;
use Snowdog\DevTest\Model\UserManager;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SitemapCommand
 * @package Snowdog\DevTest\Command
 */
class SitemapCommand
{

    /**
     * @var UserManager
     */
    private $userManager;

    /**
     * @var SitemapImporter
     */
    private $sitemapImporter;

    /**
     * SitemapCommand constructor.
     * @param UserManager $userManager
     * @param SitemapImporter $sitemapImporter
     */
    public function __construct(
        UserManager $userManager,
        SitemapImporter $sitemapImporter
    )
    {
        $this->userManager = $userManager;
        $this->sitemapImporter = $sitemapImporter;
    }

    /**
     * @param $userLogin
     * @param $sitemapUrl
     * @param OutputInterface $output
     */
    public function __invoke($userLogin, $sitemapUrl, OutputInterface $output)
    {
        $user = $this->userManager->getByLogin($userLogin);
        if (!$user) {
            $output->writeln('<error>User "' . $userLogin . '" does not exists!</error>');
            return;
        }

        if (!$sitemapUrl) {
            $output->writeln('<error>No sitemap url!</error>');
            return;
        }

        $this->sitemapImporter->parseSitemap($sitemapUrl);

        $status = $this->sitemapImporter->addWebsitePageToUser($user);
        if (false === $status) {
            $output->writeln('<error>Error during sitemap import</error>');
        }

        $sitemap = $this->sitemapImporter->getSitemap();
        if (!empty($sitemap)) {
            $output->writeln('<info>Sitemap imported</info>');
            foreach ($sitemap as $website => $pages) {
                $output->writeln('Website: <info>' . $website . '</info>');
                $output->writeln('Pages: <comment>' . implode(', ', $pages) . '</comment>');
            }
        }
    }
}
