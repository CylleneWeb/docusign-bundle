<?php

namespace DocusignBundle\ClickwrapCreator;

use DocuSign\eSign\Model\Document;

class CreateDocument
{
    private ?Document $document = null;

    public function __construct(string $pathDocument, ?array $parameter)
    {
        $content_bytes = file_get_contents($pathDocument);
        $base64_file_content = base64_encode($content_bytes);
        $pathDocument = explode('.',$pathDocument);

        $document = new Document([
            'document_base64' => $base64_file_content,
            'name' => $parameter['document_name'] ?? basename($pathDocument[0]),
            'file_extension' => end($pathDocument),
            'order' => $parameter['order'] ?? '1'
        ]);
        $this->document = $document;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

}
