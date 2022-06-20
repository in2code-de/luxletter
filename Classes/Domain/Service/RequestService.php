<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Exception\RequestException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RequestService
 */
class RequestService
{
    /**
     * @var null
     */
    protected $requestFactory = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
    }

    /**
     * @param string $uri
     * @return string
     * @throws RequestException
     */
    public function getContentFromUrl(string $uri): string
    {
        $response = $this->requestFactory->request($uri, 'GET', $this->getDefaultHeaders());
        if ($response->getStatusCode() !== 200) {
            throw new RequestException('Could not connect to: ' . $uri, 1645635195);
        }
        return $response->getBody()->getContents();
    }

    /**
     * @return array
     */
    protected function getDefaultHeaders(): array
    {
        return [
            'allow_redirects' => true,
            'headers' => [
                'Cache-Control' => 'no-cache',
                'User-Agent' => 'TYPO3 luxletter',
            ]
        ];
    }
}
