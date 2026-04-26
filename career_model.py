import pandas as pd
import numpy as np
import pickle
import re
import os
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split

# Lazy loading flags
SPACY_AVAILABLE = False
TRANSFORMER_AVAILABLE = False
nlp = None

def load_spacy():
    global nlp, SPACY_AVAILABLE
    if not SPACY_AVAILABLE:
        try:
            import spacy
            from spacy.language import Language
            from spacy.tokens import Doc
            try:
                from word2number import w2n
            except ImportError:
                w2n = None
                
            nlp = spacy.load("en_core_web_sm")
            
            if not Doc.has_extension("total_yoe"):
                Doc.set_extension("total_yoe", default=0.0)
                
            @Language.component("extract_yoe")
            def extract_yoe_component(doc):
                total_years = 0.0
                for token in doc:
                    if token.lemma_ == "year":
                        number_token = None
                        for child in token.children:
                            if child.pos_ == "NUM":
                                number_token = child
                                break
                        if number_token:
                            try:
                                if w2n:
                                    years = float(w2n.word_to_num(number_token.text))
                                else:
                                    years = float(number_token.text)
                                start = max(0, token.i - 5)
                                end = min(len(doc), token.i + 6)
                                context = doc[start:end].text.lower()
                                if any(kw in context for kw in ["experience", "worked", "developer", "engineer", "professional"]):
                                    total_years += years
                            except Exception:
                                pass
                doc._.total_yoe = total_years
                return doc
                
            nlp.add_pipe("extract_yoe", after="ner")
            SPACY_AVAILABLE = True
            print("✅ spaCy loaded successfully with custom extract_yoe component")
        except Exception as e:
            print(f"⚠️ Warning: spaCy failed to load ({e}). Using fallback keyword matching.")
            SPACY_AVAILABLE = False
    return nlp

