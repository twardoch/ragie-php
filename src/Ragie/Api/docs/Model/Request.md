# # Request

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**input** | **string** | The text used to generate the response. Generally a question or a query. |
**instructions** | **string** |  | [optional]
**tools** | [**\OpenAPI\Client\Model\Tool[]**](Tool.md) | The tools available to the agent. Currently the only tool is retrieve. The &#x60;default&#x60; partition is used by default unless an other partition is specified. | [optional]
**model** | **string** | The model to use for the agent. Currently the only model is deep-search. | [optional] [default to 'deep-search']
**reasoning** | [**\OpenAPI\Client\Model\Reasoning**](Reasoning.md) | The reasoning to use for the agent. The default effort level is medium. | [optional]
**stream** | **bool** | Whether to stream the response | [optional] [default to false]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
