import os
from fastapi import FastAPI, File, UploadFile, Form, Request, HTTPException
from fastapi.responses import HTMLResponse, JSONResponse
from fastapi.staticfiles import StaticFiles
from fastapi.templating import Jinja2Templates
from fastapi.middleware.cors import CORSMiddleware
import uvicorn

# Fix for Intel OpenMP DLL conflict (WinError 1114)
os.environ["KMP_DUPLICATE_LIB_OK"] = "TRUE"
# Fix for potential OpenBLAS threading issues on Windows
os.environ["OPENBLAS_MAIN_FREE"] = "1"

import PyPDF2
import docx2txt
import numpy as np
import joblib

# --- IMPORT MODELS ---
from career_model import CareerModel
from job_probability_model import JobProbabilityPredictor

app = FastAPI(title="Career Guidance API (FastAPI)")

# Setup CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# --- CONFIGURATION ---
UPLOAD_FOLDER = 'uploads'
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

# Mount Static Files
if os.path.exists("static"):
    app.mount("/static", StaticFiles(directory="static"), name="static")
if os.path.exists("css"):
    app.mount("/css", StaticFiles(directory="css"), name="css")
if os.path.exists("js"):
    app.mount("/js", StaticFiles(directory="js"), name="js")
if os.path.exists("img"):
    app.mount("/img", StaticFiles(directory="img"), name="img")

templates = Jinja2Templates(directory="templates")

print("--- INITIALIZING SERVER (FastAPI) ---")

# 1. LOAD RESUME MODEL
try:
    print("Loading Resume Model (v2 XGBoost)...")
    resume_model = CareerModel()
    if not os.path.exists('career_model_v2.pkl'):
        print("⚠️ Model v2 artifacts missing. Predictions will use fallback until trained.")
    print("✅ Resume Model OK")
except Exception as e:
    print(f"❌ Error loading Resume Model: {e}")

# 2. LOAD SKILL TEST MODEL
try:
    print("Loading Skill Test Model...")
    skill_model = joblib.load('career_model.pkl')
    label_encoder = joblib.load('label_encoder.pkl')
    print("✅ Skill Test Model OK")
except Exception as e:
    print("⚠️ WARNING: Skill Test files (career_model.pkl) missing.")
    skill_model = None

# 3. LOAD JOB PROBABILITY PREDICTOR
try:
    print("Loading Job Probability Predictor...")
    job_predictor = JobProbabilityPredictor()
    print("✅ Job Probability Predictor OK")
except Exception as e:
    print(f"❌ Error loading Job Predictor: {e}")
    job_predictor = None

# --- HTML ROUTES ---
@app.get("/", response_class=HTMLResponse)
async def home(request: Request):
    """Main Hub (index.html)"""
    return templates.TemplateResponse("index.html", {"request": request})

@app.get("/hometest", response_class=HTMLResponse)
async def hometest(request: Request):
    """Skill Test Quiz"""
    return templates.TemplateResponse("hometest.html", {"request": request})

@app.get("/testafter", response_class=HTMLResponse)
async def testafter(request: Request):
    """Skill Test Results"""
    return templates.TemplateResponse("testafter.html", {"request": request})

@app.get("/job-result", response_class=HTMLResponse)
async def job_result(request: Request):
    """Shows Dream Job Match Results"""
    return templates.TemplateResponse("job_result.html", {"request": request})

@app.get("/result", response_class=HTMLResponse)
async def result(request: Request):
    """Resume Analysis Results"""
    return templates.TemplateResponse("result.html", {"request": request})

# --- API ENDPOINTS ---

def extract_text_from_file(path: str) -> str:
    """Helper to synchronously extract text from PDF or DOCX"""
    text = ""
    if path.endswith('.pdf'):
        with open(path, 'rb') as f:
            reader = PyPDF2.PdfReader(f)
            for page in reader.pages: 
                text += page.extract_text() + " "
    else:
        text = docx2txt.process(path)
    return text

@app.post("/analyze_resume")
async def analyze_resume(file: UploadFile = File(...)):
    """FEATURE 1: Resume Analysis"""
    if not file:
        raise HTTPException(status_code=400, detail="No file uploaded")
    
    path = os.path.join(UPLOAD_FOLDER, file.filename)
    try:
        # Async read and save
        content = await file.read()
        with open(path, "wb") as f:
            f.write(content)
            
        # Extract text
        text = extract_text_from_file(path)
        
        # Run Resume Model
        skills = resume_model.extract_skills(text)
        education = resume_model.extract_education(text)
        predictions = resume_model.predict_career(text)
        
        os.remove(path)
        
        return {
            'success': True,
            'skills': skills,
            'education': education,
            'predictions': predictions
        }
    
    except Exception as e:
        if os.path.exists(path):
            os.remove(path)
        print(f"Server Error in /analyze_resume: {e}")
        return JSONResponse(status_code=500, content={'error': str(e)})


@app.post("/predict")
async def predict_skill(request: Request):
    """FEATURE 2: Skill Test Prediction"""
    if not skill_model: 
        raise HTTPException(status_code=500, detail="Skill model missing")
    
    try:
        data = await request.json()
        responses = data.get('responses')
        
        if not responses or len(responses) != 17:
            raise HTTPException(status_code=400, detail="Invalid inputs")
        
        # Run Skill Test Model
        arr = np.array([responses])
        pred = skill_model.predict(arr)[0]
        role = label_encoder.inverse_transform([pred])[0]
        prob = round(float(max(skill_model.predict_proba(arr)[0])) * 100, 2)
        
        return {'role': str(role), 'confidence': prob}
    
    except Exception as e:
        print(f"Error in /predict: {e}")
        return JSONResponse(status_code=500, content={'error': str(e)})


@app.post("/predict-job-probability")
async def predict_job_probability(targetJob: str = Form(...), file: UploadFile = File(...)):
    """FEATURE 3: Dream Job Probability Prediction"""
    if not job_predictor:
        raise HTTPException(status_code=500, detail="Job Predictor model not loaded")
    
    dream_job = targetJob.strip()
    if not dream_job:
        raise HTTPException(status_code=400, detail="Dream job cannot be empty")
        
    path = os.path.join(UPLOAD_FOLDER, file.filename)
    try:
        content = await file.read()
        with open(path, "wb") as f:
            f.write(content)
            
        text = extract_text_from_file(path)
        
        # Calculate probability using separate model
        result = job_predictor.calculate_job_match(text, dream_job)
        
        # --- SOFT-VOTING ENSEMBLE ---
        career_predictions = resume_model.predict_career(text)
        
        bonus_score = 0
        target_job_lower = dream_job.lower()
        for pred in career_predictions:
            if pred['role'].lower() in target_job_lower or target_job_lower in pred['role'].lower():
                bonus_score = (pred['score'] / 100.0) * 10.0 # Max +10%
                break
        
        final_prob = min(100.0, result.get('probability', 0) + bonus_score)
        result['probability'] = round(final_prob, 2)
        
        os.remove(path)
        
        result['success'] = True
        result['ensemble_bonus'] = round(bonus_score, 2)
        return result
        
    except Exception as e:
        if os.path.exists(path):
            os.remove(path)
        print(f"Server Error in /predict-job-probability: {e}")
        return JSONResponse(status_code=500, content={'error': str(e)})

if __name__ == '__main__':
    print("\n" + "="*50)
    print("🚀 FastAPI Server Running on http://127.0.0.1:5000")
    print("="*50 + "\n")
    uvicorn.run("app:app", host="0.0.0.0", port=5000)
