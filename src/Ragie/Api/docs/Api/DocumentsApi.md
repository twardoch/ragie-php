# OpenAPI\Client\DocumentsApi

All URIs are relative to https://api.ragie.ai, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**createDocument()**](DocumentsApi.md#createDocument) | **POST** /documents | Create Document |
| [**createDocumentFromUrl()**](DocumentsApi.md#createDocumentFromUrl) | **POST** /documents/url | Create Document From Url |
| [**createDocumentRaw()**](DocumentsApi.md#createDocumentRaw) | **POST** /documents/raw | Create Document Raw |
| [**deleteDocument()**](DocumentsApi.md#deleteDocument) | **DELETE** /documents/{document_id} | Delete Document |
| [**getDocument()**](DocumentsApi.md#getDocument) | **GET** /documents/{document_id} | Get Document |
| [**getDocumentChunk()**](DocumentsApi.md#getDocumentChunk) | **GET** /documents/{document_id}/chunks/{chunk_id} | Get Document Chunk |
| [**getDocumentChunkContent()**](DocumentsApi.md#getDocumentChunkContent) | **GET** /documents/{document_id}/chunks/{chunk_id}/content | Get Document Chunk Content |
| [**getDocumentChunks()**](DocumentsApi.md#getDocumentChunks) | **GET** /documents/{document_id}/chunks | Get Document Chunks |
| [**getDocumentContent()**](DocumentsApi.md#getDocumentContent) | **GET** /documents/{document_id}/content | Get Document Content |
| [**getDocumentSource()**](DocumentsApi.md#getDocumentSource) | **GET** /documents/{document_id}/source | Get Document Source |
| [**getDocumentSummary()**](DocumentsApi.md#getDocumentSummary) | **GET** /documents/{document_id}/summary | Get Document Summary |
| [**listDocuments()**](DocumentsApi.md#listDocuments) | **GET** /documents | List Documents |
| [**patchDocumentMetadata()**](DocumentsApi.md#patchDocumentMetadata) | **PATCH** /documents/{document_id}/metadata | Patch Document Metadata |
| [**updateDocumentFile()**](DocumentsApi.md#updateDocumentFile) | **PUT** /documents/{document_id}/file | Update Document File |
| [**updateDocumentFromUrl()**](DocumentsApi.md#updateDocumentFromUrl) | **PUT** /documents/{document_id}/url | Update Document Url |
| [**updateDocumentRaw()**](DocumentsApi.md#updateDocumentRaw) | **PUT** /documents/{document_id}/raw | Update Document Raw |


## `createDocument()`

```php
createDocument($file, $mode, $metadata, $external_id, $name, $partition): \OpenAPI\Client\Model\Document
```

Create Document

On ingest, the document goes through a series of steps before it is ready for retrieval. Each step is reflected in the status of the document which can be one of [`pending`, `partitioning`, `partitioned`, `refined`, `chunked`, `indexed`, `summary_indexed`, `keyword_indexed`, `ready`, `failed`]. The document is available for retrieval once it is in ready state. The summary index step can take a few seconds. You can optionally use the document for retrieval once it is in `indexed` state. However the summary will only be available once the state has changed to `summary_indexed` or `ready`.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$file = '/path/to/file.txt'; // \SplFileObject | The binary file to upload, extract, and index for retrieval. The following file types are supported: Plain Text: `.eml` `.html` `.json` `.md` `.msg` `.rst` `.rtf` `.txt` `.xml` Images: `.png` `.webp` `.jpg` `.jpeg` `.tiff` `.bmp` `.heic` Documents: `.csv` `.doc` `.docx` `.epub` `.epub+zip` `.odt` `.pdf` `.ppt` `.pptx` `.tsv` `.xlsx` `.xls`. PDF files over 2000 pages are not supported in hi_res mode.
$mode = new \OpenAPI\Client\Model\Mode2(); // \OpenAPI\Client\Model\Mode2
$metadata = NULL; // array<string,\OpenAPI\Client\Model\MetadataValue1> | Metadata for the document. Keys must be strings. Values may be strings, numbers, booleans, or lists of strings. Numbers may be integers or floating point and will be converted to 64 bit floating point. 1000 total values are allowed. Each item in an array counts towards the total. The following keys are reserved for internal use: `document_id`, `document_type`, `document_source`, `document_name`, `document_uploaded_at`, `start_time`, `end_time`.
$external_id = 'external_id_example'; // string | An optional identifier for the document. A common value might be an id in an external system or the URL where the source file may be found.
$name = 'name_example'; // string | An optional name for the document. If set, the document will have this name. Otherwise it will default to the file's name.
$partition = 'partition_example'; // string | An optional partition identifier. Documents can be scoped to a partition. Partitions must be lowercase alphanumeric and may only include the special characters `_` and `-`.  A partition is created any time a document is created.

try {
    $result = $apiInstance->createDocument($file, $mode, $metadata, $external_id, $name, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->createDocument: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **file** | **\SplFileObject****\SplFileObject**| The binary file to upload, extract, and index for retrieval. The following file types are supported: Plain Text: &#x60;.eml&#x60; &#x60;.html&#x60; &#x60;.json&#x60; &#x60;.md&#x60; &#x60;.msg&#x60; &#x60;.rst&#x60; &#x60;.rtf&#x60; &#x60;.txt&#x60; &#x60;.xml&#x60; Images: &#x60;.png&#x60; &#x60;.webp&#x60; &#x60;.jpg&#x60; &#x60;.jpeg&#x60; &#x60;.tiff&#x60; &#x60;.bmp&#x60; &#x60;.heic&#x60; Documents: &#x60;.csv&#x60; &#x60;.doc&#x60; &#x60;.docx&#x60; &#x60;.epub&#x60; &#x60;.epub+zip&#x60; &#x60;.odt&#x60; &#x60;.pdf&#x60; &#x60;.ppt&#x60; &#x60;.pptx&#x60; &#x60;.tsv&#x60; &#x60;.xlsx&#x60; &#x60;.xls&#x60;. PDF files over 2000 pages are not supported in hi_res mode. | |
| **mode** | [**\OpenAPI\Client\Model\Mode2**](../Model/Mode2.md)|  | [optional] |
| **metadata** | [**array<string,\OpenAPI\Client\Model\MetadataValue1>**](../Model/array.md)| Metadata for the document. Keys must be strings. Values may be strings, numbers, booleans, or lists of strings. Numbers may be integers or floating point and will be converted to 64 bit floating point. 1000 total values are allowed. Each item in an array counts towards the total. The following keys are reserved for internal use: &#x60;document_id&#x60;, &#x60;document_type&#x60;, &#x60;document_source&#x60;, &#x60;document_name&#x60;, &#x60;document_uploaded_at&#x60;, &#x60;start_time&#x60;, &#x60;end_time&#x60;. | [optional] |
| **external_id** | **string**| An optional identifier for the document. A common value might be an id in an external system or the URL where the source file may be found. | [optional] |
| **name** | **string**| An optional name for the document. If set, the document will have this name. Otherwise it will default to the file&#39;s name. | [optional] |
| **partition** | **string**| An optional partition identifier. Documents can be scoped to a partition. Partitions must be lowercase alphanumeric and may only include the special characters &#x60;_&#x60; and &#x60;-&#x60;.  A partition is created any time a document is created. | [optional] |

### Return type

[**\OpenAPI\Client\Model\Document**](../Model/Document.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `multipart/form-data`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createDocumentFromUrl()`

```php
createDocumentFromUrl($create_document_from_url_params): \OpenAPI\Client\Model\Document
```

Create Document From Url

Ingest a document from a publicly accessible URL. On ingest, the document goes through a series of steps before it is ready for retrieval. Each step is reflected in the status of the document which can be one of [`pending`, `partitioning`, `partitioned`, `refined`, `chunked`, `indexed`, `summary_indexed`, `keyword_indexed`, `ready`, `failed`]. The document is available for retrieval once it is in ready state. The summary index step can take a few seconds. You can optionally use the document for retrieval once it is in `indexed` state. However the summary will only be available once the state has changed to `summary_indexed` or `ready`. PDF files over 2000 pages are not supported in hi_res mode.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$create_document_from_url_params = new \OpenAPI\Client\Model\CreateDocumentFromUrlParams(); // \OpenAPI\Client\Model\CreateDocumentFromUrlParams

try {
    $result = $apiInstance->createDocumentFromUrl($create_document_from_url_params);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->createDocumentFromUrl: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **create_document_from_url_params** | [**\OpenAPI\Client\Model\CreateDocumentFromUrlParams**](../Model/CreateDocumentFromUrlParams.md)|  | |

### Return type

[**\OpenAPI\Client\Model\Document**](../Model/Document.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `createDocumentRaw()`

```php
createDocumentRaw($create_document_raw_params): \OpenAPI\Client\Model\Document
```

Create Document Raw

Ingest a document as raw text. On ingest, the document goes through a series of steps before it is ready for retrieval. Each step is reflected in the status of the document which can be one of [`pending`, `partitioning`, `partitioned`, `refined`, `chunked`, `indexed`, `summary_indexed`, `keyword_indexed`, `ready`, `failed`]. The document is available for retrieval once it is in ready state. The summary index step can take a few seconds. You can optionally use the document for retrieval once it is in `indexed` state. However the summary will only be available once the state has changed to `summary_indexed` or `ready`.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$create_document_raw_params = new \OpenAPI\Client\Model\CreateDocumentRawParams(); // \OpenAPI\Client\Model\CreateDocumentRawParams

try {
    $result = $apiInstance->createDocumentRaw($create_document_raw_params);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->createDocumentRaw: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **create_document_raw_params** | [**\OpenAPI\Client\Model\CreateDocumentRawParams**](../Model/CreateDocumentRawParams.md)|  | |

### Return type

[**\OpenAPI\Client\Model\Document**](../Model/Document.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `deleteDocument()`

```php
deleteDocument($document_id, $async, $partition): \OpenAPI\Client\Model\DocumentDelete
```

Delete Document

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$async = True; // bool | If true, performs document deletion asynchronously
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.

try {
    $result = $apiInstance->deleteDocument($document_id, $async, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->deleteDocument: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **async** | **bool**| If true, performs document deletion asynchronously | [optional] |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |

### Return type

[**\OpenAPI\Client\Model\DocumentDelete**](../Model/DocumentDelete.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getDocument()`

```php
getDocument($document_id, $partition): \OpenAPI\Client\Model\DocumentGet
```

Get Document

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.

try {
    $result = $apiInstance->getDocument($document_id, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->getDocument: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |

### Return type

[**\OpenAPI\Client\Model\DocumentGet**](../Model/DocumentGet.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getDocumentChunk()`

```php
getDocumentChunk($document_id, $chunk_id, $partition): \OpenAPI\Client\Model\DocumentChunkDetail
```

Get Document Chunk

Gets a document chunk by its document and chunk ID.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$chunk_id = 'chunk_id_example'; // string | The ID of the chunk.
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.

try {
    $result = $apiInstance->getDocumentChunk($document_id, $chunk_id, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->getDocumentChunk: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **chunk_id** | **string**| The ID of the chunk. | |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |

### Return type

[**\OpenAPI\Client\Model\DocumentChunkDetail**](../Model/DocumentChunkDetail.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getDocumentChunkContent()`

```php
getDocumentChunkContent($document_id, $chunk_id, $media_type, $download, $partition, $range): mixed
```

Get Document Chunk Content

Returns the content of a document chunk in the requested format. Can be used to stream media of the content for audio/video documents.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$chunk_id = 'chunk_id_example'; // string | The ID of the chunk.
$media_type = 'media_type_example'; // string | The desired media type of the content to return described as a mime type. An error will be returned if the requested media type is not supported for the chunk's document type.
$download = false; // bool | Whether to return the content as a file download or a raw stream. If set to `true`, the content will be returned as a named file for download.
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.
$range = 'range_example'; // string

try {
    $result = $apiInstance->getDocumentChunkContent($document_id, $chunk_id, $media_type, $download, $partition, $range);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->getDocumentChunkContent: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **chunk_id** | **string**| The ID of the chunk. | |
| **media_type** | **string**| The desired media type of the content to return described as a mime type. An error will be returned if the requested media type is not supported for the chunk&#39;s document type. | [optional] |
| **download** | **bool**| Whether to return the content as a file download or a raw stream. If set to &#x60;true&#x60;, the content will be returned as a named file for download. | [optional] [default to false] |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |
| **range** | **string**|  | [optional] |

### Return type

**mixed**

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`, `application/octet-stream`, `audio/mpeg`, `text/plain`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getDocumentChunks()`

```php
getDocumentChunks($document_id, $start_index, $end_index, $cursor, $page_size, $partition): \OpenAPI\Client\Model\DocumentChunkList
```

Get Document Chunks

List all document chunks sorted by index in ascending order. May be limited to a range of chunk indices with the `start_index` and `end_index` parameters. Documents created prior to 9/18/2024, which have not been updated since, have chunks which do not include an index and their index will be returned as -1. They will be sorted by their ID instead. Updating the document using the `Update Document File` or `Update Document Raw` endpoint will regenerate document chunks, including their index. Results are paginated with a max limit of 100. When more chunks are available, a `cursor` will be provided. Use the `cursor` parameter to retrieve the subsequent page.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$start_index = 56; // int | The inclusive starting index of the chunk range to list. If omitted and `end_index` is present effectively limits results to at most one chunk matching `end_index`. If both `start_index` and `end_index` are omitted, results are not limited by index.
$end_index = 56; // int | The inclusive ending index of the chunk range to list. If omitted and `start_index` is present effectively limits results to at most one chunk matching `start_index`. If both `start_index` and `end_index` are omitted, results are not limited by index.
$cursor = 'cursor_example'; // string | An opaque cursor for pagination
$page_size = 10; // int | The number of items per page (must be greater than 0 and less than or equal to 100)
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.

try {
    $result = $apiInstance->getDocumentChunks($document_id, $start_index, $end_index, $cursor, $page_size, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->getDocumentChunks: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **start_index** | **int**| The inclusive starting index of the chunk range to list. If omitted and &#x60;end_index&#x60; is present effectively limits results to at most one chunk matching &#x60;end_index&#x60;. If both &#x60;start_index&#x60; and &#x60;end_index&#x60; are omitted, results are not limited by index. | [optional] |
| **end_index** | **int**| The inclusive ending index of the chunk range to list. If omitted and &#x60;start_index&#x60; is present effectively limits results to at most one chunk matching &#x60;start_index&#x60;. If both &#x60;start_index&#x60; and &#x60;end_index&#x60; are omitted, results are not limited by index. | [optional] |
| **cursor** | **string**| An opaque cursor for pagination | [optional] |
| **page_size** | **int**| The number of items per page (must be greater than 0 and less than or equal to 100) | [optional] [default to 10] |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |

### Return type

[**\OpenAPI\Client\Model\DocumentChunkList**](../Model/DocumentChunkList.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getDocumentContent()`

```php
getDocumentContent($document_id, $media_type, $download, $partition, $range): \OpenAPI\Client\Model\DocumentWithContent
```

Get Document Content

Get the content of a document. The `media_type` parameter can be used to request the content in a different format. When requesting as `application/json` additional metadata about the document will be included. If the original document contained content such as images or other non-textual media, this response will include a text description of that media instead of the original file data. Using mime types such as `audio/mpeg` or `video/mp4` will stream the file in a format that can be provided to an audio video player.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$media_type = new \OpenAPI\Client\Model\\OpenAPI\Client\Model\MediaType(); // \OpenAPI\Client\Model\MediaType | The desired media type of the content to return described as a mime type. An error will be returned if the requested media type is not supported for the document's type.
$download = false; // bool | Whether to return the content as a file download or a raw stream. If set to `true`, the content will be returned as a named file for download.
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.
$range = 'range_example'; // string

try {
    $result = $apiInstance->getDocumentContent($document_id, $media_type, $download, $partition, $range);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->getDocumentContent: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **media_type** | [**\OpenAPI\Client\Model\MediaType**](../Model/.md)| The desired media type of the content to return described as a mime type. An error will be returned if the requested media type is not supported for the document&#39;s type. | [optional] |
| **download** | **bool**| Whether to return the content as a file download or a raw stream. If set to &#x60;true&#x60;, the content will be returned as a named file for download. | [optional] [default to false] |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |
| **range** | **string**|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\DocumentWithContent**](../Model/DocumentWithContent.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getDocumentSource()`

```php
getDocumentSource($document_id, $partition): \SplFileObject
```

Get Document Source

Get the source file of a document. The source file is the original file that was uploaded to create the document. If the document was created from a URL, the source file will be the content of the URL. If the document was created by a connection, the source file will vary based on the type of the connection. For example, a Google Drive connection will return the file that was synced from the Google Drive, while a SalesForce connection would return a JSON file of the data synced from SalesForce.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.

try {
    $result = $apiInstance->getDocumentSource($document_id, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->getDocumentSource: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |

### Return type

**\SplFileObject**

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/octet-stream`, `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `getDocumentSummary()`

```php
getDocumentSummary($document_id, $partition): \OpenAPI\Client\Model\DocumentSummary
```

Get Document Summary

Get a LLM generated summary of the document. The summary is created when the document is first created or updated. Documents of types ['xls', 'xlsx', 'csv', 'json'] are not supported for summarization. Documents greater than 1M in token length are not supported. This feature is in beta and may change in the future.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.

try {
    $result = $apiInstance->getDocumentSummary($document_id, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->getDocumentSummary: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |

### Return type

[**\OpenAPI\Client\Model\DocumentSummary**](../Model/DocumentSummary.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `listDocuments()`

```php
listDocuments($cursor, $page_size, $filter, $partition): \OpenAPI\Client\Model\DocumentList
```

List Documents

List all documents sorted by created_at in descending order. Results are paginated with a max limit of 100. When more documents are available, a `cursor` will be provided. Use the `cursor` parameter to retrieve the subsequent page.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$cursor = 'cursor_example'; // string | An opaque cursor for pagination
$page_size = 10; // int | The number of items per page (must be greater than 0 and less than or equal to 100)
$filter = 'filter_example'; // string | The metadata search filter. Returns only items which match the filter. The following filter operators are supported: $eq - Equal to (number, string, boolean), $ne - Not equal to (number, string, boolean), $gt - Greater than (number), $gte - Greater than or equal to (number), $lt - Less than (number), $lte - Less than or equal to (number), $in - In array (string or number), $nin - Not in array (string or number). The operators can be combined with AND and OR. Read [Metadata & Filters guide](https://docs.ragie.ai/docs/metadata-filters) for more details and examples.
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.

try {
    $result = $apiInstance->listDocuments($cursor, $page_size, $filter, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->listDocuments: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **cursor** | **string**| An opaque cursor for pagination | [optional] |
| **page_size** | **int**| The number of items per page (must be greater than 0 and less than or equal to 100) | [optional] [default to 10] |
| **filter** | **string**| The metadata search filter. Returns only items which match the filter. The following filter operators are supported: $eq - Equal to (number, string, boolean), $ne - Not equal to (number, string, boolean), $gt - Greater than (number), $gte - Greater than or equal to (number), $lt - Less than (number), $lte - Less than or equal to (number), $in - In array (string or number), $nin - Not in array (string or number). The operators can be combined with AND and OR. Read [Metadata &amp; Filters guide](https://docs.ragie.ai/docs/metadata-filters) for more details and examples. | [optional] |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |

### Return type

[**\OpenAPI\Client\Model\DocumentList**](../Model/DocumentList.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `patchDocumentMetadata()`

```php
patchDocumentMetadata($document_id, $patch_document_metadata_params, $partition): \OpenAPI\Client\Model\ResponsePatchdocumentmetadata
```

Patch Document Metadata

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$patch_document_metadata_params = new \OpenAPI\Client\Model\PatchDocumentMetadataParams(); // \OpenAPI\Client\Model\PatchDocumentMetadataParams
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.

try {
    $result = $apiInstance->patchDocumentMetadata($document_id, $patch_document_metadata_params, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->patchDocumentMetadata: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **patch_document_metadata_params** | [**\OpenAPI\Client\Model\PatchDocumentMetadataParams**](../Model/PatchDocumentMetadataParams.md)|  | |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |

### Return type

[**\OpenAPI\Client\Model\ResponsePatchdocumentmetadata**](../Model/ResponsePatchdocumentmetadata.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateDocumentFile()`

```php
updateDocumentFile($document_id, $file, $partition, $mode): \OpenAPI\Client\Model\DocumentFileUpdate
```

Update Document File

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$file = '/path/to/file.txt'; // \SplFileObject | The binary file to upload, extract, and index for retrieval. The following file types are supported: Plain Text: `.eml` `.html` `.json` `.md` `.msg` `.rst` `.rtf` `.txt` `.xml` Images: `.png` `.webp` `.jpg` `.jpeg` `.tiff` `.bmp` `.heic` Documents: `.csv` `.doc` `.docx` `.epub` `.epub+zip` `.odt` `.pdf` `.ppt` `.pptx` `.tsv` `.xlsx` `.xls`. PDF files over 2000 pages are not supported in hi_res mode.
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.
$mode = new \OpenAPI\Client\Model\Mode2(); // \OpenAPI\Client\Model\Mode2

try {
    $result = $apiInstance->updateDocumentFile($document_id, $file, $partition, $mode);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->updateDocumentFile: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **file** | **\SplFileObject****\SplFileObject**| The binary file to upload, extract, and index for retrieval. The following file types are supported: Plain Text: &#x60;.eml&#x60; &#x60;.html&#x60; &#x60;.json&#x60; &#x60;.md&#x60; &#x60;.msg&#x60; &#x60;.rst&#x60; &#x60;.rtf&#x60; &#x60;.txt&#x60; &#x60;.xml&#x60; Images: &#x60;.png&#x60; &#x60;.webp&#x60; &#x60;.jpg&#x60; &#x60;.jpeg&#x60; &#x60;.tiff&#x60; &#x60;.bmp&#x60; &#x60;.heic&#x60; Documents: &#x60;.csv&#x60; &#x60;.doc&#x60; &#x60;.docx&#x60; &#x60;.epub&#x60; &#x60;.epub+zip&#x60; &#x60;.odt&#x60; &#x60;.pdf&#x60; &#x60;.ppt&#x60; &#x60;.pptx&#x60; &#x60;.tsv&#x60; &#x60;.xlsx&#x60; &#x60;.xls&#x60;. PDF files over 2000 pages are not supported in hi_res mode. | |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |
| **mode** | [**\OpenAPI\Client\Model\Mode2**](../Model/Mode2.md)|  | [optional] |

### Return type

[**\OpenAPI\Client\Model\DocumentFileUpdate**](../Model/DocumentFileUpdate.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `multipart/form-data`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateDocumentFromUrl()`

```php
updateDocumentFromUrl($document_id, $update_document_from_url_params, $partition): \OpenAPI\Client\Model\DocumentUrlUpdate
```

Update Document Url

Updates a document from a publicly accessible URL. On ingest, the document goes through a series of steps before it is ready for retrieval. Each step is reflected in the status of the document which can be one of [`pending`, `partitioning`, `partitioned`, `refined`, `chunked`, `indexed`, `summary_indexed`, `keyword_indexed`, `ready`, `failed`]. The document is available for retrieval once it is in ready state. The summary index step can take a few seconds. You can optionally use the document for retrieval once it is in `indexed` state. However the summary will only be available once the state has changed to `summary_indexed` or `ready`. PDF files over 2000 pages are not supported in hi_res mode.

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$update_document_from_url_params = new \OpenAPI\Client\Model\UpdateDocumentFromUrlParams(); // \OpenAPI\Client\Model\UpdateDocumentFromUrlParams
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.

try {
    $result = $apiInstance->updateDocumentFromUrl($document_id, $update_document_from_url_params, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->updateDocumentFromUrl: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **update_document_from_url_params** | [**\OpenAPI\Client\Model\UpdateDocumentFromUrlParams**](../Model/UpdateDocumentFromUrlParams.md)|  | |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |

### Return type

[**\OpenAPI\Client\Model\DocumentUrlUpdate**](../Model/DocumentUrlUpdate.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `updateDocumentRaw()`

```php
updateDocumentRaw($document_id, $update_document_raw_params, $partition): \OpenAPI\Client\Model\DocumentRawUpdate
```

Update Document Raw

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');


// Configure Bearer authorization: auth
$config = OpenAPI\Client\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new OpenAPI\Client\Api\DocumentsApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$document_id = 'document_id_example'; // string | The id of the document.
$update_document_raw_params = new \OpenAPI\Client\Model\UpdateDocumentRawParams(); // \OpenAPI\Client\Model\UpdateDocumentRawParams
$partition = 'partition_example'; // string | An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition.

try {
    $result = $apiInstance->updateDocumentRaw($document_id, $update_document_raw_params, $partition);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DocumentsApi->updateDocumentRaw: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **document_id** | **string**| The id of the document. | |
| **update_document_raw_params** | [**\OpenAPI\Client\Model\UpdateDocumentRawParams**](../Model/UpdateDocumentRawParams.md)|  | |
| **partition** | **string**| An optional partition to scope the request to. If omitted, accounts created after 1/9/2025 will have the request scoped to the default partition, while older accounts will have the request scoped to all partitions. Older accounts may opt in to strict partition scoping by contacting support@ragie.ai. Older accounts using the partitions feature are strongly recommended to scope the request to a partition. | [optional] |

### Return type

[**\OpenAPI\Client\Model\DocumentRawUpdate**](../Model/DocumentRawUpdate.md)

### Authorization

[auth](../../README.md#auth)

### HTTP request headers

- **Content-Type**: `application/json`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
