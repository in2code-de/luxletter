<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Exception\RequestException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class RequestService
{
    protected $requestFactory = null;

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

    protected function getDefaultHeaders(): array
    {
        $options = [
            'allow_redirects' => true,
            'headers' => [
                'Cache-Control' => 'no-cache',
                'User-Agent' => 'TYPO3 luxletter',
            ],
        ];
        if (getenv('LUXLETTER_AUTH_USER') && getenv('LUXLETTER_AUTH_PASS')) {
            $options['auth'] = [
                0 => getenv('LUXLETTER_AUTH_USER'),
                1 => getenv('LUXLETTER_AUTH_PASS'),
            ];
        }
        return $options;
    }
}
