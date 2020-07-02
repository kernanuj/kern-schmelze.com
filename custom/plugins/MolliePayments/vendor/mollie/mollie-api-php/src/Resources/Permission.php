<?php

namespace Mollie\Api\Resources;

use stdClass;

class Permission extends BaseResource
{
    /**
     * @var string
     */
    public $resource;

    /**
     * @var string
     * @example payments.read
     */
    public $id;

    /**
     * @var string
     */
    public $description;

    /**
     * @var bool
     */
    public $granted;

    /**
     * @var stdClass
     */
    public $_links;
}
