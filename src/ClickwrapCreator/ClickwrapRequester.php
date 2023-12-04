<?php

namespace DocusignBundle\ClickwrapCreator;

use DocuSign\Click\Api\AccountsApi;
use DocuSign\Click\Client\ApiClient;
use DocuSign\Click\Model\ClickwrapRequest;
use DocuSign\Click\Configuration;
use DocusignBundle\DependencyInjection\DocusignExtension;
use DocusignBundle\EnvelopeBuilderInterface;
use DocusignBundle\Grant\GrantInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\VarDumper\Command\Descriptor\CliDescriptor;

class ClickwrapRequester implements ClickwrapRequesterInterface
{

    public const DEMO_ACCOUNT_API_URI = 'https://demo.docusign.net/clickapi';
    public const ACCOUNT_API_URI = 'https://docusign.net/clickapi';

    public function __construct(private readonly string $apiAccountId, private readonly bool $demo, private readonly GrantInterface $grant){}


    public function createClickwrap(array $document_info, array $parameters)
    {
        $clickwrap = new ClickwrapRequest([
            'clickwrap_name' => $document_info['name'],
            'display_settings' => $this->handleClickwrapSetting($parameters),
            'documents' => $this->handleCreateDocument($document_info['path'],$parameters),
            'require_reacceptance' => true
        ]);

        // envoyer le clickwrap et retourner la reponse
        return $this->getAccountApi()->createClickwrap($this->apiAccountId, $clickwrap);

    }

    public function activateClickwrap($clickwrapId, string $versionId)
    {
        $clickwrap_request = new ClickwrapRequest(['status' => 'active']);
        return $this->getAccountApi()->updateClickwrapVersion(
            $this->apiAccountId,
            $clickwrapId,
            $versionId
        );
    }

    public function updateClickwrap(string $clickwrapId, array $document_info, array $parameters)
    {
        $clickwrap = new ClickwrapRequest([
            'clickwrap_name' => $document_info['name'],
            'display_settings' => $this->handleClickwrapSetting($parameters),
            'documents' => $this->handleCreateDocument($document_info['path'],$parameters),
            'require_reacceptance' => true
        ]);

        // envoyer le clickwrap et retourner la reponse
        return $this->getAccountApi()->createClickwrapVersion($this->apiAccountId,$clickwrapId,$clickwrap);
    }

    public function deleteClickwrap(string $clickwrapId)
    {
        return $this->getAccountApi()->deleteClickwrap($this->apiAccountId, $clickwrapId);
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
        $requires = ['document_base64','name','file_extension','order'];
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
        $host = $this->demo ? self::DEMO_ACCOUNT_API_URI : self::ACCOUNT_API_URI;
        $config = new Configuration();
        $config->setHost($host);
        $config->addDefaultHeader('Authorization', 'Bearer '.($this->grant)());
        $apiClient =  new ApiClient($config);
        return new AccountsApi($apiClient);

    }
}
