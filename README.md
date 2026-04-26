# PathIntel.ai — Intelligent Career Guidance

PathIntel.ai is a premium, AI-driven career guidance ecosystem reimagined as a modern research journal. It blends high-performance machine learning with a disciplined, editorial-grade interface to help students navigate the complexities of the modern job market.

## 🌌 Design Philosophy: "Structured Weightlessness"
The platform follows a signature visual language inspired by NASA research journals and modern academic grids:
- **Atmosphere**: Deep Space Navy (#0A0E1A) with Cool Cyan (#00D4FF) accents.
- **Weight**: Elements that float, breathe, and hover within a strict 8pt grid.
- **Typography**: Paired **Cormorant Garamond** (Editorial Headings) with **Sora** (Modern UI) and **DM Mono** (Technical Metadata).

## 🚀 Core Features
- **AI Career Hub**: Advanced resume analysis and personality-based skill assessments powered by Gemini AI and Scikit-learn.
- **Premium Courses Grid**: A high-fidelity, interactive library of learning resources with staggered load animations and "lift" interactions.
- **Intelligent Prediction**: Real-time job probability modeling using `RandomForestRegressor` to match skills with market demand.
- **Knowledge Network**: A curated ecosystem of career paths ranging from AI/ML Engineering to Cyber Security.
- **Unified Auth Suite**: A cohesive, secure login and registration system integrated with the platform's navy/cyan DNA.

## 🛠️ Technology Stack
- **Legacy Foundation**: PHP 8.x for session management, routing, and core portal logic.
- **AI Services**: Python (FastAPI/Flask) handling machine learning inference and LLM integrations.
- **Styling**: Vanilla CSS3 with high-performance transitions and glassmorphism.
- **Icons**: Lucide Iconography for clean, lightweight visuals.
- **Database**: MySQL/MariaDB for structured student data and course tracking.

## 📂 Architecture
- `main.php`: The high-impact landing page and student portal.
- `app.py`: The AI backend service (Career prediction & Resume analysis).
- `courses.php`: The premium editorial course library.
- `contact.php`: approachable "Open Desk" support interface.
- `css/core.css`: The source of truth for the platform's design system.
- `*.pkl`: Pre-trained machine learning models for career path probability.

## 🔧 Installation & Setup

### 🐳 Docker (Recommended)
1. Build and run the entire stack:
   ```bash
   docker-compose up --build
   ```
2. Access the platform at `http://localhost:8080`.

### 💻 Manual Setup
1. **PHP/Web**: Place the repository in your `xampp/htdocs` folder and start Apache/MySQL.
2. **Database**: Import `init.sql` (if provided) or configure your database in `config.php`.
3. **Python AI Hub**:
   ```bash
   pip install -r requirements.txt
   python app.py
   ```
4. Access the portal at `http://localhost/career_guidance/main.php`.

## 📄 License
Internal Project - All Rights Reserved.

---
*Made by Akshat and Tanistha*
