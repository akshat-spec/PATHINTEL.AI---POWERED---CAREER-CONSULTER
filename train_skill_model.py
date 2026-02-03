import pandas as pd
import numpy as np
import joblib
from sklearn.svm import SVC
from sklearn.preprocessing import LabelEncoder
from sklearn.model_selection import train_test_split

print("🚀 Loading Dataset...")

# 1. LOAD YOUR CSV
try:
    df = pd.read_csv('dataset9000.csv')
    print(f"✅ Dataset Loaded: {len(df)} rows")
except FileNotFoundError:
    print("❌ Error: 'dataset9000.csv' not found. Please put it in the same folder.")
    exit()

# 2. MAP TEXT TO NUMBERS
# Your CSV has "Not Interested", "Poor", "Beginner", etc.
# We map these to the 1-9 scale used in your hometest.html
rating_map = {
    'Not Interested': 1,
    'Poor': 2,
    'Beginner': 3,
    'Average': 5,
    'Intermediate': 7,
    'Excellent': 9,
    'Professional': 9  # Map Professional to max score
}

# Apply mapping to all feature columns (excluding 'Role')
feature_cols = df.columns[:-1] # All columns except the last one (Role)

for col in feature_cols:
    df[col] = df[col].map(rating_map)
    # Fill any missing/unmapped values with 1 (lowest score) just in case
    df[col] = df[col].fillna(1)

print("✅ Data Preprocessing Complete")

# 3. PREPARE TRAINING DATA
X = df[feature_cols]  # Input Features (17 columns)
y = df['Role']        # Target Label (Career Role)

# Encode Roles (Text -> Numbers)
label_encoder = LabelEncoder()
y_encoded = label_encoder.fit_transform(y)

# 4. TRAIN MODEL
print("⏳ Training SVM Model...")
model = SVC(kernel='linear', probability=True)
model.fit(X, y_encoded)

# 5. SAVE ARTIFACTS
joblib.dump(model, 'career_model.pkl')
joblib.dump(label_encoder, 'label_encoder.pkl')

print("🎉 SUCCESS: Model Trained & Saved!")
print("Files created: 'career_model.pkl' and 'label_encoder.pkl'")
