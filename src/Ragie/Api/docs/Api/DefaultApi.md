# OpenAPI\Client\DefaultApi

All URIs are relative to https://api.ragie.ai, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**eventeventPost()**](DefaultApi.md#eventeventPost) | **POST** /event | Event |


## `eventeventPost()`

```php
eventeventPost($body): mixed
```

Event

When events occur in Ragie such as a document being processed, we'll send this data to URLs that you can register in app. Learn more about webhooks in our docs: https://docs.ragie.ai/docs/webhooks.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$body = new \OpenAPI\Client\Model\Body(); // \OpenAPI\Client\Model\Body

try {
    $result = $apiInstance->eventeventPost($body);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->eventeventPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **body** | [**\OpenAPI\Client\Model\Body**](../Model/Body.md)|  | |

### Return type

**mixed**

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
