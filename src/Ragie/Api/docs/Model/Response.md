# # Response

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**id** | **string** |  |
**object** | **string** |  | [optional] [default to 'response']
**created_at** | **int** |  |
**status** | **string** |  |
**error** | **string** |  | [optional]
**incomplete_details** | **mixed** |  | [optional]
**instructions** | **string** |  | [optional]
**max_output_tokens** | **mixed** |  | [optional]
**model** | **string** |  | [optional] [default to 'deep-search']
**output** | [**\OpenAPI\Client\Model\ResponseOutputInner[]**](ResponseOutputInner.md) |  |
**output_parsed** | [**\OpenAPI\Client\Model\FinalAnswer**](FinalAnswer.md) |  | [optional]
**tools** | [**\OpenAPI\Client\Model\Tool[]**](Tool.md) |  |
**reasoning** | [**\OpenAPI\Client\Model\Reasoning**](Reasoning.md) |  |
**parallel_tool_calls** | **bool** |  | [optional] [default to false]
**store** | **bool** |  | [optional] [default to false]
**temperature** | **float** |  | [optional] [default to 1.0]
**previous_response_id** | **string** |  | [optional]
**tool_choice** | **string** |  | [optional] [default to 'auto']
**top_p** | **float** |  | [optional] [default to 1.0]
**truncation** | **string** |  | [optional] [default to 'disabled']
**usage** | [**\OpenAPI\Client\Model\RagieApiSchemaResponseUsage**](RagieApiSchemaResponseUsage.md) |  |
**user** | **mixed** |  | [optional]
**metadata** | **array<string,mixed>** |  | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
