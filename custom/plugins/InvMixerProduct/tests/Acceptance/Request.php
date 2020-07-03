<?php declare(strict_types=1);

namespace InvMixerProduct\Test\Acceptance;

/**
 * Class Request
 * @package InvMixerProduct\Test\Acceptance
 */
class Request
{

    /**
     * @var string
     */
    public $baseUrl;

    /**
     * @var string
     */
    public $controllerUrl;

    /**
     * @var string
     */
    public $method = 'GET';

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return Request
     */
    public function setMethod(string $method): Request
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullUrl(): string
    {
        return $this->getBaseUrl() . $this->getControllerUrl();
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     * @return Request
     */
    public function setBaseUrl(string $baseUrl): Request
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getControllerUrl(): string
    {
        return $this->controllerUrl;
    }

    /**
     * @param string $controllerUrl
     * @return Request
     */
    public function setControllerUrl(string $controllerUrl): Request
    {
        $this->controllerUrl = $controllerUrl;
        return $this;
    }


}
