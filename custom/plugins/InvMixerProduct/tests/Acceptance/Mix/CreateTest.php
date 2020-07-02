<?php declare(strict_types=1);

namespace InvMixerProduct\Test\Acceptance\Mix;


use InvMixerProduct\Test\Acceptance\AcceptanceTestCase;

class CreateTest extends AcceptanceTestCase
{



    public function testMixIsInitiated(): void
    {

        $response = $this->doRequest(
            RequestFactory::create()
            ->setControllerUrl('/produkt-mixer/mix')
            ->setMethod('GET')
        );


    }

    public function testCanAddProduct(): void
    {


    }



}
