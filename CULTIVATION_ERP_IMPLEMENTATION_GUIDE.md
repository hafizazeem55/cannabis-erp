# Cultivation ERP Module - Implementation Guide

## Overview
Based on the SRS, User Stories, and MVP documents, the Cultivation ERP module manages cannabis plant growth from seed/clone through all stages until harvest.

## Core Requirements (From SRS Section 3.2)

### Key Features:
1. **Batch Lifecycle Management** - Track batches through stages: Clone/Propagation → Vegetative → Flower → Harvest
2. **Daily Operations Logging** - Record daily activities (watering, pruning, nutrients, notes)
3. **Stage Progression** - Controlled advancement with supervisor approval
4. **Environmental Monitoring** - Auto-trigger deviations when thresholds breached
5. **Batch Transfers** - Move batches between rooms with capacity validation
6. **Harvest Management** - Record harvest data and auto-create material lots

## Database Schema Required

### Core Tables:

1. **strains** - Cannabis strain genetics and profiles
2. **batches** - Cultivation batch records
3. **batch_logs** - Daily operation logs
4. **batch_stage_history** - Stage progression tracking
5. **batch_transfers** - Room transfer records
6. **harvests** - Harvest records
7. **rooms** - Facility rooms (from Admin module)
8. **sensor_readings** - Environmental data (from Admin module)

## Implementation Plan

### Phase 1: Database & Models
- [ ] Create migrations for all cultivation tables
- [ ] Create Eloquent models with relationships
- [ ] Add model factories for testing

### Phase 2: Filament Resources
- [ ] Strain Resource (CRUD)
- [ ] Batch Resource (with lifecycle management)
- [ ] Batch Log Resource (daily logging)
- [ ] Harvest Resource (harvest recording)

### Phase 3: Business Logic
- [ ] Batch code generation (B-{year}-{increment})
- [ ] Stage progression workflow
- [ ] Room capacity validation
- [ ] Environmental deviation triggers
- [ ] Harvest lot creation

### Phase 4: Dashboard & Reporting
- [ ] Cultivation dashboard widgets
- [ ] Batch progress tracking
- [ ] Yield metrics
- [ ] Environmental overview

## User Stories to Implement

### CCMS-ERP-001: Create New Cultivation Batch
- Generate unique batch_code (B-{year}-{increment})
- Link to strain_id and room_id
- Default status = "Clone/Propagation"
- Validate room capacity
- Audit log entry

### CCMS-ERP-002: Log Daily Batch Activities
- Record daily actions (watering, pruning, nutrients, notes)
- Store as JSON in batch_logs
- Auto-pull environmental readings
- Recalculate batch progress

### CCMS-ERP-003: Update Batch Stage Progression
- Move through stages: Clone → Veg → Flower → Harvest
- Require supervisor approval
- Check training compliance
- Update environmental thresholds
- Log in audit trail

### CCMS-ERP-004: Record Environmental Deviations
- Auto-compare sensor data vs thresholds
- Create deviation records
- Calculate severity (Minor <5%, Major >10%)
- Notify QA Manager

### CCMS-ERP-005: Manage Batch Reassignments
- Transfer batches between rooms
- Validate destination capacity
- Log transfers
- Update room utilization

### CCMS-ERP-006: Record Harvest and Generate Lots
- Record harvest date, weights (wet, trim, waste)
- Auto-create lots for Flower, Trim, Waste
- Calculate yield percentage
- Auto-raise low-yield deviations (<85%)
- Set batch status to "Completed"

## Next Steps

Let's start implementing! I'll create:
1. Database migrations
2. Eloquent models
3. Filament Resources
4. Business logic

Ready to proceed?

