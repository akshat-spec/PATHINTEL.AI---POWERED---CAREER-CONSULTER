from flask import Flask, request, jsonify, render_template
from flask_cors import CORS
import os
import PyPDF2
import docx2txt
import numpy as np
import joblib
from werkzeug.utils import secure_filename

# --- IMPORT MODELS ---
from career_model import CareerModel
from job_probability_model import JobProbabilityPredictor  # NEW IMPORT

app = Flask(__name__, template_folder='templates')
CORS(app)

# --- CONFIGURATION ---
UPLOAD_FOLDER = 'uploads'
os.makedirs(UPLOAD_FOLDER, exist_ok=True)
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

print("--- INITIALIZING SERVER ---")

# 1. LOAD RESUME MODEL
try:
    print("Loading Resume Model...")
    resume_model = CareerModel()
    if not os.path.exists('model_artifacts.pkl'):
        resume_model.train_model()
    print("✅ Resume Model OK")
except Exception as e:
    print(f"❌ Error loading Resume Model: {e}")

# 2. LOAD SKILL TEST MODEL
try:
    print("Loading Skill Test Model...")
    skill_model = joblib.load('career_model.pkl')
    label_encoder = joblib.load('label_encoder.pkl')
    print("✅ Skill Test Model OK")
except:
    print("⚠️ WARNING: Skill Test files (career_model.pkl) missing.")
    skill_model = None

# 3. LOAD JOB PROBABILITY PREDICTOR (NEW SEPARATE MODEL)
try:
    print("Loading Job Probability Predictor...")
    job_predictor = JobProbabilityPredictor()
    print("✅ Job Probability Predictor OK")
except Exception as e:
    print(f"❌ Error loading Job Predictor: {e}")
    job_predictor = None

# --- HTML ROUTES ---
@app.route('/')
def home():
    """Main Hub (index.html)"""
    return render_template('index.html')

@app.route('/hometest')
def hometest():
    """Skill Test Quiz"""
    return render_template('hometest.html')

@app.route('/testafter')
def testafter():
    """Skill Test Results"""
    return render_template('testafter.html')

@app.route('/job-result')
def job_result():
    """Shows Dream Job Match Results"""
    return render_template('job_result.html')

@app.route('/result')
def result():
    """Resume Analysis Results"""
    return render_template('result.html')

# --- API ENDPOINTS ---

@app.route('/analyze_resume', methods=['POST'])
def analyze_resume():
    """FEATURE 1: Resume Analysis"""
    if 'file' not in request.files:
        return jsonify({'error': 'No file uploaded'}), 400
    
    file = request.files['file']
    filename = secure_filename(file.filename)
    path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
    file.save(path)
    
    try:
        text = ""
        if path.endswith('.pdf'):
            with open(path, 'rb') as f:
                reader = PyPDF2.PdfReader(f)
                for page in reader.pages: 
                    text += page.extract_text() + " "
        else:
            text = docx2txt.process(path)
        
        # Run Resume Model
        skills = resume_model.extract_skills(text)
        education = resume_model.extract_education(text)
        predictions = resume_model.predict_career(text)
        
        os.remove(path)
        
        return jsonify({
            'success': True,
            'skills': skills,
            'education': education,
            'predictions': predictions
        })
    
    except Exception as e:
        if os.path.exists(path):
            os.remove(path)
        return jsonify({'error': str(e)}), 500


@app.route('/predict', methods=['POST'])
def predict_skill():
    """FEATURE 2: Skill Test Prediction"""
    if not skill_model: 
        return jsonify({'error': 'Skill model missing'}), 500
    
    try:
        data = request.json
        responses = data.get('responses')
        
        if not responses or len(responses) != 17:
            return jsonify({'error': 'Invalid inputs'}), 400
        
        # Run Skill Test Model
        arr = np.array([responses])
        pred = skill_model.predict(arr)[0]
        role = label_encoder.inverse_transform([pred])[0]
        prob = round(max(skill_model.predict_proba(arr)[0]) * 100, 2)
        
        return jsonify({'role': role, 'confidence': prob})
    
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/predict-job-probability', methods=['POST'])
def predict_job_probability():
    """FEATURE 3: Dream Job Probability Prediction (NEW SEPARATE MODEL)"""
    
    if not job_predictor:
        return jsonify({'error': 'Job Predictor model not loaded'}), 500
    
    try:
        # Validate inputs
        if 'file' not in request.files:
            return jsonify({'error': 'No resume file provided'}), 400
        
        if 'targetJob' not in request.form:
            return jsonify({'error': 'No dream job specified'}), 400
        
        file = request.files['file']
        dream_job = request.form['targetJob'].strip()
        
        if not dream_job:
            return jsonify({'error': 'Dream job cannot be empty'}), 400
        
        # Save and parse resume
        filename = secure_filename(file.filename)
        path = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        file.save(path)
        
        # Extract text from resume
        text = ""
        try:
            if path.endswith('.pdf'):
                with open(path, 'rb') as f:
                    reader = PyPDF2.PdfReader(f)
                    for page in reader.pages:
                        text += page.extract_text() + " "
            else:
                text = docx2txt.process(path)
        except Exception as e:
            os.remove(path)
            return jsonify({'error': f'File parsing error: {str(e)}'}), 400
        
        # Calculate probability using separate model
        result = job_predictor.calculate_job_match(text, dream_job)
        
        # Clean up
        os.remove(path)
        
        return jsonify({
            'success': True,
            'probability': result.get('probability', 0),
            'confidence': result.get('confidence', 'Low'),
            'message': result.get('message', ''),
            'matching_skills': result.get('matching_skills', []),
            'missing_skills': result.get('missing_skills', []),
            'user_skills': result.get('user_skills', []),
            'job_required_skills': result.get('job_required_skills', []),
            'skill_recommendations': result.get('skill_recommendations', []),
            'skill_match_percentage': result.get('skill_match_percentage', 0),
            'total_skills_found': result.get('total_skills_found', 0),
            'model_used': result.get('model_used', 'Unknown'),
            'experience_analysis': result.get('experience_analysis', {}),
            'education_analysis': result.get('education_analysis', {}),
            'soft_skills': result.get('soft_skills', []),
            'roadmap': result.get('roadmap', {}),
            'resource_materials': result.get('resource_materials', [])
        })
        
    except Exception as e:
        if 'path' in locals() and os.path.exists(path):
            os.remove(path)
        print(f"Server Error: {e}")
        return jsonify({'error': str(e)}), 500


if __name__ == '__main__':
    print("\n" + "="*50)
    print("🚀 Server Running on http://127.0.0.1:5000")
    print("="*50 + "\n")
    app.run(debug=True, port=5000)
