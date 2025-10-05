# OpenAPI\Client\RetrievalsApi

All URIs are relative to https://api.ragie.ai, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**retrieve()**](RetrievalsApi.md#retrieve) | **POST** /retrievals | Retrieve |


## `retrieve()`

```php
retrieve($retrieve_params): \OpenAPI\Client\Model\Retrieval
```

Retrieve

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\RetrievalsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$retrieve_params = new \OpenAPI\Client\Model\RetrieveParams(); // \OpenAPI\Client\Model\RetrieveParams

try {
    $result = $apiInstance->retrieve($retrieve_params);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling RetrievalsApi->retrieve: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **retrieve_params** | [**\OpenAPI\Client\Model\RetrieveParams**](../Model/RetrieveParams.md)|  | |

### Return type

[**\OpenAPI\Client\Model\Retrieval**](../Model/Retrieval.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
