# Career Guidance and Job Fit Prediction Algorithm

## Executive Summary

This document provides a comprehensive explanation of the algorithms implemented in the Career Guidance System. The system uses multiple machine learning approaches to provide:
1. **Career Path Prediction** - Based on resume analysis
2. **Job Fit Prediction** - Match percentage between user profile and target job
3. **Skill Gap Analysis** - Identification of missing skills and recommendations

---

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Algorithm 1: Career Classification Model](#algorithm-1-career-classification-model)
3. [Algorithm 2: Job Fit Prediction Model](#algorithm-2-job-fit-prediction-model)
4. [Algorithm 3: Skill-Based Assessment Model](#algorithm-3-skill-based-assessment-model)
5. [Feature Engineering](#feature-engineering)
6. [Data Processing Pipeline](#data-processing-pipeline)
7. [Model Training Process](#model-training-process)
8. [Prediction Workflow](#prediction-workflow)
9. [Project Workflow Diagram](file:///c:/SWSetup/xampp/htdocs/career_guidance/PROJECT_WORKFLOW.md)
10. [Ensemble Methods](#ensemble-methods)

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        CAREER GUIDANCE SYSTEM                          │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐ │
│  │   User Input     │    │   User Input     │    │   User Input     │ │
│  │  (Resume File)   │    │  (Skill Test)    │    │ (Job Target)     │ │
│  └────────┬─────────┘    └────────┬─────────┘    └────────┬─────────┘ │
│           │                       │                       │            │
│           ▼                       ▼                       ▼            │
│  ┌─────────────────────────────────────────────────────────────────┐  │
│  │                    Flask API Layer (app.py)                     │  │
│  └─────────────────────────────────────────────────────────────────┘  │
│           │                       │                       │            │
│           ▼                       ▼                       ▼            │
│  ┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐ │
│  │ CareerModel      │    │ Skill Model      │    │ JobProbability   │ │
│  │ (career_model.py)│    │ (career_model.pkl)│  │ Predictor        │ │
│  │                  │    │                   │    │ (job_probability │ │
│  │ • XGBoost        │    │ • Random Forest  │    │  _model.py)      │ │
│  │ • Transformer    │    │ • LabelEncoder   │    │                  │ │
│  │ • spaCy NER     │    │                   │    │ • XGBoost Regr.  │ │
│  └──────────────────┘    └──────────────────┘    │ • Transformer   │ │
│                                                    └──────────────────┘ │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Algorithm 1: Career Classification Model

### Purpose
Predict suitable career paths based on resume content analysis.

### Input
- Resume text (PDF or DOCX format)
- Extracted skills, education, experience

### Processing Steps

#### Step 1: Text Preprocessing
```
python
def clean_text(self, text):
    # Remove URLs
    text = re.sub(r'http\S+\s*', ' ', text)
    # Remove special characters (keep C++, .NET, etc.)
    text = re.sub(r'[^\w\s\+\.#]', ' ', text)
    # Remove non-ASCII characters
    text = re.sub(r'[^\x00-\x7f]', r' ', text)
    # Normalize whitespace
    text = re.sub(r'\s+', ' ', text).strip()
    return text.lower()
```

#### Step 2: Named Entity Recognition (NER)
```
python
def get_ner_entities(self, text):
    entities = {
        "years_experience": 0,
        "technical_skills": set(),
        "education": []
    }
    
    # Extract Experience using regex patterns
    exp_patterns = [
        r'(\d+)\s*(?:\+)?\s*(?:years?|yrs?)\s*(?:of)?\s*experience',
        r'(\d+)\s*(?:\+)?\s*(?:years?|yrs?)\s*(?:in|working)',
    ]
    
    # Extract Skills using hybrid approach:
    # 1. spaCy NER for entity detection
    # 2. Keyword matching against skill database
    
    # Extract Education
    edu_patterns = [
        r"(?i)(b\.?tech|b\.?e|bachelor)",
        r"(?i)(m\.?tech|m\.?e|master)",
        r"(?i)(bca|mca|bsc|msc|mba|phd|bba)"
    ]
```

#### Step 3: Feature Extraction using Sentence Transformers
```
python
# Transform resume text to embeddings
embedding = transformer_model.encode([cleaned_text])
# Model: all-MiniLM-L6-v2 (384-dimensional embeddings)
```

#### Step 4: Classification
```
python
# XGBoost Multi-class Classifier
classifier = XGBClassifier(
    n_estimators=200,
    learning_rate=0.05,
    max_depth=6,
    objective='multi:softprob',
    random_state=42
)

# Get probability distribution over all career paths
probs = classifier.predict_proba(embedding)[0]

# Return top 5 predictions
top_indices = np.argsort(probs)[::-1][:5]
```

### Output
```
json
[
  {"role": "Data Scientist", "score": 85.5},
  {"role": "Machine Learning Engineer", "score": 72.3},
  {"role": "AI Specialist", "score": 68.1},
  {"role": "Data Analyst", "score": 45.2},
  {"role": "Software Developer", "score": 32.8}
]
```

---

## Algorithm 2: Job Fit Prediction Model

### Purpose
Calculate the match probability between a user's resume and a specific target job.

### Input
- Resume text (extracted from file)
- Target job title (e.g., "Senior Python Developer")

### Processing Steps

#### Step 1: Feature Extraction
```
python
def _extract_features(self, resume_text, job_text):
    features = []
    
    # 1. Semantic Similarity (Transformer-based)
    embeddings = transformer.encode([resume_clean, job_clean])
    semantic_sim = cosine_similarity(embeddings[0], embeddings[1])
    
    # 2. Skill Matching
    resume_skills = self._extract_skills(resume_text)
    job_skills = self._extract_skills(job_text)
    skill_overlap = len(set(resume_skills) & set(job_skills))
    skill_match_ratio = skill_overlap / max(len(job_skills), 1)
    
    # 3. Experience Analysis
    exp_years = self._extract_years_experience(resume_text)
    
    # 4. Keyword Density
    resume_words = len(resume_clean.split())
    resume_density = len(resume_skills) / max(resume_words, 1)
    
    # 5. Title/Role Match
    title_match_score = calculate_title_overlap(resume_clean, job_clean)
    
    # 6. Bigram Overlap
    resume_bigrams = set(get_bigrams(resume_clean))
    job_bigrams = set(get_bigrams(job_clean))
    bigram_overlap = len(resume_bigrams & job_bigrams)
    
    return [semantic_sim, skill_match_ratio, skill_overlap, 
            exp_years, resume_density, title_match_score, 
            bigram_overlap, len(resume_skills), len(job_skills)]
```

#### Step 2: Feature Scaling
```
python
scaler = StandardScaler()
X_scaled = scaler.fit_transform(X_features)
```

#### Step 3: Regression Prediction
```
python
# XGBoost Regressor for probability prediction
model = XGBRegressor(
    n_estimators=100,
    learning_rate=0.1,
    max_depth=5,
    objective='reg:squarederror'
)

# Predict match score (0-100)
probability = model.predict(features_scaled)[0]
```

#### Step 4: Confidence Classification
```
python
def get_confidence(probability):
    if probability >= 80:
        return 'High', 'Excellent match!'
    elif probability >= 60:
        return 'Medium-High', 'Good match!'
    elif probability >= 40:
        return 'Medium', 'Decent match'
    else:
        return 'Low', 'Significant gaps'
```

### Output
```
json
{
  "probability": 78.5,
  "confidence": "High",
  "message": "Excellent match! Your profile aligns well.",
  "matching_skills": ["Python", "Django", "SQL", "REST API"],
  "missing_skills": ["AWS", "Docker", "Kubernetes"],
  "skill_match_percentage": 66.7,
  "experience_analysis": {
    "resume_years": 5,
    "job_years_required": 3,
    "status": "Exceeds"
  },
  "roadmap": {
    "total_duration": "6 Months",
    "phases": [...]
  }
}
```

---

## Algorithm 3: Skill-Based Assessment Model

### Purpose
Predict career based on user's self-assessed skill levels across 17 dimensions.

### Input
- 17 skill ratings (Poor, Beginner, Average, Intermediate, Excellent, Not Interested)

### Processing Steps

#### Step 1: Data Encoding
```
python
# Convert categorical responses to numeric
skill_mapping = {
    'Not Interested': 0,
    'Poor': 1,
    'Beginner': 2,
    'Average': 3,
    'Intermediate': 4,
    'Excellent': 5
}

# Apply encoding
encoded_responses = [skill_mapping[r] for r in responses]
```

#### Step 2: Prediction
```
python
# Random Forest Classifier
model = joblib.load('career_model.pkl')
label_encoder = joblib.load('label_encoder.pkl')

prediction = model.predict([encoded_responses])[0]
confidence = model.predict_proba([encoded_responses]).max()

# Decode prediction
predicted_role = label_encoder.inverse_transform([prediction])[0]
```

### Output
```
json
{
  "role": "Data Scientist",
  "confidence": 87.5
}
```

---

## Feature Engineering

### Skill Database
The system maintains a comprehensive skill database with 100+ skills across categories:

```
python
SKILL_CATEGORIES = {
    'programming': ['python', 'java', 'javascript', 'c++', 'c#', 'ruby', 'php'],
    'web': ['html', 'css', 'react', 'angular', 'vue', 'node.js', 'django', 'flask'],
    'data_science': ['machine learning', 'deep learning', 'tensorflow', 'pytorch', 'pandas'],
    'databases': ['sql', 'mysql', 'postgresql', 'mongodb', 'redis'],
    'cloud': ['aws', 'azure', 'gcp', 'docker', 'kubernetes'],
    'soft_skills': ['leadership', 'communication', 'problem solving', 'agile']
}
```

### Feature Vector Composition

| Feature | Type | Description |
|---------|------|-------------|
| Semantic Similarity | Float (0-1) | Cosine similarity between resume and job embeddings |
| Skill Match Ratio | Float (0-1) | Overlap of skills / Total job skills |
| Skill Overlap Count | Integer | Number of matching skills |
| Experience Years | Integer | Years of experience extracted |
| Keyword Density | Float | Skills density in resume |
| Title Match Score | Float | Job title keyword overlap |
| Bigram Overlap | Integer | Common bigrams between texts |
| Resume Skills Count | Integer | Total skills found in resume |
| Job Skills Count | Integer | Total skills required for job |

---

## Data Processing Pipeline

```
┌─────────────────────────────────────────────────────────────────────┐
│                        DATA PROCESSING PIPELINE                    │
└─────────────────────────────────────────────────────────────────────┘

    ┌──────────┐
    │  Raw     │
    │  Resume  │
    └────┬─────┘
         │
         ▼
┌────────────────────────────────┐
│  1. Text Extraction           │
│  • PDF: PyPDF2                │
│  • DOCX: docx2txt            │
└────────────┬───────────────────┘
             │
             ▼
┌────────────────────────────────┐
│  2. Text Cleaning             │
│  • Remove URLs                │
│  • Remove special chars       │
│  • Normalize whitespace       │
│  • Convert to lowercase       │
└────────────┬───────────────────┘
             │
             ▼
┌────────────────────────────────┐
│  3. Entity Extraction         │
│  • Skills (NER + Keyword)    │
│  • Experience (Regex)         │
│  • Education (Regex)          │
└────────────┬───────────────────┘
             │
             ▼
┌────────────────────────────────┐
│  4. Feature Generation        │
│  • Sentence Embeddings        │
│  • Skill Matching             │
│  • Statistical Features       │
└────────────┬───────────────────┘
             │
             ▼
┌────────────────────────────────┐
│  5. Model Inference           │
│  • XGBoost Prediction         │
│  • Probability Calculation    │
│  • Result Aggregation         │
└────────────┬───────────────────┘
             │
             ▼
    ┌──────────┐
    │ Result   │
    │ JSON     │
    └──────────┘
```

---

## Model Training Process

### Training Data

#### Dataset 1: Career Classification
- **Source**: dataset9000.csv
- **Size**: ~9,000 samples
- **Features**: 17 skill dimensions
- **Target**: Career role (24 classes)

#### Dataset 2: Job Match Prediction
- **Source**: job_dataset.csv + Synthetic Generation
- **Size**: ~600 samples
- **Features**: Resume text, Job title, Match score (0-100)
- **Target**: Match score regression

### Training Pipeline

```
python
def train_career_model():
    # 1. Load and preprocess data
    df = pd.read_csv('dataset9000.csv')
    df['cleaned_resume'] = df['Resume'].apply(clean_text)
    
    # 2. Encode labels
    y = encoder.fit_transform(df['Category'])
    
    # 3. Generate embeddings
    X = transformer.encode(df['cleaned_resume'].tolist())
    
    # 4. Train-test split
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2)
    
    # 5. Train XGBoost
    classifier.fit(X_train, y_train, eval_set=[(X_test, y_test)])
    
    # 6. Save model artifacts
    pickle.dump({'classifier': classifier, 'encoder': encoder}, file)
```

---

## Prediction Workflow

### Career Guidance Flow
```
User Uploads Resume
        │
        ▼
Extract Text (PDF/DOCX)
        │
        ▼
Clean & Preprocess
        │
        ▼
Extract Skills, Education, Experience
        │
        ▼
Generate Sentence Embeddings
        │
        ▼
XGBoost Classification
        │
        ▼
Get Top 5 Predictions with Probabilities
        │
        ▼
Return Career Recommendations
```

### Job Match Flow
```
User Provides Resume + Target Job
        │
        ▼
Extract Resume Text
        │
        ▼
Extract Skills (Resume + Job Description)
        │
        ▼
Calculate Feature Vector:
  • Semantic Similarity
  • Skill Match Ratio
  • Experience Match
  • Keyword Overlap
        │
        ▼
XGBoost Regression
        │
        ▼
Calculate Match Probability
        │
        ▼
Generate:
  • Skill Gap Analysis
  • Recommendations
  • Learning Roadmap
  • Resource Materials
        │
        ▼
Return Job Fit Results
```

---

## Ensemble Methods

### Soft-Voting Ensemble
The system combines predictions from multiple models:

```
python
# 1. Get Job Probability Model prediction
job_probability = job_predictor.calculate_job_match(resume_text, dream_job)

# 2. Get Career Classification prediction
career_predictions = career_model.predict_career(resume_text)

# 3. Apply soft-voting bonus
bonus_score = 0
for pred in career_predictions:
    if pred['role'].lower() in dream_job.lower():
        bonus_score = (pred['score'] / 100.0) * 10.0  # Max +10%

# 4. Final probability
final_prob = min(100.0, job_probability + bonus_score)
```

### Fallback Strategy
If ML models fail to load:
```
python
# Rule-based fallback
def _rule_based_prediction(self, resume, job, r_skills, j_skills):
    matched = len(set(r_skills) & set(j_skills))
    total = max(len(j_skills), 1)
    return (matched / total) * 100
```

---

## Algorithm Complexity Analysis

| Operation | Time Complexity | Space Complexity |
|-----------|-----------------|-------------------|
| Text Cleaning | O(n) | O(n) |
| NER Extraction | O(n) | O(n) |
| Sentence Embedding | O(n × d) | O(d) |
| XGBoost Prediction | O(t × d) | O(1) |
| Overall Pipeline | O(n × d) | O(n + d) |

Where:
- n = text length
- d = embedding dimension (384 for all-MiniLM-L6-v2)
- t = number of trees in XGBoost

---

## Model Performance Metrics

### Career Classification
- **Algorithm**: XGBoost Multi-class Classifier
- **Training Data**: 9,000+ samples
- **Classes**: 24 career paths

### Job Match Prediction
- **Algorithm**: XGBoost Regressor
- **Training Data**: 600+ samples (with synthetic augmentation)
- **Metric**: R² Score (typically 0.85+)

---

## Future Improvements

1. **Deep Learning Enhancement**: 
   - Fine-tune BERT/RoBERTa for career classification
   - Implement attention mechanisms for skill importance

2. **Data Augmentation**:
   - Generate more synthetic resume-job pairs
   - Use GPT-based data generation

3. **Multi-modal Analysis**:
   - Include GitHub profile analysis
   - LinkedIn profile integration
   - Project portfolio evaluation

4. **Real-time Learning**:
   - User feedback integration
   - Continuous model updating

5. **Advanced Analytics**:
   - Career progression prediction
   - Salary range estimation
   - Market demand analysis

---

## Technical Stack

| Component | Technology |
|-----------|------------|
| Backend | Flask + Flask-CORS |
| ML Framework | scikit-learn, XGBoost |
| NLP | spaCy, Sentence Transformers |
| Text Processing | PyPDF2, docx2txt |
| Data Processing | Pandas, NumPy |
| Serialization | Pickle, Joblib |

---

## API Endpoints

### 1. Resume Analysis
```
http
POST /analyze_resume
Content-Type: multipart/form-data

Response:
{
  "success": true,
  "skills": ["Python", "Java", "SQL"],
  "education": ["B.Tech", "M.Tech"],
  "predictions": [{"role": "Data Scientist", "score": 85.5}]
}
```

### 2. Skill Test Prediction
```
http
POST /predict
Content-Type: application/json
{"responses": [5, 4, 3, ...]}  // 17 values

Response:
{"role": "Data Scientist", "confidence": 87.5}
```

### 3. Job Match Prediction
```
http
POST /predict-job-probability
Content-Type: multipart/form-data
file: resume.pdf
targetJob: "Senior Python Developer"

Response:
{
  "probability": 78.5,
  "confidence": "High",
  "matching_skills": [...],
  "missing_skills": [...],
  "roadmap": {...}
}
```

---

*Document Version: 1.0*
*Last Updated: 2024*
