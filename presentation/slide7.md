# Scenario 5: Notification & Communication Packages

- **Custom notification channels**
  - Extending Laravel's notification system
  - Integration with messaging services (Slack, Discord, etc.)
  - Push notification systems for mobile apps
  - Custom delivery retry logic
  - Channel preference management
  
  ```php
  // Custom notification channel for SMS
  namespace YourVendor\SmsNotifications;
  
  use Illuminate\Notifications\Notification;
  
  class SmsChannel
  {
      protected $client;
      
      public function __construct(SmsClient $client)
      {
          $this->client = $client;
      }
      
      public function send($notifiable, Notification $notification)
      {
          if (!method_exists($notification, 'toSms')) {
              throw new \Exception('Notification class must have a toSms method');
          }
          
          // Get the phone number from the notifiable entity
          $to = $notifiable->routeNotificationFor('sms', $notification);
          
          if (empty($to)) {
              return;
          }
          
          // Get the notification content
          $message = $notification->toSms($notifiable);
          
          // Send the SMS
          $this->client->send($to, $message);
      }
  }
  
  // Using the custom channel in a notification
  class OrderShipped extends Notification
  {
      public function via($notifiable)
      {
          return ['mail', 'sms', 'database'];
      }
      
      public function toSms($notifiable)
      {
          return "Your order #{$this->order->id} has been shipped and will arrive on {$this->order->estimated_delivery}.";
      }
  }
  ```

- **Chat & messaging systems**
  - Real-time messaging with WebSockets
  - Message formatting and rich content
  - Group conversations and channels
  - Message history and search
  - Presence indicators and typing notifications
  
  ```php
  // Chat event class
  namespace YourVendor\ChatSystem\Events;
  
  use Illuminate\Broadcasting\InteractsWithSockets;
  use Illuminate\Broadcasting\PresenceChannel;
  use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
  use Illuminate\Foundation\Events\Dispatchable;
  use Illuminate\Queue\SerializesModels;
  
  class MessageSent implements ShouldBroadcast
  {
      use Dispatchable, InteractsWithSockets, SerializesModels;
      
      public $message;
      public $user;
      public $conversation;
      
      public function __construct($user, $message, $conversation)
      {
          $this->user = $user;
          $this->message = $message;
          $this->conversation = $conversation;
      }
      
      public function broadcastOn()
      {
          return new PresenceChannel('conversation.' . $this->conversation->id);
      }
      
      public function broadcastWith()
      {
          return [
              'id' => $this->message->id,
              'content' => $this->message->content,
              'created_at' => $this->message->created_at->toIso8601String(),
              'user' => [
                  'id' => $this->user->id,
                  'name' => $this->user->name,
                  'avatar' => $this->user->profile_photo_url,
              ],
          ];
      }
  }
  
  // WebSocket channel authorization
  Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
      $conversation = Conversation::findOrFail($conversationId);
      return $conversation->participants->contains('id', $user->id);
  });
  ```

- **Email template management**
  - Template inheritance systems
  - Dynamic content injection
  - Multilingual email support
  - Customizable themes and layouts
  - Preview and testing tools
  
  ```php
  // Email template manager
  namespace YourVendor\EmailTemplates;
  
  class TemplateManager
  {
      protected $templatePath;
      protected $defaultTheme;
      protected $cache;
      
      public function __construct($templatePath, $defaultTheme, $cache)
      {
          $this->templatePath = $templatePath;
          $this->defaultTheme = $defaultTheme;
          $this->cache = $cache;
      }
      
      public function render($template, $data = [], $theme = null, $locale = null)
      {
          // Determine theme and locale
          $theme = $theme ?? $this->defaultTheme;
          $locale = $locale ?? app()->getLocale();
          
          // Build the template path
          $path = "{$this->templatePath}/{$theme}/{$locale}/{$template}.blade.php";
          
          // Check if the template exists, fall back to default theme or locale if needed
          if (!file_exists($path)) {
              // Try the default theme with the requested locale
              $path = "{$this->templatePath}/{$this->defaultTheme}/{$locale}/{$template}.blade.php";
              
              if (!file_exists($path)) {
                  // Finally, fall back to default theme and locale
                  $path = "{$this->templatePath}/{$this->defaultTheme}/en/{$template}.blade.php";
              }
          }
          
          // Render the template with given data using Laravel's Blade compiler
          $cacheKey = "email_template:{$theme}:{$locale}:{$template}";
          
          return $this->cache->remember($cacheKey, 3600, function () use ($path, $data) {
              $blade = app('blade.compiler');
              $rendered = $blade->compileString(file_get_contents($path));
              
              // Extract rendered content and evaluate with data
              return view()->file($rendered, $data)->render();
          });
      }
  }
  
  // Using the template manager in a Mailablle
  class OrderConfirmation extends Mailable
  {
      protected $order;
      protected $user;
      
      public function __construct(Order $order, User $user)
      {
          $this->order = $order;
          $this->user = $user;
      }
      
      public function build()
      {
          $template = app(TemplateManager::class);
          $html = $template->render('order-confirmation', [
              'order' => $this->order,
              'user' => $this->user,
          ], $this->user->email_theme, $this->user->locale);
          
          return $this->subject('Order Confirmation #' . $this->order->id)
                      ->html($html);
      }
  }
  ```

