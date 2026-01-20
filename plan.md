# Business Model - Simple Breakdown

**The Problem:**
Construction workers on site need to record what's happening - incidents, progress updates, delays, materials delivered, etc. But they're busy, dirty, wearing gloves, and don't want to fill out forms or open special apps.

**The Solution:**
Workers just text WhatsApp like they're messaging a coworker. An AI assistant understands what they're saying, extracts the important info, and logs everything automatically.

**Example:**
- Worker texts: "Concrete delivered to site 3, 5 trucks, driver was late"
- AI captures: Site ID, material type, quantity, timestamp, notes issue
- Data appears in company's project management system automatically

## The Technology in Simple Terms

```
Worker's Phone (WhatsApp)
         ↓
    AI Brain (reads message, understands intent)
         ↓
    Database (stores structured data)
         ↓
    Admin Dashboard (managers see everything organized)
         ↓
    Other Tools (connects to existing software)
```
# MVP Todo List

## Phase 1: Core Foundation (Week 1-2)

### Database & Models
- [x] Set up Laravel 12 project with Sail
- [ ] Create database schema:
    - [ ] `organizations` table (multi-tenant)
    - [ ] `users` table (field workers)
    - [ ] `projects` table (construction sites)
    - [ ] `site_events` table (logged incidents/notes)
    - [ ] `whatsapp_messages` table (message history)
    - [ ] `ai_conversations` table (conversation threads)
- [ ] Create Eloquent models with relationships
- [ ] Set up multi-tenancy (likely using `organization_id` scope)
- [ ] Write Pest tests for models and relationships

## Database Schema

