## Comprehensive Plan for Ragie PHP Client


Comprehensive Plan: Building a High-Level Ragie PHP Client

Objective: Create a developer-friendly, robust, and intuitive PHP client for
the Ragie API. This client will act as a wrapper around the auto-generated API
code located in `src/Ragie/Api`, offering a simplified and more resilient
interface for common Retrieval-Augmented Generation (RAG) operations.

This document is your complete, step-by-step guide. We will walk through the
entire process, explaining not just what to do, but also why each decision is
made, ensuring you build a high-quality, maintainable library.

Phase 1: Project Foundation and Structure

Goal: Establish a clean and modern project structure for our new client code.
We will create the main Client class, define a clear interface for it, and set
up custom exceptions. This foundation is crucial for making our client
testable, maintainable, and easy for other developers to integrate.

Step 1.1: Create the File Structure

First, let's create the necessary directories and files. This structure
intentionally separates our new high-level code from the auto-generated code,
following modern PHP best practices.

Inside the `src/Ragie/` directory, create the following:

```text
src/Ragie/
├── Api/                      <-- The existing auto-generated client
├── Client.php                <-- Our new high-level client class
├── RagieClientInterface.php  <-- A contract defining our client's public methods
├── Exception/
│   ├── RagieException.php    <-- A base exception for our client
│   ├── DocumentException.php <-- For errors specifically related to documents
│   └── QueryException.php    <-- For errors during search or ask operations
└── DTO/
├── AbstractDTO.php       <-- A base class for our Data Transfer Objects
├── Document.php          <-- An object representing a single document
├── SearchResult.php      <-- An object representing a search query result
└── RagAnswer.php         <-- An object representing the final RAG answer
```

Step 1.2: Define the Client Interface (RagieClientInterface.php)

An interface is a "contract." It dictates which public methods our Client
class must implement. This is essential for testing, as it allows us (and
users of our library) to easily "mock" the client, simulating its behavior
without making actual, slow, and data-dependent API calls.


```php
<?php

namespace Ragie;

use Ragie\DTO\Document; use Ragie\DTO\RagAnswer; use Ragie\DTO\SearchResult;

/**

  * Defines the contract for the high-level Ragie client. _/ interface RagieClientInterface { /_ *

    * Uploads a document to the Ragie service.

    * @param string $name A user-friendly name for the document.

    * @param string $content The full text content of the document.

    * @return Document The created document object.

    * @throws \Ragie\Exception\DocumentException On failure. */ public function createDocument(string $name, string $content): Document;

/**

    * Retrieves a document by its unique ID.

    * @param string $documentId The ID of the document to retrieve.

    * @return Document|null The found document object, or null if not found.

    * @throws \Ragie\Exception\DocumentException On API errors other than 404. */ public function getDocument(string $documentId): ?Document;

/**

    * Deletes a document by its unique ID.

    * @param string $documentId The ID of the document to delete.

    * @return bool True on successful deletion.

    * @throws \Ragie\Exception\DocumentException On failure. */ public function deleteDocument(string $documentId): bool;

/**

    * Performs a semantic search over your documents.

    * @param string $query The natural language query to search for.

    * @param int $limit The maximum number of results to return.

    * @return SearchResult An object containing the search results.

    * @throws \Ragie\Exception\QueryException On failure. */ public function search(string $query, int $limit = 5): SearchResult;

/**

    * Asks a question and gets an answer synthesized from the best search results.

    * @param string $question The question to ask.

    * @return RagAnswer An object containing the synthesized answer and source documents.

    * @throws \Ragie\Exception\QueryException On failure. */ public function ask(string $question): RagAnswer; } 
```

Step 1.3: Create the Client Class Skeleton

Now, create the main Client.php file. The constructor is the entry point and
will handle the configuration and instantiation of the low-level, auto-
generated API clients.

