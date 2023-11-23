<?php

declare(strict_types=1);

namespace DocusignBundle\Utils;

use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration;
use DocusignBundle\EnvelopeBuilderInterface;
use DocusignBundle\Grant\GrantInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class InfoEnvelope
{
    public $grant;
    private $envelopeBuilder;
    private $eventDispatcher;

    public function __construct(EnvelopeBuilderInterface $envelopeBuilder, GrantInterface $grant, EventDispatcherInterface $eventDispatcher)
    {
        $this->grant = $grant;
        $this->envelopeBuilder = $envelopeBuilder;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getEnvelope(?string $idEnvelope, string $docusignAccountId)
    {
       $envelopeApi = $this->setUpConfiguration();
       return $envelopeApi->getEnvelope($docusignAccountId, $idEnvelope);
    }

    public function getRecipients(?string $idEnvelope, string $docusignAccountId)
    {
        $envelopeApi = $this->setUpConfiguration();
        return $envelopeApi->listRecipients($docusignAccountId, $idEnvelope);
    }

    public function getCustomFields(?string $idEnvelope, string $docusignAccountId)
    {
        $envelopeApi = $this->setUpConfiguration();
        return $envelopeApi->listCustomFields($docusignAccountId, $idEnvelope);
    }

    private function setUpConfiguration(): EnvelopesApi
    {
        $config = new Configuration();
        $config->setHost($this->envelopeBuilder->getApiUri());
        $config->addDefaultHeader('Authorization', 'Bearer '.($this->grant)());

        return new EnvelopesApi(new ApiClient($config));
    }
}
