# User Management

Manage user accounts and registration.

## Get Current User

Retrieve information about the authenticated user:

```php
$user = $shipit->user()->me();

// Returns UserResponseData
echo $user->id;
echo $user->name;
echo $user->email;
echo $user->phone;
echo $user->role;
```

## Register New Merchant

Create a merchant account and receive API credentials:

```php
use Cline\Shipit\Data\RegistrationRequestData;

$registration = $shipit->user()->register(
    RegistrationRequestData::from([
        'name' => 'Company Ltd',
        'email' => 'ops@company.com',
        'phone' => '+358401234567',
        'address' => 'Street 1',
        'postcode' => '00100',
        'city' => 'Helsinki',
        'country' => 'FI',
        'isCompany' => true,
        'contactPerson' => 'John Doe',
        'businessId' => '1234567-8',
        'subscribeNewsletter' => false,
    ])
);

// Returns RegistrationResponseData with credentials.key / credentials.secret
if ($registration->hasCredentials()) {
    echo $registration->credentials->key;
    echo $registration->credentials->secret;
}
```

## Store Merchant Credentials

```php
class MerchantRegistrar
{
    public function __construct(
        private ShipitConnector $shipit
    ) {}

    /**
     * @return array{0: string, 1: string} API key and secret
     */
    public function registerMerchant(array $merchantData): array
    {
        $registration = $this->shipit->user()->register(
            RegistrationRequestData::from($merchantData)
        );

        if ($registration->hasError() || !$registration->hasCredentials()) {
            throw new RuntimeException('Shipit merchant registration failed.');
        }

        return [
            $registration->credentials->key,
            $registration->credentials->secret,
        ];
    }
}
```

## User Profile

Display user profile information:

```php
$user = $shipit->user()->me();

echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Phone: {$user->phone}\n";
echo "Role: {$user->role}\n";

// Organization information
if ($user->organization) {
    echo "Organization: {$user->organization->name}\n";
    echo "Business ID: {$user->organization->businessId}\n";
}
```

## User Roles and Permissions

Different user roles have different permissions:

```php
$user = $shipit->user()->me();

switch ($user->role) {
    case 'admin':
        // Full access to all features
        echo "Admin user - full access";
        break;

    case 'member':
        // Can create shipments and view reports
        echo "Member - can create shipments";
        break;

    case 'viewer':
        // Read-only access
        echo "Viewer - read-only access";
        break;
}
```

## Next Steps

- [Balance & Accounting](./10-balance-accounting.md)
- [Advanced Features](./11-advanced-features.md)
