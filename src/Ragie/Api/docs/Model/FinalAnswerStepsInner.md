# # FinalAnswerStepsInner

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**type** | **string** |  | [optional] [default to 'answer']
**think** | **string** |  |
**current_question** | **string** |  |
**other_resolved_question_ids** | **string[]** | A list of questions ids that are no longer relevant to the current answer referenced by their IDs. | [optional]
**answer** | [**\OpenAPI\Client\Model\Answer**](Answer.md) |  |
**search** | [**\OpenAPI\Client\Model\Search**](Search.md) | The search request to be made. |
**query_details** | [**\OpenAPI\Client\Model\QueryDetails[]**](QueryDetails.md) |  | [optional]
**search_log** | **string** | A log of the search results you found. | [optional] [default to '']
**questions_to_answer** | **string[]** | The questions that need to be answered to answer the original question. | [optional]
**code_issue** | **string** | The natural language description of the code issue you need to solve. |
**code** | **string** | The code you generated to solve the code issue. | [optional] [default to '']
**code_result** | **string** | The result of the code you generated after executing it. | [optional] [default to '']
**partial_answer** | [**\OpenAPI\Client\Model\Answer**](Answer.md) | The a potential partial answer when a full answer was not possible. |
**eval_passed** | **bool** |  |
**eval_reason** | **string** |  |

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
