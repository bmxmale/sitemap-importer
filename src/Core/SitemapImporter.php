<?php

namespace DevTest\Core;

use Snowdog\DevTest\Model\PageManager;
use Snowdog\DevTest\Model\User;
use Snowdog\DevTest\Model\Website;
use Snowdog\DevTest\Model\WebsiteManager;
use vipnytt\SitemapParser;
use vipnytt\SitemapParser\Exceptions\SitemapParserException;

/**
 * Class SitemapImporter
 * @package DevTest\Core
 */
class SitemapImporter
{
    /**
     * @var SitemapParser
     */
    private $parser;

    /**
     * @var WebsiteManager
     */
    private $websiteManager;
    /**
     * @var PageManager
     */
    private $pageManager;

    /**
     * @var array
     */
    private $sitemap = [];

    /**
     * @var array
     */
    private $websiteIds = [];

    /**
     * SitemapImporter constructor.
     * @param SitemapParser $parser
     * @param WebsiteManager $websiteManager
     * @param PageManager $pageManager
     */
    public function __construct(
        SitemapParser $parser,
        WebsiteManager $websiteManager,
        PageManager $pageManager
    )
    {
        $this->parser = $parser;
        $this->websiteManager = $websiteManager;
        $this->pageManager = $pageManager;
    }

    /**
     * @return array
     */
    public function getSitemap()
    {
        return $this->sitemap;
    }

    /**
     * @return array
     */
    public function getWebsiteIds()
    {
        return $this->websiteIds;
    }

    /**
     * @param $sitemapUrl
     * @return array|bool
     */
    public function parseSitemap($sitemapUrl)
    {
        if (!$sitemapUrl) {
            return false;
        }

        try {
            $this->parser->parse($sitemapUrl);
            $sitemapURLs = array_keys($this->parser->getURLs());
            foreach ($sitemapURLs as $sitemapURL) {
                $parsedUrl = parse_url($sitemapURL);
                $pageUrl = substr($parsedUrl['path'], 1);
                if ($pageUrl) {
                    $this->sitemap[$parsedUrl['host']][] = $pageUrl;
                }
            }
        } catch (SitemapParserException $e) {
            dump($e->getMessage());
            return false;
        }

        return $this->sitemap;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function addWebsitePageToUser(User $user)
    {
        if (empty($this->sitemap)) {
            return false;
        }

        foreach ($this->sitemap as $websiteHost => $pages) {
            /** @var Website $website */
            $website = $this->websiteManager->getByHostname($websiteHost);
            if (!$website) {
                $websiteId = $this->websiteManager->create($user, $websiteHost, $websiteHost);
                /** @var Website $website */
                $website = $this->websiteManager->getById($websiteId);
            }

            $this->websiteIds[] = $website->getWebsiteId();

            $existPages = $this->getExistPagesForWebsite($website);
            foreach ($pages as $pageUrl) {
                if (in_array($pageUrl, $existPages)) {
                    continue;
                }
                $this->pageManager->create($website, $pageUrl);
            }
        }

        return true;
    }

    /**
     * Get array with exist pages assigned to website
     *
     * @param Website $website
     * @return array
     */
    private function getExistPagesForWebsite(Website $website)
    {
        $existPages = $this->pageManager->getAllByWebsite($website);

        $pages = [];
        foreach ($existPages as $page) {
            $pages[$page->getPageId()] = $page->getUrl();
        }

        return $pages;
    }
}