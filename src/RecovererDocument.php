<?php

namespace DocusignBundle;

use DocuSign\eSign\Api\AccountsApi;
use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration;
use DocusignBundle\EnvelopeBuilderInterface;
use DocusignBundle\Grant\JwtGrant;
use Symfony\Component\HttpFoundation\Request;

final class RecovererDocument
{
    private $apiAccountId;
    private $demo;
    private $grant;

    private $queryParams = [];
    private $headerParams = [
        "Accept" => "application/pdf",
        'Content-Type' => "application/pdf",
    ];

    public const DEMO_ACCOUNT_API_URI = 'https://demo.docusign.net/restapi';
    public const ACCOUNT_API_URI = 'https://www.docusign.net/restapi';

    public function __construct(string $apiAccountId, bool $demo, JwtGrant $grant)
    {
        $this->apiAccountId = $apiAccountId;
        $this->demo = $demo ? self::DEMO_ACCOUNT_API_URI : self::ACCOUNT_API_URI;;
        $this->grant = $grant;
    }

    public function getDocument(array $params)
    {
        if(isset($params['envelopeId'])){
            $url = "/v2.1/accounts/{$this->apiAccountId}/envelopes/{$params['envelopeId']}/documents/1";
            $this->setParams($params);
            $apiClient = $this->getAccountApi()->getApiClient();

            try {
                list($response, $statusCode, $httpHeader) = $apiClient->callApi(
                    $url,
                    'GET',
                    $this->queryParams,
                    '',
                    []
                );
                if($statusCode === 200 && !empty($response)) {
                    $base64 = base64_encode($response);
                    return [
                        'file' => $base64,
                        'name' => "document.pdf",
                        'mimeType' => "application/pdf"
                    ];
                }
            } catch (ApiException $e) {
                echo json_encode(["error" => "Erreur API : " . $e->getMessage()]);
            }

        }
    }

    public function isSignHereTabs(array $params): bool
    {
        if(isset($params['envelopeId'], $params['recipientId'])){
            $apiClient = $this->getAccountApi()->getApiClient();
            $envelopeApi = new EnvelopesApi($apiClient);
            $tabs = $envelopeApi->listTabs(
                $this->apiAccountId,
                $params['envelopeId'],
                $params['recipientId']
            );
            return !is_null($tabs->getSignHereTabs());
        }
    }

    private function setParams(array $params) :void
    {
        if(isset($params['queryParams'])){
            $this->queryParams = $params['queryParams'];
        }

        if(isset($params['headerParams'])){
            $this->headerParams = $params['headerParams'];
        }
    }

    private function getAccountApi(): AccountsApi
    {
        $grant = ($this->grant)();
        $host = $this->demo ? self::DEMO_ACCOUNT_API_URI : self::ACCOUNT_API_URI;
        $config = new Configuration();
        $config->setHost($host);
        $config->addDefaultHeader('Authorization', 'Bearer ' . $grant);
        $config->setAccessToken($grant);
        $apiClient = new ApiClient($config);
        return new AccountsApi($apiClient);

    }
}