```php
<?php

namespace Ragie;

use GuzzleHttp\Client as HttpClient; use OpenAPI\Client\Api\DocumentsApi; use
OpenAPI\Client\Api\SearchApi; use OpenAPI\Client\Api\ChatApi; // NOTE: Verify
and adjust this class name if needed use OpenAPI\Client\Configuration; use
Psr\Log\LoggerInterface; use Psr\Log\NullLogger;

class Client implements RagieClientInterface { private Configuration $config;
private DocumentsApi $documentsApi; private SearchApi $searchApi; private
ChatApi $chatApi; private LoggerInterface $logger;

    
    
    public function __construct(
        string $apiKey,
        ?LoggerInterface $logger = null,
        ?HttpClient $httpClient = null
    ) {
        $this-&gt;config = Configuration::getDefaultConfiguration()-&gt;setApiKey(&#39;x-api-key&#39;, $apiKey);
        $this-&gt;logger = $logger ?? new NullLogger();
    
        // If a custom HTTP client is provided, use it. Otherwise, Guzzle creates a default one.
        // This gives users advanced control over proxies, timeouts, etc.
        $guzzle = $httpClient ?? new HttpClient();
    
        $this-&gt;documentsApi = new DocumentsApi($guzzle, $this-&gt;config);
        $this-&gt;searchApi = new SearchApi($guzzle, $this-&gt;config);
        $this-&gt;chatApi = new ChatApi($guzzle, $this-&gt;config);
    
        $this-&gt;logger-&gt;info(&#39;Ragie Client Initialized.&#39;);
    }
    
    // --- Methods will be implemented in Phase 3 ---
      
    

}
```

Why inject a LoggerInterface? This is a core principle of flexible software
design. It allows users of our client to integrate it seamlessly with their
application's existing logging system (like Monolog). The NullLogger is a safe
default that simply does nothing if no logger is provided, avoiding errors.

Why allow a custom HttpClient? Power users may need to configure advanced HTTP
settings like connection timeouts, proxies, or custom middleware. Accepting a
Guzzle client in the constructor gives them this flexibility without
cluttering our client's API.

Phase 2: Implement Data Transfer Objects (DTOs)

Goal: Create dedicated classes to represent the data structures returned by
the API. Using DTOs is vastly superior to returning raw arrays because it
provides type-safety, enables auto-completion in modern IDEs, and creates a
clear, self-documenting contract for the data.

Step 2.1: The Base DTO

Create a simple abstract class with a helper method to easily construct DTOs
from associative arrays.

```php
<?php

namespace Ragie\DTO;

/**

  * Base class for all Data Transfer Objects. _/ abstract class AbstractDTO { /_ *

    * Creates an instance of the DTO from an associative array.

    * @param array $data

    * @return static */ public static function fromArray(array $data): static { // This uses named arguments to safely construct the DTO. // It's robust against extra fields in the API response. return new static(...$data); } } 
```

Step 2.2: Implement the Core DTOs

Now create the specific DTOs for Document, SearchResult, and RagAnswer. These
classes will be final because they are simple data holders and are not
designed to be extended.

```php
<?php

namespace Ragie\DTO;

/**

  * Represents a document stored in the Ragie service. */ final class Document extends AbstractDTO { public function __construct( public readonly string $id, public readonly string $name, public readonly int $characterCount, public readonly string $createdAt, // Consider casting to DateTimeImmutable in a future version ) {} } </code>

<code> <?php

namespace Ragie\DTO;

/**

  * Represents the result of a search query. _/ final class SearchResult extends AbstractDTO { /_ * @var Document[] */ public readonly array $results;

/**

    * @param array $results An array of document data from the API. */ public function __construct(array $results = []) { // We override the constructor here to hydrate each result into a Document DTO. $this->results = array_map( fn(array $docData) => Document::fromArray($docData), $results ); } } 
```

```php
<?php

namespace Ragie\DTO;

final class RagAnswer extends AbstractDTO { /** * @param string $answer The
synthesized answer from the LLM. * @param Document[] $sourceDocuments The
documents used as context to generate the answer. */ public function
__construct( public readonly string $answer, public readonly array
$sourceDocuments, ) {} } 
```

Phase 3: Implement Core Client Methods

Goal: Implement the methods defined in our RagieClientInterface. We will wrap
the low-level API calls, add automatic retry logic to handle transient network
errors, and convert API exceptions into our more specific, custom exceptions.

Step 3.1: Create a Helper Method for API Calls

