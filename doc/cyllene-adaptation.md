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
        $response = $clickwrapRequester->createClickwrap($infos, []);
        $myObject
            ->setClickwrapId($response["clickwrapId"])
            ->setClickwrapVersionId($response["versionId"])
        ;
        
    ```
    
    2. Activate a new clickwrap

    ```php
    // src/Service/AnyService.php
    $clickwrapId = $myObject->getClickwrapId();
    $versionId = $myObject->getClickwrapVersionId();
    $response = $clickwrapRequester->activateClickwrap($clickwrapId, $versionId)
    return $response["status"] === "active";
  
    ```
    3. Updating an existing clickwrap

    ```php
    // src/Service/AnyService.php
    $infos = [
        "name" => $filename,
        "path" => $documentPath
    ];

    $clickwrapId = $myObject->getClickwrapId();
    $response = $clickwrapRequest->updateClickwrap($clickwrapId, $infos, [])
    $myObject
       ->setClickwrapId($response["clickwrapId"])
       ->setClickwrapVersionId($response["versionId"])
    ;
    ```
  
    4. Remove an existing clickwrap
    
    ```php
    // src/Service/AnyService.php
    $clickwrapId = $myObject->getClickwrapId();
    $response = $clickwrapRequest->deleteClickwrap($clickwrapId)
    return $response["status"] === "Deleted"
    
    ```
  
  
