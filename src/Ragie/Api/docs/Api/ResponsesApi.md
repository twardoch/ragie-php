# OpenAPI\Client\ResponsesApi

All URIs are relative to https://api.ragie.ai, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**createResponseResponsesPost()**](ResponsesApi.md#createResponseResponsesPost) | **POST** /responses | Create Response |
| [**getResponseResponsesResponseIdGet()**](ResponsesApi.md#getResponseResponsesResponseIdGet) | **GET** /responses/{response_id} | Get Response |


## `createResponseResponsesPost()`

```php
createResponseResponsesPost($request): \OpenAPI\Client\Model\Response
```

Create Response

Create a response. This will generate an LLM or agentic response. At this time the only supported model is `deep-search`. Responses may be streamed or returned synchronously. The `retrieve` tool is currently the only supported tool, more tools will be added in the future. A single partition may be provided in the retrieve tool. If omitted the `default` partition is used.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ResponsesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$request = new \OpenAPI\Client\Model\Request(); // \OpenAPI\Client\Model\Request

try {
    $result = $apiInstance->createResponseResponsesPost($request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ResponsesApi->createResponseResponsesPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **request** | [**\OpenAPI\Client\Model\Request**](../Model/Request.md)|  | |

### Return type

[**\OpenAPI\Client\Model\Response**](../Model/Response.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getResponseResponsesResponseIdGet()`

```php
getResponseResponsesResponseIdGet($response_id): \OpenAPI\Client\Model\Response
```

Get Response

Get a response by its ID. This will return the response and its status. If the response is still in progress, the status will be `in_progress`. If the response is completed, the status will be `completed`. If the response is failed, the status will be `failed`.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ResponsesApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$response_id = 'response_id_example'; // string

try {
    $result = $apiInstance->getResponseResponsesResponseIdGet($response_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ResponsesApi->getResponseResponsesResponseIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **response_id** | **string**|  | |

### Return type

[**\OpenAPI\Client\Model\Response**](../Model/Response.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
