
from job_probability_model import JobProbabilityPredictor

predictor = JobProbabilityPredictor(force_retrain=False)

# Test cases with messy text
test_cases = [
    "I have experience in Python/Django and React.js",
    "Skills:Java,Spring,Hibernate",
    "frontend developer(HTML5/CSS3/JavaScript)",
    "Machine Learning-TensorFlow-PyTorch"
]

print("--- Skill Extraction Verification ---")
for text in test_cases:
    skills = predictor._extract_skills(text)
    print(f"\nText: {text}")
    print(f"Extracted: {skills}")
    
print("\n--- Model Prediction Check ---")
res = predictor.calculate_job_match(test_cases[0], "Senior Python Developer")
print(f"Total Skills Found: {res.get('total_skills_found')}")
