<?php declare(strict_types=1);

namespace InvTest\Acceptance;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;

/**
 * Class AcceptanceTestCase
 * @package InvMixerProduct\Tests\Acceptance
 */
abstract class AcceptanceTestCase extends TestCase
{

    use IntegrationTestBehaviour;

    /**
     * @param Request $request
     * @return Response
     */
    public function doRequest(Request $request): Response
    {

        $browser = $this->createBrowser();
        $browser->request(
            $request->getMethod(),
            $request->getFullUrl()
        );

        return Response::fromRequest($browser);


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
