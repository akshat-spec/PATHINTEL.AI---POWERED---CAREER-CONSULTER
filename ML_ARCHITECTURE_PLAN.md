# Career Guidance & Job Fit System: Production Architecture Plan

This document outlines the technical execution plan for upgrading your Career Guidance and Job Fit Prediction System from a prototype to a production-ready, scalable service. It covers ML accuracy improvements, explainable AI enhancements, backend refactoring, and advanced entity extraction.

---

## 1. ML Accuracy & Data Augmentation

### Feature Weighting: Beyond Simple "Skill Match Ratios"
A simple overlap percentage treats all skills equally. To improve accuracy, you should introduce **Semantic Skill Weighting** and **Category-Based Importance**.

**Strategy:**
1.  **Differentiate "Hard" vs. "Soft" Skills**: Give hard technical skills (e.g., "Python", "Docker") a higher weight multiplier (e.g., 1.5x) compared to soft skills (e.g., "Communication" - 0.8x) for technical roles.
2.  **TF-IDF Inspired Weighting**: Instead of boolean matches, weight skills based on their rarity in the job market. Common skills ("Microsoft Word") get lower weights than rare, specialized ones ("CUDA").
3.  **Semantic Similarity**: Don't rely on exact keyword matches. Use your `all-MiniLM-L6-v2` embeddings to compute Cosine Similarity between the *Resume Skills Vector* and *Job Requirements Vector*.

```python
import numpy as np
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity

# Assuming loaded model
# model = SentenceTransformer('all-MiniLM-L6-v2')

def compute_weighted_semantic_match(resume_skills: list, job_skills: dict, model: SentenceTransformer):
    """
    job_skills dict format: {'Skill': weight} e.g., {'Python': 1.5, 'Communication': 0.8}
    """
    if not resume_skills or not job_skills:
        return 0.0
        
    resume_embeddings = model.encode(resume_skills)
    job_skill_names = list(job_skills.keys())
    job_embeddings = model.encode(job_skill_names)
    job_weights = np.array(list(job_skills.values()))
    
    # Compute similarity matrix (len(resume_skills) x len(job_skills))
    similarity_matrix = cosine_similarity(resume_embeddings, job_embeddings)
    
    # Best semantic match for each job skill
    best_matches = np.max(similarity_matrix, axis=0)
    
    # Apply weights
    weighted_score = np.sum(best_matches * job_weights) / np.sum(job_weights)
    
    # Constrain between 0 and 1
    return min(max(weighted_score, 0.0), 1.0)
```

### Data Augmentation: Handling the 600-Sample Bottleneck
With only 600 samples, your Regressor will likely overfit. I recommend a two-pronged approach:

1.  **Tabular SMOTE (Synthetic Minority Over-sampling Technique)**: Use SMOTE for Regressors (SMOGN or SmoteR) to balance the distribution of your target variable (Job Fit Score).
2.  **LLM-Driven Synthetic Resume Generation (GPT-4o)**: Generate entirely new resume-job pair permutations dynamically using an LLM.

```python
import openai
import json

client = openai.OpenAI(api_key="YOUR_API_KEY")

def generate_synthetic_pair(base_job_title, target_fit_score):
    """
    Use GPT-4o to generate a synthetic resume tailored to yield a specific fit score 
    for a given job role.
    """
    prompt = f"""
    You are an expert HR data generator. Generate a synthetic Resume Profile and a Job Description for a {base_job_title}. 
    The Resume should be tailored so that a strict HR Recruiter would rate their 'Job Fit Score' as exactly {target_fit_score} out of 100.
    
    Output strictly in JSON:
    {{
        "resume_text": "...",
        "job_description": "...",
        "expected_fit_score": {target_fit_score}
    }}
    """
    
    response = client.chat.completions.create(
        model="gpt-4o",
        messages=[{"role": "user", "content": prompt}],
        response_format={"type": "json_object"}
    )
    
    return json.loads(response.choices[0].message.content)
```

---

## 2. Enhanced Dashboard Analytics (Explainable AI)

### Feature Attribution with SHAP
To tell the user *why* they got their score, we extract Shapley values from the XGBoost model.

```python
import shap
import xgboost as xgb
import pandas as pd

# Assume xgb_model is your trained XGBoost Job Fit Regressor
# X_user is a single-row DataFrame containing the user's features

def generate_shap_explanation(xgb_model, X_user, feature_names):
    explainer = shap.TreeExplainer(xgb_model)
    shap_values = explainer.shap_values(X_user)
    
    # Map values to feature names
    contributions = dict(zip(feature_names, shap_values[0]))
    base_value = explainer.expected_value
    
    # Sort contributions by magnitude
    sorted_contributions = sorted(contributions.items(), key=lambda item: abs(item[1]), reverse=True)
    
    explanation_text = f"Starting from a baseline score of {base_value:.1f}%, "
    positives, negatives = [], []
    
    for feat, impact in sorted_contributions[:3]: # Top 3 most impactful features
        if impact > 0:
            positives.append(f"increased by {impact:.1f}% due to {feat}")
        else:
            negatives.append(f"dropped {abs(impact):.1f}% due to {feat}")
            
    explanation_text += ", ".join(positives)
    if negatives:
        explanation_text += f", but {', '.join(negatives)}."
        
    return explanation_text, contributions
```

