import pandas as pd
import numpy as np
import pickle
import re
import os
import spacy
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.ensemble import RandomForestClassifier
from sklearn.preprocessing import LabelEncoder

# Load NLP model safely
try:
    nlp = spacy.load("en_core_web_sm")
except OSError:
    print("Downloading language model...")
    from spacy.cli import download
    download("en_core_web_sm")
    nlp = spacy.load("en_core_web_sm")

class CareerModel:
    def __init__(self):
        self.vectorizer = TfidfVectorizer(stop_words='english', max_features=1500)
        self.classifier = RandomForestClassifier(n_estimators=100, random_state=42)
        self.encoder = LabelEncoder()
        self.model_path = 'model_artifacts.pkl'
        
        # Comprehensive Skills List
        self.skills_db = [
            "python", "java", "c++", "c", "html", "css", "javascript", "react", "angular", 
            "node.js", "php", "sql", "mysql", "mongodb", "aws", "docker", "kubernetes",
            "machine learning", "deep learning", "data analysis", "tensorflow", "pytorch",
            "scikit-learn", "pandas", "numpy", "tableau", "power bi", "excel", "git",
            "communication", "leadership", "problem solving", "agile", "scrum", "linux", 
            "devops", "azure", "jenkins", "spark", "hadoop", "flutter", "dart", "android", "ios"
        ]

    def clean_text(self, text):
        text = re.sub('http\S+\s*', ' ', text)  # remove URLs
        text = re.sub('[%s]' % re.escape("""!"#$%&'()*+,-./:;<=>?@[\]^_`{|}~"""), ' ', text)
        text = re.sub(r'[^\x00-\x7f]',r' ', text) 
        text = re.sub('\s+', ' ', text)
        return text.lower()

    def extract_skills(self, text):
        doc = nlp(text.lower())
        extracted_skills = set()
        
        # 1. Check against DB
        for token in doc:
            if token.text in self.skills_db:
                extracted_skills.add(token.text.capitalize())
        
        # 2. Check Noun Chunks
        for chunk in doc.noun_chunks:
            if chunk.text in self.skills_db:
                extracted_skills.add(chunk.text.capitalize())
                
        return list(extracted_skills)

    def extract_education(self, text):
        education = []
        # Common degree patterns
        patterns = [
            r"(?i)(b\.?tech|b\.?e|bachelor of technology|bachelor of engineering)",
            r"(?i)(m\.?tech|m\.?e|master of technology)",
            r"(?i)(bca|mca|bsc|msc|mba|phd|bba)"
        ]
        for pattern in patterns:
            match = re.search(pattern, text)
            if match:
                education.append(match.group(0).upper())
        return list(set(education))

    def train_model(self, csv_path='data/UpdatedResumeDataSet.csv'):
        if not os.path.exists(csv_path):
            print(f"❌ Error: Dataset not found at {csv_path}")
            return False

        print("⏳ Training Model... This may take a few seconds.")
        df = pd.read_csv(csv_path)
        
        # Clean and Prepare
        df['cleaned_resume'] = df['Resume'].apply(lambda x: self.clean_text(x))
        y_encoded = self.encoder.fit_transform(df['Category'])
        X_vectorized = self.vectorizer.fit_transform(df['cleaned_resume'])
        
        # Train
        self.classifier.fit(X_vectorized, y_encoded)
        
        # Save
        with open(self.model_path, 'wb') as f:
            pickle.dump({
                'vectorizer': self.vectorizer,
                'classifier': self.classifier,
                'encoder': self.encoder
            }, f)
        print("✅ Model Trained & Saved!")
        return True

    def predict_career(self, text):
        if not os.path.exists(self.model_path):
            success = self.train_model()
            if not success: return []
            
        with open(self.model_path, 'rb') as f:
            artifacts = pickle.load(f)
        
        cleaned_text = self.clean_text(text)
        vectorized_text = artifacts['vectorizer'].transform([cleaned_text])
        probs = artifacts['classifier'].predict_proba(vectorized_text)[0]
        
        # Top 3 Predictions
        top_indices = np.argsort(probs)[::-1][:3]
        results = []
        for idx in top_indices:
            if probs[idx] > 0.01:
                results.append({
                    "role": artifacts['encoder'].inverse_transform([idx])[0],
                    "score": round(probs[idx] * 100, 1)
                })
        return results
