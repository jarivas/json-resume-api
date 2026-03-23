# MCP Logging Guide

## Overview

The Chat Service has been updated to:
1. **Inform the AI model** about available MCP (Model Context Protocol) servers
2. **Log all MCP activity** to track which servers are being accessed

## MCP Servers Available

The following 12 MCP servers are available to the chat agent:

- **Award Server** - Access awards and recognitions
- **Basic Server** - Access basic profile information (name, summary, contact)
- **Certificate Server** - Access professional certificates and certifications
- **Education Server** - Access educational background and qualifications
- **Interest Server** - Access personal interests and hobbies
- **Language Server** - Access language proficiencies
- **Project Server** - Access portfolio projects and their details
- **Publication Server** - Access published works and research
- **Reference Server** - Access professional references
- **Skill Server** - Access technical and professional skills
- **Volunteer Server** - Access voluntary work and community service
- **Work Server** - Access professional work experience

## How Logging Works

### 1. Agent Creation Log
```
Creating ResumeAgent with MCP capabilities
- Lists all available MCP servers
- Shows the AI provider being used
```

Example:
```json
{
  "provider": "gemini",
  "mcp_servers": [
    "award", "basic", "certificate", "education", "interest", "language",
    "project", "publication", "reference", "skill", "volunteer", "work"
  ]
}
```

### 2. Message Received Log
```
Chat service received message
- Logs the incoming user message (truncated and masked for privacy)
- Tracks session ID if provided
- Shows the AI provider
```

### 3. Prompt Sending Log
```
Chat service sending prompt to MCP-enabled agent
- Shows that the agent has MCP capabilities enabled
- Lists all available MCP servers
- Indicates whether the agent has tools configured
```

### 4. MCP Activity Detection Log
```
MCP activity detected in chat
- Lists probable MCP servers being used based on the message/response content
- Analyzes keywords to determine which MCP resources were likely accessed
```

Example:
```json
{
  "probable_mcp_servers_used": ["work", "project", "skill"],
  "total_mcp_servers_available": 12,
  "server_list": [
    "award", "basic", "certificate", "education", "interest", "language",
    "project", "publication", "reference", "skill", "volunteer", "work"
  ]
}
```

### 5. Response Log
```
Chat service successful response
- Logs the final response to the user
- Tracks token usage if available
- Triggers MCP activity detection
```

## Monitoring MCP Usage

### View Logs in Real-Time

```bash
# Watch logs as they happen
php artisan pail

# Filter for MCP-specific logs only
php artisan pail --filter="MCP activity detected"

# Filter for chat service logs
php artisan pail --filter="Chat service"
```

### View Logs in Log File

```bash
# View the last 50 log entries
tail -50 storage/logs/laravel.log

# Search for MCP activity
grep "MCP activity detected" storage/logs/laravel.log

# Search for chat service logs
grep "Chat service" storage/logs/laravel.log
```

### Query Logs from PHP

```php
// Using tinker
php artisan tinker

// Get recent chat logs
\Illuminate\Support\Facades\Log::channel('stack')->getLogger();

// Or query the database if using database logging
DB::table('logs')->where('channel', 'stack')
  ->where('message', 'like', '%MCP%')
  ->orderBy('created_at', 'desc')
  ->limit(50)
  ->get();
```

## Keyword Mapping for MCP Detection

The system uses keyword analysis to detect which MCP servers are likely being used:

- **work** - "work", "experience", "job", "position", "empresa", "empleo"
- **skill** - "skill", "habilidad", "technical", "proficiency", "competencia"
- **education** - "education", "educación", "degree", "university", "school"
- **project** - "project", "proyecto", "portfolio"
- **award** - "award", "recognition", "premio"
- **basic** - "name", "summary", "contact", "email", "phone", "location"
- **certificate** - "certificate", "certificado", "certification"
- **interest** - "interest", "interés", "hobby", "passion"
- **language** - "language", "idioma", "speak"
- **volunteer** - "volunteer", "voluntario", "community"
- **publication** - "publication", "publicación", "published"
- **reference** - "reference", "referencia", "recommend"

## Understanding Log Levels

- **INFO** - Successful operations, normal flow (message received, response sent)
- **DEBUG** - Detailed tracking (agent creation, MCP activity detection)
- **WARNING** - Issues that were handled (query validation failed, fallback used)
- **ERROR** - Failures that need attention (rate limited, provider errors)

## Example Log Flow

When a user asks "Tell me about my work experience":

1. **INFO: Chat service received message**
   - Session ID: null
   - Message preview: "Tell me about my work experience"
   - Provider: gemini

2. **DEBUG: Creating ResumeAgent with MCP capabilities**
   - Lists 12 available MCP servers

3. **DEBUG: Chat service sending prompt to MCP-enabled agent**
   - Lists all available MCP servers

4. **INFO: Chat service successful response**
   - Shows the beginning of the response
   - Token usage (if available)

5. **DEBUG: MCP activity detected in chat**
   - Probable MCP servers: ["work", "basic", "project"]
   - Shows these were likely used based on keywords

## Improving MCP Utilization

To make the AI model better use MCP resources:

1. **Add more MCP tools** - Currently tools are not being passed to the agent
   - Modify the ResumeAgent constructor to pass actual tool implementations
   - See `/app/routes/ai.php` for configured MCP servers

2. **Update instructions** - The instructions in ChatService now mention MCP availability
   - Located in `createAgent()` method
   - Add specific examples of what each MCP server provides

3. **Monitor usage patterns** - Use the logging to identify which MCP servers are most/least used
   - Adjust instructions if certain servers are underutilized

## Database Schema

If logs are stored in the database, they are available in the `ai_requests` table:

```php
Schema::create('ai_requests', function (Blueprint $table) {
    $table->id();
    $table->string('session_id')->nullable();
    $table->string('provider')->nullable();
    $table->longText('prompt')->nullable();
    $table->longText('message');
    $table->longText('reply');
    $table->json('metadata')->nullable();
    $table->timestamps();
});
```

Query usage statistics:
```php
// Count requests by provider
AiRequest::groupBy('provider')->select('provider', DB::raw('count(*) as total'))->get();

// Find requests that likely used specific MCP servers
AiRequest::where('reply', 'like', '%work%')->get();
```
