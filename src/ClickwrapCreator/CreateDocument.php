<?php

namespace DocusignBundle\ClickwrapCreator;

use DocuSign\eSign\Model\Document;

class CreateDocument
{
    public function invoke(string $pathDocument, ?array $parameter)
    {
        $content_bytes = file_get_contents($pathDocument);
        $base64_file_content = base64_encode($content_bytes);

        $document = new Document([
            'document_base64' => $base64_file_content,
            'document_name' => isset($parameter['document_name']) ?? 'Lorem Ipsum',
            'file_extension' => end(strpos($pathDocument, '.')),
            'order' => isset($parameter['order']) ?? '1'
        ]);

        return $document;
    }
}
