# # FinalAnswerEvidenceInner

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**type** | **string** |  | [optional] [default to 'code_interpreter']
**text** | **string** |  |
**code** | **string** | The code that was executed. |
**code_issue** | **string** | The issue that the code was written to solve. |
**code_result** | **string** | The result of the code that was executed. |
**id** | **string** | The chunk id of the evidence. |
**index** | **int** | The index of the chunk in the document. |
**document_id** | **string** | The document id of the document containing the chunk being used as evidence. |
**document_name** | **string** | The name of the document that contains the chunk being used as evidence. |
**metadata** | **array<string,mixed>** | The metadata of the chunk being used as evidence. | [optional]
**document_metadata** | **array<string,mixed>** | The metadata of the document that contains the evidence. | [optional]
**links** | [**array<string,\OpenAPI\Client\Model\SearchResultLink>**](SearchResultLink.md) | The links to the evidence. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
