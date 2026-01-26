# Technical Guidelines

## Architecture
- Multi-tenant Laravel app (company isolation via middleware)
- Queue-based message processing (don't block WhatsApp webhook)
- Event-driven for AI processing pipeline

## Key Patterns
- **Repository pattern** for data access
- **Service classes** for business logic (MessageProcessingService, AIExtractionService)
- **Jobs** for async work (ProcessWhatsAppMessage, ExtractEntryData)
- **Events** for side effects (MessageReceived, EntryCreated, AIConfidenceLow)

## Database Conventions
- Soft deletes for all user-generated content
- Audit trail columns: created_by, updated_by, original_message_id
- Tenant scoping: company_id on all tenant-specific tables

## Testing Requirements
- Feature tests for WhatsApp webhook flow
- Unit tests for AI extraction logic
- Test tenant isolation rigorously

## Security
- Webhook signature verification (WhatsApp)
- Rate limiting on API endpoints
- Tenant data must NEVER leak between companies

## General code instructions

- Don't generate code comments above the methods or code blocks if they are obvious. Don't add docblock comments when defining variables, unless instructed to, like `/** @var \App\Models\User $currentUser */`. Generate comments only for something that needs extra explanation for the reasons why that code was written.

## PHP instructions

- Use strict types where possible
- declare strict type for PHP files if possible (skip blade files)
- In PHP, use `match` operator over `switch` whenever possible
- Use PHP 8 constructor property promotion. Don't create an empty Constructor method if it doesn't have any parameters.
- Using Services in Controllers: if Service class is used only in ONE method of Controller, inject it directly into that method with type-hinting. If Service class is used in MULTIPLE methods of Controller, initialize it in Constructor.
- Use return types in functions whenever possible, adding the full path to classname to the top in `use` section
- Generate Enums always in the folder `app/Enums`, not in the main `app/` folder, unless instructed differently.
- Use DTOs in the folder `app/DataTransferObjects`.
- Use the Repository Pattern in the folder `app/Repositories`.

## Laravel instructions

- Use latest Laravel features and code standards
- For DB pivot tables, use correct alphabetical order, like "project_role" instead of "role_project"
- I am using Laravel Sail locally, so the command is `vendor/bin/sail`
- **Eloquent Observers** should be registered in Eloquent Models with PHP Attributes, and not in AppServiceProvider. Example: `#[ObservedBy([UserObserver::class])]` with `use Illuminate\Database\Eloquent\Attributes\ObservedBy;` on top
- When generating Controllers, put validation in Form Request classes and use `$request->validated()` instead of listing inputs one by one.
- Aim for "slim" Controllers and put larger logic pieces in Action classes
- Use Laravel helpers instead of `use` section classes in blade views and Facades in PHP
- Don't use `whereKey()` or `whereKeyNot()`, use specific fields like `id`. Example: instead of `->whereKeyNot($currentUser->getKey())`, use `->where('id', '!=', $currentUser->id)`.
- Don't add `::query()` when running Eloquent `create()` statements. Example: instead of `User::query()->create()`, use `User::create()`.
- When creating pivot tables in migrations, if you use `timestamps()`, then in Eloquent Models, add `withTimestamps()` to the `BelongsToMany` relationships.
- Do not add the UseFactory to models

## Use Laravel 12+ skeleton structure

- **Service Providers**: there are no other service providers except AppServiceProvider. Don't create new service providers unless absolutely necessary. Use Laravel 12+ new features, instead. Or, if you really need to create a new service provider, register it in `bootstrap/providers.php` and not `config/app.php` like it used to be before Laravel 11.
- **Event Listeners**: since Laravel 11, Listeners auto-listen for the events if they are type-hinted correctly.
- **Console Scheduler**: scheduled commands should be in `routes/console.php` and not `app/Console/Kernel.php` which doesn't exist since Laravel 11.
- **Middleware**: whenever possible, use Middleware by class name in the routes. But if you do need to register Middleware alias, it should be registered in `bootstrap/app.php` and not `app/Http/Kernel.php` which doesn't exist since Laravel 11.
- **Tailwind**: in new Blade pages, use Tailwind and not Bootstrap, unless instructed otherwise in the prompt. Tailwind is already pre-configured since Laravel 11, with Vite.
- **Faker**: in Factories, use `fake()` helper instead of `$this->faker`.
- **Policies**: Laravel automatically auto-discovers Policies, no need to register them in the Service Providers.

## Testing instructions

Every test method should be structured with Arrange-Act-Assert.

In the Arrange phase, use Laravel factories but add meaningful column values and variable names if they help to understand failed tests better.
Bad example: `$user1 = UserFactory()->create();`
Better example: `$adminUser = UserFactory()->create(['email' => 'admin@admin.com'])`;

In the Assert phase, perform these assertions when applicable:
- HTTP status code returned from Act: `assertStatus()`
- Structure/data returned from Act (Blade or JSON): functions like `assertViewHas()`, `assertSee()`, `assertDontSee()` or `assertJsonContains()`
- Or, redirect assertions like `assertRedirect()` and `assertSessionHas()` in case of Flash session values passed
- DB changes if any create/update/delete operation was performed: functions like `assertDatabaseHas()`, `assertDatabaseMissing()`, `expect($variable)->toBe()` and similar.
