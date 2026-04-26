"""
Enhanced Dataset-Based Job Probability Predictor - v2.0
- Optimized TF-IDF Vectorization
- Random Forest Regression
- Synthetic Data Augmentation
"""

import re
import numpy as np
import pandas as pd
import re
import numpy as np
import pandas as pd
import pickle
import os
import random
from sklearn.model_selection import train_test_split
from sklearn.preprocessing import StandardScaler

# Lazy loading flags
SPACY_AVAILABLE = False
TRANSFORMER_AVAILABLE = False
XGBOOST_AVAILABLE = False
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
        except Exception as e:
            print(f"⚠️ Warning: spaCy failed to load in Job Predictor ({e})")
            SPACY_AVAILABLE = False
    return nlp


class JobProbabilityPredictor:
    """
    Advanced ML-based job probability predictor with synthetic data augmentation
    """
    
    def __init__(self, force_retrain=False):
        self.model = None
        self.scaler = None
        self.model_path = 'job_probability_model_v2.pkl'
        self.dataset_path = 'job_dataset.csv'
        self.embedding_model = 'all-MiniLM-L6-v2'
        self._transformer = None
        
        # XGBoost (Lazy)
        global XGBOOST_AVAILABLE
        self.XGBRegressor = None
        try:
            from xgboost import XGBRegressor
            XGBOOST_AVAILABLE = True
            self.XGBRegressor = XGBRegressor
        except ImportError:
            XGBOOST_AVAILABLE = False
            print("⚠️ XGBoost not available for Job Predictor. Falling back to rule-based.")
        
        # Comprehensive skill database
        self.all_skills = self._load_skill_database()
        
        # --- CAREER ARCHITECT DATA ---
        self.resource_db = self._load_resource_database()
        self.roadmap_templates = self._load_roadmap_templates()
        
        # Auto-load or fallback
        if os.path.exists(self.model_path) and not force_retrain:
            print("Loading pre-trained job probability model v2...")
            if not self.load_model():
                print("⚠️ Model load failed. Using rule-based fallback.")
        else:
            print("⚠️ No valid model found. Predictions will use rule-based fallback until trained.")

    @property
    def transformer(self):
        global TRANSFORMER_AVAILABLE
        if self._transformer is None:
            try:
                from sentence_transformers import SentenceTransformer, util
                self.util = util
                print("⏳ Loading Transformer Model (Job Predictor)...")
                self._transformer = SentenceTransformer(self.embedding_model)
                TRANSFORMER_AVAILABLE = True
            except Exception as e:
                print(f"⚠️ Warning: Transformer failed to load in Job Predictor ({e})")
                TRANSFORMER_AVAILABLE = False
        return self._transformer
    
    def _load_skill_database(self):
        """Load comprehensive skill list"""
        skills = [
            # Programming
            'python', 'java', 'javascript', 'c++', 'c#', 'ruby', 'php', 'swift', 'kotlin',
            'go', 'golang', 'rust', 'typescript', 'scala', 'r programming', 'dart', 'assembly',
            # Web
            'html', 'html5', 'css', 'css3', 'react', 'angular', 'vue', 'vue.js', 'node.js',
            'express', 'django', 'flask', 'fastapi', 'spring boot', 'asp.net', 'laravel',
            'next.js', 'nuxt', 'redux', 'graphql', 'rest api', 'webpack', 'babel', 'tailwind',
            # Mobile
            'android', 'ios', 'flutter', 'react native', 'swift', 'kotlin', 'xamarin', 'ionic',
            # Data Science
            'machine learning', 'deep learning', 'data science', 'data analysis', 'statistics',
            'pandas', 'numpy', 'scipy', 'matplotlib', 'seaborn', 'tensorflow', 'pytorch',
            'keras', 'scikit-learn', 'xgboost', 'computer vision', 'nlp', 'opencv',
            'natural language processing', 'neural networks', 'cnn', 'rnn', 'lstm', 'bert', 'llm',
            # Databases
            'sql', 'mysql', 'postgresql', 'mongodb', 'redis', 'cassandra', 'oracle',
            'dynamodb', 'elasticsearch', 'firebase', 'sqlite', 'neo4j',
            # Cloud & DevOps
            'aws', 'azure', 'gcp', 'google cloud', 'docker', 'kubernetes', 'jenkins',
            'ci/cd', 'terraform', 'ansible', 'gitlab', 'github actions', 'ec2', 's3',
            'lambda', 'cloudformation', 'microservices', 'serverless', 'prometheus', 'grafana',
            # Tools
            'git', 'github', 'jira', 'postman', 'swagger', 'linux', 'bash', 'vim', 'vscode',
            # Quality Assurance
            'selenium', 'junit', 'pytest', 'jest', 'cypress', 'testing', 'tdd', 'bdd', 'manual testing',
            # Soft Skills & Management
            'agile', 'scrum', 'leadership', 'communication', 'problem solving', 'project management',
            'ui/ux', 'figma', 'canva', 'photoshop'
        ]
        return skills

    def _load_resource_database(self):
        """Curated database of study materials"""
        return {
            'Python': [
                {'name': 'Python for Everybody Specialization', 'platform': 'Coursera', 'url': 'https://www.coursera.org/specializations/python'},
                {'name': 'Automate the Boring Stuff with Python', 'platform': 'Book/Online', 'url': 'https://automatetheboringstuff.com/'}
            ],
            'Django': [
                {'name': 'Django for Beginners', 'platform': 'Book', 'url': 'https://djangoforbeginners.com/'},
                {'name': 'Django Certification Training', 'platform': 'Edureka', 'url': 'https://www.edureka.co/django-certification-training'}
            ],
            'Machine Learning': [
                {'name': 'Machine Learning Specialization (Andrew Ng)', 'platform': 'Coursera', 'url': 'https://www.coursera.org/specializations/machine-learning-introduction'},
                {'name': 'Practical Deep Learning for Coders', 'platform': 'Fast.ai', 'url': 'https://www.fast.ai/'}
            ],
            'Data Science': [
                {'name': 'Google Data Analytics Professional Certificate', 'platform': 'Coursera', 'url': 'https://www.coursera.org/professional-certificates/google-data-analytics'},
                {'name': 'Data Science A-Z', 'platform': 'Udemy', 'url': 'https://www.udemy.com/course/datascience/'}
            ],
            'React': [
                {'name': 'Modern React with Redux', 'platform': 'Udemy', 'url': 'https://www.udemy.com/course/react-redux/'},
                {'name': 'React Documentation (Beta)', 'platform': 'Official', 'url': 'https://react.dev/'}
            ],
            'AWS': [
                {'name': 'AWS Certified Solutions Architect', 'platform': 'A Cloud Guru', 'url': 'https://www.pluralsight.com/cloud-computing/cloud-guru'},
                {'name': 'AWS Cloud Practitioner Essentials', 'platform': 'AWS Training', 'url': 'https://explore.skillbuilder.aws/'}
            ],
            'Docker': [
                {'name': 'Docker Mastery', 'platform': 'Udemy', 'url': 'https://www.udemy.com/course/docker-mastery/'},
                {'name': 'Docker for Beginners', 'platform': 'Docker Docs', 'url': 'https://docs.docker.com/get-started/'}
            ],
            'Kubernetes': [
                {'name': 'Certified Kubernetes Administrator (CKA)', 'platform': 'Mumshad Mannambeth', 'url': 'https://kodekloud.com/courses/certified-kubernetes-administrator-cka/'},
                {'name': 'Kubernetes Basics', 'platform': 'Official', 'url': 'https://kubernetes.io/docs/tutorials/kubernetes-basics/'}
            ],
            'SQL': [
                {'name': 'SQL for Data Science', 'platform': 'Coursera', 'url': 'https://www.coursera.org/learn/sql-for-data-science'},
                {'name': 'Complete SQL Bootcamp', 'platform': 'Udemy', 'url': 'https://www.udemy.com/course/the-complete-sql-bootcamp/'}
            ],
            'Java': [
                {'name': 'Java Programming and Software Engineering Fundamentals', 'platform': 'Coursera', 'url': 'https://www.coursera.org/specializations/java-programming'},
                {'name': 'Spring Framework 6 & Spring Boot 3', 'platform': 'Udemy', 'url': 'https://www.udemy.com/course/spring-hibernate-tutorial/'}
            ],
            'TensorFlow': [
                {'name': 'TensorFlow Developer Professional Certificate', 'platform': 'Coursera', 'url': 'https://www.coursera.org/professional-certificates/tensorflow-in-practice'}
            ],
            'Cyber Security': [
                {'name': 'Google Cybersecurity Professional Certificate', 'platform': 'Coursera', 'url': 'https://www.coursera.org/professional-certificates/google-cybersecurity'},
                {'name': 'CompTIA Security+ (SY0-701) Complete Course', 'platform': 'Udemy', 'url': 'https://www.udemy.com/course/securityplus/'}
            ]
        }

    def _load_roadmap_templates(self):
        """Professional phase-based roadmap templates"""
        return {
            'data': {
                'Phase 1: Foundations': 'Master the core languages and mathematical foundations.',
                'Phase 2: Core Skills': 'Build proficiency in industry-standard tools and frameworks.',
                'Phase 3: Advanced Projects': 'Apply knowledge to complex, real-world scenarios.',
                'Phase 4: Interview & Portfolio': 'Prepare for coding interviews and polish your public presence.'
            }
        }
    
    def train_model(self):
        """Train ML model on dataset with augmentation"""
        try:
            print("\n" + "="*60)
            print("TRAINING ENHANCED JOB PROBABILITY MODEL V2 (XGBOOST)")
            print("="*60)
            
            # Load or create dataset
            if os.path.exists(self.dataset_path):
                print(f"\nLoading dataset from {self.dataset_path}...")
                df = pd.read_csv(self.dataset_path)
            else:
                print("Dataset not found. Creating new dataset...")
                df = pd.DataFrame(columns=['resume_text', 'job_title', 'match_score'])
            
            # Augment data if too small
            if len(df) < 500:
                print(f"Dataset size ({len(df)}) is small. Generating synthetic data...")
                synthetic_df = self._generate_synthetic_data(600 - len(df))
                df = pd.concat([df, synthetic_df], ignore_index=True)
                df.to_csv(self.dataset_path, index=False)
                print(f"Dataset augmented to {len(df)} rows.")

            print(f"Final Training Count: {len(df)} examples")
            
            # 1. Warm up transformer
            _ = self.transformer
            
            # 2. Extract Features
            print(f"\nExtracting features for {len(df)} rows...")
            X_features = []
            y = df['match_score'].values
            
            # Pre-calculate embeddings for efficiency if needed, but here we'll do it per row in _extract_features
            for idx, row in df.iterrows():
                if idx % 50 == 0:
                    print(f"   Processing {idx}/{len(df)}...", end='\r')
                features = self._extract_features(row['resume_text'], row['job_title'])
                X_features.append(features)
            
            X = np.array(X_features)
            print(f"\nFeature extraction complete. Shape: {X.shape}")
            
            # 3. Scale Features
            print(f"\nScaling features...")
            self.scaler = StandardScaler()
            X_scaled = self.scaler.fit_transform(X)
            
            # 4. Train XGBoost Regressor
            print(f"\nTraining XGBoost Regressor...")
            X_train, X_test, y_train, y_test = train_test_split(X_scaled, y, test_size=0.2, random_state=42)
            
            # Train XGBoost
            if XGBOOST_AVAILABLE and self.XGBRegressor:
                self.model = self.XGBRegressor(
                    n_estimators=100,
                    learning_rate=0.1,
                    max_depth=5,
                    objective='reg:squarederror',
                    random_state=42
                )
                self.model.fit(X_train, y_train)
            else:
                # Simple Regression Fallback or Failure
                print("🛑 Error: Cannot train/load model without XGBoost.")
                return False
            
            # Evaluate
            train_score = self.model.score(X_train, y_train)
            test_score = self.model.score(X_test, y_test)
            
            print(f"\nModel Performance:")
            print(f"   Training R2 Score: {train_score:.4f}")
            print(f"   Testing R2 Score: {test_score:.4f}")
            
            # Save model
            self.save_model()
            print(f"\nModel saved to: {self.model_path}")
            
            print("\n" + "="*60)
            print("MODEL TRAINING COMPLETE!")
            print("="*60)
            
        except Exception as e:
            print(f"\nError training model: {e}")
            import traceback
            traceback.print_exc()
            self.model = None
            
        except Exception as e:
            print(f"\nError training model: {e}")
            import traceback
            traceback.print_exc()
            self.model = None
            
    def _extract_features(self, resume_text, job_text):
        """Extract robust numerical features using Transformers"""
        resume_clean = self.clean_text(resume_text)
        job_clean = self.clean_text(job_text)
        
        # 1. Transformer Semantic Similarity
        semantic_sim = 0
        trans = self.transformer
        if trans is not None and hasattr(trans, 'encode'):
            try:
                embeddings = trans.encode([resume_clean, job_clean])
                if self.util and hasattr(self.util, 'cos_sim'):
                    semantic_sim = float(self.util.cos_sim(embeddings[0], embeddings[1])[0][0])
                else:
                    semantic_sim = 0.5 # Baseline if util missing
            except Exception as e:
                print(f"⚠️ Job Inference Error: {e}")
                semantic_sim = 0.1
        else:
            # Fallback semantic sim using bigrams or skip
            semantic_sim = 0.1 # Small default
            
        # 2. Skill Matching
        resume_skills = self._extract_skills(resume_text)
        job_skills = self._extract_skills(job_text)
        
        skill_overlap = len(set(resume_skills) & set(job_skills))
        job_skill_count = max(len(job_skills), 1)
        skill_match_ratio = skill_overlap / job_skill_count
        
        # 3. Experience
        exp_years = self._extract_years_experience(resume_text)
        
        # 4. Keyword Density
        resume_words = len(resume_clean.split())
        resume_density = len(resume_skills) / max(resume_words, 1)
        
        # 5. Title/Role Match
        title_match = 0
        job_title_words = self.clean_text(job_text).split()
        for word in job_title_words:
            if word in resume_clean and len(word) > 3:
                title_match += 1
        title_match_score = title_match / max(len(job_title_words), 1)

        # 6. Bigram overlap
        resume_bigrams = set(self._get_bigrams(resume_clean))
        job_bigrams = set(self._get_bigrams(job_clean))
        bigram_overlap = len(resume_bigrams & job_bigrams)

        return [
            semantic_sim,        # 0
            skill_match_ratio,   # 1
            skill_overlap,       # 2
            exp_years,           # 3
            resume_density,      # 4
            title_match_score,   # 5
            bigram_overlap,      # 6
            len(resume_skills),  # 7
            len(job_skills)      # 8
        ]
    
    def _generate_synthetic_data(self, count):
        """Generate high-quality synthetic training data"""
        roles_data = {
            'Python Developer': ['Python', 'Django', 'Flask', 'SQL', 'REST API', 'Redis', 'Celery', 'PostgreSQL'],
            'Java Developer': ['Java', 'Spring Boot', 'Hibernate', 'Microservices', 'Maven', 'Kafka', 'Junit'],
            'Frontend Developer': ['React', 'JavaScript', 'HTML5', 'CSS3', 'Redux', 'TypeScript', 'Webpack', 'Figma'],
            'Data Scientist': ['Python', 'Pandas', 'NumPy', 'Scikit-learn', 'TensorFlow', 'PyTorch', 'Jupyter', 'Statistics'],
            'DevOps Engineer': ['Docker', 'Kubernetes', 'AWS', 'Terraform', 'Jenkins', 'Linux', 'Bash', 'CI/CD'],
            'Mobile Developer': ['Flutter', 'Dart', 'Firebase', 'Android', 'iOS', 'Swift', 'Kotlin', 'Mobile UI'],
            'QA Engineer': ['Selenium', 'Python', 'Java', 'JMeter', 'TestNG', 'Appium', 'Manual Testing', 'Bug Tracking'],
            'Cloud Architect': ['AWS', 'Azure', 'Google Cloud', 'System Design', 'Security', 'Networking', 'Load Balancing'],
            'Cyber Security': ['Penetration Testing', 'Network Security', 'Firewalls', 'Wireshark', 'Cryptography', 'Linux', 'Ethical Hacking'],
            'Full Stack Developer': ['React', 'Node.js', 'Express', 'MongoDB', 'Python', 'AWS', 'Git', 'REST API']
        }
        
        experience_levels = [
            ('start', 0, 1), ('junior', 1, 3), ('mid', 3, 5), ('senior', 5, 8), ('lead', 8, 15)
        ]
        
        phrases = [
            "Experienced in {skills}.",
            "Looking for a challenging role. Proficient in {skills}.",
            "Skilled {role} with {years} years of experience. Tech stack: {skills}.",
            "Expertise involves {skills} and cloud technologies.",
            "Passionate backend engineer. key skills: {skills}.",
            "Fresh graduate with knowledge of {skills}.",
            "Professional summary: {years}+ years in industry working with {skills}."
        ]

        data = []
        for _ in range(count):
            target_role, target_skills = random.choice(list(roles_data.items()))
            
            # --- Scenario 1: High Match (Good Candidate) ---
            if random.random() > 0.4:
                # Pick 70-100% of required skills
                k = random.randint(int(len(target_skills)*0.7), len(target_skills))
                candidate_skills = random.sample(target_skills, k=k)
                
                # Add some random other skills
                candidate_skills += random.sample(self.all_skills, k=random.randint(1, 3))
                
                # Random experience
                level_name, min_y, max_y = random.choice(experience_levels[2:]) # Mid to Lead
                years = random.randint(min_y, max_y)
                
                # Score Calculation (High)
                score = random.randint(75, 98)
                
                # Construct Text
                phrase = random.choice(phrases)
                text = phrase.format(role=target_role, years=years, skills=', '.join(candidate_skills))
                
            # --- Scenario 2: Medium Match (Junior/Partial Skills) ---
            elif random.random() > 0.3:
                # Pick 40-60% of skills
                k = random.randint(int(len(target_skills)*0.4), int(len(target_skills)*0.6))
                candidate_skills = random.sample(target_skills, k=k)
                level_name, min_y, max_y = random.choice(experience_levels[:3]) # Start to Mid
                years = random.randint(min_y, max_y)
                
                score = random.randint(45, 70)
                text = f"Junior {target_role}. Skills include {', '.join(candidate_skills)}. {years} years exp."

            # --- Scenario 3: Low Match (Irrelevant) ---
            else:
                # Pick a DIFFERENT role's skills
                other_role, other_skills = random.choice([x for x in roles_data.items() if x[0] != target_role])
                candidate_skills = random.sample(other_skills, k=min(4, len(other_skills)))
                years = random.randint(0, 5)
                
                score = random.randint(10, 35)
                text = f"I am a {other_role} with skills in {', '.join(candidate_skills)}."

            data.append({
                'resume_text': text,
                'job_title': target_role,
                'match_score': score
            })
            
        return pd.DataFrame(data)

    def _extract_years_experience(self, text):
        spacy_nlp = load_spacy()
        if SPACY_AVAILABLE and spacy_nlp:
            try:
                doc = spacy_nlp(text)
                if hasattr(doc._, 'total_yoe') and doc._.total_yoe > 0:
                    return int(doc._.total_yoe)
            except Exception:
                pass
        
        # Fallback to Regex
        matches = re.findall(r'(\d+)\s*(?:\+)?\s*(?:years?|yrs?)', text.lower())
        if matches:
            return int(max([int(m) for m in matches]))
        return 0
    
    def _get_bigrams(self, text):
        words = text.split()
        return [f"{words[i]} {words[i+1]}" for i in range(len(words)-1)]
    
    def _extract_skills(self, text):
        """Extract skills from text"""
        # CLEAN TEXT FIRST to ensure separation of concatenated words
        text_lower = self.clean_text(text)
        found_skills = set()
        
        for skill in self.all_skills:
            # Word boundary check for accurate matching
            if re.search(r'\b' + re.escape(skill) + r'\b', text_lower):
                found_skills.add(skill.title())
        return list(found_skills)

    def calculate_job_match(self, resume_text, dream_job):
        """Calculate prediction using trained model"""
        try:
            resume_skills = self._extract_skills(resume_text)
            job_skills = self._extract_skills(dream_job)
            
            matching_skills = [s for s in job_skills if s in resume_skills]
            missing_skills = [s for s in job_skills if s not in resume_skills]
            
            # --- DEEP ANALYSIS ---
            # 1. Experience Analysis
            resume_exp = self._extract_years_experience(resume_text)
            job_exp = self._extract_years_experience(dream_job)
            exp_status = "Match"
            if resume_exp < job_exp:
                exp_status = "Gap"
            elif resume_exp > job_exp + 2:
                exp_status = "Exceeds"
                
            # 2. Education Analysis
            resume_edu = self._extract_education(resume_text)
            job_edu = self._extract_education(dream_job)
            edu_match = any(e in resume_edu for e in job_edu) if job_edu else True
            
            # 3. Soft Skills Analysis
            soft_skills = self._extract_soft_skills(resume_text)
            
            # Inference
            explanation = ""
            if self.model and self.scaler:
                features = self._extract_features(resume_text, dream_job)
                features_scaled = self.scaler.transform([features])
                probability = self.model.predict(features_scaled)[0]
                model_used = 'XGBoost Regressor v2.0'
                
                # --- SHAP EXPLAINABILITY ---
                try:
                    import shap
                    explainer = shap.TreeExplainer(self.model)
                    shap_values = explainer.shap_values(features_scaled)
                    
                    feature_names = [
                        "Semantic Similarity", "Skill Match Ratio", "Skill Overlap", "Years of Experience",
                        "Keyword Density", "Title Match", "Bigram Overlap", "Total Resume Skills", "Total Job Skills"
                    ]
                    
                    contributions = dict(zip(feature_names, shap_values[0]))
                    
                    # Handle expected_value datatype
                    base_val = explainer.expected_value
                    if isinstance(base_val, np.ndarray):
                        base_val = float(base_val[0])
                    else:
                        base_val = float(base_val)
                        
                    sorted_contributions = sorted(contributions.items(), key=lambda item: abs(item[1]), reverse=True)
                    
                    explanation = f"Base fit is {base_val:.1f}%. "
                    positives, negatives = [], []
                    for feat, impact in sorted_contributions[:3]:
                        if impact > 0:
                            positives.append(f"increased by {impact:.1f}% due to {feat}")
                        else:
                            negatives.append(f"dropped {abs(impact):.1f}% due to {feat}")
                            
                    explanation += ", ".join(positives)
                    if negatives:
                        explanation += f", but {', '.join(negatives)}."
                except Exception as e:
                    print(f"SHAP Error: {e}")
                    explanation = "AI explanation unavailable."
            else:
                # Fallback if model missing
                probability = self._rule_based_prediction(resume_text, dream_job, resume_skills, job_skills)
                model_used = 'Rule-Based Fallback'
                explanation = "Based on basic skill overlap."
            
            probability = float(np.clip(probability, 0, 100))
            
            # Determine confidence/message
            if probability >= 80:
                confidence, message = 'High', 'Excellent match! Your profile aligns perfectly.'
            elif probability >= 60:
                confidence, message = 'Medium-High', 'Good match! You have strong potential.'
            elif probability >= 40:
                confidence, message = 'Medium', 'Decent match, but some gaps exist.'
            else:
                confidence, message = 'Low', 'Significant skill gaps found. Recommended upskilling.'
            
            
            recommendations = self._get_recommendations(dream_job, resume_skills, missing_skills)
            
            # --- 4. NEW: GENERATE CAREER ROADMAP ---
            roadmap = self._generate_enhanced_roadmap(dream_job, missing_skills, resume_exp)
            
            print(f"DEBUG: Resume Length: {len(resume_text)}")
            print(f"DEBUG: Extracted User Skills: {resume_skills}")
            print(f"DEBUG: Extracted Job Skills: {job_skills}")

            return {
                'probability': round(probability, 2),
                'confidence': confidence,
                'message': message,
                'user_skills': resume_skills[:20],
                'job_required_skills': job_skills[:15],
                'matching_skills': matching_skills,
                'missing_skills': missing_skills[:10],
                'skill_recommendations': recommendations,
                'skill_match_percentage': round((len(matching_skills) / max(len(job_skills), 1)) * 100, 1),
                'total_skills_found': len(resume_skills),
                'model_used': model_used,
                'explanation': explanation,
                # Deep Analysis Fields
                'experience_analysis': {
                    'resume_years': resume_exp,
                    'job_years_required': job_exp,
                    'status': exp_status
                },
                'education_analysis': {
                    'detected_degrees': resume_edu,
                    'job_requirements': job_edu,
                    'match': edu_match
                },
                'soft_skills': soft_skills,
                # New Transformation Architect Fields
                'roadmap': roadmap,
                'resource_materials': self._get_specific_resources(missing_skills + recommendations)
            }
            
        except Exception as e:
            print(f"Prediction Error: {e}")
            return self._get_error_response(str(e))

    def _extract_education(self, text):
        text_lower = text.lower()
        degrees = []
        patterns = {
            'PhD': r'\b(phd|doctorate)\b',
            'Masters': r'\b(master|ms|msc|m\.tech|mba)\b',
            'Bachelors': r'\b(bachelor|bs|bsc|b\.tech|b\.e|undergraduate)\b',
            'Diploma': r'\b(diploma)\b'
        }
        for degree, pattern in patterns.items():
            if re.search(pattern, text_lower):
                degrees.append(degree)
        return degrees

    def _extract_soft_skills(self, text):
        soft_skills_list = [
            'communication', 'leadership', 'teamwork', 'problem solving', 'adaptability', 
            'critical thinking', 'time management', 'collaboration', 'creativity', 
            'mentoring', 'agile', 'scrum', 'presentation'
        ]
        text_lower = text.lower()
        found = []
        for skill in soft_skills_list:
            if skill in text_lower:
                found.append(skill.title())
        return found


    def _generate_enhanced_roadmap(self, job_title, gaps, experience):
        """Generate a multi-phase learning roadmap"""
        phases = []
        duration_map = {0: "12 Months", 1: "9 Months", 3: "6 Months", 5: "3 Months"}
        total_duration = duration_map.get(min(filter(lambda x: x <= experience, duration_map.keys()), default=0), "12 Months")
        
        # Phase 1: Foundations (Months 1-3)
        foundations = [s for s in gaps if s in ['Python', 'Java', 'SQL', 'HTML', 'CSS', 'Javascript']]
        phases.append({
            "title": "Phase 1: Foundations & Prerequisites",
            "period": "Months 1-2",
            "focus": foundations if foundations else ["Core Fundamentals"],
            "tasks": [f"Deep dive into {s if foundations else 'programming basics'}", "Build 3 mini-utility projects", "Master debugging and Git workflow"],
            "milestone": "Portfolio Site & Basic Scripts deployed"
        })
        
        # Phase 2: Core Competencies (Months 3-6)
        core = [s for s in gaps if s not in foundations][:4]
        phases.append({
            "title": "Phase 2: Core Professional Skills",
            "period": "Months 3-5",
            "focus": core if core else ["Frameworks & Tools"],
            "tasks": [f"Learn and apply {s}" for s in (core if core else ["Industry Frameworks"])],
            "milestone": "Complete an end-to-end CRUD application"
        })
        
        # Phase 3: Advanced & Specialization
        adv = [s for s in gaps if s not in foundations and s not in core][:4]
        phases.append({
            "title": "Phase 3: Deep Specialization",
            "period": "Months 6-8",
            "focus": adv if adv else ["Advanced Systems"],
            "tasks": ["System Design fundamentals", "Performance optimization", "Advanced certifications"],
            "milestone": "Full-stack project with cloud deployment"
        })
        
        # Phase 4: Market Ready
        phases.append({
            "title": "Phase 4: Interview & Placement",
            "period": "Months 9-12",
            "focus": ["DSA", "System Design", "Behavioral"],
            "tasks": ["LeetCode Medium (50+ problems)", "Mock Interviews", "Resume optimization"],
            "milestone": "Ready for high-tier company interviews"
        })
        
        return {
            "total_duration": total_duration,
            "phases": phases
        }

    def _get_specific_resources(self, skills):
        """Map skills to resources in the database"""
        resources = []
        seen = set()
        for skill in skills:
            skill_base = skill.title()
            if skill_base in self.resource_db:
                for res in self.resource_db[skill_base]:
                    if res['name'] not in seen:
                        resources.append(res)
                        seen.add(res['name'])
        
        # Default global resources if too few
        if len(resources) < 3:
            resources.append({'name': 'LeetCode', 'platform': 'Practice', 'url': 'https://leetcode.com'})
            resources.append({'name': 'FreeCodeCamp', 'platform': 'Courses', 'url': 'https://www.freecodecamp.org'})
            
        return resources[:8] # Return top 8

    def _rule_based_prediction(self, resume, job, r_skills, j_skills):
        # Fallback simple logic
        matched = len(set(r_skills) & set(j_skills))
        total = max(len(j_skills), 1)
        return (matched / total) * 100

    def _get_recommendations(self, job_text, current_skills, missing_skills):
        """Generate smart recommendations based on missing skills and job role"""
        # Start with explicit gaps (high priority)
        recs = missing_skills[:6]
        
        # Domain knowledge base
        domain_skills = {
            'data scientist': ['Python', 'Machine Learning', 'SQL', 'Pandas', 'TensorFlow', 'Statistics'],
            'data analyst': ['Excel', 'SQL', 'Python', 'Tableau', 'Power BI'],
            'frontend': ['React', 'JavaScript', 'HTML', 'CSS', 'TypeScript', 'Redux', 'Figma'],
            'react': ['Redux', 'TypeScript', 'Next.js', 'Tailwind'],
            'backend': ['Python', 'Java', 'Node.js', 'SQL', 'Docker', 'API'],
            'full stack': ['React', 'Node.js', 'SQL', 'Docker', 'Git'],
            'devops': ['AWS', 'Docker', 'Kubernetes', 'CI/CD', 'Linux', 'Terraform'],
            'java': ['Spring Boot', 'Hibernate', 'Microservices', 'SQL', 'Maven'],
            'python': ['Django', 'Flask', 'SQL', 'Pandas', 'API'],
            'android': ['Kotlin', 'Java', 'Android SDK', 'Firebase', 'MVVM'],
            'ios': ['Swift', 'SwiftUI', 'Xcode', 'Core Data']
        }
        
        job_lower = job_text.lower()
        current_skills_set = set(s.lower() for s in current_skills)
        recs_set = set(r.lower() for r in recs)
        
        # Check for inferred domain gaps
        for role, skills in domain_skills.items():
            if role in job_lower:
                for skill in skills:
                    skill_lower = skill.lower()
                    if skill_lower not in current_skills_set and skill_lower not in recs_set:
                        recs.append(skill)
                        recs_set.add(skill_lower)
        
        return recs[:10]
    
    def clean_text(self, text):
        if not text: return ""
        text = re.sub(r'http\S+', '', text)
        text = re.sub(r'[^\w\s]', ' ', text)
        return text.lower().strip()
    
    def save_model(self):
        with open(self.model_path, 'wb') as f:
            pickle.dump({
                'model': self.model,
                'scaler': self.scaler,
                'embedding_model': self.embedding_model
            }, f)
            
    def load_model(self):
        try:
            with open(self.model_path, 'rb') as f:
                data = pickle.load(f)
                self.model = data['model']
                self.scaler = data['scaler']
            return True
        except Exception as e:
            print(f"Error loading model: {e}")
            return False

    def _get_error_response(self, error):
        return {
            'probability': 0, 'confidence': 'Error', 'message': str(error),
            'user_skills': [], 'job_required_skills': [], 'matching_skills': [],
            'missing_skills': [], 'skill_recommendations': [], 'skill_match_percentage': 0,
            'model_used': 'None'
        }

if __name__ == "__main__":
    # Test run
    predictor = JobProbabilityPredictor(force_retrain=True)
    
    test_resume = "I am a skilled Python developer with 5 years of experience in Django, Machine Learning and SQL."
    test_job = "Senior Python Developer required with experience in Django and ML."
    
    result = predictor.calculate_job_match(test_resume, test_job)
    print("\nTest Prediction Result:")
    print(result)

