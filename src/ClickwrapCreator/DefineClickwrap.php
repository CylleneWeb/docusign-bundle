<?php

namespace DocusignBundle\ClickwrapCreator;

use DocuSign\Click\Model\DisplaySettings;

class DefineClickwrap
{
    private DisplaySettings $displaySettings;

    public function __construct(?array $parameters)
    {
        $displaySettings = new DisplaySettings(
            [
                'consent_button_text' => $parameters['consent_button_text'] ?? 'I aggree',
                'display_name' => $parameters['display_name'] ?? 'Terms of Service',
                'downloadable' => $parameters['downloadable'] ?? true,
                'format' => $parameters['format'] ?? 'modal',
                'has_decline_button' => $parameters['has_decline_button'] ?? true,
                'must_read' => $parameters['has_decline_button'] ?? true,
                'require_accept' => $parameters['require_accept'] ?? true,
                'document_display' => $parameters['document_display'] ?? 'document'
            ]
        );

        $this->displaySettings = $displaySettings;
    }

   public function getDisplaySettings(): DisplaySettings
   {
       return $this->displaySettings;
   }
}
