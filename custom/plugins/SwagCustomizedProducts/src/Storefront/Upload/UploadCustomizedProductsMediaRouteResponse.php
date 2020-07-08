<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Upload;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

class UploadCustomizedProductsMediaRouteResponse extends StoreApiResponse
{
    /**
     * @var ArrayStruct
     */
    protected $object;

    public function __construct(string $mediaId, string $filename)
    {
        parent::__construct(new ArrayStruct(['mediaId' => $mediaId, 'fileName' => $filename]));
    }

    public function getMediaId(): string
    {
        return $this->object->get('mediaId');
    }

    public function getFileName(): string
    {
        return $this->object->get('fileName');
    }
}
