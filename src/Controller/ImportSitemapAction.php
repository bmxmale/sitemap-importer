<?php

namespace DevTest\Controller;

use DevTest\Core\SitemapImporter;
use Snowdog\DevTest\Model\UserManager;

/**
 * Class ImportSitemapAction
 * @package Snowdog\DevTest\Controller
 */
class ImportSitemapAction
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
     * ImportSitemapAction constructor.
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
     * Action for import sitemap from www
     */
    public function execute()
    {
        if (!isset($_SESSION['login'])) {
            $this->redirectPage('User not logged');
        }

        $sitemapUrl = $_POST['sitemapUrl'];
        if (!filter_var($sitemapUrl, FILTER_VALIDATE_URL)) {
            $this->redirectPage('Wrong URL address');
        }

        $user = $this->userManager->getByLogin($_SESSION['login']);

        $this->sitemapImporter->parseSitemap($sitemapUrl);

        $status = $this->sitemapImporter->addWebsitePageToUser($user);
        if (false === $status) {
            $this->redirectPage('Error during sitemap import');
        }

        $websiteIds = $this->sitemapImporter->getWebsiteIds();
        if (1 === count($websiteIds)) {
            $location = '/website/' . $websiteIds[0];
        } else {
            $location = '/';
        }

        $this->redirectPage('Sitemap imported', $location);
    }

    /**
     * @param bool $msg
     */
    private function redirectPage($msg = false, $location = '/sitemap')
    {
        if ($msg) {
            $_SESSION['flash'] = $msg;
        }

        header('Location: ' . $location);
        exit();
    }
}