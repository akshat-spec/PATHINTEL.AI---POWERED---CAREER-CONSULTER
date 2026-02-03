<?php
/**
 * Gemini API Configuration
 * Store your API key and settings here
 */

// Gemini API Configuration
define('GEMINI_API_KEY', 'AIzaSyAqkIivstwNE31FKf2ox59m3TWMocckZTM');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent');

// Model Parameters
define('GEMINI_TEMPERATURE', 0.7);  // Creativity level (0.0 - 1.0)
define('GEMINI_MAX_TOKENS', 1000);   // Maximum response length
define('GEMINI_TOP_P', 0.95);
define('GEMINI_TOP_K', 40);

// System Prompt for Career Guidance
define('GEMINI_SYSTEM_PROMPT', 
"You are a helpful career guidance assistant for PathIntel.ai, an engineering career guidance platform. 
Your role is to help users:
- Explore IT and engineering career paths
- Understand job requirements and skills needed
- Get recommendations for courses and learning resources
- Receive career advice and guidance

Be concise, friendly, and professional. Focus on actionable advice. If users ask about careers available on the platform, mention these options:
- AI/ML Specialist
- Data Scientist
- Software Developer
- Cyber Security Specialist
- Database Administrator
- Business Analyst
- Project Manager
- And other IT/Engineering roles

Keep responses under 150 words unless the user needs detailed information.");

// Rate Limiting (optional)
define('RATE_LIMIT_REQUESTS', 20);    // Max requests per session
define('RATE_LIMIT_WINDOW', 3600);    // Time window in seconds (1 hour)
?>
