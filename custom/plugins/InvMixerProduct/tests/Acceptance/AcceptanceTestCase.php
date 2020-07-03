<?php declare(strict_types=1);

namespace InvMixerProduct\Test\Acceptance;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class AcceptanceTestCase
 * @package InvMixerProduct\Tests\Acceptance
 */
abstract class AcceptanceTestCase extends TestCase
{

    use IntegrationTestBehaviour;

    /**
     * @param Request $request
     * @return \Symfony\Component\DomCrawler\Crawler|null
     */
    public function doRequest(Request $request): Crawler
    {

        $browser = $this->createBrowser();
        return $browser->request(
            $request->getMethod(),
            $request->getFullUrl()
        );


    }

    private function createBrowser()
    {
        $browser = KernelLifecycleManager::createBrowser(
            $this->getKernel(), true
        );
        $browser->setServerParameters([
            'HTTP_ACCEPT' => 'application/json',
        ]);

        return $browser;
    }
}
