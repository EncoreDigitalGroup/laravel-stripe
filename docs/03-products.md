# Products

Products represent the goods or services you sell. In Stripe's model, products are high-level descriptions that can have multiple prices attached to them. This chapter
covers everything you need to know about product management in the Laravel Stripe library—from creation to lifecycle management, including the archive/reactivate pattern
that Stripe uses.

## Table of Contents

- [Understanding Products vs Prices](#understanding-products-vs-prices)
- [Basic Product Operations](#basic-product-operations)
- [Product Data Objects](#product-data-objects)
- [Advanced Product Features](#advanced-product-features)
- [Product Lifecycle Management](#product-lifecycle-management)
- [Testing Product Operations](#testing-product-operations)
- [Common Patterns](#common-patterns)

## Understanding Products vs Prices

Before diving into the code, it's important to understand Stripe's product/price model:

- **Products** describe what you're selling (name, description, images)
- **Prices** define how much it costs and billing details (amount, currency, recurring intervals)
- One product can have multiple prices (different currencies, billing intervals, tiers)

```php
// Example: A SaaS subscription product
$product = Stripe::product(
    name: 'Premium Subscription',
    description: 'Access to premium features and priority support'
);

// This product might have multiple prices:
// - Monthly: $29/month
// - Annual: $290/year (2 months free)
// - Student: $15/month
```

## Basic Product Operations

The `StripeProductService` provides all standard CRUD operations plus special methods for product lifecycle management.

### Creating Products

There are three ways to create product data objects. All three are functionally equivalent—choose based on your preference and use case.

#### Method 1: Direct DTO Creation (Shortest)

```php
use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;

$productData = StripeProduct::make(
    name: 'Basic Widget',
    description: 'A basic widget for everyday use'
);
```

This is the most concise approach when you don't need IDE discovery or chaining.

#### Method 2: Using the Builder Pattern (Most Discoverable)

```php
use EncoreDigitalGroup\Stripe\Stripe;

$productData = Stripe::builder()->product()->build(
    name: 'Basic Widget',
    description: 'A basic widget for everyday use'
);
```

The builder pattern provides excellent IDE autocompletion and discoverability. It's especially useful for complex nested objects.

#### Method 3: Using the Facade Shortcut (Recommended)

```php
use EncoreDigitalGroup\Stripe\Stripe;

$productData = Stripe::product(
    name: 'Basic Widget',
    description: 'A basic widget for everyday use'
);
```

This is the recommended approach—it's concise like Method 1 but provides better discoverability through the Stripe facade. Under the hood, it uses the builder pattern.

#### Creating Products in Stripe

Once you have your product data object, pass it to the service to create it in Stripe:

```php
use EncoreDigitalGroup\Stripe\Stripe;

// Simple product
$product = Stripe::products()->create(Stripe::product(
    name: 'Basic Widget',
    description: 'A basic widget for everyday use'
));

// Product with rich metadata
$product = Stripe::products()->create(Stripe::product(
    name: 'Premium Software License',
    description: 'Enterprise software with full support',
    active: true,
    metadata: [
        'category' => 'software',
        'support_tier' => 'premium',
        'license_type' => 'enterprise'
    ],
    url: 'https://example.com/products/premium-license'
));

echo $product->id; // "prod_abc123..."
```

### Retrieving Products

```php
// Get by ID
$product = Stripe::products()->get('prod_abc123');

// Access all properties with full type safety
echo $product->name;        // "Premium Software License"
echo $product->description; // "Enterprise software with full support"
echo $product->active;      // true
echo $product->url;         // "https://example.com/products/premium-license"

// Metadata is a regular PHP array
echo $product->metadata['category']; // "software"
```

### Updating Products

```php
// Update specific fields
$updatedProduct = Stripe::products()->update('prod_abc123', Stripe::product(
    description: 'Updated: Enterprise software with 24/7 support',
    metadata: [
        'category' => 'software',
        'support_tier' => 'platinum',  // Upgraded support
        'last_updated' => date('Y-m-d')
    ]
));

// The response includes all fields, updated and unchanged
echo $updatedProduct->name;         // Still "Premium Software License"
echo $updatedProduct->description;  // "Updated: Enterprise software..."
```

### Deleting Products

```php
// Hard delete (rarely used - prefer archiving)
$deleted = Stripe::products()->delete('prod_abc123');

if ($deleted) {
    echo "Product permanently deleted";
}
```

**Important**: Deleting products can break existing subscriptions and payments. In most cases, you should use archiving instead.

### Listing Products

```php
// Get all products
$products = Stripe::products()->list();

// With filters
$activeProducts = Stripe::products()->list([
    'active' => true,
    'limit' => 50
]);

// By creation date
$recentProducts = Stripe::products()->list([
    'created' => [
        'gte' => strtotime('-30 days')
    ]
]);

// Work with the collection
$productNames = $products->pluck('name')->toArray();
$premiumProducts = $products->filter(fn($p) =>
    isset($p->metadata['tier']) && $p->metadata['tier'] === 'premium'
);
```

## Product Data Objects

The `StripeProduct` class represents all product data with full type safety and intelligent defaults.

### StripeProduct Properties

```php
use EncoreDigitalGroup\Stripe\Objects\Product\StripeProduct;

$product = Stripe::product(
    id: 'prod_123',                    // string|null - Stripe product ID (read-only on create)
    name: 'Product Name',              // string|null - Product name (required for create)
    description: 'Product description', // string|null - Detailed description
    active: true,                      // bool|null - Whether product is active (default: true)
    images: ['https://...'],           // array|null - Array of image URLs
    metadata: ['key' => 'value'],      // array|null - Custom metadata
    defaultPrice: 'price_123',         // string|null - Default price ID
    taxCode: 'txcd_123',              // string|null - Tax code
    unitLabel: 'seat',                // string|null - Unit of measurement
    url: 'https://...',               // string|null - Product URL
    shippable: false,                 // bool|null - Whether product requires shipping
    packageDimensions: [...],         // array|null - Package dimensions for shipping
    created: 1640995200,              // int|null - Creation timestamp (read-only)
    updated: 1640995200               // int|null - Last update timestamp (read-only)
);
```

### Metadata Handling

Metadata allows you to store arbitrary key-value pairs with products:

```php
$product = Stripe::products()->create(Stripe::product(
    name: 'Custom Widget',
    metadata: [
        'category' => 'widgets',
        'manufacturer' => 'ACME Corp',
        'weight_kg' => '1.5',
        'warranty_months' => '24',
        'internal_sku' => 'WDG-001'
    ]
));

// Retrieve metadata
$category = $product->metadata['category']; // 'widgets'

// Update metadata (merges with existing)
Stripe::products()->update($product->id, Stripe::product(
    metadata: [
        'category' => 'premium-widgets',  // Updated
        'color' => 'blue'                // Added
        // Other metadata keys remain unchanged
    ]
));
```

### Images and Media

```php
$product = Stripe::products()->create(Stripe::product(
    name: 'Stylish Shoes',
    description: 'Comfortable and fashionable footwear',
    images: [
        'https://example.com/images/shoes-front.jpg',
        'https://example.com/images/shoes-side.jpg',
        'https://example.com/images/shoes-back.jpg'
    ]
));

// Images are stored as an array of URLs
foreach ($product->images as $imageUrl) {
    echo "<img src=\"{$imageUrl}\" alt=\"Product image\">";
}
```

### Package Dimensions

For physical products that require shipping:

```php
$product = Stripe::products()->create(Stripe::product(
    name: 'Laptop Computer',
    shippable: true,
    packageDimensions: [
        'height' => 5.0,   // inches
        'length' => 15.0,
        'width' => 10.0,
        'weight' => 3.5    // pounds
    ]
));

// Access package dimensions
$dimensions = $product->packageDimensions;
echo "Shipping weight: {$dimensions['weight']} lbs";
```

## Advanced Product Features

### Tax Codes

Stripe supports tax codes for automatic tax calculation:

```php
// Product with tax code
$product = Stripe::products()->create(Stripe::product(
    name: 'Digital Software',
    taxCode: 'txcd_10000000',  // Software - downloaded
    description: 'Downloadable productivity software'
));

// Tax codes can be referenced by ID or object
// The library handles both cases automatically
```

### Unit Labels

For usage-based or per-unit products:

```php
$product = Stripe::products()->create(Stripe::product(
    name: 'API Calls',
    unitLabel: 'request',     // Singular form
    description: 'Pay per API request'
));

// This affects how quantities are displayed in Stripe
// e.g., "1000 requests" instead of "1000 units"
```

### Product URLs

Link products to your website or documentation:

```php
$product = Stripe::products()->create(Stripe::product(
    name: 'Premium Plan',
    url: 'https://myapp.com/plans/premium',
    description: 'Full access to all premium features'
));
```

## Product Lifecycle Management

Unlike customers, Stripe products follow a soft-delete pattern called "archiving." This preserves data integrity while hiding products from new purchases.

### Archiving Products

```php
// Archive a product (sets active = false)
$archivedProduct = Stripe::products()->archive('prod_abc123');

echo $archivedProduct->active; // false

// Archived products:
// - Don't appear in product lists by default
// - Can't be used for new prices or purchases
// - Preserve existing subscriptions and payments
// - Maintain all historical data
```

### Reactivating Products

```php
// Reactivate an archived product
$activeProduct = Stripe::products()->reactivate('prod_abc123');

echo $activeProduct->active; // true

// Reactivated products:
// - Appear in product lists again
// - Can be used for new prices
// - All historical data remains intact
```

### Why Archive Instead of Delete?

```php
// ❌ Don't do this (unless you're absolutely sure)
Stripe::products()->delete('prod_abc123');

// ✅ Do this instead
Stripe::products()->archive('prod_abc123');
```

Reasons to prefer archiving:

1. **Data Integrity**: Existing subscriptions and invoices remain valid
2. **Analytics**: Historical data is preserved for reporting
3. **Reversibility**: Can reactivate if needed
4. **Stripe Best Practice**: Recommended by Stripe for production use

### Filtering by Active Status

```php
// Get only active products (default behavior)
$activeProducts = Stripe::products()->list(['active' => true]);

// Get only archived products
$archivedProducts = Stripe::products()->list(['active' => false]);

// Get all products (active and archived)
$allProducts = Stripe::products()->list();
```

## Testing Product Operations

The library provides testing utilities for all product operations.

### Basic Testing

```php
use EncoreDigitalGroup\Stripe\Stripe;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeFixtures;
use EncoreDigitalGroup\Stripe\Support\Testing\StripeMethod;

test('can create a product', function () {
    $fake = Stripe::fake([
        StripeMethod::ProductsCreate->value => StripeFixtures::product([
            'id' => 'prod_test123',
            'name' => 'Test Product',
            'description' => 'A test product'
        ])
    ]);

    $product = Stripe::products()->create(Stripe::product(
        name: 'Test Product',
        description: 'A test product'
    ));

    expect($product)
        ->toBeInstanceOf(StripeProduct::class)
        ->and($product->id)->toBe('prod_test123')
        ->and($product->name)->toBe('Test Product')
        ->and($fake)->toHaveCalledStripeMethod(StripeMethod::ProductsCreate);
});
```

### Testing with Metadata

```php
test('creates product with metadata', function () {
    $fake = Stripe::fake([
        'products.create' => StripeFixtures::product([
            'id' => 'prod_meta',
            'name' => 'Product with Metadata',
            'metadata' => [
                'category' => 'widgets',
                'sku' => 'WDG-001'
            ]
        ])
    ]);

    $product = Stripe::products()->create(Stripe::product(
        name: 'Product with Metadata',
        metadata: [
            'category' => 'widgets',
            'sku' => 'WDG-001'
        ]
    ));

    expect($product->metadata)
        ->toHaveKey('category', 'widgets')
        ->toHaveKey('sku', 'WDG-001');
});
```

### Testing Archive/Reactivate

```php
test('can archive and reactivate products', function () {
    $fake = Stripe::fake([
        'products.update' => fn($params) => StripeFixtures::product([
            'id' => 'prod_123',
            'active' => $params['active']
        ])
    ]);

    // Archive
    $archived = Stripe::products()->archive('prod_123');
    expect($archived->active)->toBeFalse();

    // Reactivate
    $active = Stripe::products()->reactivate('prod_123');
    expect($active->active)->toBeTrue();

    expect($fake)->toHaveCalledStripeMethodTimes('products.update', 2);
});
```

### Testing Data Cleanup

```php
test('create removes read-only fields from payload', function () {
    $fake = Stripe::fake([
        'products.create' => StripeFixtures::product(['id' => 'prod_new'])
    ]);

    $product = Stripe::product(
        id: 'should_be_removed',
        name: 'Test Product',
        created: 1234567890,
        updated: 1234567890
    );

    Stripe::products()->create($product);

    $params = $fake->getCall('products.create');

    expect($params)
        ->not->toHaveKey('id')
        ->not->toHaveKey('created')
        ->not->toHaveKey('updated')
        ->toHaveKey('name', 'Test Product');
});
```

### Testing Search

```php
test('can search products', function () {
    $fake = Stripe::fake([
        'products.search' => StripeFixtures::productList([
            StripeFixtures::product(['id' => 'prod_1', 'name' => 'Searchable Product'])
        ])
    ]);

    $products = Stripe::products()->search('name:"Searchable Product"');

    expect($products)
        ->toHaveCount(1)
        ->and($products->first()->name)->toBe('Searchable Product')
        ->and($fake)->toHaveCalledStripeMethod('products.search');
});
```

## Common Patterns

Here are some real-world patterns for working with products in Laravel applications.

### Product Catalog Management

```php
class ProductCatalogService
{
    public function __construct()
    {}

    public function createProduct(array $productData): StripeProduct
    {
        return Stripe::products()->create(Stripe::product(
            name: $productData['name'],
            description: $productData['description'],
            active: $productData['active'] ?? true,
            images: $productData['images'] ?? [],
            metadata: [
                'internal_id' => $productData['internal_id'],
                'category' => $productData['category'],
                'created_by' => auth()->id(),
                'created_at' => now()->toISOString()
            ],
            url: $productData['url'] ?? null,
            shippable: $productData['requires_shipping'] ?? false
        ));
    }

    public function syncProductWithDatabase(StripeProduct $stripeProduct): void
    {
        Product::updateOrCreate(
            ['stripe_product_id' => $stripeProduct->id],
            [
                'name' => $stripeProduct->name,
                'description' => $stripeProduct->description,
                'active' => $stripeProduct->active,
                'images' => $stripeProduct->images,
                'metadata' => $stripeProduct->metadata,
                'stripe_updated_at' => $stripeProduct->updated
            ]
        );
    }

    public function getActiveProducts(): Collection
    {
        return Stripe::products()->list(['active' => true]);
    }
}
```

### Product Lifecycle Management

```php
class ProductLifecycleService
{
    public function __construct()
    {}

    public function discontinueProduct(string $productId, string $reason = null): StripeProduct
    {
        // Archive in Stripe
        $product = Stripe::products()->archive($productId);

        // Update local database
        Product::where('stripe_product_id', $productId)->update([
            'status' => 'discontinued',
            'discontinued_at' => now(),
            'discontinuation_reason' => $reason
        ]);

        // Log the action
        logger()->info('Product discontinued', [
            'product_id' => $productId,
            'reason' => $reason,
            'user_id' => auth()->id()
        ]);

        return $product;
    }

    public function relaunchProduct(string $productId): StripeProduct
    {
        // Reactivate in Stripe
        $product = Stripe::products()->reactivate($productId);

        // Update local database
        Product::where('stripe_product_id', $productId)->update([
            'status' => 'active',
            'relaunched_at' => now(),
            'discontinued_at' => null
        ]);

        return $product;
    }

    public function updateProductMetadata(string $productId, array $metadata): StripeProduct
    {
        // Get current product to merge metadata
        $currentProduct = Stripe::products()->get($productId);

        return Stripe::products()->update($productId, Stripe::product(
            metadata: array_merge($currentProduct->metadata ?? [], $metadata)
        ));
    }
}
```

### Search and Analytics

```php
class ProductAnalyticsService
{
    public function __construct()
    {}

    public function getProductsByCategory(string $category): Collection
    {
        return Stripe::products()->search("metadata['category']:'{$category}'");
    }

    public function getRecentProducts(int $days = 30): Collection
    {
        $timestamp = strtotime("-{$days} days");
        return Stripe::products()->list([
            'created' => ['gte' => $timestamp],
            'limit' => 100
        ]);
    }

    public function getProductStatistics(): array
    {
        $allProducts = Stripe::products()->list(['limit' => 100]);

        $stats = [
            'total' => $allProducts->count(),
            'active' => $allProducts->where('active', true)->count(),
            'archived' => $allProducts->where('active', false)->count(),
            'with_images' => $allProducts->filter(fn($p) => !empty($p->images))->count(),
            'shippable' => $allProducts->where('shippable', true)->count()
        ];

        // Category breakdown
        $categoryStats = $allProducts
            ->filter(fn($p) => isset($p->metadata['category']))
            ->groupBy(fn($p) => $p->metadata['category'])
            ->map(fn($group) => $group->count())
            ->sortDesc();

        $stats['categories'] = $categoryStats->toArray();

        return $stats;
    }

    public function findProductsNeedingAttention(): Collection
    {
        $products = Stripe::products()->list(['limit' => 100]);

        return $products->filter(function ($product) {
            // Products without descriptions
            if (empty($product->description)) {
                return true;
            }

            // Products without images
            if (empty($product->images)) {
                return true;
            }

            // Products without proper metadata
            if (empty($product->metadata['category'])) {
                return true;
            }

            return false;
        });
    }
}
```

### Error Handling and Validation

```php
use Stripe\Exception\ApiErrorException;

class SafeProductService
{
    public function createProductSafely(array $productData): ?StripeProduct
    {
        try {
            return Stripe::products()->create(Stripe::product(
                name: $productData['name'],
                description: $productData['description'] ?? null,
                active: $productData['active'] ?? true,
                metadata: $productData['metadata'] ?? []
            ));

        } catch (ApiErrorException $e) {
            logger()->error('Stripe product creation failed', [
                'error' => $e->getMessage(),
                'stripe_error_code' => $e->getStripeCode(),
                'product_data' => $productData
            ]);

            return null;
        }
    }

    public function validateProductData(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Product name is required';
        }

        if (strlen($data['name'] ?? '') > 250) {
            $errors[] = 'Product name must be 250 characters or less';
        }

        if (isset($data['images']) && count($data['images']) > 8) {
            $errors[] = 'Maximum 8 images allowed per product';
        }

        if (isset($data['metadata']) && count($data['metadata']) > 50) {
            $errors[] = 'Maximum 50 metadata keys allowed';
        }

        return $errors;
    }
}
```

## Next Steps

Now that you understand product management, you're ready to explore the pricing layer:

- **[Prices](04-prices.md)** - Master complex pricing including recurring billing, tiers, and usage-based models

Or explore other parts of the system:

- **[Customers](02-customers.md)** - Customer management and relationships
- **[Testing](05-testing.md)** - Comprehensive testing strategies
- **[Architecture](06-architecture.md)** - Deep dive into library design