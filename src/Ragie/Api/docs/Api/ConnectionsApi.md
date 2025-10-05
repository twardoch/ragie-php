# OpenAPI\Client\ConnectionsApi

All URIs are relative to https://api.ragie.ai, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**createConnection()**](ConnectionsApi.md#createConnection) | **POST** /connection | Create Connection |
| [**createOauthRedirectUrlConnectionsOauthPost()**](ConnectionsApi.md#createOauthRedirectUrlConnectionsOauthPost) | **POST** /connections/oauth | Create Oauth Redirect Url |
| [**deleteConnectionConnectionsConnectionIdDeletePost()**](ConnectionsApi.md#deleteConnectionConnectionsConnectionIdDeletePost) | **POST** /connections/{connection_id}/delete | Delete Connection |
| [**getConnectionConnectionsConnectionIdGet()**](ConnectionsApi.md#getConnectionConnectionsConnectionIdGet) | **GET** /connections/{connection_id} | Get Connection |
| [**getConnectionStatsConnectionsConnectionIdStatsGet()**](ConnectionsApi.md#getConnectionStatsConnectionsConnectionIdStatsGet) | **GET** /connections/{connection_id}/stats | Get Connection Stats |
| [**listConnectionSourceTypesConnectionsSourceTypeGet()**](ConnectionsApi.md#listConnectionSourceTypesConnectionsSourceTypeGet) | **GET** /connections/source-type | List Connection Source Types |
| [**listConnectionsConnectionsGet()**](ConnectionsApi.md#listConnectionsConnectionsGet) | **GET** /connections | List Connections |
| [**setConnectionEnabledConnectionsConnectionIdEnabledPut()**](ConnectionsApi.md#setConnectionEnabledConnectionsConnectionIdEnabledPut) | **PUT** /connections/{connection_id}/enabled | Set Connection Enabled |
| [**setConnectionLimitsConnectionsConnectionIdLimitPut()**](ConnectionsApi.md#setConnectionLimitsConnectionsConnectionIdLimitPut) | **PUT** /connections/{connection_id}/limit | Set Connection Limits |
| [**syncConnection()**](ConnectionsApi.md#syncConnection) | **POST** /connections/{connection_id}/sync | Sync Connection |
| [**updateConnectionConnectionsConnectionIdPut()**](ConnectionsApi.md#updateConnectionConnectionsConnectionIdPut) | **PUT** /connections/{connection_id} | Update Connection |


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


$apiInstance = new OpenAPI\Client\Api\ConnectionsApi(
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
    echo 'Exception when calling ConnectionsApi->createConnection: ', $e->getMessage(), PHP_EOL;
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

## `createOauthRedirectUrlConnectionsOauthPost()`

```php
createOauthRedirectUrlConnectionsOauthPost($o_auth_url_create): \OpenAPI\Client\Model\OAuthUrlResponse
```

Create Oauth Redirect Url

Creates a redirect url to redirect the user to when initializing an embedded connector.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ConnectionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$o_auth_url_create = new \OpenAPI\Client\Model\OAuthUrlCreate(); // \OpenAPI\Client\Model\OAuthUrlCreate

try {
    $result = $apiInstance->createOauthRedirectUrlConnectionsOauthPost($o_auth_url_create);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectionsApi->createOauthRedirectUrlConnectionsOauthPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **o_auth_url_create** | [**\OpenAPI\Client\Model\OAuthUrlCreate**](../Model/OAuthUrlCreate.md)|  | |

### Return type

[**\OpenAPI\Client\Model\OAuthUrlResponse**](../Model/OAuthUrlResponse.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteConnectionConnectionsConnectionIdDeletePost()`

```php
deleteConnectionConnectionsConnectionIdDeletePost($connection_id, $delete_connection_payload): array<string,string>
```

Delete Connection

Schedules a connection to be deleted. You can choose to keep the files from the connection or delete them all. If you keep the files, they will no longer be associated to the connection. Deleting can take some time, so you will still see files for a bit after this is called.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ConnectionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$connection_id = 'connection_id_example'; // string
$delete_connection_payload = new \OpenAPI\Client\Model\DeleteConnectionPayload(); // \OpenAPI\Client\Model\DeleteConnectionPayload

try {
    $result = $apiInstance->deleteConnectionConnectionsConnectionIdDeletePost($connection_id, $delete_connection_payload);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectionsApi->deleteConnectionConnectionsConnectionIdDeletePost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **connection_id** | **string**|  | |
| **delete_connection_payload** | [**\OpenAPI\Client\Model\DeleteConnectionPayload**](../Model/DeleteConnectionPayload.md)|  | |

### Return type

**array<string,string>**

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getConnectionConnectionsConnectionIdGet()`

```php
getConnectionConnectionsConnectionIdGet($connection_id): \OpenAPI\Client\Model\Connection
```

Get Connection

Get a connection.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ConnectionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$connection_id = 'connection_id_example'; // string

try {
    $result = $apiInstance->getConnectionConnectionsConnectionIdGet($connection_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectionsApi->getConnectionConnectionsConnectionIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **connection_id** | **string**|  | |

### Return type

[**\OpenAPI\Client\Model\Connection**](../Model/Connection.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getConnectionStatsConnectionsConnectionIdStatsGet()`

```php
getConnectionStatsConnectionsConnectionIdStatsGet($connection_id): \OpenAPI\Client\Model\ConnectionStats
```

Get Connection Stats

Lists connection stats: total documents active documents, total active pages.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ConnectionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$connection_id = 'connection_id_example'; // string

try {
    $result = $apiInstance->getConnectionStatsConnectionsConnectionIdStatsGet($connection_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectionsApi->getConnectionStatsConnectionsConnectionIdStatsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **connection_id** | **string**|  | |

### Return type

[**\OpenAPI\Client\Model\ConnectionStats**](../Model/ConnectionStats.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `listConnectionSourceTypesConnectionsSourceTypeGet()`

```php
listConnectionSourceTypesConnectionsSourceTypeGet(): \OpenAPI\Client\Model\ListConnectorSourceTypeInfo
```

List Connection Source Types

List available connection source types like 'google_drive' and 'notion' along with their metadata

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ConnectionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->listConnectionSourceTypesConnectionsSourceTypeGet();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectionsApi->listConnectionSourceTypesConnectionsSourceTypeGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\OpenAPI\Client\Model\ListConnectorSourceTypeInfo**](../Model/ListConnectorSourceTypeInfo.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `listConnectionsConnectionsGet()`

```php
listConnectionsConnectionsGet($cursor, $page_size, $filter, $partition): \OpenAPI\Client\Model\ConnectionList
```

List Connections

List all connections sorted by created_at in descending order. Results are paginated with a max limit of 100. When more documents are available, a `cursor` will be provided. Use the `cursor` parameter to retrieve the subsequent page.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ConnectionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$cursor = 'cursor_example'; // string | An opaque cursor for pagination
$page_size = 10; // int | The number of items per page (must be greater than 0 and less than or equal to 100)
$filter = 'filter_example'; // string | The metadata search filter. Returns only items which match the filter. The following filter operators are supported: $eq - Equal to (number, string, boolean), $ne - Not equal to (number, string, boolean), $gt - Greater than (number), $gte - Greater than or equal to (number), $lt - Less than (number), $lte - Less than or equal to (number), $in - In array (string or number), $nin - Not in array (string or number). The operators can be combined with AND and OR. Read [Metadata & Filters guide](https://docs.ragie.ai/docs/metadata-filters) for more details and examples.
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, the request will be scoped to the default partition.

try {
    $result = $apiInstance->listConnectionsConnectionsGet($cursor, $page_size, $filter, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectionsApi->listConnectionsConnectionsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **cursor** | **string**| An opaque cursor for pagination | [optional] |
| **page_size** | **int**| The number of items per page (must be greater than 0 and less than or equal to 100) | [optional] [default to 10] |
| **filter** | **string**| The metadata search filter. Returns only items which match the filter. The following filter operators are supported: $eq - Equal to (number, string, boolean), $ne - Not equal to (number, string, boolean), $gt - Greater than (number), $gte - Greater than or equal to (number), $lt - Less than (number), $lte - Less than or equal to (number), $in - In array (string or number), $nin - Not in array (string or number). The operators can be combined with AND and OR. Read [Metadata &amp; Filters guide](https://docs.ragie.ai/docs/metadata-filters) for more details and examples. | [optional] |
| **partition** | **string**| An optional partition to scope the request to. If omitted, the request will be scoped to the default partition. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ConnectionList**](../Model/ConnectionList.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `setConnectionEnabledConnectionsConnectionIdEnabledPut()`

```php
setConnectionEnabledConnectionsConnectionIdEnabledPut($connection_id, $set_connection_enabled_payload): \OpenAPI\Client\Model\Connection
```

Set Connection Enabled

Enable or disable the connection. A disabled connection won't sync.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ConnectionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$connection_id = 'connection_id_example'; // string
$set_connection_enabled_payload = new \OpenAPI\Client\Model\SetConnectionEnabledPayload(); // \OpenAPI\Client\Model\SetConnectionEnabledPayload

try {
    $result = $apiInstance->setConnectionEnabledConnectionsConnectionIdEnabledPut($connection_id, $set_connection_enabled_payload);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectionsApi->setConnectionEnabledConnectionsConnectionIdEnabledPut: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **connection_id** | **string**|  | |
| **set_connection_enabled_payload** | [**\OpenAPI\Client\Model\SetConnectionEnabledPayload**](../Model/SetConnectionEnabledPayload.md)|  | |

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

## `setConnectionLimitsConnectionsConnectionIdLimitPut()`

```php
setConnectionLimitsConnectionsConnectionIdLimitPut($connection_id, $connection_limit_params): \OpenAPI\Client\Model\Connection
```

Set Connection Limits

Sets limits on a connection. Limits can be set on the total number of pages a connection can sync. When the limit is reached, the connection will be disabled. Limit may be removed by setting it to `null`.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ConnectionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$connection_id = 'connection_id_example'; // string
$connection_limit_params = new \OpenAPI\Client\Model\ConnectionLimitParams(); // \OpenAPI\Client\Model\ConnectionLimitParams

try {
    $result = $apiInstance->setConnectionLimitsConnectionsConnectionIdLimitPut($connection_id, $connection_limit_params);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectionsApi->setConnectionLimitsConnectionsConnectionIdLimitPut: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **connection_id** | **string**|  | |
| **connection_limit_params** | [**\OpenAPI\Client\Model\ConnectionLimitParams**](../Model/ConnectionLimitParams.md)|  | |

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

## `syncConnection()`

```php
syncConnection($connection_id): \OpenAPI\Client\Model\ResponseOK
```

Sync Connection

Schedules a connector to sync as soon as possible.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ConnectionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$connection_id = 'connection_id_example'; // string

try {
    $result = $apiInstance->syncConnection($connection_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectionsApi->syncConnection: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **connection_id** | **string**|  | |

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

## `updateConnectionConnectionsConnectionIdPut()`

```php
updateConnectionConnectionsConnectionIdPut($connection_id, $connection_base): \OpenAPI\Client\Model\Connection
```

Update Connection

Updates a connections metadata or mode. These changes will be seen after the next sync.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\ConnectionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$connection_id = 'connection_id_example'; // string
$connection_base = new \OpenAPI\Client\Model\ConnectionBase(); // \OpenAPI\Client\Model\ConnectionBase

try {
    $result = $apiInstance->updateConnectionConnectionsConnectionIdPut($connection_id, $connection_base);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling ConnectionsApi->updateConnectionConnectionsConnectionIdPut: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **connection_id** | **string**|  | |
| **connection_base** | [**\OpenAPI\Client\Model\ConnectionBase**](../Model/ConnectionBase.md)|  | |

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
