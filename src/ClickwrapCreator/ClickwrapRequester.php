<?php

namespace DocusignBundle\ClickwrapCreator;

use DocuSign\Click\Api\AccountsApi;
use DocuSign\Click\Client\ApiClient;
use DocuSign\Click\Model\ClickwrapRequest;
use DocuSign\Click\Configuration;
use DocuSign\Click\Model\DocumentData;
use DocuSign\Click\Model\UserAgreementRequest;
use DocusignBundle\DependencyInjection\DocusignExtension;
use DocusignBundle\EnvelopeBuilderInterface;
use DocusignBundle\Grant\GrantInterface;
use DocusignBundle\Grant\JwtGrant;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor;

class ClickwrapRequester implements ClickwrapRequesterInterface
{

    public const DEMO_ACCOUNT_API_URI = 'https://demo.docusign.net/clickapi';
    public const ACCOUNT_API_URI = 'https://docusign.net/clickapi';

    public function __construct(private readonly string $apiAccountId, private readonly bool $demo, private readonly JwtGrant $grant){}


    public function createClickwrap(array $document_info, array $parameters)
    {
        $clickwrap = new ClickwrapRequest([
            'clickwrap_name' => $document_info['name'],
            'display_settings' => $this->handleClickwrapSetting($parameters),
            'documents' => [$this->handleCreateDocument($document_info['path'],$parameters)],
            'require_reacceptance' => true,
        ]);

        // envoyer le clickwrap et retourner la reponse
        $response = $this->getAccountApi()->createClickwrap($this->apiAccountId, $clickwrap);
        $this->activateClickwrap($response["clickwrap_id"], $response["version_id"]);
        return [
            "clickwrap_id" => $response["clickwrap_id"],
            "version_id" => $response["version_id"]
        ];
    }

    public function updateClickwrap(string $clickwrapId, array $document_info, array $parameters)
    {
        $clickwrap = new ClickwrapRequest([
            'clickwrap_name' => $document_info['name'],
            'display_settings' => $this->handleClickwrapSetting($parameters),
            'documents' => [$this->handleCreateDocument($document_info['path'],$parameters)],
            'require_reacceptance' => true
        ]);

        // envoyer le clickwrap et retourner la reponse
        $response = $this->getAccountApi()->createClickwrapVersion($this->apiAccountId,$clickwrapId,$clickwrap);
        $this->activateClickwrap($response["clickwrap_id"], $response["version_id"]);
        return [
            "clickwrap_id" => $response["clickwrap_id"],
            "version_id" => $response["version_id"]
        ];
    }

    public function deleteClickwrap(string $clickwrapId)
    {
        $this->getAccountApi()->updateClickwrap($this->apiAccountId, $clickwrapId, ['status' => 'inactive']);
        return $this->getAccountApi()->deleteClickwrap($this->apiAccountId, $clickwrapId);
    }

    public function signClickwrap(array $params)
    {
        if($this->checkParamForSignClickwrap($params)['error'] === true){
            throw new \Exception($this->checkParamForSignClickwrap($params)['message'], 500);
        }

        $documentData = new UserAgreementRequest();
        $documentData->setClientUserId($params["email"]);
        $rawData = [
          'fullName' => $params["fullname"],
          'email' => $params["email"],
          'company' => $params["company"] ?? "company",
          'title' => $params["title"] ?? "title",
          'date' => (new \DateTimeImmutable())->format('Y-m-d')
        ];
        $documentData->setDocumentData($rawData);
        $response = $this->getAccountApi()->createHasAgreed($this->apiAccountId, $params['clickwrapId'], $documentData);
        if($response->getStatus() === "created"){
            return $response->getAgreementUrl();
        } else {
            return null;
        }
    }

    private function checkParamForSignClickwrap(array $params): array
    {
        if(!empty($params))
        {
            if(!isset($params['email'])){
                return $this->formatError(true,"email");
            }
            elseif (!isset($params['fullname'])){
                return $this->formatError(true,"fullname");
            }
            elseif (!isset($params['clickwrapId'])){
                return $this->formatError(true,"clickwrapId");
            }
            else
            {
                return $this->formatError(false);
            }
        }
        return ["error" => true, "message" => "The parameter array is empty, 'email' and 'fullname' parameters are mandatory."];
    }

    private function formatError(bool $error, ?string $message = null): array
    {
        $response = [];
        $error ? $response = ['error' => $error, 'message' => sprintf("The '%s' parameter is missing.", $message)] : $response = ['error' => $error, 'message' => sprintf("ok", $message)];
        return $response ;
    }

    private function activateClickwrap($clickwrapId, string $versionId)
    {
        $clickwrap_request = new ClickwrapRequest(['status' => 'active']);
        return $this->getAccountApi()->updateClickwrapVersion(
            $this->apiAccountId,
            $clickwrapId,
            $versionId,
            $clickwrap_request
        );
    }

    private function handleClickwrapSetting(array $parameters)
    {
        $requires = ['consent_button_text','display_name','downloadable','format',
        'has_decline_button','must_read','require_accept','document_display'];

        $param = [];

        foreach ($parameters as $key => $parameter){
            if(in_array($key, $requires))
            {
                $param[$key] = $parameter;
            }
        }
        return (new DefineClickwrap($param))->getDisplaySettings();
    }

    private function handleCreateDocument(?string $path, array $parameters)
    {
        $requires = ['document_base64','document_name','file_extension','order'];
        $param = [];
        foreach ($parameters as $key => $parameter){
            if(in_array($key, $requires))
            {
                $param[$key] = $parameter;
            }
        }
        return (new CreateDocument($path,$param))->getDocument();
    }

    private function getAccountApi(): AccountsApi
    {
        $grant = ($this->grant)();
        $host = $this->demo ? self::DEMO_ACCOUNT_API_URI : self::ACCOUNT_API_URI;
        $config = new Configuration();
        $config->setHost($host);
        $config->addDefaultHeader('Authorization', 'Bearer '.$grant);
        $config->setAccessToken($grant);
        $apiClient =  new ApiClient($config);
        return new AccountsApi($apiClient);

    }
}
