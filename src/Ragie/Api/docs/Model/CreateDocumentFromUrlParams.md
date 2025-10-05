# # CreateDocumentFromUrlParams

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**name** | **string** |  | [optional]
**metadata** | [**array<string,\OpenAPI\Client\Model\MetadataValue>**](MetadataValue.md) | Metadata for the document. Keys must be strings. Values may be strings, numbers, booleans, or lists of strings. Numbers may be integers or floating point and will be converted to 64 bit floating point. 1000 total values are allowed. Each item in an array counts towards the total. The following keys are reserved for internal use: &#x60;document_id&#x60;, &#x60;document_type&#x60;, &#x60;document_source&#x60;, &#x60;document_name&#x60;, &#x60;document_uploaded_at&#x60;, &#x60;start_time&#x60;, &#x60;end_time&#x60;. | [optional]
**mode** | [**\OpenAPI\Client\Model\Mode**](Mode.md) |  | [optional]
**external_id** | **string** |  | [optional]
**partition** | **string** | An optional partition identifier. Documents can be scoped to a partition. Partitions must be lowercase alphanumeric and may only include the special characters &#x60;_&#x60; and &#x60;-&#x60;.  A partition is created any time a document is created. | [optional]
**url** | **string** | Url of the file to download. Must be publicly accessible and HTTP or HTTPS scheme. |

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
