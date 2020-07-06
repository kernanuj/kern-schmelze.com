<?php declare(strict_types=1);

namespace InvTest\Acceptance\Mix;


use InvTest\Acceptance\AcceptanceTestCase;
use InvTest\Acceptance\RequestFactory;

/**
 * Class CreateTest
 * @package InvTest\Acceptance\Mix
 */
class CreateTest extends AcceptanceTestCase
{

    /**
     *
     */
    public function testMixIsInitiated(): void
    {
        $response = $this->doRequest(
            RequestFactory::create()
                ->setControllerUrl('/produkt-mixer/mix/state')
                ->setMethod('GET')
        );

        self::assertEquals(200, $response->getHttpStatusCode());
    }

}
