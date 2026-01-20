# Technical Guidelines

## General
- Use strict types where possible
- Use latest Laravel features and code standards
- declare strict type for PHP files if possible (skip blade files)

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
