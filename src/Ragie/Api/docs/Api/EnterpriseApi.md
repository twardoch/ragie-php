# OpenAPI\Client\EnterpriseApi

All URIs are relative to https://api.ragie.ai, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**createAuthenticator()**](EnterpriseApi.md#createAuthenticator) | **POST** /authenticators | Create Authenticator |
| [**createAuthenticatorConnection()**](EnterpriseApi.md#createAuthenticatorConnection) | **POST** /authenticators/{authenticator_id}/connection | Create Authenticator Connection |
| [**createConnection()**](EnterpriseApi.md#createConnection) | **POST** /connection | Create Connection |
| [**deleteAuthenticatorConnection()**](EnterpriseApi.md#deleteAuthenticatorConnection) | **DELETE** /authenticators/{authenticator_id} | Delete Authenticator |
| [**listAuthenticators()**](EnterpriseApi.md#listAuthenticators) | **GET** /authenticators | List Authenticators |


## `createAuthenticator()`

```php
createAuthenticator($payload): \OpenAPI\Client\Model\BaseGetAuthenticator
```

Create Authenticator

Create White labeled connector credentials

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\EnterpriseApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$payload = new \OpenAPI\Client\Model\Payload(); // \OpenAPI\Client\Model\Payload

try {
    $result = $apiInstance->createAuthenticator($payload);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EnterpriseApi->createAuthenticator: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **payload** | [**\OpenAPI\Client\Model\Payload**](../Model/Payload.md)|  | |

### Return type

[**\OpenAPI\Client\Model\BaseGetAuthenticator**](../Model/BaseGetAuthenticator.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createAuthenticatorConnection()`

```php
createAuthenticatorConnection($authenticator_id, $create_authenticator_connection): \OpenAPI\Client\Model\Connection
```

Create Authenticator Connection

Create a connector for a given authenticator. This requires credentials dependent on the provider. For google drive it is a refresh token.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\EnterpriseApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$authenticator_id = 'authenticator_id_example'; // string
$create_authenticator_connection = new \OpenAPI\Client\Model\CreateAuthenticatorConnection(); // \OpenAPI\Client\Model\CreateAuthenticatorConnection

try {
    $result = $apiInstance->createAuthenticatorConnection($authenticator_id, $create_authenticator_connection);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EnterpriseApi->createAuthenticatorConnection: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **authenticator_id** | **string**|  | |
| **create_authenticator_connection** | [**\OpenAPI\Client\Model\CreateAuthenticatorConnection**](../Model/CreateAuthenticatorConnection.md)|  | |

### Return type

[**\OpenAPI\Client\Model\Connection**](../Model/Connection.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createConnection()`

```php
createConnection($public_create_connection): \OpenAPI\Client\Model\Connection
```

Create Connection

Create a connection. This is only for non-oauth connections such as S3 compatible connections, Freshdesk, and Zendesk.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\EnterpriseApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$public_create_connection = new \OpenAPI\Client\Model\PublicCreateConnection(); // \OpenAPI\Client\Model\PublicCreateConnection

try {
    $result = $apiInstance->createConnection($public_create_connection);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EnterpriseApi->createConnection: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **public_create_connection** | [**\OpenAPI\Client\Model\PublicCreateConnection**](../Model/PublicCreateConnection.md)|  | |

### Return type

[**\OpenAPI\Client\Model\Connection**](../Model/Connection.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteAuthenticatorConnection()`

```php
deleteAuthenticatorConnection($authenticator_id): \OpenAPI\Client\Model\ResponseOK
```

Delete Authenticator

Delete an authenticator. This requires all connections created by that authenticator to be deleted first.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\EnterpriseApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$authenticator_id = 'authenticator_id_example'; // string

try {
    $result = $apiInstance->deleteAuthenticatorConnection($authenticator_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EnterpriseApi->deleteAuthenticatorConnection: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **authenticator_id** | **string**|  | |

### Return type

[**\OpenAPI\Client\Model\ResponseOK**](../Model/ResponseOK.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `listAuthenticators()`

```php
listAuthenticators($cursor, $page_size): \OpenAPI\Client\Model\AuthenticatorList
```

List Authenticators

List all authenticators sorted by created_at in descending order. Results are paginated with a max limit of 100. When more authenticators are available, a `cursor` will be provided. Use the `cursor` parameter to retrieve the subsequent page.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\EnterpriseApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$cursor = 'cursor_example'; // string | An opaque cursor for pagination
$page_size = 10; // int | The number of items per page (must be greater than 0 and less than or equal to 100)

try {
    $result = $apiInstance->listAuthenticators($cursor, $page_size);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling EnterpriseApi->listAuthenticators: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **cursor** | **string**| An opaque cursor for pagination | [optional] |
| **page_size** | **int**| The number of items per page (must be greater than 0 and less than or equal to 100) | [optional] [default to 10] |

### Return type

[**\OpenAPI\Client\Model\AuthenticatorList**](../Model/AuthenticatorList.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