- **SMS/WhatsApp integration**
  - Multiple provider support and failover
  - Template message compliance
  - Delivery tracking and reporting
  - Cost optimization strategies
  - Two-way messaging support
  
  ```php
  // SMS message gateway with failover
  namespace YourVendor\SmsIntegration;
  
  class SmsGateway
  {
      protected $providers = [];
      protected $defaultProvider;
      
      public function __construct(array $providers, $defaultProvider)
      {
          $this->providers = $providers;
          $this->defaultProvider = $defaultProvider;
      }
      
      public function send($to, $message, $options = [])
      {
          $provider = $options['provider'] ?? $this->defaultProvider;
          $attempt = 0;
          $maxAttempts = count($this->providers);
          
          // Try sending with selected provider, then failover if needed
          while ($attempt < $maxAttempts) {
              try {
                  $result = $this->sendWithProvider($provider, $to, $message, $options);
                  
                  // Track the delivery
                  $this->trackDelivery($provider, $to, $message, $result);
                  
                  return $result;
              } catch (\Exception $e) {
                  // Log the failure
                  logger()->error("SMS delivery failed with provider {$provider}", [
                      'error' => $e->getMessage(),
                      'to' => $to,
                  ]);
                  
                  // Move to the next provider
                  $attempt++;
                  $provider = array_keys($this->providers)[$attempt % count($this->providers)];
              }
          }
          
          throw new \Exception("Failed to send SMS after {$maxAttempts} attempts");
      }
      
      protected function sendWithProvider($provider, $to, $message, $options)
      {
          if (!isset($this->providers[$provider])) {
              throw new \InvalidArgumentException("Provider {$provider} not configured");
          }
          
          $client = $this->providers[$provider];
          
          return $client->sendMessage([
              'to' => $to,
              'text' => $message,
              'options' => $options,
          ]);
      }
      
      protected function trackDelivery($provider, $to, $message, $result)
      {
          return SmsLog::create([
              'provider' => $provider,
              'to' => $to,
              'message' => $message,
              'message_id' => $result['message_id'] ?? null,
              'status' => $result['status'] ?? 'sent',
              'cost' => $result['cost'] ?? null,
              'meta' => $result,
          ]);
      }
  }
  ```

- **Webhook systems**
  - Webhook registration and management
  - Payload validation and transformation
  - Retry mechanisms for failed deliveries
  - Security (HMAC signatures, IP filtering)
  - Webhook debugging and logging tools
  
  ```php
  // Webhook dispatcher
  namespace YourVendor\WebhookSystem;
  
  class WebhookDispatcher
  {
      protected $queue;
      protected $secret;
      
      public function __construct($queue, $secret)
      {
          $this->queue = $queue;
          $this->secret = $secret;
      }
      
      public function dispatch($event, $payload, $webhooks)
      {
          foreach ($webhooks as $webhook) {
              $this->sendWebhook($webhook, $event, $payload);
          }
      }
      
      protected function sendWebhook($webhook, $event, $payload)
      {
          // Add webhook job to queue
          $this->queue->push(new DeliverWebhookJob(
              $webhook->url,
              $event,
              $payload,
              $this->buildHeaders($webhook, $payload),
              $webhook->id
          ));
      }
      
      protected function buildHeaders($webhook, $payload)
      {
          $signature = hash_hmac('sha256', json_encode($payload), $this->secret);
          
          return [
              'Content-Type' => 'application/json',
              'X-Webhook-Signature' => $signature,
              'X-Webhook-Event' => $event,
              'X-Webhook-Delivery' => Str::uuid()->toString(),
          ];
      }
  }
  
  // Webhook delivery job
  class DeliverWebhookJob implements ShouldQueue
  {
      use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
      
      public $tries = 3;
      public $backoff = [30, 60, 120]; // Retry after 30s, 60s, 120s
      
      protected $url;
      protected $event;
      protected $payload;
      protected $headers;
      protected $webhookId;
      
      public function __construct($url, $event, $payload, $headers, $webhookId)
      {
          $this->url = $url;
          $this->event = $event;
          $this->payload = $payload;
          $this->headers = $headers;
          $this->webhookId = $webhookId;
      }
      
      public function handle()
      {
          try {
              $client = new \GuzzleHttp\Client();
              $response = $client->post($this->url, [
                  'json' => $this->payload,
                  'headers' => $this->headers,
                  'timeout' => 15.0,
              ]);
              
              // Log successful delivery
              WebhookDelivery::create([
                  'webhook_id' => $this->webhookId,
                  'event' => $this->event,
                  'payload' => $this->payload,
                  'status' => $response->getStatusCode(),
                  'response' => $response->getBody()->getContents(),
                  'delivered_at' => now(),
              ]);
          } catch (\Exception $e) {
              // Log failed delivery
              WebhookDelivery::create([
                  'webhook_id' => $this->webhookId,
                  'event' => $this->event,
                  'payload' => $this->payload,
                  'status' => $e->getCode(),
                  'response' => $e->getMessage(),
                  'delivered_at' => null,
              ]);
              
              // Rethrow to trigger retry
              throw $e;
          }
      }
  }
  ```