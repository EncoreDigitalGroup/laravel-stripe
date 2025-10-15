# Financial Connections

Stripe Financial Connections allows your users to securely connect their bank accounts to your application, enabling ACH payments, account verification, and balance
information. This chapter covers the Laravel Stripe library's implementation of Financial Connections, from session creation to handling connected accounts.

## Table of Contents

- [Understanding Financial Connections](#understanding-financial-connections)
- [Financial Connection Data Objects](#financial-connection-data-objects)
- [Creating Financial Connection Sessions](#creating-financial-connection-sessions)
- [Frontend Integration](#frontend-integration)
- [Handling Connected Accounts](#handling-connected-accounts)
- [Bank Account Data Objects](#bank-account-data-objects)
- [Testing Financial Connections](#testing-financial-connections)
- [Common Patterns](#common-patterns)

## Understanding Financial Connections

Financial Connections provides a secure, Stripe-hosted UI for users to link their bank accounts. The process involves several steps:

1. **Create a Financial Connection Session** - Server-side session creation with customer and permissions
2. **Display Stripe UI** - Frontend component that loads the Stripe-hosted connection flow
3. **User Links Account** - User authenticates with their bank through Stripe's secure interface
4. **Receive Connected Account Data** - Your backend receives the connected account information
5. **Store and Use Account** - Save account details and use for payments or verification

```php
// The basic flow
// 1. Create session server-side
$session = $stripe->financialConnections->sessions->create([
    'account_holder' => [
        'type' => 'customer',
        'customer' => 'cus_123'
    ],
    'permissions' => ['transactions', 'balances']
]);

// 2. Pass session secret to frontend
// 3. User completes bank linking in Stripe UI
// 4. Receive webhook or callback with connected account
```

## Financial Connection Data Objects

The library provides DTOs for working with Financial Connection data.

### StripeFinancialConnection

This object represents the configuration for creating a Financial Connection session:

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeFinancialConnection;

// Create financial connection using builder pattern
$connection = Stripe::builder()->financialConnection()->build(
    customer: $customer,
    permissions: ['transactions', 'balances', 'ownership']
);

// All financial connections are created via builder
$connection = Stripe::builder()->financialConnection()->build(
    customer: $customer,
    permissions: ['payment_method']
);
```

### StripeFinancialConnection Properties

```php
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;

$customer = Stripe::builder()->customer()->build(id: 'cus_123');

$connection = Stripe::builder()->financialConnection()->build(
    customer: $customer,              // StripeCustomer - Required customer object
    permissions: ['transactions']     // array - Permissions to request (default: ['transactions'])
);
```

**Available Permissions:**

- `transactions` - Access to transaction history
- `balances` - Access to account balance information
- `ownership` - Access to account ownership details
- `payment_method` - Ability to use account for payments

### Converting to Array

The `toArray()` method formats the data for Stripe API calls:

```php
$connection = Stripe::builder()->financialConnection()->build(
    customer: Stripe::builder()->customer()->build(id: 'cus_abc123'),
    permissions: ['transactions', 'payment_method']
);

$array = $connection->toArray();

// Returns:
// [
//     'account_holder' => [
//         'type' => 'customer',
//         'customer' => 'cus_abc123'
//     ],
//     'permissions' => ['transactions', 'payment_method']
// ]
```

## Creating Financial Connection Sessions

Financial Connection sessions are created through the Stripe SDK directly, as this is typically a one-time setup flow:

```php
use EncoreDigitalGroup\Stripe\Stripe;
use Stripe\StripeClient;

class BankAccountController extends Controller
{
    public function createConnectionSession(Request $request)
    {
        // Get the Stripe client
        $stripe = app(StripeClient::class);

        // Get or create customer
        $customer = Stripe::customers()->get($request->user()->stripe_customer_id);

        // Create financial connection configuration
        $connectionConfig = Stripe::builder()->financialConnection()->build(
            customer: $customer,
            permissions: ['transactions', 'payment_method', 'balances']
        );

        // Create the session through Stripe SDK
        $session = $stripe->financialConnections->sessions->create(
            $connectionConfig->toArray()
        );

        return response()->json([
            'client_secret' => $session->client_secret,
            'session_id' => $session->id
        ]);
    }
}
```

### Session Configuration Options

```php
// Minimal configuration
$connection = Stripe::builder()->financialConnection()->build(
    customer: Stripe::builder()->customer()->build(id: 'cus_123'),
    permissions: ['payment_method']
);

// Multiple permissions for comprehensive access
$connection = Stripe::builder()->financialConnection()->build(
    customer: Stripe::builder()->customer()->build(id: 'cus_123'),
    permissions: [
        'transactions',    // Transaction history
        'balances',        // Balance information
        'ownership',       // Account ownership details
        'payment_method'   // Use for payments
    ]
);
```

## Frontend Integration

The library provides a Laravel Blade component for easy frontend integration:

### Using the Blade Component

```blade
<x-stripe-financial-connections
    :stripe-public-key="config('services.stripe.key')"
    :stripe-session-secret="$sessionClientSecret"
    :stripe-customer-id="$user->stripe_customer_id"
    :redirect-success-url="route('bank-account.success')"
    :redirect-error-url="route('bank-account.error')"
    :post-success-url="route('api.bank-account.connected')"
    :public-security-key="$publicKey"
    :private-security-key="$privateKey"
/>
```

### Component Parameters

- `stripe-public-key` - Your Stripe publishable key
- `stripe-session-secret` - The client secret from the session creation
- `stripe-customer-id` - Your customer's Stripe ID
- `redirect-success-url` - Where to redirect after successful connection
- `redirect-error-url` - Where to redirect if connection fails
- `post-success-url` - API endpoint to POST connected account data
- `public-security-key` - Optional security key for payload verification
- `private-security-key` - Optional security key for payload verification

### Complete Flow Example

```php
// routes/web.php
Route::get('/connect-bank', [BankAccountController::class, 'show'])
    ->name('bank-account.connect');
Route::get('/bank-connected', [BankAccountController::class, 'success'])
    ->name('bank-account.success');
Route::get('/bank-error', [BankAccountController::class, 'error'])
    ->name('bank-account.error');

// routes/api.php
Route::post('/bank-account/connected', [BankAccountController::class, 'handleConnected'])
    ->name('api.bank-account.connected');
```

```php
// Controller
class BankAccountController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();
        $stripe = app(StripeClient::class);

        // Create financial connection session
        $connection = Stripe::builder()->financialConnection()->build(
            customer: Stripe::builder()->customer()->build(id: $user->stripe_customer_id),
            permissions: ['transactions', 'payment_method']
        );

        $session = $stripe->financialConnections->sessions->create(
            $connection->toArray()
        );

        // Generate security keys (optional, for payload verification)
        $publicKey = Str::random(32);
        $privateKey = Str::random(32);

        // Store keys temporarily in session
        session([
            'fc_public_key' => $publicKey,
            'fc_private_key' => $privateKey
        ]);

        return view('bank-account.connect', [
            'sessionClientSecret' => $session->client_secret,
            'publicKey' => $publicKey,
            'privateKey' => $privateKey
        ]);
    }

    public function handleConnected(Request $request)
    {
        // Verify security keys (if using)
        $publicKey = session('fc_public_key');
        $privateKey = session('fc_private_key');

        $payload = $request->json()->all();

        if ($payload['securityKeys']['publicKey'] !== $publicKey ||
            $payload['securityKeys']['privateKey'] !== $privateKey) {
            return response()->json(['error' => 'Invalid security keys'], 401);
        }

        // Process connected accounts
        $customerId = $payload['stripeCustomerId'];
        $accounts = $payload['accounts'];

        foreach ($accounts as $account) {
            // Store account information
            BankAccount::create([
                'user_id' => auth()->id(),
                'stripe_account_id' => $account['id'],
                'institution_name' => $account['institution_name'],
                'last4' => $account['last4'],
                'account_type' => $account['category']
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function success()
    {
        return view('bank-account.success');
    }

    public function error()
    {
        return view('bank-account.error');
    }
}
```

```blade
{{-- resources/views/bank-account/connect.blade.php --}}
<x-app-layout>
    <div class="max-w-2xl mx-auto py-8">
        <h1 class="text-2xl font-bold mb-4">Connect Your Bank Account</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="mb-4">
                Securely connect your bank account to enable instant payments and account verification.
            </p>

            <x-stripe-financial-connections
                :stripe-public-key="config('services.stripe.key')"
                :stripe-session-secret="$sessionClientSecret"
                :stripe-customer-id="auth()->user()->stripe_customer_id"
                :redirect-success-url="route('bank-account.success')"
                :redirect-error-url="route('bank-account.error')"
                :post-success-url="route('api.bank-account.connected')"
                :public-security-key="$publicKey"
                :private-security-key="$privateKey"
            />
        </div>
    </div>
</x-app-layout>
```

## Handling Connected Accounts

After a user successfully connects their bank account, you'll receive the account data in your POST endpoint.

### Bank Account Data Structure

```php
// Example payload structure
[
    'securityKeys' => [
        'publicKey' => 'abc123...',
        'privateKey' => 'xyz789...'
    ],
    'stripeCustomerId' => 'cus_abc123',
    'accounts' => [
        [
            'id' => 'fca_abc123',
            'institution_name' => 'Chase Bank',
            'last4' => '1234',
            'category' => 'checking',
            'display_name' => 'Chase Checking',
            'permissions' => ['payment_method', 'transactions'],
            'livemode' => false
        ]
    ]
]
```

### Processing Connected Accounts

```php
use EncoreDigitalGroup\Stripe\Objects\Support\StripeBankAccountConnectedPayload;

public function handleConnected(Request $request)
{
    // Validate the request
    $validated = $request->validate([
        'securityKeys' => 'required|array',
        'securityKeys.publicKey' => 'required|string',
        'securityKeys.privateKey' => 'required|string',
        'stripeCustomerId' => 'required|string',
        'accounts' => 'required|array'
    ]);

    // Verify security keys
    if (!$this->verifySecurityKeys($validated['securityKeys'])) {
        return response()->json(['error' => 'Invalid security keys'], 401);
    }

    // Find the user
    $user = User::where('stripe_customer_id', $validated['stripeCustomerId'])->first();

    if (!$user) {
        return response()->json(['error' => 'Customer not found'], 404);
    }

    // Process each connected account
    foreach ($validated['accounts'] as $accountData) {
        $this->storeConnectedAccount($user, $accountData);
    }

    // Clear security keys from session
    session()->forget(['fc_public_key', 'fc_private_key']);

    return response()->json(['success' => true]);
}

protected function storeConnectedAccount(User $user, array $accountData): void
{
    BankAccount::updateOrCreate(
        [
            'user_id' => $user->id,
            'stripe_account_id' => $accountData['id']
        ],
        [
            'institution_name' => $accountData['institution_name'] ?? 'Unknown',
            'last4' => $accountData['last4'] ?? null,
            'account_type' => $accountData['category'] ?? 'unknown',
            'display_name' => $accountData['display_name'] ?? null,
            'permissions' => $accountData['permissions'] ?? [],
            'is_live_mode' => $accountData['livemode'] ?? false,
            'connected_at' => now()
        ]
    );
}
```

## Bank Account Data Objects

The library provides DTOs for working with connected bank account data.

### StripeBankAccount

Represents a connected bank account with full details:

```php
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;

$bankAccount = Stripe::builder()->financialConnection()->bankAccount()->build(
    id: 'fca_abc123',
    category: 'checking',
    displayName: 'Chase Checking',
    institutionName: 'Chase Bank',
    last4: '1234',
    liveMode: false,
    permissions: ['payment_method', 'transactions'],
    subscriptions: ['transactions'],
    supportedPaymentMethodTypes: ['us_bank_account'],
    transactionRefresh: Stripe::builder()->financialConnection()->transactionRefresh()->build(
        status: 'succeeded',
        lastAttemptedAt: time() - 3600,
        nextRefreshAvailableAt: time() + 82800
    )
);
```

### StripeBankAccount Properties

```php
$bankAccount = Stripe::builder()->financialConnection()->bankAccount()->build(
    id: 'fca_123',                              // string|null - Stripe Financial Connection Account ID
    category: 'checking',                       // string|null - Account type (checking, savings, etc.)
    created: CarbonImmutable::now(),           // CarbonImmutable|null - Creation timestamp
    displayName: 'Primary Checking',           // string|null - User-friendly account name
    institutionName: 'Chase Bank',             // string|null - Financial institution name
    last4: '1234',                             // string|null - Last 4 digits of account number
    liveMode: true,                            // bool|null - Whether in live mode
    permissions: ['transactions'],              // array - Granted permissions
    subscriptions: ['transactions'],            // array - Active data subscriptions
    supportedPaymentMethodTypes: ['us_bank_account'], // array - Supported payment methods
    transactionRefresh: $refreshData           // StripeTransactionRefresh|null - Transaction refresh info
);
```

### StripeTransactionRefresh

Represents transaction synchronization status:

```php
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;

$transactionRefresh = Stripe::builder()->financialConnection()->transactionRefresh()->build(
    id: 'tr_abc123',                    // string|null - Refresh ID
    lastAttemptedAt: time() - 3600,     // int|null - Unix timestamp of last refresh attempt
    nextRefreshAvailableAt: time() + 82800, // int|null - Unix timestamp when next refresh is available
    status: 'succeeded'                  // string|null - Status (pending, succeeded, failed)
);
```

**Transaction Refresh Statuses:**

- `pending` - Refresh is in progress
- `succeeded` - Last refresh completed successfully
- `failed` - Last refresh encountered an error

## Testing Financial Connections

Testing Financial Connections involves mocking the Stripe SDK and handling the frontend flow.

### Testing Configuration Creation

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeFinancialConnection;
use EncoreDigitalGroup\Stripe\Objects\Customer\StripeCustomer;

test('can create financial connection configuration', function () {
    $customer = Stripe::builder()->customer()->build(
        id: 'cus_test123',
        email: 'test@example.com'
    );

    $connection = Stripe::builder()->financialConnection()->build(
        customer: $customer,
        permissions: ['transactions', 'payment_method']
    );

    expect($connection)
        ->toBeInstanceOf(StripeFinancialConnection::class)
        ->and($connection->customer)->toBe($customer)
        ->and($connection->permissions)->toBe(['transactions', 'payment_method']);
});

test('financial connection has default permissions', function () {
    $customer = Stripe::builder()->customer()->build(id: 'cus_test');

    $connection = Stripe::builder()->financialConnection()->build(customer: $customer);

    expect($connection->permissions)->toBe(['transactions']);
});

test('financial connection toArray returns correct structure', function () {
    $customer = Stripe::builder()->customer()->build(id: 'cus_abc123');

    $connection = Stripe::builder()->financialConnection()->build(
        customer: $customer,
        permissions: ['transactions', 'balances']
    );

    $array = $connection->toArray();

    expect($array)->toBe([
        'account_holder' => [
            'type' => 'customer',
            'customer' => 'cus_abc123'
        ],
        'permissions' => ['transactions', 'balances']
    ]);
});
```

### Testing Bank Account Objects

```php
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;

test('can create bank account object', function () {
    $bankAccount = Stripe::builder()->financialConnection()->bankAccount()->build(
        id: 'fca_test123',
        category: 'checking',
        displayName: 'Test Checking',
        institutionName: 'Test Bank',
        last4: '4242',
        permissions: ['payment_method']
    );

    expect($bankAccount)
        ->toBeInstanceOf(StripeBankAccount::class)
        ->and($bankAccount->id)->toBe('fca_test123')
        ->and($bankAccount->category)->toBe('checking')
        ->and($bankAccount->last4)->toBe('4242');
});

test('can create bank account with transaction refresh', function () {
    $refresh = Stripe::builder()->financialConnection()->transactionRefresh()->build(
        status: 'succeeded',
        lastAttemptedAt: 1640995200
    );

    $bankAccount = Stripe::builder()->financialConnection()->bankAccount()->build(
        id: 'fca_test',
        transactionRefresh: $refresh
    );

    expect($bankAccount->transactionRefresh)
        ->toBeInstanceOf(StripeTransactionRefresh::class)
        ->and($bankAccount->transactionRefresh->status)->toBe('succeeded');
});
```

### Testing Session Creation

```php
use Stripe\StripeClient;
use Stripe\FinancialConnections\Session;

test('creates financial connection session', function () {
    // Mock Stripe client
    $mockSession = new Session('fcs_test123');
    $mockSession->client_secret = 'fcs_secret_abc123';

    $stripeClient = Mockery::mock(StripeClient::class);
    $stripeClient->financialConnections = Mockery::mock();
    $stripeClient->financialConnections->sessions = Mockery::mock();
    $stripeClient->financialConnections->sessions
        ->shouldReceive('create')
        ->once()
        ->andReturn($mockSession);

    app()->instance(StripeClient::class, $stripeClient);

    // Create connection config
    $connection = Stripe::builder()->financialConnection()->build(
        customer: Stripe::builder()->customer()->build(id: 'cus_test'),
        permissions: ['payment_method']
    );

    // Create session
    $session = app(StripeClient::class)->financialConnections->sessions->create(
        $connection->toArray()
    );

    expect($session->client_secret)->toBe('fcs_secret_abc123');
});
```

## Common Patterns

Real-world patterns for implementing Financial Connections in Laravel applications.

### Complete Bank Account Service

```php
namespace App\Services;

use App\Models\User;
use App\Models\BankAccount;
use EncoreDigitalGroup\Stripe\Stripe;
use Stripe\StripeClient;
use Illuminate\Support\Str;

class BankAccountService
{
    public function __construct(
        protected StripeClient $stripe
    ) {}

    public function createConnectionSession(User $user, array $permissions = ['payment_method']): array
    {
        // Create financial connection configuration
        $connection = Stripe::builder()->financialConnection()->build(
            customer: Stripe::builder()->customer()->build(id: $user->stripe_customer_id),
            permissions: $permissions
        );

        // Create session
        $session = $this->stripe->financialConnections->sessions->create(
            $connection->toArray()
        );

        // Generate security keys
        $publicKey = Str::random(32);
        $privateKey = Str::random(32);

        // Store in cache for 1 hour
        cache()->put("fc_keys:{$user->id}", [
            'public' => $publicKey,
            'private' => $privateKey
        ], now()->addHour());

        return [
            'client_secret' => $session->client_secret,
            'session_id' => $session->id,
            'public_key' => $publicKey,
            'private_key' => $privateKey
        ];
    }

    public function verifyAndStoreAccounts(array $payload, User $user): bool
    {
        // Verify security keys
        $cachedKeys = cache()->get("fc_keys:{$user->id}");

        if (!$cachedKeys ||
            $cachedKeys['public'] !== $payload['securityKeys']['publicKey'] ||
            $cachedKeys['private'] !== $payload['securityKeys']['privateKey']) {
            return false;
        }

        // Store accounts
        foreach ($payload['accounts'] as $accountData) {
            $this->storeAccount($user, $accountData);
        }

        // Clear cache
        cache()->forget("fc_keys:{$user->id}");

        return true;
    }

    protected function storeAccount(User $user, array $accountData): BankAccount
    {
        return BankAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'stripe_account_id' => $accountData['id']
            ],
            [
                'institution_name' => $accountData['institution_name'] ?? 'Unknown',
                'last4' => $accountData['last4'] ?? null,
                'account_type' => $accountData['category'] ?? 'unknown',
                'display_name' => $accountData['display_name'] ?? null,
                'permissions' => $accountData['permissions'] ?? [],
                'is_live_mode' => $accountData['livemode'] ?? false,
                'is_active' => true,
                'connected_at' => now()
            ]
        );
    }

    public function disconnectAccount(BankAccount $account): bool
    {
        try {
            // Optionally, call Stripe API to revoke permissions
            // $this->stripe->financialConnections->accounts->disconnect($account->stripe_account_id);

            $account->update(['is_active' => false, 'disconnected_at' => now()]);

            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to disconnect bank account', [
                'account_id' => $account->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function getUserAccounts(User $user)
    {
        return BankAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->orderBy('connected_at', 'desc')
            ->get();
    }
}
```

### Blade Component Usage in Views

```blade
{{-- resources/views/settings/banking.blade.php --}}
<x-app-layout>
    <div class="max-w-4xl mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6">Bank Accounts</h1>

        {{-- Connected Accounts List --}}
        @if($accounts->count() > 0)
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">Connected Accounts</h2>
                </div>

                <ul class="divide-y divide-gray-200">
                    @foreach($accounts as $account)
                        <li class="px-6 py-4 flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $account->institution_name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ ucfirst($account->account_type) }} ••••{{ $account->last4 }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                @if($account->is_default)
                                    <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded">
                                        Default
                                    </span>
                                @endif

                                <button
                                    type="button"
                                    onclick="disconnectAccount({{ $account->id }})"
                                    class="text-red-600 hover:text-red-900 text-sm font-medium"
                                >
                                    Disconnect
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Add New Account Button --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Add a New Bank Account</h2>
            <p class="text-gray-600 mb-4">
                Securely connect your bank account to enable instant payments and transfers.
            </p>

            <button
                onclick="initializeConnection()"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
            >
                <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Connect Bank Account
            </button>
        </div>

        {{-- Financial Connections Component (hidden initially) --}}
        <div id="financial-connections-container" style="display: none;">
            <x-stripe-financial-connections
                :stripe-public-key="config('services.stripe.key')"
                :stripe-session-secret="$sessionClientSecret ?? ''"
                :stripe-customer-id="auth()->user()->stripe_customer_id"
                :redirect-success-url="route('settings.banking.success')"
                :redirect-error-url="route('settings.banking.error')"
                :post-success-url="route('api.bank-account.connected')"
                :public-security-key="$publicKey ?? ''"
                :private-security-key="$privateKey ?? ''"
            />
        </div>
    </div>

    @push('scripts')
    <script>
        async function initializeConnection() {
            try {
                // Call backend to create session
                const response = await fetch('/api/bank-account/create-session', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                // Update component with session data and show it
                document.getElementById('financial-connections-container').style.display = 'block';

                // Trigger the Financial Connections flow
                // (This would need additional JavaScript integration with the component)
            } catch (error) {
                console.error('Failed to initialize connection:', error);
                alert('Failed to start bank connection process. Please try again.');
            }
        }

        async function disconnectAccount(accountId) {
            if (!confirm('Are you sure you want to disconnect this bank account?')) {
                return;
            }

            try {
                const response = await fetch(`/api/bank-account/${accountId}/disconnect`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Failed to disconnect account. Please try again.');
                }
            } catch (error) {
                console.error('Failed to disconnect account:', error);
                alert('Failed to disconnect account. Please try again.');
            }
        }
    </script>
    @endpush
</x-app-layout>
```

## Next Steps

Now that you understand Financial Connections, explore other important topics:

- **[Webhooks](09-webhooks.md)** - Handle Stripe webhooks and events
- **[Builders Reference](10-builders-reference.md)** - Comprehensive guide to the builder pattern
- **[Testing](05-testing.md)** - Testing strategies and patterns

Or revisit core concepts:

- **[Customers](02-customers.md)** - Customer management
- **[Subscriptions](07-subscriptions.md)** - Subscription lifecycle management
