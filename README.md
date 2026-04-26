# PATHINTEL.AI - AI Powered Career Consulter

PATHINTEL.AI is an advanced, AI-driven career guidance system designed to help students and professionals navigate their career paths with precision. By leveraging machine learning models and a robust web interface, the platform provides tailored recommendations based on skills, interests, and industry trends.

## 🚀 Features

- **AI Career Prediction**: Recommends the best career paths based on user input and skill assessment.
- **Job Probability Modeling**: Predicts the likelihood of success in various roles using a trained `RandomForestRegressor`.
- **Dynamic Role Exploration**: Detailed pages for various specialists (AI/ML, Cyber Security, Data Science, etc.).
- **Course Recommendations**: Suggests relevant courses to bridge skill gaps.
- **Interactive Chatbot**: Integrated AI assistant for real-time guidance.
- **Modern UI/UX**: Futuristic dark mode and cyberpunk light theme system.

## 🛠️ Technology Stack

- **Backend**: PHP (Main application logic) & Python (Machine learning models).
- **Frontend**: HTML5, Vanilla CSS, JavaScript.
- **Database**: MySQL (via XAMPP/MariaDB).
- **AI/ML**: Python (Flask, Scikit-learn, Pandas, Gemini API).

## 📂 Project Structure

- `*.php`: Specialist role pages and core site logic.
- `app.py`: Flask server for ML model integration.
- `career_model.py` / `job_probability_model.py`: ML model training scripts.
- `css/` & `js/`: Styling and dynamic frontend features.
- `static/` & `templates/`: Assets and templates for the web interface.
- `*.pkl`: Pre-trained machine learning models.

## 🔧 Installation & Setup

1. **Clone the repository**:
   ```bash
   git clone https://github.com/akshat-spec/PATHINTEL.AI---POWERED---CAREER-CONSULTER.git
   ```

2. **PHP Setup**:
   - Move the project folder to your `htdocs` directory (e.g., `C:\xampp\htdocs\career_guidance`).
   - Start Apache and MySQL via XAMPP.
   - Configure `config.php` with your database credentials.

3. **Python Setup**:
   - Install required dependencies:
     ```bash
     pip install -r requirements.txt
     ```
   - Run the Flask server:
     ```bash
     python app.py
     ```

4. **Access the App**:
   - Open your browser and navigate to `http://localhost/career_guidance/main.php`.

## 📄 License

Internal Project - All Rights Reserved.

---
*Created by [akshat-spec](https://github.com/akshat-spec)*
