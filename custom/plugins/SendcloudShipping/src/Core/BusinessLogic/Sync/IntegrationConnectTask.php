<?php

namespace Sendcloud\Shipping\Core\BusinessLogic\Sync;

use Sendcloud\Shipping\Core\BusinessLogic\DTO\CredentialsDTO;

/**
 * Class IntegrationConnectTask
 * @package Sendcloud\Shipping\Core\BusinessLogic\Sync
 */
class IntegrationConnectTask extends BaseSyncTask
{

    /**
     * @var CredentialsDTO Credentials for SendCloud connection
     */
    private $credentials;

    /**
     * IntegrationConnectTask constructor.
     * @param CredentialsDTO $credentials
     */
    public function __construct(CredentialsDTO $credentials)
    {
        $this->credentials = $credentials;
    }

    /**
     * String representation of object
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize($this->credentials);
    }

    /**
     * Constructs the object
     *
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->credentials = unserialize($serialized);
    }
    
    /**
     * Runs task logic
     */
    public function execute()
    {
        $this->getConnectService()->initializeConnection($this->credentials);

        $this->reportProgress(100);
    }
}
