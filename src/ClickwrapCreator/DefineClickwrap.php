<?php

namespace DocusignBundle\ClickwrapCreator;

use DocuSign\eSign\Model\DocumentHtmlDisplaySettings;

class DefineClickwrap
{
    public function __invoke(?array $parameters): DocumentHtmlDisplaySettings
    {
        $displaySettings = new DocumentHtmlDisplaySettings(
            [
                'consent_button_text' => isset($parameters['consent_button_text']) ?? 'I aggree',
                'display_name' => isset($parameters['display_name']) ?? 'Terms of Service',
                'downloadable' => isset($parameters['downloadable']) && $parameters['downloadable'] === false ?? true,
                'format' => isset($parameters['format']) ?? 'modal',
                'has_decline_button' => isset($parameters['has_decline_button']) && $parameters['has_decline_button'] === false ?? true,
                'must_read' => isset($parameters['has_decline_button']) && $parameters['has_decline_button'] === false ?? true,
                'require_accept' => isset($parameters['require_accept']) && $parameters['require_accept'] === false ?? true,
                'document_display' => isset($parameters['document_display']) ?? 'document'
            ]
        );
        return $displaySettings;
    }
}