### Dynamic Skill Gap Analysis
```python
def generate_priority_learning_list(resume_skills: set, job_skills: set, job_skill_weights: dict):
    # What skills are in the job but missing from the resume?
    missing_skills = job_skills - resume_skills
    
    # Sort missing skills by their importance/weight in the job description
    priority_list = sorted(list(missing_skills), key=lambda x: job_skill_weights.get(x, 1.0), reverse=True)
    
    return priority_list[:5] # Top 5 skills to learn
```

### Confidence Calibration (ECE)
To ensure that when the model says "80% confidence", there is an actual 80% probability of success, use Isotonic Regression.

```python
from sklearn.calibration import CalibratedClassifierCV
# Assume `base_clf` is your trained Career Classifier (Random Forest or XGBoost)

# Wrap your existing model in a calibrator. 
# cv="prefit" means you train base_clf on your training set, and calibrate on a validation set.
calibrated_clf = CalibratedClassifierCV(base_estimator=base_clf, method='isotonic', cv='prefit')

# Fit on validation data (X_val, y_val)
calibrated_clf.fit(X_val, y_val)

# Now, probabilities are actual calibrated confidences
calibrated_probs = calibrated_clf.predict_proba(X_user)
confidence = max(calibrated_probs[0])
```

---

## 3. Production-Ready Backend Refactoring

### Migration: Flask to FastAPI
FastAPI natively supports `async`/`await`, which is critical for non-blocking I/O when processing large PDFs or computing embeddings.

**Migration Roadmap:**
1.  Replace `@app.route` with `@app.post` / `@app.get`.
2.  Use Pydantic models for request/response validation instead of `request.json`.
3.  Make PDF parsing and database I/O `async`.

*Example Router:*
```python
from fastapi import FastAPI, File, UploadFile, BackgroundTasks
from pydantic import BaseModel

app = FastAPI(title="Job Fit Engine")

class PredictionResponse(BaseModel):
    fit_score: float
    explanation: str

@app.post("/predict_fit", response_model=PredictionResponse)
async def predict_fit(resume: UploadFile = File(...)):
    # Async read file
    content = await resume.read()
    
    # Offload heavy CPU bound tasks (embeddings/inference) to a threadpool/background task
    # or process immediately if lightweight
    parsed_text = extract_text_from_pdf(content) 
    
    # Model inference logic...
    score = 85.5
    
    return {"fit_score": score, "explanation": "Good match!"}
```

### Inference Scaling: MiniLM-L6-v2 Embeddings
Inference is CPU/GPU intensive. Do not run it inline in the FastAPI request loop for scale.

**Architecture Recommendation:**
1.  **Task Queue**: Use **Celery + Redis** (or RQ). When a user uploads a resume, FastAPI returns a `task_id`. Celery picks up the task, parses the text, generates the 384-d embeddings, runs XGBoost, and saves the result to the DB. The frontend polls or uses WebSockets to get the result.
2.  **Vector Database**: Instead of re-embedding job descriptions every time, pre-compute the 384-d embeddings for all jobs and store them in **Pinecone** (if cloud-hosted) or **Qdrant / Weaviate** (if self-hosted). When a resume comes in, embed the resume *once*, and perform an Approximate Nearest Neighbor (ANN) search against the vector DB to find top job matches in milliseconds.

---

## 4. Advanced Entity Extraction: Years of Experience (YoE)

Basic regex fails on "five years of proven experience in React". Here's a custom spaCy Pipeline Component that utilizes dependency parsing to link temporal entities to skills or aggregate them.

```python
import spacy
from spacy.language import Language
from spacy.tokens import Doc, Span
from word2number import w2n

nlp = spacy.load("en_core_web_sm")

# Define the custom extension property
if not Doc.has_extension("total_yoe"):
    Doc.set_extension("total_yoe", default=0.0)

@Language.component("extract_yoe")
def extract_yoe_component(doc):
    total_years = 0.0
    
    # Look for patterns like "X years of experience" or "worked for X years"
    for token in doc:
        if token.lemma_ == "year":
            # Check for modifying numbers (e.g., "5", "five")
            number_token = None
            for child in token.children:
                if child.pos_ == "NUM":
                    number_token = child
                    break
            
            if number_token:
                try:
                    # Convert "five" to 5, or "5" to 5
                    years = float(w2n.word_to_num(number_token.text))
                    
                    # Verify context - is "experience" or a job-related noun nearby?
                    # Check window of 5 tokens ahead and behind
                    start = max(0, token.i - 5)
                    end = min(len(doc), token.i + 6)
                    context = doc[start:end].text.lower()
                    
                    if any(kw in context for kw in ["experience", "worked", "developer", "engineer", "professional"]):
                        total_years += years
                except ValueError:
                    pass # Couldn't parse number
                
    doc._.total_yoe = total_years
    return doc

# Add pipeline component
nlp.add_pipe("extract_yoe", after="ner")

# Test it
text = "I am a senior frontend engineer with five years of professional experience. Previously, I worked for 3 years at Google."
doc = nlp(text)

print(f"Extracted Total Years of Experience: {doc._.total_yoe}") 
# Expected Output: Extracted Total Years of Experience: 8.0
```

### Next Steps
Let me know which of these sections you'd like to implement first! If you're ready, I can help you migrate the current `app.py` to FastAPI or start laying down the SHAP explanation logic.
