# # PatchDocumentMetadataParams

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**metadata** | **array<string,mixed>** | The metadata to update on the document. Performs a partial update of the document&#39;s metadata. Keys must be strings. Values may be strings, numbers, booleans, or lists of strings. Numbers may be integers or floating point and will be converted to 64 bit floating point. Keys set to &#x60;null&#x60; are deleted. 1000 total values are allowed, inclusive of existing metadata. Each item in an array counts towards the total. The following keys are reserved for internal use: &#x60;document_id&#x60;, &#x60;document_type&#x60;, &#x60;document_source&#x60;, &#x60;document_name&#x60;, &#x60;document_uploaded_at&#x60;. If the document is managed by a connection, this operation will extend a metadata overlay which is applied to the document any time the connection syncs the document. |
**async** | **bool** | Whether to run the metadata update asynchronously. If true, the metadata update will be run in the background and the response will be 202. If false, the metadata update will be run synchronously and the response will be 200. | [optional] [default to false]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
