# Scenario 1: Creating an API Wrapper Package

- **Use case: Simplifying third-party API integration**
  - Abstract away complex API interactions
  - Present consistent interface for developers
  - Handle authentication and authorization flows
  - Standardize response formatting and error handling
  - Examples: Payment gateways, CRMs, social media platforms

- **Structuring API clients**
  - Client class with method per endpoint
  - Request/response DTOs (Data Transfer Objects)
  - Resource classes for API entity mapping
  - Pagination and collection handling
  - Fluent builder pattern for complex queries
  
  ```php
  // API Client
  namespace YourVendor\PaymentGateway;

  class Client
  {
      protected $apiKey;
      protected $baseUrl;
      protected $httpClient;
      
      public function __construct($apiKey, $baseUrl = 'https://api.payment-gateway.com')
      {
          $this->apiKey = $apiKey;
          $this->baseUrl = $baseUrl;
          $this->httpClient = new \GuzzleHttp\Client([
              'base_uri' => $this->baseUrl,
              'headers' => [
                  'Authorization' => 'Bearer ' . $this->apiKey,
                  'Content-Type' => 'application/json',
                  'Accept' => 'application/json',
              ]
          ]);
      }
      
      public function createCharge(ChargeRequest $request): ChargeResponse
      {
          $response = $this->httpClient->post('/v1/charges', [
              'json' => $request->toArray(),
          ]);
          
          return new ChargeResponse(
              json_decode($response->getBody()->getContents(), true)
          );
      }
      
      // Other API methods...
  }
  
  // Request DTO
  class ChargeRequest
  {
      public $amount;
      public $currency;
      public $description;
      public $customerId;
      
      public function toArray()
      {
          return [
              'amount' => $this->amount,
              'currency' => $this->currency,
              'description' => $this->description,
              'customer_id' => $this->customerId,
          ];
      }
  }
  ```

- **Config management**
  - Secure credential storage options
  - Environment-specific configurations
  - Fallback and default values
  - Configuration validation on boot
  - Runtime configuration changes
  
  ```php
  // config/payment-gateway.php
  return [
      'api_key' => env('PAYMENT_GATEWAY_API_KEY'),
      'secret' => env('PAYMENT_GATEWAY_SECRET'),
      'environment' => env('PAYMENT_GATEWAY_ENV', 'sandbox'),
      'endpoints' => [
          'sandbox' => 'https://sandbox.api.payment-gateway.com',
          'production' => 'https://api.payment-gateway.com',
      ],
      'timeout' => 30,
      'retries' => 3,
  ];
  
  // ServiceProvider
  public function register()
  {
      $this->app->singleton('payment-gateway', function ($app) {
          $config = $app['config']['payment-gateway'];
          
          // Validate configuration
          if (empty($config['api_key'])) {
              throw new \InvalidArgumentException('Payment gateway API key is required');
          }
          
          $baseUrl = $config['endpoints'][$config['environment']];
          
          return new Client(
              $config['api_key'], 
              $baseUrl, 
              $config['timeout'], 
              $config['retries']
          );
      });
  }
  ```

- **Rate limiting and caching**
  - Respecting API rate limits with throttling
  - Implementing retry mechanisms with backoff
  - Response caching for performance
  - Cache invalidation strategies
  - Selective caching based on endpoint importance
  
  ```php
  class CachedClient extends Client
  {
      protected $cache;
      protected $rateLimiter;
      
      public function __construct($apiKey, $baseUrl, Cache $cache, RateLimiter $rateLimiter)
      {
          parent::__construct($apiKey, $baseUrl);
          $this->cache = $cache;
          $this->rateLimiter = $rateLimiter;
      }
      
      public function getCustomer($customerId)
      {
          $cacheKey = "customer:{$customerId}";
          
          return $this->cache->remember($cacheKey, 3600, function () use ($customerId) {
              // Check rate limit before making request
              $this->rateLimiter->throttle('api-calls', 60, 100); // 100 calls per minute
              
              return parent::getCustomer($customerId);
          });
      }
      
      public function createCharge(ChargeRequest $request): ChargeResponse
      {
          // Don't cache writes, but still throttle
          $this->rateLimiter->throttle('api-calls', 60, 100);
          
          return parent::createCharge($request);
      }
  }
  ```

- **Error handling**
  - Custom exception hierarchy
  - Translating API errors to application errors
  - Logging and monitoring integration
  - Graceful degradation strategies
  - Circuit breaker pattern for unstable APIs
  
  ```php
  // Exception hierarchy
  namespace YourVendor\PaymentGateway\Exceptions;
  
  class PaymentGatewayException extends \Exception {}
  class AuthenticationException extends PaymentGatewayException {}
  class RateLimitException extends PaymentGatewayException {}
  class ValidationException extends PaymentGatewayException {}
  class ServerException extends PaymentGatewayException {}
  
  // In the client
  protected function handleRequest($method, $endpoint, $options = [])
  {
      try {
          $response = $this->httpClient->$method($endpoint, $options);
          return json_decode($response->getBody()->getContents(), true);
      } catch (\GuzzleHttp\Exception\ClientException $e) {
          $responseBody = json_decode($e->getResponse()->getBody()->getContents(), true);
          
          // Map API error codes to our exceptions
          switch ($e->getResponse()->getStatusCode()) {
              case 401:
                  throw new AuthenticationException($responseBody['message'] ?? 'Authentication failed');
              case 422:
                  throw new ValidationException($responseBody['message'] ?? 'Validation failed', 0, null, $responseBody['errors'] ?? []);
              case 429:
                  throw new RateLimitException($responseBody['message'] ?? 'Rate limit exceeded');
              default:
                  throw new PaymentGatewayException($responseBody['message'] ?? 'Unknown error');
          }
      } catch (\GuzzleHttp\Exception\ServerException $e) {
          throw new ServerException('The payment gateway is experiencing issues', 0, $e);
      }
  }
  ```