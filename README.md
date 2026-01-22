# Onsite AI - Laravel WhatsApp Message Classification

A Laravel application for construction site management with AI-powered WhatsApp message classification and automated workflow routing.

## Features

- **WhatsApp Integration**: Process incoming WhatsApp messages from construction workers
- **AI Classification**: Automatically categorize messages using Ollama LLM (Safety Incidents, Material Requests, Questions, Site Notes, etc.)
- **Workflow Automation**: Route messages to appropriate teams (supervisors, procurement, AI agents)
- **Admin Panel**: Filament-based admin interface for managing users and workflows
- **Modern Stack**: Laravel 12, Livewire, Flux UI, PostgreSQL, Redis

## Prerequisites

- Docker & Docker Compose
- PHP 8.5+
- Composer
- Node.js & npm
- Git

## Quick Setup

1. **Clone the repository**
   ```bash
   git clone git@github.com:delabon/onsite-ai.git
   cd onsite-ai
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Start Docker containers**
   ```bash
   ./vendor/bin/sail up --build -d
   ```

6. **Pull Ollama model for message classification**
   ```bash
   ./vendor/bin/sail exec laravel.test ollama pull llama3.2:latest
   ```

7. **Run database migrations**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

8. **Install and build frontend assets**
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run build
   ```

9. **Run tests (optional)**
   ```bash
   ./vendor/bin/sail artisan test
   ```

Your application should now be available at `http://localhost` (or your configured APP_PORT).

## Detailed Setup

### Environment Configuration

After copying `.env.example` to `.env`, configure the following key variables:

```env
# Application
APP_NAME="Onsite AI"
APP_ENV=local
APP_KEY=base64:your-generated-key
APP_DEBUG=true
APP_URL=http://localhost

# Database (PostgreSQL via Sail)
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (Mailpit for development)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

# Ollama Configuration
OLLAMA_URL=http://ollama:11434
OLLAMA_MODEL=llama3.2:latest
OLLAMA_TIMEOUT=30
OLLAMA_TEMPERATURE=0.1
OLLAMA_RESPONSE_LENGTH=50
```

### Sail Commands

Laravel Sail provides convenient commands for common operations:

```bash
# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail stop

# Access container shell
./vendor/bin/sail shell

# Run artisan commands
./vendor/bin/sail artisan <command>

# Run npm commands
./vendor/bin/sail npm <command>

# Run tests
./vendor/bin/sail artisan test

# View logs
./vendor/bin/sail logs
```

### WhatsApp Integration Setup

1. **Configure Webhook URL**: Point your WhatsApp provider to `https://yourdomain.com/webhooks/whatsapp`

2. **Webhook Signature Validation**: Implement signature validation in `WebhookController` based on your WhatsApp provider's documentation

3. **Message Processing**: Messages are processed asynchronously via the `whatsapp` queue. Ensure your queue worker is running:

   ```bash
   ./vendor/bin/sail artisan queue:work --queue=whatsapp
   ```

## Development

### Running the Development Server

```bash
./vendor/bin/sail npm run dev
```

This starts the Vite development server with hot reloading.

### Code Quality

```bash
# Lint code
./vendor/bin/sail pint

# Run tests
./vendor/bin/sail artisan test
```

### Database Management

```bash
# Create migration
./vendor/bin/sail artisan make:migration create_example_table

# Run migrations
./vendor/bin/sail artisan migrate

# Create seeder
./vendor/bin/sail artisan make:seeder ExampleSeeder

# Seed database
./vendor/bin/sail artisan db:seed
```

## Troubleshooting

### Common Issues

- **Ollama model not found**: Ensure you've pulled the model with `ollama pull llama3.2:latest`
- **Database connection failed**: Check that PostgreSQL container is running and credentials are correct
- **Assets not loading**: Run `npm run build` or `npm run dev` for development
- **Queue not processing**: Ensure queue worker is running with `./vendor/bin/sail artisan queue:work`

## License

This project is licensed under the MIT License.
