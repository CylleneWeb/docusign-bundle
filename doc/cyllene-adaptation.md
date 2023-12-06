# DocuSign Cyllene adaptation

## eSignature
- Possibility to place the signature based on a text anchor format.
```php
// src/EventSubscriber/PreSignSubscriber.php
$envelopeBuilder->addAnchorSignatureZone(
    string $anchorString, 
    array $offsets = ["x_offset" => 1, "y_offset" => 3], 
    ?bool $isNotMandatory = false, 
    ?int $recipientId = null
)
```

## click
- The bundle also allows for creating, modifying, and deleting clickwrap signatures.
```yaml
# config/packages/docusign.yml
demo: "%kernel.debug%"
mode: clickwrap
auth_jwt:
    private_key: "Your private key"
    integration_key: "Your integration key%"
    user_guid: "Your user id%"
auth_clickwrap:
    api_account_id: "Your api account id %"
    clickwrap_id: "YourClickwrapId" // facultative
    user_guid: "Your user id"
```

- The different methods to implement are as follows:

    1. Creating a new clickwrap

    ```php
    // src/Service/AnyService.php
        $infos = [
            "name" => $filename,
            "path" => $documentPath
        ];
        $params = [
            // display_settings
            'consent_button_text', // optional
            'display_name', // optional
            'downloadable', // optional
            'format', // optional
            'has_decline_button', // optional
            'must_read', // optional
            'require_accept', // optional
            'document_display', // optional
            // document
            'document_name', // optional
            'order' // optional
        ];
        $response = $clickwrapRequester->createClickwrap($infos, $params);
        $myObject
            ->setClickwrapId($response["clickwrapId"])
            ->setClickwrapVersionId($response["versionId"])
        ;
        
    ```
  
    2. Updating an existing clickwrap
    
        ```php
        // src/Service/AnyService.php
        $infos = [
            "name" => $filename,
            "path" => $documentPath
        ];
    
        $params = [
                // display_settings
                'consent_button_text', // optional
                'display_name', // optional
                'downloadable', // optional
                'format', // optional
                'has_decline_button', // optional
                'must_read', // optional
                'require_accept', // optional
                'document_display', // optional
                // document
                'document_name', // optional
                'order' // optional
            ];
    
        $clickwrapId = $myObject->getClickwrapId();
        $response = $clickwrapRequest->updateClickwrap($clickwrapId, $infos, ["display_name" => "Document name"])
        $myObject
           ->setClickwrapId($response["clickwrapId"])
           ->setClickwrapVersionId($response["versionId"])
        ;
        ```

    3. Remove an existing clickwrap

    ```php
    // src/Service/AnyService.php
    $clickwrapId = $myObject->getClickwrapId();
    $response = $clickwrapRequest->deleteClickwrap($clickwrapId)
    return $response["status"] === "Deleted"
    
    ```
  
    4. Sign an existing clickwrap for user
    
    ```php
    // src/Service/AnyService.php
    $params = [
        "fullname" => "Pierre Kiroul", // mandatory
        "email" => "Pierre-kiroul@example.com", // mandatory
        "clickwrapId" => "Here a clickwrap id", // mandatory
        'company' => "Your company", // optional
        'title' => "Your title", // optional
    ];
     $response = $clickwrapRequester->signClickwrap($params);
     if(null !== $response){
        //If the response is not null, everything went well, and you will find a URL in the response. 
        //You will need to redirect to this URL to continue the signing process on DocuSign.
     }
    ```
  
  