class CareerModel:
    def __init__(self):
        self.encoder = LabelEncoder()
        self.model_path = 'career_model_v2.pkl'
        self.embedding_model = 'all-MiniLM-L6-v2'
        self._transformer = None
        
        # XGBoost import (Lazy)
        try:
            from xgboost import XGBClassifier
            self.classifier = XGBClassifier(
                n_estimators=200,
                learning_rate=0.05,
                max_depth=6,
                objective='multi:softprob',
                random_state=42
            )
        except ImportError:
            from sklearn.ensemble import RandomForestClassifier
            self.classifier = RandomForestClassifier(n_estimators=100, random_state=42)
            print("⚠️ Warning: XGBoost not available, falling back to RandomForest.")
        
        # Comprehensive Skills List
        self.skills_db = [
            "python", "java", "c++", "c", "html", "css", "javascript", "react", "angular", 
            "node.js", "php", "sql", "mysql", "mongodb", "aws", "docker", "kubernetes",
            "machine learning", "deep learning", "data analysis", "tensorflow", "pytorch",
            "scikit-learn", "pandas", "numpy", "tableau", "power bi", "excel", "git",
            "communication", "leadership", "problem solving", "agile", "scrum", "linux", 
            "devops", "azure", "jenkins", "spark", "hadoop", "flutter", "dart", "android", "ios"
        ]

    @property
    def transformer(self):
        global TRANSFORMER_AVAILABLE
        if self._transformer is None:
            try:
                from sentence_transformers import SentenceTransformer
                print("⏳ Loading Transformer Model...")
                self._transformer = SentenceTransformer(self.embedding_model)
                TRANSFORMER_AVAILABLE = True
                print("✅ Transformer loaded successfully")
            except Exception as e:
                print(f"⚠️ Warning: Transformer failed to load ({e}). Careers will be predicted using zero-vectors/fallback if needed.")
                TRANSFORMER_AVAILABLE = False
        return self._transformer

    def clean_text(self, text):
        """Advanced cleaning of Resume/Docx text"""
        if not text: return ""
        # Remove URLs
        text = re.sub(r'http\S+\s*', ' ', text)
        # Remove special characters but keep important ones for tech (e.g., C++, .NET)
        text = re.sub(r'[^\w\s\+\.#]', ' ', text)
        # Remove non-ascii
        text = re.sub(r'[^\x00-\x7f]', r' ', text)
        # Normalize whitespace
        text = re.sub(r'\s+', ' ', text).strip()
        return text.lower()

    def get_ner_entities(self, text):
        """Extract 'Years of Experience' and 'Skills' using NER/Rule-based hybrid"""
        entities = {
            "years_experience": 0,
            "technical_skills": set(),
            "education": []
        }
        
        text_lower = text.lower()
        
        # 1. Extract Experience
        exp_patterns = [
            r'(\d+)\s*(?:\+)?\s*(?:years?|yrs?)\s*(?:of)?\s*experience',
            r'(\d+)\s*(?:\+)?\s*(?:years?|yrs?)\s*(?:in|working)',
            r'experience\s*of\s*(\d+)\s*(?:\+)?\s*(?:years?|yrs?)'
        ]
        for pattern in exp_patterns:
            matches = re.findall(pattern, text_lower)
            if matches:
                entities["years_experience"] = max(entities["years_experience"], int(matches[0]))

        # 2. Extract Skills (Hybrid: NER + Keyword matching)
        spacy_nlp = load_spacy()
        if SPACY_AVAILABLE and spacy_nlp:
            try:
                doc = spacy_nlp(text)
                
                # Check for YoE extracted from custom component
                if hasattr(doc._, 'total_yoe') and doc._.total_yoe > 0:
                    entities["years_experience"] = max(entities["years_experience"], int(doc._.total_yoe))
                    
                for ent in doc.ents:
                    if ent.label_ in ["PRODUCT", "ORG", "WORK_OF_ART"]:
                        if ent.text.lower() in self.skills_db:
                            entities["technical_skills"].add(ent.text.capitalize())
            except Exception as e:
                print(f"⚠️ NER Error: {e}")
        
        # Fallback keyword matching
        for skill in self.skills_db:
            if re.search(r'\b' + re.escape(skill) + r'\b', text_lower):
                entities["technical_skills"].add(skill.capitalize())

        # 3. Extract Education
        edu_patterns = [
            r"(?i)(b\.?tech|b\.?e|bachelor of technology|bachelor of engineering)",
            r"(?i)(m\.?tech|m\.?e|master of technology)",
            r"(?i)(bca|mca|bsc|msc|mba|phd|bba)"
        ]
        for pattern in edu_patterns:
            match = re.search(pattern, text)
            if match:
                entities["education"].append(match.group(0).upper())
                
        entities["technical_skills"] = list(entities["technical_skills"])
        entities["education"] = list(set(entities["education"]))
        return entities

    def train_model(self, csv_path='dataset9000.csv'):
        if not os.path.exists(csv_path):
            print(f"❌ Error: Dataset not found at {csv_path}")
            return False

        print("⏳ Preparing Dataset for XGBoost...")
        df = pd.read_csv(csv_path)
        
        # Check if 'Category' and 'Resume' columns exist
        if 'Category' not in df.columns or 'Resume' not in df.columns:
            # Try dataset9000 specific columns if different
            target_col = 'Category' if 'Category' in df.columns else df.columns[0]
            text_col = 'Resume' if 'Resume' in df.columns else df.columns[1]
        else:
            target_col = 'Category'
            text_col = 'Resume'

        df['cleaned_resume'] = df[text_col].apply(lambda x: self.clean_text(str(x)))
        y_encoded = self.encoder.fit_transform(df[target_col])
        
        print(f"⏳ Generating Embeddings with {self.embedding_model}...")
        trans = self.transformer
        if trans is not None and hasattr(trans, 'encode'):
            try:
                X_embeddings = trans.encode(df['cleaned_resume'].tolist(), show_progress_bar=True)
            except Exception as e:
                print(f"🛑 Error during encoding: {e}")
                return False
        else:
            print("🛑 Fatal Error: Transformer model failed to load or has no 'encode' method.")
            return False
        
        # Train-Test Split
        X_train, X_test, y_train, y_test = train_test_split(X_embeddings, y_encoded, test_size=0.2, random_state=42)
        
        print("⏳ Training XGBoost Classifier...")
        self.classifier.fit(X_train, y_train, eval_set=[(X_test, y_test)], verbose=False)
        
        from sklearn.calibration import CalibratedClassifierCV
        try:
            print("⏳ Calibrating Model with Isotonic Regression (ECE)...")
            self.calibrator = CalibratedClassifierCV(estimator=self.classifier, method='isotonic', cv='prefit')
            self.calibrator.fit(X_test, y_test)
            self.use_calibrator = True
        except Exception as e:
            print(f"⚠️ Calibration failed: {e}")
            self.use_calibrator = False
            self.calibrator = None
            
        # Save
        print(f"✅ Saving Model to {self.model_path}...")
        with open(self.model_path, 'wb') as f:
            pickle.dump({
                'classifier': self.classifier,
                'encoder': self.encoder,
                'embedding_model': self.embedding_model,
                'calibrator': getattr(self, 'calibrator', None),
                'use_calibrator': getattr(self, 'use_calibrator', False)
            }, f)
        return True

    def predict_career(self, text):
        if not os.path.exists(self.model_path):
            success = self.train_model()
            if not success: return []
            
        with open(self.model_path, 'rb') as f:
            artifacts = pickle.load(f)
        
        cleaned_text = self.clean_text(text)
        
        # Handle Transformer Availability
        trans = self.transformer
        if trans is not None and hasattr(trans, 'encode'):
            try:
                embedding = trans.encode([cleaned_text])
                
                # Use Calibrator if available
                if artifacts.get('use_calibrator', False) and artifacts.get('calibrator') is not None:
                    probs = artifacts['calibrator'].predict_proba(embedding)[0]
                else:
                    probs = artifacts['classifier'].predict_proba(embedding)[0]
            except Exception as e:
                print(f"⚠️ Inference Error: {e}. Falling back to error role.")
                return [{"role": "Inference Error (Fallback)", "score": 0.0}]
        else:
            print("🛑 Error: Transformer unavailable or invalid. Using fallback.")
            return [{"role": "System Loading/Error", "score": 0.0}]
        
        # Top 5 Predictions for broader range
        top_indices = np.argsort(probs)[::-1][:5]
        results = []
        for idx in top_indices:
            if probs[idx] > 0.01:
                results.append({
                    "role": artifacts['encoder'].inverse_transform([idx])[0],
                    "score": round(float(probs[idx]) * 100, 1)
                })
        return results

    def extract_skills(self, text):
        """Wrapper for backward compatibility"""
        entities = self.get_ner_entities(text)
        return entities["technical_skills"]

    def extract_education(self, text):
        """Wrapper for backward compatibility"""
        entities = self.get_ner_entities(text)
        return entities["education"]
