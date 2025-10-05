# # CreateInstructionParams

## Properties

Name | Type | Description | Notes
------------ | ------------- | ------------- | -------------
**name** | **string** | The name of the instruction. Must be unique. |
**active** | **bool** | Whether the instruction is active. Active instructions are applied to documents when they&#39;re created or when their file is updated. | [optional] [default to true]
**scope** | **string** | The scope of the instruction. Determines whether the instruction is applied to the entire document or to each chunk of the document. Options are &#x60;&#39;document&#39;&#x60; or &#x60;&#39;chunk&#39;&#x60;. Generally &#x60;&#39;document&#39;&#x60; should be used when analyzing the full document is desired, such as when generating a summary or determining sentiment, and &#x60;&#39;chunk&#39;&#x60; should be used when a fine grained search over a document is desired. | [optional] [default to 'chunk']
**prompt** | **string** | A natural language instruction which will be applied to documents as they are created and updated. The results of the &#x60;instruction_prompt&#x60; will be stored as an &#x60;entity&#x60; in the schema defined by the &#x60;entity_schema&#x60; parameter. |
**entity_schema** | **array<string,mixed>** |  |
**filter** | **array<string,mixed>** | An optional metadata filter that is matched against document metadata during update and creation. The instruction will only be applied to documents with metadata matching the filter.  The following filter operators are supported: $eq - Equal to (number, string, boolean), $ne - Not equal to (number, string, boolean), $gt - Greater than (number), $gte - Greater than or equal to (number), $lt - Less than (number), $lte - Less than or equal to (number), $in - In array (string or number), $nin - Not in array (string or number). The operators can be combined with AND and OR. Read [Metadata &amp; Filters guide](https://docs.ragie.ai/docs/metadata-filters) for more details and examples. | [optional]
**partition** | **string** | An optional partition identifier. Instructions can be scoped to a partition. An instruction that defines a partition will only be executed for documents in that partition. | [optional]

[[Back to Model list]](../../README.md#models) [[Back to API list]](../../README.md#endpoints) [[Back to README]](../../README.md)
