# AI Module Integration - Complete Documentation

## 🚀 Overview

A comprehensive AI module has been integrated into your Cannabis Cultivation ERP system with three core capabilities:

1. **Plant Anomaly Detection** - Detects diseases, pests, nutrient issues, and other plant problems from images
2. **Plant Classification** - Classifies growth stages, health status, leaf issues, and strain types
3. **RAG-Powered AI Chatbot** - Answers cultivation questions using your ERP data

---

## 📋 Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Features](#features)
- [API Endpoints](#api-endpoints)
- [Filament Admin UI](#filament-admin-ui)
- [Artisan Commands](#artisan-commands)
- [Permissions](#permissions)
- [Architecture](#architecture)

---

## 🔧 Installation

### Step 1: Run Migrations

```bash
php artisan migrate
```

This creates 4 new tables:
- `ai_anomaly_reports` - Stores plant anomaly detection results
- `ai_classification_results` - Stores plant classification results
- `ai_chat_logs` - Stores chatbot conversations
- `ai_embeddings` - Stores RAG vector embeddings

### Step 2: Configure Environment

Add these variables to your `.env` file:

```env
# AI Provider (openai or local)
AI_PROVIDER=openai

# OpenAI Configuration
OPENAI_API_KEY=your-openai-api-key-here
OPENAI_ORGANIZATION=your-org-id (optional)

# AI Models
AI_MODEL=gpt-4-turbo-preview
AI_VISION_MODEL=gpt-4-vision-preview
AI_EMBEDDING_MODEL=text-embedding-3-large

# AI Features
AI_ANOMALY_DETECTION_ENABLED=true
AI_CLASSIFICATION_ENABLED=true
AI_RAG_ENABLED=true
AI_CHATBOT_ENABLED=true

# Local Provider (if using custom PyTorch endpoints)
LOCAL_ANOMALY_ENDPOINT=http://localhost:5000/detect
LOCAL_CLASSIFICATION_ENDPOINT=http://localhost:5000/classify
```

### Step 3: Seed AI Permissions

```bash
php artisan db:seed --class=AIPermissionsSeeder
```

This creates AI permissions and assigns them to appropriate roles.

### Step 4: Build Knowledge Base

```bash
php artisan ai:build-knowledgebase
```

This generates embeddings from your cultivation data for the RAG chatbot.

---

## ⚙️ Configuration

All AI settings are in `config/ai.php`:

### Provider Configuration
- Switch between OpenAI and local PyTorch providers
- Configure API keys, timeouts, and retry logic

### Model Settings
- Text generation model for chatbot
- Vision model for image analysis
- Embedding model for RAG
- Temperature and token limits

### Anomaly Detection
- Confidence thresholds
- Supported issue types (pests, diseases, nutrient deficiencies, etc.)
- Auto-flagging of critical batches

### Classification
- Classification categories (growth stage, health status, leaf issues)
- Confidence thresholds

### RAG Settings
- Chunk size and overlap
- Top-K results for retrieval
- Data sources to include in knowledge base

### Image Processing
- Max upload size
- Allowed formats
- Storage configuration
- Auto-cleanup settings

---

## 🎯 Features

### 1. Plant Anomaly Detection

**What it does:**
- Analyzes plant images to detect health issues
- Identifies specific problems (pests, diseases, nutrient deficiencies, etc.)
- Provides severity ratings (low, medium, high, critical)
- Recommends corrective actions

**How to use:**
- Upload image via API or Filament UI
- Select associated batch
- AI automatically detects issues and saves report
- Review and mark reports as actioned

**API Example:**
```bash
curl -X POST http://your-domain/api/ai/detect-anomaly \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@plant.jpg" \
  -F "batch_id=1"
```

### 2. Plant Classification

**What it does:**
- Classifies plant growth stage (seedling, vegetative, flowering, etc.)
- Determines health status (healthy, stressed, diseased, dying)
- Identifies leaf issues (yellowing, browning, spotting, etc.)
- Predicts strain type (indica, sativa, hybrid)

**How to use:**
- Upload plant image
- AI returns multi-class predictions with confidence scores
- Results stored for tracking and analytics

**API Example:**
```bash
curl -X POST http://your-domain/api/ai/classify-plant \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "image=@plant.jpg" \
  -F "batch_id=1"
```

### 3. AI Cultivation Chatbot

**What it does:**
- Answers questions about your cultivation data
- Uses RAG (Retrieval Augmented Generation) to fetch relevant context
- References specific batches, strains, rooms, and environmental data
- Provides cultivation best practices and recommendations

**How to use:**
- Access via Filament UI: **AI Tools → AI Cultivation Assistant**
- Ask questions in natural language
- Optionally select a specific batch for context
- Chatbot retrieves relevant data and generates accurate answers

**API Example:**
```bash
curl -X POST http://your-domain/api/ai/chat \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "query": "What is the current status of batch B-2025-0001?",
    "batch_id": 1
  }'
```

**Streaming Example:**
```bash
curl -X POST http://your-domain/api/ai/chat/stream \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "query": "How can I improve yield for indica strains?"
  }'
```

---

## 🔌 API Endpoints

### Anomaly Detection

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/ai/detect-anomaly` | Detect anomalies in image |
| GET | `/api/ai/anomaly/{id}` | Get anomaly report |
| POST | `/api/ai/anomaly/{id}/review` | Mark report as reviewed |
| GET | `/api/ai/batch/{batchId}/anomalies` | List anomalies for batch |
| GET | `/api/ai/batch/{batchId}/anomaly-stats` | Get batch anomaly statistics |

### Plant Classification

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/ai/classify-plant` | Classify plant image |
| GET | `/api/ai/classification/{id}` | Get classification result |
| GET | `/api/ai/batch/{batchId}/classifications` | List classifications for batch |
| GET | `/api/ai/batch/{batchId}/classification-stats` | Get batch classification stats |

### AI Chatbot

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/ai/chat` | Send chat message |
| POST | `/api/ai/chat/stream` | Stream chat response (SSE) |
| GET | `/api/ai/chat/history` | Get conversation history |
| POST | `/api/ai/chat/{chatId}/feedback` | Provide feedback on response |
| GET | `/api/ai/chat/stats` | Get user chat statistics |

---

## 🖥️ Filament Admin UI

### AI Tools Menu Group

The AI module adds a new "**AI Tools**" navigation group with:

1. **AI Anomaly Reports**
   - View all anomaly detection reports
   - Upload images for detection
   - Review and approve reports
   - Filter by severity, batch, review status

2. **AI Cultivation Assistant**
   - Interactive chat interface
   - Select specific batches for context
   - View conversation history
   - Real-time AI responses

### AI Dashboard Widget

The AI Stats Widget shows:
- Total AI scans performed
- Critical anomalies requiring attention
- Unhealthy plants detected
- Knowledge base size

### Batch Resource Integration

Added to existing Batch Resource:
- "Detect Anomaly" button on batch view
- Quick access to batch anomaly reports
- Batch-specific AI insights

---

## 🛠️ Artisan Commands

### Build Knowledge Base

```bash
php artisan ai:build-knowledgebase

# Options:
--source=batches,strains       # Specific sources to build
--rebuild                       # Rebuild all embeddings
--batch-size=10                 # Items per batch
```

**What it does:**
- Extracts text from cultivation data
- Generates vector embeddings using AI
- Stores in `ai_embeddings` table
- Enables RAG for chatbot

**Data sources processed:**
- Batches (batch codes, strain, status, dates)
- Strains (genetics, characteristics, requirements)
- Batch logs (daily activities, environmental data)
- Environmental readings
- Harvests (yield data, quality notes)
- Rooms (capacities, parameters)
- Facilities
- Growth cycles

**Run regularly:**
- After adding new batches
- After significant data updates
- Schedule weekly: `0 2 * * 0` (Sundays at 2 AM)

---

## 🔐 Permissions

### AI Permissions Created

| Permission | Description | Default Roles |
|------------|-------------|---------------|
| `ai.use` | Access AI features | Administrator, QA Manager, Cultivation Supervisor/Operator |
| `ai.detect.anomaly` | Use anomaly detection | Administrator, QA Manager, Cultivation roles |
| `ai.classify.plant` | Use plant classification | Administrator, QA Manager, Cultivation roles |
| `ai.chat` | Use AI chatbot | Administrator, all cultivation and management roles |
| `ai.manage` | Manage AI settings | Administrator only |

### Checking Permissions in Code

```php
// Check if user can use AI
if (auth()->user()->can('ai.use')) {
    // Allow access
}

// In Filament Resources
public static function canViewAny(): bool
{
    return auth()->user()->can('ai.use') 
        || auth()->user()->hasRole('Administrator');
}
```

---

## 🏗️ Architecture

### Service Layer

```
app/Services/AI/
├── AIProviderInterface.php          # Provider contract
├── OpenAIProvider.php                # OpenAI implementation
├── LocalVisionProvider.php          # Local PyTorch endpoint
├── PlantAnomalyDetectionService.php # Anomaly detection logic
├── PlantClassificationService.php   # Classification logic
├── CultivationChatbotService.php    # Chatbot with RAG
└── RetrieverService.php              # RAG retrieval logic
```

### Models

```
app/Models/
├── AiAnomalyReport.php
├── AiClassificationResult.php
├── AiChatLog.php
└── AiEmbedding.php
```

### Controllers

```
app/Http/Controllers/AI/
├── AnomalyDetectionController.php
├── ClassificationController.php
└── ChatbotController.php
```

### Filament Resources

```
app/Filament/
├── Resources/
│   └── AiAnomalyReportResource.php
├── Pages/
│   └── AICultivationAssistant.php
└── Widgets/
    └── AIStatsWidget.php
```

---

## 🔄 RAG (Retrieval Augmented Generation)

### How It Works

1. **Building Knowledge Base:**
   - Extracts text from ERP data
   - Chunks text into manageable pieces
   - Generates embeddings using `text-embedding-3-large`
   - Stores in `ai_embeddings` table

2. **Query Processing:**
   - User asks question
   - Question converted to embedding
   - Cosine similarity search finds relevant chunks
   - Top-K most relevant chunks retrieved

3. **Response Generation:**
   - Retrieved chunks added to prompt as context
   - Question + context sent to GPT-4
   - AI generates accurate, data-driven answer
   - Response logged for analytics

### Improving RAG Accuracy

- Keep knowledge base updated (run `ai:build-knowledgebase` regularly)
- Adjust `ai.rag.top_k_results` for more/less context
- Modify `ai.rag.similarity_threshold` to filter irrelevant chunks
- Enable/disable specific data sources in `config/ai.php`

---

## 📊 Usage Examples

### Example 1: Detecting Pest Infestation

```php
use App\Services\AI\PlantAnomalyDetectionService;

$service = app(PlantAnomalyDetectionService::class);

$report = $service->detectAnomaly(
    imagePath: 'storage/plants/plant-123.jpg',
    batchId: 5,
    roomId: 2,
    userId: auth()->id()
);

// Result:
// $report->is_anomaly = true
// $report->detected_issue = "pest_infestation"
// $report->confidence = 0.94
// $report->severity = "high"
// $report->recommended_action = "Immediately isolate affected plants..."
```

### Example 2: Asking Chatbot About Batch

```php
use App\Services\AI\CultivationChatbotService;

$chatbot = app(CultivationChatbotService::class);

$chatLog = $chatbot->chat(
    query: "What were the environmental conditions for batch B-2025-0001 last week?",
    userId: auth()->id(),
    batchId: 1
);

// AI retrieves:
// - Batch logs from past week
// - Environmental readings
// - Room parameters
// Generates comprehensive answer with specific data points
```

### Example 3: Batch Anomaly Statistics

```php
$stats = $service->getBatchAnomalyStats(batchId: 1);

// Returns:
// [
//     'total_reports' => 15,
//     'anomalies_detected' => 3,
//     'by_severity' => [
//         'critical' => 1,
//         'high' => 2,
//         'medium' => 0,
//         'low' => 0
//     ],
//     'unreviewed' => 2,
//     'avg_confidence' => 0.87
// ]
```

---

## 🧪 Testing

### Manual Testing

1. **Anomaly Detection:**
   - Upload plant image with visible issues
   - Verify AI detects problem correctly
   - Check confidence scores and recommendations

2. **Classification:**
   - Upload images at different growth stages
   - Verify correct stage classification
   - Test with healthy vs. unhealthy plants

3. **Chatbot:**
   - Ask about specific batches
   - Query environmental data
   - Request cultivation advice
   - Verify responses reference actual data

### Automated Tests

Create tests in `tests/Feature/`:

```bash
php artisan make:test AI/AnomalyDetectionTest
php artisan make:test AI/ClassificationTest
php artisan make:test AI/ChatbotTest
```

---

## 🚨 Troubleshooting

### API Key Issues
**Problem:** "OpenAI provider is not available"
**Solution:** Check `OPENAI_API_KEY` in `.env`

### Knowledge Base Empty
**Problem:** Chatbot gives generic answers
**Solution:** Run `php artisan ai:build-knowledgebase`

### Image Upload Fails
**Problem:** Image too large or wrong format
**Solution:** Check `ai.images.max_size_mb` and `ai.images.allowed_formats`

### Slow Performance
**Problem:** AI requests timeout
**Solution:** 
- Increase `ai.openai.timeout`
- Use smaller images
- Enable caching: `ai.cache.enabled`

### Permission Denied
**Problem:** Users can't access AI features
**Solution:** Run `php artisan db:seed --class=AIPermissionsSeeder`

---

## 📈 Performance Optimization

### Caching
Enable Redis caching for faster responses:
```env
AI_CACHE_ENABLED=true
AI_CACHE_DRIVER=redis
AI_CACHE_TTL=3600
```

### Rate Limiting
Protect API from abuse:
```env
AI_RATE_LIMITING_ENABLED=true
AI_MAX_REQUESTS_PER_MINUTE=30
```

### Image Optimization
- Compress images before upload
- Use recommended formats (JPEG, PNG, WebP)
- Enable auto-cleanup: `AI_IMAGE_CLEANUP_DAYS=90`

### Vector Database
For production, consider using a dedicated vector database:
- **Pinecone** - Cloud vector database
- **Weaviate** - Open-source vector search
- **pgvector** - PostgreSQL extension

---

## 🔮 Future Enhancements

### Planned Features
- [ ] Bulk image processing
- [ ] Real-time environmental anomaly detection
- [ ] Predictive yield modeling
- [ ] Disease spread tracking
- [ ] Automated action workflows
- [ ] Multi-language support
- [ ] Voice interaction
- [ ] Mobile app integration

### Custom Training
- Fine-tune models on your specific strains
- Train custom classification models
- Optimize for local growing conditions

---

## 📞 Support

For questions or issues:
- Check logs: `storage/logs/laravel.log`
- Review config: `config/ai.php`
- Run diagnostics: `php artisan ai:build-knowledgebase --dry-run`

---

## 📝 License

This AI module is part of the Cannabis Cultivation ERP system and follows the same licensing terms.

---

**🎉 You're all set! The AI module is ready to enhance your cultivation operations.**
