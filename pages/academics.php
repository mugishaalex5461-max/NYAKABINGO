<?php include '../includes/header.php'; ?>

<style>
    .page-header {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        color: white;
        padding: 50px 20px;
        text-align: center;
    }
    
    .page-header h1 {
        font-size: 40px;
        margin-bottom: 10px;
    }
    
    .page-header p {
        font-size: 18px;
        opacity: 0.9;
    }
    
    .page-content {
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
    }
    
    .content-section {
        margin-bottom: 50px;
    }
    
    .content-section h2 {
        font-size: 32px;
        color: #1e3a8a;
        margin-bottom: 20px;
        border-bottom: 3px solid #3b82f6;
        padding-bottom: 10px;
    }
    
    .content-section p {
        font-size: 16px;
        color: #555;
        line-height: 1.8;
        margin-bottom: 15px;
        text-align: justify;
    }
    
    .subjects-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .subject-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s;
    }
    
    .subject-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
    }
    
    .subject-card .icon {
        font-size: 40px;
        margin-bottom: 10px;
    }
    
    .subject-card h3 {
        color: #1e3a8a;
        margin-bottom: 10px;
    }
    
    .subject-card p {
        color: #666;
        font-size: 14px;
        text-align: center;
    }
    
    .classes-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .class-card {
        background: linear-gradient(135deg, #3b82f6, #1e3a8a);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }
    
    .class-card h3 {
        font-size: 24px;
        margin-bottom: 10px;
    }
    
    .class-card p {
        font-size: 14px;
        opacity: 0.9;
    }
    
    .info-box {
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
        padding: 20px;
        margin: 20px 0;
        border-radius: 5px;
    }
    
    .assessment-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .assessment-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 25px;
    }
    
    .assessment-card h4 {
        color: #1e3a8a;
        margin-bottom: 12px;
    }
    
    .assessment-card p {
        color: #666;
        font-size: 14px;
        line-height: 1.6;
    }
</style>

<div class="page-header">
    <h1>Academics</h1>
    <p>Quality Education for Every Child</p>
</div>

<div class="page-content">
    <div class="content-section">
        <h2>Curriculum Overview</h2>
        <p>
            Nyakabingo Primary School follows the Uganda National Primary Curriculum, which is designed to provide 
            a balanced, broad-based education that develops the whole child. Our curriculum emphasizes literacy, 
            numeracy, and foundational skills while incorporating life skills, sports, and cultural education.
        </p>
        <p>
            We are committed to making learning relevant, engaging, and meaningful through a combination of 
            classroom instruction, practical activities, and assessment methods that cater to different learning styles.
        </p>
    </div>
    
    <div class="content-section">
        <h2>Core Subjects</h2>
        <div class="subjects-grid">
            <div class="subject-card">
                <div class="icon">📖</div>
                <h3>English Language</h3>
                <p>Speaking, reading, writing, and communication skills</p>
            </div>
            <div class="subject-card">
                <div class="icon">🔢</div>
                <h3>Mathematics</h3>
                <p>Numeracy, problem-solving, and logical thinking</p>
            </div>
            <div class="subject-card">
                <div class="icon">🌍</div>
                <h3>Social Studies</h3>
                <p>History, geography, and civic education</p>
            </div>
            <div class="subject-card">
                <div class="icon">🧬</div>
                <h3>Science</h3>
                <p>Biology, chemistry, physics, and practical investigations</p>
            </div>
            <div class="subject-card">
                <div class="icon">🎨</div>
                <h3>Creative Arts</h3>
                <p>Drawing, painting, music, and drama</p>
            </div>
            <div class="subject-card">
                <div class="icon">⚽</div>
                <h3>Physical Education</h3>
                <p>Sports, fitness, and recreational activities</p>
            </div>
        </div>
    </div>
    
    <div class="content-section">
        <h2>Class Structure</h2>
        <p>
            Our primary school is organized into seven classes, from Senior Two (P2) to Primary Seven (P7). 
            Each class is taught by dedicated teachers who specialize in primary education.
        </p>
        <div class="classes-grid">
            <div class="class-card">
                <h3>Primary 1 (P1)</h3>
                <p>Foundation Level</p>
            </div>
            <div class="class-card">
                <h3>Primary 2 (P2)</h3>
                <p>Early Literacy & Numeracy</p>
            </div>
            <div class="class-card">
                <h3>Primary 3 (P3)</h3>
                <p>Transitional Level</p>
            </div>
            <div class="class-card">
                <h3>Primary 4 (P4)</h3>
                <p>Consolidation Level</p>
            </div>
            <div class="class-card">
                <h3>Primary 5 (P5)</h3>
                <p>Preparation Level</p>
            </div>
            <div class="class-card">
                <h3>Primary 6 (P6)</h3>
                <p>Advanced Level</p>
            </div>
            <div class="class-card">
                <h3>Primary 7 (P7)</h3>
                <p>Final Examination Level</p>
            </div>
        </div>
    </div>
    
    <div class="info-box">
        <strong>Assessment Methods:</strong> We employ continuous assessment (formative) and summative assessment 
        strategies to monitor student progress. Regular tests, assignments, class participation, and end-of-term 
        examinations help us track achievement and identify learning needs.
    </div>
    
    <div class="content-section">
        <h2>Learning Approach</h2>
        <div class="assessment-grid">
            <div class="assessment-card">
                <h4>🎯 Learner-Centered</h4>
                <p>We place the student at the center of learning, encouraging active participation, critical thinking, and problem-solving</p>
            </div>
            <div class="assessment-card">
                <h4>🤝 Inclusive Education</h4>
                <p>We welcome all children regardless of background and provide support to students with special learning needs</p>
            </div>
            <div class="assessment-card">
                <h4>💡 Practical Learning</h4>
                <p>We integrate hands-on activities and real-world applications to make learning relevant and meaningful</p>
            </div>
            <div class="assessment-card">
                <h4>📊 Progress Monitoring</h4>
                <p>Regular assessment and parent communication ensure continuous monitoring of student achievement and support</p>
            </div>
        </div>
    </div>
    
    <div class="content-section">
        <h2>Teacher Quality</h2>
        <p>
            Our teaching staff are qualified, experienced, and committed to professional development. Teachers regularly 
            participate in training programs to stay updated with modern teaching methodologies and improve their practice. 
            We foster a collaborative environment where teachers share best practices and support each other's growth.
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
