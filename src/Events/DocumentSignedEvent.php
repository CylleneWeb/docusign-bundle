<?php

declare(strict_types=1);

namespace DocusignBundle\Events;

use Symfony\Contracts\EventDispatcher\Event;

final class DocumentSignedEvent extends Event
{
    /**
     * @var string
     */
    private $document;

    public function __construct($document)
    {
        $this->document = $document;
    }

    /**
     * @return string the base64 encoded document
     */
    public function getDocument(): string
    {
        return $this->document;
    }
}