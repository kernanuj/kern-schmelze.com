<?php declare(strict_types=1);

namespace InvTest\Acceptance;

use Symfony\Component\BrowserKit;
use Symfony\Component\HttpFoundation;

/**
 * Class Request
 * @package InvTest\Acceptance
 */
class Response
{

    /**
     * @var BrowserKit\AbstractBrowser
     */
    private $browser;

    /**
     * Response constructor.
     * @param BrowserKit\AbstractBrowser $browser
     */
    public function __construct(BrowserKit\AbstractBrowser $browser)
    {
        $this->browser = $browser;
    }

    /**
     * @param BrowserKit\AbstractBrowser $browser
     * @return $this
     */
    public static function fromRequest(BrowserKit\AbstractBrowser $browser): self
    {
        return new self($browser);
    }

    /**
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return $this->getResponseObject()->getStatusCode();
    }

    /**
     * @return HttpFoundation\Response
     */
    public function getResponseObject(): HttpFoundation\Response
    {
        $responseObject = $this->browser->getResponse();
        \assert($responseObject instanceof HttpFoundation\Response);

        return $responseObject;
    }

    /**
     * @return string
     */
    public function getResponseContent(): string
    {
        return $this->getResponseObject()->getContent();
    }


}