### 1. `organizations` Table
Multi-tenant foundation:
```sql
- id (bigint, primary key)
- name (varchar, company name)
- email (varchar, unique)
- phone (varchar, nullable)
- address (jsonb, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### 2. Extend `users` Table
Add organization and role fields:
```sql
- organization_id (bigint, foreign key to organizations.id)
- phone (varchar, WhatsApp phone number)
- role (enum: admin, manager, worker)
- created_at (timestamp)
- updated_at (timestamp)
```

### 3. `projects` Table
Construction sites:
```sql
- id (bigint, primary key)
- organization_id (bigint, foreign key)
- name (varchar, project name)
- description (text, nullable)
- site_address (text)
- status (enum: planning, active, completed, on_hold)
- start_date (date)
- end_date (date, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### 4. `site_events` Table
Core table for logged incidents:
```sql
- id (bigint, primary key)
- organization_id (bigint, foreign key)
- project_id (bigint, foreign key)
- user_id (bigint, foreign key, who reported it)
- event_type (enum: material_delivery, incident, progress_update, delay, safety_issue, note)
- title (varchar, AI-generated summary)
- description (text, full details)
- ai_extracted_data (json, structured data from AI)
- severity (enum: low, medium, high, critical)
- status (enum: open, in_progress, resolved, closed)
- occurred_at (timestamp, when the event happened)
- created_at (timestamp)
- updated_at (timestamp)
```

### 5. `whatsapp_messages` Table
Message history:
```sql
- id (bigint, primary key)
- organization_id (bigint, foreign key)
- conversation_id (varchar, groups related messages)
- user_id (bigint, foreign key, nullable for unknown numbers)
- message_id (varchar, WhatsApp message ID, unique)
- direction (enum: inbound, outbound)
- message_type (enum: text, image, audio, document)
- content (text, message content)
- media_url (varchar, nullable)
- whatsapp_message_status (enum: sent, delivered, read, failed)
- ai_processed_at (timestamp, nullable)
- received_at (timestamp)
- created_at (timestamp)
- updated_at (timestamp)
```

### 6. `ai_conversations` Table
Conversation threads:
```sql
- id (bigint, primary key)
- organization_id (bigint, foreign key)
- user_id (bigint, foreign key)
- conversation_id (varchar, unique identifier)
- status (enum: active, archived, closed)
- last_message_at (timestamp)
- message_count (integer, default 0)
- created_at (timestamp)
- updated_at (timestamp)
```

## Key Relationships
- `organizations` → `users` (one-to-many)
- `organizations` → `projects` (one-to-many)
- `organizations` → `site_events` (one-to-many)
- `organizations` → `whatsapp_messages` (one-to-many)
- `organizations` → `ai_conversations` (one-to-many)
- `projects` → `site_events` (one-to-many)
- `users` → `site_events` (one-to-many)
- `users` → `whatsapp_messages` (one-to-many)
- `ai_conversations` → `whatsapp_messages` (one-to-many)

### Authentication & Authorization
- [ ] Implement Filament authentication
- [ ] Create user roles (Admin, Manager, Worker)
- [ ] Set up Laravel Policies for access control
- [ ] Test multi-tenant data isolation

## Phase 2: WhatsApp Integration (Week 2-3)

### WhatsApp Business API Setup
- [ ] Register WhatsApp Business account
- [ ] Set up webhook endpoint (`/webhooks/whatsapp`)
- [ ] Create WhatsApp service class using Saloon
- [ ] Handle incoming message webhook
- [ ] Handle message status updates
- [ ] Implement message sending functionality
- [ ] Write Pest tests for webhook handlers

### Message Processing Pipeline
- [ ] Create queue job: `ProcessIncomingWhatsAppMessage`
- [ ] Implement message validation
- [ ] Store incoming messages in database
- [ ] Create conversation threading logic
- [ ] Handle different message types (text, image, audio)
- [ ] Write comprehensive Pest tests

## Phase 3: AI Agent Development (Week 3-4)

### AI Integration
- [ ] Set up Anthropic/OpenRouter API client
- [ ] Create base AI service class
- [ ] Implement conversation context management
- [ ] Create prompt templates for:
    - [ ] Site event logging
    - [ ] Information extraction
    - [ ] Intent classification

### AI Tools (Function Calling)
- [ ] Tool: `log_site_event` - Record incident/note
- [ ] Tool: `get_project_info` - Retrieve project details
- [ ] Tool: `list_projects` - Show available projects
- [ ] Tool: `create_note` - Save general note
- [ ] Implement tool execution handler
- [ ] Write Pest tests for each tool

### AI Response Processing
- [ ] Parse AI tool calls
- [ ] Execute tools and get results
- [ ] Format responses for WhatsApp
- [ ] Handle errors gracefully
- [ ] Test full conversation flows

## Phase 4: Admin Panel (Week 4-5)

### Filament Resources
- [ ] **Organization Resource:**
    - [ ] List organizations
    - [ ] Create/edit organization
    - [ ] View subscription status

- [ ] **User Resource:**
    - [ ] List users (scoped to organization)
    - [ ] Create/edit users
    - [ ] Assign roles
    - [ ] View user activity

- [ ] **Project Resource:**
    - [ ] List projects
    - [ ] Create/edit projects
    - [ ] Assign workers to projects
    - [ ] View project timeline

- [ ] **Site Events Resource:**
    - [ ] List all events (filterable)
    - [ ] View event details
    - [ ] Edit/annotate events
    - [ ] Export events (CSV/PDF)
    - [ ] Search and filter (date, project, type, worker)

### Custom Filament Pages
- [ ] Dashboard with key metrics:
    - [ ] Active projects count
    - [ ] Events logged today/week
    - [ ] Active workers
    - [ ] Recent activity feed
- [ ] Conversation history viewer
- [ ] Analytics page (basic charts using Filament widgets)

### Filament Actions
- [ ] Bulk actions for site events (export, categorize)
- [ ] Send WhatsApp message to worker
- [ ] Archive/unarchive projects

## Phase 5: Core Features (Week 5-6)

### Livewire Components
- [ ] Real-time event feed component
- [ ] Conversation viewer component
- [ ] Project selector component
- [ ] Worker status indicator

### Background Jobs
- [ ] `SendWhatsAppMessage` - Queue outbound messages
- [ ] `ProcessAIResponse` - Handle AI completions
- [ ] `SyncProjectData` - Keep data fresh
- [ ] `GenerateDailyReport` - Automated summaries
- [ ] Write Pest tests for all jobs

### Notifications
- [ ] Laravel notification system setup
- [ ] Event logged notification
- [ ] Worker joined project notification
- [ ] Daily summary notification (email)

## Phase 6: Testing & Polish (Week 6-7)

### Comprehensive Testing
- [ ] Feature tests for complete workflows:
    - [ ] Worker sends message → AI processes → Event created
    - [ ] Admin views events in panel
    - [ ] Multi-tenant isolation works
- [ ] Browser tests (Pest with Dusk) for critical paths
- [ ] API integration tests
- [ ] Load testing for WhatsApp webhook

### Data & Validation
- [ ] Form requests for all inputs
- [ ] Validation rules for AI-extracted data
- [ ] Error handling and logging
- [ ] Rate limiting on webhooks

### Documentation
- [ ] README with setup instructions
- [ ] API documentation (for future integrations)
- [ ] User guide for admins
- [ ] Worker onboarding flow (in WhatsApp)

## Phase 7: Deployment & Monitoring (Week 7-8)

### Infrastructure
- [ ] Set up production environment (Laravel Forge/Vapor)
- [ ] Configure queues (Redis/database)
- [ ] Set up scheduled tasks (cron jobs)
- [ ] Configure logging (Laravel Log or external)
- [ ] Set up error tracking (Sentry/Flare)

### Security
- [ ] Webhook signature verification (WhatsApp)
- [ ] API rate limiting
- [ ] CSRF protection
- [ ] SQL injection prevention (Eloquent does this)
- [ ] XSS prevention (Blade/Livewire does this)

### Monitoring
- [ ] Health check endpoint
- [ ] Queue monitoring
- [ ] AI API usage tracking
- [ ] WhatsApp message volume tracking
- [ ] Set up alerts for failures

## MVP Feature Checklist

### Must-Have for Launch
- [x] Worker can send WhatsApp message
- [x] AI understands and logs site events
- [x] Events appear in admin panel
- [x] Multi-tenant (multiple companies can use it)
- [x] Basic authentication and roles
- [x] Conversation history visible

### Nice-to-Have (Post-MVP)
- [ ] Image recognition (worker sends photo, AI describes it)
- [ ] Voice message transcription
- [ ] Integration with project management tools
- [ ] Advanced analytics dashboard
- [ ] Mobile-responsive admin panel
- [ ] Scheduled reports
- [ ] Custom event types per organization

## Success Metrics for MVP

**Technical:**
- Message processing < 3 seconds
- 99% AI classification accuracy
- Zero data leaks between tenants
- Test coverage for critical features

**Business:**
- 3-5 pilot construction companies signed up
- Workers sending 10+ messages per day
- Admins logging in 2+ times per week
- Positive feedback from field workers

---

## Estimated Timeline

**Realistic MVP:** 6-8 weeks with one senior developer
**With AI assistance (Claude Code):** 4-6 weeks
**Aggressive timeline:** 3-4 weeks (cutting testing/polish)

## Key Success Factors

1. **Start with WhatsApp + AI working well** - This is your core value
2. **Keep admin panel simple** - Don't over-engineer Filament resources
3. **Test multi-tenancy early** - Data isolation is critical
4. **Use AI to write tests** - Maintain velocity while keeping quality high
5. **Real construction company feedback ASAP** - Build what they need, not what you think they need
