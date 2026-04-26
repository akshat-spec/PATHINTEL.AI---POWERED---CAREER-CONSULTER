import sys
import os

# Add current directory to path
sys.path.append(os.getcwd())

from career_model import CareerModel
from job_probability_model import JobProbabilityPredictor

def test_models():
    print("--- STARTING VERIFICATION ---")
    
    # 1. Test CareerModel
    print("\nTesting CareerModel (v2 XGBoost)...")
    cm = CareerModel()
    test_text = "I am a Python developer with 5 years of experience in Django and Machine Learning."
    career_results = cm.predict_career(test_text)
    print(f"Career Predictions: {career_results}")
    
    # 2. Test NER/Skills Extraction
    skills = cm.extract_skills(test_text)
    print(f"Extracted Skills: {skills}")
    
    # 3. Test JobProbabilityPredictor
    print("\nTesting JobProbabilityPredictor (v2 XGBoost)...")
    jp = JobProbabilityPredictor()
    match_result = jp.calculate_job_match(test_text, "Senior Backend Developer")
    print(f"Job Match Probability: {match_result['probability']}%")
    print(f"Confidence: {match_result['confidence']}")
    print(f"Model Used: {match_result['model_used']}")

    print("\n--- VERIFICATION COMPLETE ---")

if __name__ == "__main__":
    test_models()