To keep our code DRY (Don't Repeat Yourself), we'll create a private helper
method to encapsulate the retry logic. This ensures all our API calls are
consistently resilient.

Inside `src/Ragie/Client.php`:

```php
<?php // Add these 'use' statements at the top of Client.php use
OpenAPI\Client\ApiException; use Throwable; use Ragie\DTO\Document; use
Ragie\DTO\RagAnswer; use Ragie\DTO\SearchResult; use
Ragie\Exception\DocumentException; use Ragie\Exception\QueryException;

// ... inside the Client class

/**

  * Executes a given API call with an exponential backoff retry strategy.

  * @param callable $apiCall The closure containing the API call to execute.

  * @param int $maxAttempts The maximum number of times to attempt the call.

  * @return mixed The result of the successful API call.

  * @throws ApiException If the call fails on the final attempt. */ private function executeWithRetries(callable $apiCall, int $maxAttempts = 3) { for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) { try { return $apiCall(); } catch (ApiException $e) { $this->logger->warning('Ragie API call failed on attempt {attempt}: {message}', [ 'attempt' => $attempt, 'code' => $e->getCode(), 'message' => $e->getMessage(), 'response_body' => $e->getResponseBody() ]);
        
        // If this was the last attempt, re-throw the exception to be caught by the public method.
             if ($attempt === $maxAttempts) {
                 throw $e;
             }
        
             // Wait for 1, 2 seconds before the next retry (exponential backoff).
             sleep(pow(2, $attempt - 1));
         }
          
        

} } 
```

Step 3.2: Implement the Document Management Methods

Now, we'll use our executeWithRetries helper to implement the methods for
creating, getting, and deleting documents.

Inside `src/Ragie/Client.php`, add the full implementations:

```php
<?php

public function createDocument(string $name, string $content): Document
{ $this->logger->debug('Creating document: {name}', ['name' => $name]); try {
$response = $this->executeWithRetries(function () use ($name, $content) { //
NOTE: Adjust the method and parameters below based on the actual generated
client return $this->documentsApi->createDocument($name, $content); });

    
    
        // The response from the generated client is an object. We cast it to an array
        // to safely pass it to our DTO&#39;s fromArray method.
        $responseData = (array) $response-&gt;jsonSerialize();
        return Document::fromArray($responseData);
    
    } catch (ApiException $e) {
        // Wrap the generic ApiException in our more specific, user-friendly exception.
        throw new DocumentException(&quot;Failed to create document &#39;{$name}&#39;: &quot; . $e-&gt;getMessage(), $e-&gt;getCode(), $e);
    }
      
    

} 
```

```php
<?php

public function getDocument(string $documentId): ?Document {
$this->logger->debug('Retrieving document: {id}', ['id' => $documentId]); try
{ $response = $this->executeWithRetries(function () use ($documentId) { return
$this->documentsApi->getDocumentById($documentId); // NOTE: Adjust method name
});

    
    
        $responseData = (array) $response-&gt;jsonSerialize();
        return Document::fromArray($responseData);
        
    } catch (ApiException $e) {
        // A 404 is an expected outcome (document not found), so we return null.
        if ($e-&gt;getCode() === 404) {
            $this-&gt;logger-&gt;info(&#39;Document not found: {id}&#39;, [&#39;id&#39; =&gt; $documentId]);
            return null;
        }
        // For all other errors, we throw.
        throw new DocumentException(&quot;Failed to retrieve document &#39;{$documentId}&#39;: &quot; . $e-&gt;getMessage(), $e-&gt;getCode(), $e);
    }
      
    

} 
```

```php
<?php

public function deleteDocument(string $documentId): bool {
$this->logger->debug('Deleting document: {id}', ['id' => $documentId]); try {
$this->executeWithRetries(function () use ($documentId) { // Deletion
endpoints often return a 204 No Content response, which the generated //
client might represent as a null return.
$this->documentsApi->deleteDocumentById($documentId); // NOTE: Adjust method
name }); return true; } catch (ApiException $e) { // We don't expect a 404 to
be a success, so we throw an exception. throw new DocumentException("Failed to
delete document '{$documentId}': " . $e->getMessage(), $e->getCode(), $e); } }
```

Step 3.3: Implement the Core RAG Methods (search and ask)

Finally, implement the primary RAG functions using the same robust pattern.

Inside `src/Ragie/Client.php`, add the full implementations:

```php
<?php

public function search(string $query, int $limit = 5): SearchResult {
$this->logger->debug('Searching with query: {query}', ['query' => $query]);
try { $response = $this->executeWithRetries(function () use ($query, $limit) {
// NOTE: Adjust method name and parameters as needed return
$this->searchApi->searchDocuments($query, $limit); });

    
    
        return SearchResult::fromArray((array) $response-&gt;jsonSerialize());
    } catch (ApiException $e) {
        throw new QueryException(&quot;Search failed for query &#39;{$query}&#39;: &quot; . $e-&gt;getMessage(), $e-&gt;getCode(), $e);
    }
      
    

} 
```

```php
<?php

public function ask(string $question): RagAnswer {
$this->logger->debug('Asking question: {question}', ['question' =>
$question]); try { $response = $this->executeWithRetries(function () use
($question) { // NOTE: Adjust method name and parameters as needed return
$this->chatApi->getAnswer($question); });

    
    
        return RagAnswer::fromArray((array) $response-&gt;jsonSerialize());
    } catch (ApiException $e) {
        throw new QueryException(&quot;Ask failed for question &#39;{$question}&#39;: &quot; . $e-&gt;getMessage(), $e-&gt;getCode(), $e);
    }
      
    

} 
```

Phase 4: Verification and Developer Experience

Goal: Ensure the client works as expected and provide clear examples and
documentation to make it a pleasure for other developers to use.

Step 4.1: Write Unit Tests

Unit testing is non-negotiable for a high-quality library. It verifies
correctness and prevents future regressions.

1. Setup: Ensure you have phpunit/phpunit as a dev-dependency in your composer.json.

2. Create Test File: Create tests/Unit/RagieClientTest.php.

3. Mock Dependencies: Use PHPUnit's built-in mocking capabilities to create "fake" versions of the DocumentsApi, SearchApi, and ChatApi. Your tests should never make real network calls.

4. Write Test Cases:

* Test that the constructor correctly configures the API key.

* Test the createDocument success path: Assert that the low-level documentsApi->createDocument method is called once and that your client returns a properly populated Document DTO.

* Test the createDocument failure path: Make your mocked documentsApi throw an ApiException. Assert that it is called exactly 3 times (due to retries) and that your client ultimately throws your custom DocumentException.

* Test getDocument for a 404 response, ensuring it returns null.

* Repeat this pattern (testing success, failure, and edge cases) for all other public methods.

Step 4.2: Create a Practical Example Script

The best documentation is working code. Create an `examples/` directory in the
project root and add a script that demonstrates how to use the client.

`examples/01_basic_rag_flow.php`:

```php
<?php require **DIR** . '/../vendor/autoload.php';

use Ragie\Client; use Ragie\Exception\RagieException;

// For security, load the API key from an environment variable, not hardcoded
in the file. $apiKey = getenv('RAGIE_API_KEY'); if (!$apiKey) { die("Error:
Please set the RAGIE_API_KEY environment variable.\nRun: export
RAGIE_API_KEY='your_key_here'\n"); }

$client = new Client($apiKey);

try { echo "1. Creating a new document...\n"; $docContent = 'Annual leave
entitlement is 25 days per year, plus public holidays. Sick leave requires a
doctor&#39;s note after the third consecutive day of absence.'; $doc =
$client->createDocument('Internal HR Policy v2', $docContent); echo " ->
Success! Document created with ID: {$doc->id}\n\n";

    
    
    echo &quot;2. Asking a question related to the document...\n&quot;;
    $answer = $client-&gt;ask(&#39;How many days of holiday do I get per year?&#39;);
    
    echo &quot;   -&gt; Answer: {$answer-&gt;answer}\n\n&quot;;
    echo &quot;   -&gt; Source Documents Used by the AI: \n&quot;;
    foreach ($answer-&gt;sourceDocuments as $sourceDoc) {
        echo &quot;      - ID: {$sourceDoc-&gt;id}, Name: &#39;{$sourceDoc-&gt;name}&#39;\n&quot;;
    }
    echo &quot;\n&quot;;
    
    echo &quot;3. Deleting the document...\n&quot;;
    $client-&gt;deleteDocument($doc-&gt;id);
    echo &quot;   -&gt; Success! Document {$doc-&gt;id} deleted.\n&quot;;
      
    

} catch (RagieException $e) { echo "[ERROR] An API operation failed: " .
$e->getMessage() . "\n"; // If available, you can get the original exception
for more details if ($e->getPrevious()) { echo " Original Exception: " .
$e->getPrevious()->getMessage() . "\n"; } } 
```

Step 4.3: Document All New Code

Go back through all the new classes and methods you've created and add
comprehensive PHPDoc comments. Explain what each class is for, what each
method does, the purpose of each parameter (@param), and what is returned
(@return) or thrown (@throws). This is invaluable for IDEs and for other
developers who will use your client.

This detailed plan provides a complete and sound roadmap. By following these
phases, you will build a high-quality, robust, and easy-to-use PHP client that
significantly improves the developer experience of integrating with Ragie.

