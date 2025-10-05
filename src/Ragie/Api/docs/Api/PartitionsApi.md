# OpenAPI\Client\PartitionsApi

All URIs are relative to https://api.ragie.ai, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**createPartitionPartitionsPost()**](PartitionsApi.md#createPartitionPartitionsPost) | **POST** /partitions | Create Partition |
| [**deletePartitionPartitionsPartitionIdDelete()**](PartitionsApi.md#deletePartitionPartitionsPartitionIdDelete) | **DELETE** /partitions/{partition_id} | Delete Partition |
| [**disableMcpPartitionsPartitionIdMcpDelete()**](PartitionsApi.md#disableMcpPartitionsPartitionIdMcpDelete) | **DELETE** /partitions/{partition_id}/mcp | Disable Mcp |
| [**enableMcpPartitionsPartitionIdMcpPost()**](PartitionsApi.md#enableMcpPartitionsPartitionIdMcpPost) | **POST** /partitions/{partition_id}/mcp | Enable Mcp |
| [**getPartitionPartitionsPartitionIdGet()**](PartitionsApi.md#getPartitionPartitionsPartitionIdGet) | **GET** /partitions/{partition_id} | Get Partition |
| [**listPartitionsPartitionsGet()**](PartitionsApi.md#listPartitionsPartitionsGet) | **GET** /partitions | List Partitions |
| [**setPartitionLimitsPartitionsPartitionIdLimitsPut()**](PartitionsApi.md#setPartitionLimitsPartitionsPartitionIdLimitsPut) | **PUT** /partitions/{partition_id}/limits | Set Partition Limits |


## `createPartitionPartitionsPost()`

```php
createPartitionPartitionsPost($create_partition_params): \OpenAPI\Client\Model\Partition
```

Create Partition

Create a new partition. Partitions are used to scope documents, connections, and instructions. Partitions must be lowercase alphanumeric and may only include the special characters `_` and `-`. A partition may also be created by creating a document in it. Limits for a partition may optionally be defined when creating.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PartitionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$create_partition_params = new \OpenAPI\Client\Model\CreatePartitionParams(); // \OpenAPI\Client\Model\CreatePartitionParams

try {
    $result = $apiInstance->createPartitionPartitionsPost($create_partition_params);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PartitionsApi->createPartitionPartitionsPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **create_partition_params** | [**\OpenAPI\Client\Model\CreatePartitionParams**](../Model/CreatePartitionParams.md)|  | |

### Return type

[**\OpenAPI\Client\Model\Partition**](../Model/Partition.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deletePartitionPartitionsPartitionIdDelete()`

```php
deletePartitionPartitionsPartitionIdDelete($partition_id): array<string,string>
```

Delete Partition

Deletes a partition and all of its associated data. This includes connections, documents, and partition specific instructions. This operation is irreversible.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PartitionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$partition_id = 'partition_id_example'; // string

try {
    $result = $apiInstance->deletePartitionPartitionsPartitionIdDelete($partition_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PartitionsApi->deletePartitionPartitionsPartitionIdDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **partition_id** | **string**|  | |

### Return type

**array<string,string>**

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `disableMcpPartitionsPartitionIdMcpDelete()`

```php
disableMcpPartitionsPartitionIdMcpDelete($partition_id): mixed
```

Disable Mcp

Disables context-aware descriptions for a partition. This will stop automatically generating descriptions for the partition.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PartitionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$partition_id = 'partition_id_example'; // string

try {
    $result = $apiInstance->disableMcpPartitionsPartitionIdMcpDelete($partition_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PartitionsApi->disableMcpPartitionsPartitionIdMcpDelete: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **partition_id** | **string**|  | |

### Return type

**mixed**

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `enableMcpPartitionsPartitionIdMcpPost()`

```php
enableMcpPartitionsPartitionIdMcpPost($partition_id): mixed
```

Enable Mcp

Enables context-aware descriptions for a partition. This will allow the automatically generate a desccription for based on the documents in the partition.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PartitionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$partition_id = 'partition_id_example'; // string

try {
    $result = $apiInstance->enableMcpPartitionsPartitionIdMcpPost($partition_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PartitionsApi->enableMcpPartitionsPartitionIdMcpPost: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **partition_id** | **string**|  | |

### Return type

**mixed**

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getPartitionPartitionsPartitionIdGet()`

```php
getPartitionPartitionsPartitionIdGet($partition_id): \OpenAPI\Client\Model\PartitionDetail
```

Get Partition

Get a partition by its ID. Includes usage information such as the number of documents and pages hosted and processed. The partition's limits are also included.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PartitionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$partition_id = 'partition_id_example'; // string

try {
    $result = $apiInstance->getPartitionPartitionsPartitionIdGet($partition_id);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PartitionsApi->getPartitionPartitionsPartitionIdGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **partition_id** | **string**|  | |

### Return type

[**\OpenAPI\Client\Model\PartitionDetail**](../Model/PartitionDetail.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `listPartitionsPartitionsGet()`

```php
listPartitionsPartitionsGet($cursor, $page_size): \OpenAPI\Client\Model\PartitionList
```

List Partitions

List all partitions sorted by name in ascending order. Results are paginated with a max limit of 100. When more partitions are available, a `cursor` will be provided. Use the `cursor` parameter to retrieve the subsequent page.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PartitionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$cursor = 'cursor_example'; // string | An opaque cursor for pagination
$page_size = 10; // int | The number of items per page (must be greater than 0 and less than or equal to 100)

try {
    $result = $apiInstance->listPartitionsPartitionsGet($cursor, $page_size);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PartitionsApi->listPartitionsPartitionsGet: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **cursor** | **string**| An opaque cursor for pagination | [optional] |
| **page_size** | **int**| The number of items per page (must be greater than 0 and less than or equal to 100) | [optional] [default to 10] |

### Return type

[**\OpenAPI\Client\Model\PartitionList**](../Model/PartitionList.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `setPartitionLimitsPartitionsPartitionIdLimitsPut()`

```php
setPartitionLimitsPartitionsPartitionIdLimitsPut($partition_id, $partition_limit_params): \OpenAPI\Client\Model\PartitionDetail
```

Set Partition Limits

Sets limits on a partition. Limits can be set on the total number of pages a partition can host and process. When the limit is reached, the partition will be disabled. A limit may be removed by setting it to `null`.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\PartitionsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$partition_id = 'partition_id_example'; // string
$partition_limit_params = new \OpenAPI\Client\Model\PartitionLimitParams(); // \OpenAPI\Client\Model\PartitionLimitParams

try {
    $result = $apiInstance->setPartitionLimitsPartitionsPartitionIdLimitsPut($partition_id, $partition_limit_params);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling PartitionsApi->setPartitionLimitsPartitionsPartitionIdLimitsPut: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **partition_id** | **string**|  | |
| **partition_limit_params** | [**\OpenAPI\Client\Model\PartitionLimitParams**](../Model/PartitionLimitParams.md)|  | |

### Return type

[**\OpenAPI\Client\Model\PartitionDetail**](../Model/PartitionDetail.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
