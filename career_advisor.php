<?php 
// Include your existing header
if(file_exists('header.php')) {
    include 'header.php'; 
} else {
    // Fallback if header is missing
    echo '<!DOCTYPE html><html><head><title>Career Advisor</title></head><body>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Career Advisor | PathIntel.ai</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Modern Purple/Blue Theme */
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --success: #28a745;
            --bg-color: #f4f7f6;
            --text-color: #333;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding-bottom: 50px;
        }

        .main-container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        /* Header Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 50px 20px;
            text-align: center;
        }
        .hero-section h1 { margin: 0; font-size: 2.5em; }
        .hero-section p { font-size: 1.1em; opacity: 0.9; margin-top: 10px; }

        /* Upload Section */
        .upload-area {
            padding: 40px;
            text-align: center;
        }

        .drop-zone {
            border: 2px dashed #cbd5e0;
            border-radius: 10px;
            padding: 40px;
            background: #fafafa;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        .drop-zone:hover {
            border-color: var(--primary);
            background: #f0f4ff;
        }
        .drop-zone input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }
        
        .btn-predict {
            background: linear-gradient(90deg, #ff0080 0%, #7928ca 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 30px;
            font-size: 18px;
            font-weight: bold;
            margin-top: 25px;
            cursor: pointer;
            transition: transform 0.2s;
            box-shadow: 0 5px 15px rgba(121, 40, 202, 0.4);
        }
        .btn-predict:hover { transform: scale(1.05); }

        /* Results Section */
        #resultsSection {
            display: none;
            padding: 30px;
            background: #fff;
        }

        .result-card {
            margin-bottom: 30px;
            animation: fadeIn 0.8s ease;
        }
        
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .card-title {
            font-size: 1.4em;
            color: var(--primary);
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        /* Badges for Skills */
        .skill-badge {
            display: inline-block;
            background: rgba(106, 17, 203, 0.1);
            color: var(--primary);
            padding: 8px 16px;
            border-radius: 20px;
            margin: 5px;
            font-weight: 600;
        }

        /* Career Progress Bars */
        .career-row {
            margin-bottom: 20px;
        }
        .career-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-weight: bold;
            color: #444;
        }
        .progress-track {
            background: #eee;
            height: 15px;
            border-radius: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            width: 0%;
            transition: width 1.5s ease-out;
            border-radius: 10px;
        }

        /* Loader */
        .loader-container {
            display: none;
            text-align: center;
            padding: 40px;
        }
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px auto;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        
        #fileNameDisplay {
            margin-top: 15px;
            color: var(--primary);
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="hero-section">
        <h1><i class="fas fa-brain"></i> AI Career Architect</h1>
        <p>Upload your resume. Our Machine Learning model will analyze your skills and suggest the best career path.</p>
    </div>

    <!-- Upload Form -->
    <div class="upload-area" id="uploadArea">
        <form id="resumeForm">
            <div class="drop-zone">
                <i class="fas fa-cloud-upload-alt fa-3x" style="color: #cbd5e0;"></i>
                <h3 style="color: #777;">Drag & Drop Resume (PDF/DOCX)</h3>
                <input type="file" name="resume" id="resumeInput" accept=".pdf,.docx" required>
            </div>
            <div id="fileNameDisplay"></div>
            <button type="submit" class="btn-predict">Analyze Profile</button>
        </form>
    </div>

    <!-- Loader -->
    <div class="loader-container" id="loader">
        <div class="spinner"></div>
        <p>Reading Resume... Analyzing Skills... Predicting Career...</p>
    </div>

    <!-- Results -->
    <div id="resultsSection">
        
        <!-- Education -->
        <div class="result-card">
            <h3 class="card-title"><i class="fas fa-graduation-cap"></i> Education Detected</h3>
            <div id="educationData"></div>
        </div>

        <!-- Skills -->
        <div class="result-card">
            <h3 class="card-title"><i class="fas fa-tools"></i> Skills Extracted</h3>
            <div id="skillsData"></div>
        </div>

        <!-- Predictions -->
        <div class="result-card">
            <h3 class="card-title"><i class="fas fa-chart-line"></i> Top Career Recommendations</h3>
            <div id="careerData"></div>
        </div>

        <div style="text-align:center; margin-top:30px;">
            <button onclick="location.reload()" style="padding: 10px 20px; cursor: pointer; background: #eee; border:none; border-radius: 5px;">Analyze Another</button>
        </div>
    </div>
</div>

<script>
    const fileInput = document.getElementById('resumeInput');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    const form = document.getElementById('resumeForm');
    const loader = document.getElementById('loader');
    const resultsSection = document.getElementById('resultsSection');
    const uploadArea = document.getElementById('uploadArea');

    // Show filename on select
    fileInput.addEventListener('change', function(e) {
        if(e.target.files.length > 0) {
            fileNameDisplay.textContent = "Selected: " + e.target.files[0].name;
        }
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if(fileInput.files.length === 0) {
            alert("Please select a file.");
            return;
        }

        // UI State: Loading
        uploadArea.style.display = 'none';
        loader.style.display = 'block';

        const formData = new FormData();
        formData.append('resume', fileInput.files[0]);

        try {
            // Call Python API
            const response = await fetch('http://127.0.0.1:5000/analyze_resume', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error("Server Error");
            }

            const data = await response.json();
            
            if(data.error) {
                throw new Error(data.error);
            }

            displayResults(data);

        } catch (error) {
            console.error(error);
            alert("An error occurred: " + error.message + ". Make sure the Python server is running.");
            loader.style.display = 'none';
            uploadArea.style.display = 'block';
        }
    });

    function displayResults(data) {
        loader.style.display = 'none';
        resultsSection.style.display = 'block';

        // 1. Education
        const eduDiv = document.getElementById('educationData');
        if (data.education && data.education.length > 0) {
            eduDiv.innerHTML = data.education.map(e => `<p style="font-size:1.1em; font-weight:bold;">${e}</p>`).join('');
        } else {
            eduDiv.innerHTML = "<p>No specific degree detected (Result may depend on resume format)</p>";
        }

        // 2. Skills
        const skillsDiv = document.getElementById('skillsData');
        if (data.skills && data.skills.length > 0) {
            skillsDiv.innerHTML = data.skills.map(s => `<span class="skill-badge">${s}</span>`).join('');
        } else {
            skillsDiv.innerHTML = "<p>No specific technical skills detected.</p>";
        }

        // 3. Careers
        const careerDiv = document.getElementById('careerData');
        careerDiv.innerHTML = data.recommended_careers.map(c => `
            <div class="career-row">
                <div class="career-header">
                    <span>${c.career}</span>
                    <span>${c.confidence}%</span>
                </div>
                <div class="progress-track">
                    <div class="progress-fill" style="width: ${c.confidence}%"></div>
                </div>
            </div>
        `).join('');
    }
</script>

</body>
</html>
