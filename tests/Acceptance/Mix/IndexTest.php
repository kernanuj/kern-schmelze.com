<?php declare(strict_types=1);

namespace InvTest\Acceptance\Mix;


use InvTest\Acceptance\AcceptanceTestCase;
use InvTest\Acceptance\RequestFactory;

/**
 * Class IndexTest
 * @package InvTest\Acceptance\Mix
 */
class IndexTest extends AcceptanceTestCase
{

    /**
     *
     */
    public function testCanAccessProductIndex(): void
    {
        $response = $this->doRequest(
            RequestFactory::create()
                ->setControllerUrl('/produkt-mixer/mix')
                ->setMethod('GET')
        );

        self::assertEquals(200, $response->getHttpStatusCode());
    }

}
