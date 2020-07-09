<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template\TemplateOption\Api;

use PHPUnit\Framework\TestCase;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Api\TemplateOptionController;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class TemplateOptionControllerTest extends TestCase
{
    use ServicesTrait;

    /**
     * @var TemplateOptionController
     */
    private $controller;

    public function setUp(): void
    {
        $this->controller = $this->getTemplateOptionController();
    }

    public function testGetTypes(): void
    {
        $response = $this->controller->getTypes();
        $data = \json_decode($response->getContent() ?: '', true);

        static::assertTrue($response->isSuccessful());
        static::assertSame($data, $this->getExpectedTypes());
    }
}
