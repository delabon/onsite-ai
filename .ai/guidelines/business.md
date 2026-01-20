# Business Domain

## Core Concept
Workers text WhatsApp naturally → AI extracts structured data → Appears in dashboards

## Key Entities & Relationships
- **Companies**: Pay subscription, have multiple projects
- **Projects/Sites**: Construction sites, have many workers and entries
- **Workers**: Send WhatsApp messages, belong to company
- **Entries**: Structured data extracted from messages (incidents, deliveries, progress, delays)
- **Messages**: Raw WhatsApp text + AI processing results

## Business Rules
- One message can create multiple entries (e.g., "2 incidents on site 3")
- Messages must be timestamped and attributed to worker
- All entries must link back to original message (audit trail)
- Site/project identification is critical - AI must extract or ask for clarification
- Material deliveries need: type, quantity, supplier, timestamp
- Incidents need: severity, type, location, people involved

## AI Processing Requirements
- Extract: Site ID, entry type, quantities, timestamps, people
- Handle ambiguity (ask for clarification via WhatsApp)
- Learn from corrections (when admin fixes AI's interpretation)
- Support multiple languages (construction workers speak various languages)

## Pricing/Tenant Rules
- Each company is a tenant (data isolation critical)
- Billing based on: active workers, message volume, project count
- Free trial period before charging
