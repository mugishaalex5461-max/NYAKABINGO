<?php include '../includes/header.php'; ?>

<style>
    .page-header {
        background: linear-gradient(rgba(30, 58, 138, 0.7), rgba(59, 130, 246, 0.7)), url('/NYAKABINGO_PRIMARY/images/gallery_header.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 100px 20px;
        text-align: center;
        min-height: 400px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .page-header h1 {
        font-size: 48px;
        margin-bottom: 15px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }
    
    .page-header p {
        font-size: 22px;
        opacity: 1;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
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
    
    .staff-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }
    
    .staff-member {
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
    }
    
    .staff-member .photo {
        height: 200px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 60px;
        margin-bottom: 15px;
        overflow: hidden;
    }
    
    .staff-member .photo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    .staff-member h3 {
        color: #1e3a8a;
        margin-bottom: 5px;
    }
    
    .staff-member p.position {
        color: #f59e0b;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .staff-member p.qualification {
        color: #666;
        font-size: 14px;
        margin-bottom: 10px;
    }
    
    .info-box {
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
        padding: 20px;
        margin: 20px 0;
        border-radius: 5px;
    }
    
    .info-box strong {
        color: #1e3a8a;
    }
    
    .values-list {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 30px;
        margin: 20px 0;
    }
    
    .values-list ul {
        list-style: none;
    }
    
    .values-list li {
        padding: 12px 0;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
    }
    
    .values-list li:last-child {
        border-bottom: none;
    }
    
    .values-list li:before {
        content: "✓";
        color: #10b981;
        font-weight: bold;
        margin-right: 15px;
        font-size: 20px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin: 20px 0 30px;
    }

    .stat-card {
        background: #f8fafc;
        border: 1px solid #dbeafe;
        border-radius: 10px;
        padding: 16px;
    }

    .stat-card h3 {
        color: #1e3a8a;
        margin-bottom: 6px;
        font-size: 16px;
    }

    .stat-card p {
        margin: 0;
        color: #111827;
        font-weight: 700;
        font-size: 20px;
        text-align: left;
    }

    .photo-gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .photo-card {
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }

    .photo-card img {
        width: 100%;
        height: 190px;
        object-fit: cover;
        display: block;
    }

    .photo-card p {
        margin: 0;
        padding: 10px 12px;
        font-weight: 700;
        color: #ffffff;
        text-align: center;
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to top, rgba(30, 58, 138, 0.92), rgba(30, 58, 138, 0.3));
        backdrop-filter: blur(1px);
    }
</style>

<div class="page-header">
    <h1>About Our School</h1>
    <p>Building Excellence Through Education</p>
</div>

<div class="page-content">
    <div class="content-section">
        <h2>Our Mission & Vision</h2>
        <p>
            <strong>Mission:</strong> To provide quality primary education that prepares pupils for secondary 
            education and develops responsible, knowledgeable, and skills-oriented citizens of Uganda.
        </p>
        <p>
            <strong>Vision:</strong> To be a leading primary school in Kisoro District, known for academic excellence, 
            discipline, and holistic development of our learners.
        </p>
    </div>
    
    <div class="info-box">
        <strong>School Information:</strong><br>
        <strong>Name:</strong> <?php echo $school_config['name']; ?><br>
        <strong>Location:</strong> <?php echo $school_config['location']; ?><br>
        <strong>Type:</strong> <?php echo $school_config['classification']; ?><br>
        <strong>District:</strong> Kisoro District, South Western Uganda
    </div>
    
    <div class="content-section">
        <h2>Our Core Values</h2>
        <div class="values-list">
            <ul>
                <li>Integrity - Honesty and truthfulness in all our dealings</li>
                <li>Excellence - Striving for the highest standards in education</li>
                <li>Respect - Valuing diversity and treating all with dignity</li>
                <li>Dedication - Commitment to student success and development</li>
                <li>Teamwork - Collaboration between staff, pupils, and parents</li>
                <li>Discipline - Maintaining order and responsibility</li>
            </ul>
        </div>
    </div>
    
    <div class="content-section">
        <h2>School Leadership</h2>
        <div class="staff-grid">
            <div class="staff-member">
                <div class="photo"><img src="/NYAKABINGO_PRIMARY/images/uploads/head_teacher.jpg" alt="Head Teacher"></div>
                <h3><?php echo $school_config['principal']['name']; ?></h3>
                <p class="position"><?php echo $school_config['principal']['title']; ?></p>
                <p class="qualification">Responsible for overall school management, academic quality, and policy implementation</p>
            </div>
            <div class="staff-member">
                <div class="photo"><img src="/NYAKABINGO_PRIMARY/images/uploads/deputy-head-teacher.jpg" alt="Deputy Head Teacher"></div>
                <h3><?php echo $school_config['deputy_principal']['name']; ?></h3>
                <p class="position"><?php echo $school_config['deputy_principal']['title']; ?></p>
                <p class="qualification">Supporting Head Teacher and overseeing pupil discipline and welfare</p>
            </div>
        </div>
    </div>

    <div class="content-section">
        <h2>Professional Teaching &amp; Support Staff</h2>
        <p>
            Our school is staffed by dedicated and qualified teachers and support staff committed to providing quality
            education and mentorship to all our pupils. Each staff member plays a crucial role in creating a supportive
            learning environment.
        </p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Number of Staff</h3>
                <p>15+</p>
            </div>
            <div class="stat-card">
                <h3>Teachers</h3>
                <p>5+</p>
            </div>
            <div class="stat-card">
                <h3>Support Staff</h3>
                <p>10+</p>
            </div>
        </div>

        <div class="photo-gallery-grid">
            <div class="photo-card">
                <img src="/NYAKABINGO_PRIMARY/images/uploads/staff1.jpg" alt="Staff Photo 1">
                <p>Staff Photo 1</p>
            </div>
            <div class="photo-card">
                <img src="/NYAKABINGO_PRIMARY/images/uploads/staff2.jpg" alt="Staff Photo 2">
                <p>Staff Photo 2</p>
            </div>
            <div class="photo-card">
                <img src="/NYAKABINGO_PRIMARY/images/uploads/staff3.jpg" alt="Staff Photo 3">
                <p>Staff Photo 3</p>
            </div>
            <div class="photo-card">
                <img src="/NYAKABINGO_PRIMARY/images/uploads/staff4.jpg" alt="Staff Photo 4">
                <p>Staff Photo 4</p>
            </div>
        </div>
    </div>

    <div class="content-section">
        <h2>Our Pupils</h2>
        <h3 style="color: #1e3a8a; margin-bottom: 10px;">Brilliant Young Learners</h3>
        <p>
            Nyakabingo Primary School serves pupils from various backgrounds and communities. We provide a nurturing
            environment where each pupil can develop academically, socially, and physically. Our pupils come from
            families within Nyakabingo Parish and surrounding areas.
        </p>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Pupils</h3>
                <p>7</p>
            </div>
            <div class="stat-card">
                <h3>Classes</h3>
                <p>1</p>
            </div>
            <div class="stat-card">
                <h3>Pupil:Teacher Ratio</h3>
                <p>1:45</p>
            </div>
        </div>

        <div class="photo-gallery-grid">
            <div class="photo-card">
                <img src="/NYAKABINGO_PRIMARY/images/uploads/pupils1.jpg" alt="Pupils Photo 1">
                <p>Pupils Photo 1</p>
            </div>
            <div class="photo-card">
                <img src="/NYAKABINGO_PRIMARY/images/uploads/pupils2.jpg" alt="Pupils Photo 2">
                <p>Pupils Photo 2</p>
            </div>
            <div class="photo-card">
                <img src="/NYAKABINGO_PRIMARY/images/uploads/pupils3.jpg" alt="Pupils Photo 3">
                <p>Pupils Photo 3</p>
            </div>
            <div class="photo-card">
                <img src="/NYAKABINGO_PRIMARY/images/uploads/pupils4.jpg" alt="Pupils Photo 4">
                <p>Pupils Photo 4</p>
            </div>
        </div>
    </div>
    
    <div class="content-section">
        <h2>Our Facilities</h2>
        <p>
            Nyakabingo Primary School is equipped with essential learning facilities to support quality education:
        </p>
        <div class="values-list">
            <ul>
                <li>Adequate Classrooms - Well-ventilated and spacious learning spaces</li>
                <li>Staff Room - Dedicated space for teachers to plan and prepare lessons</li>
                <li>Administrative Office - For academic records and pupil management</li>
                <li>Sports Grounds - For physical education and athletic activities</li>
                <li>Playgrounds - Safe spaces for recreational activities</li>
                <li>Water & Sanitation - Adequate facilities for pupil health and hygiene</li>
            </ul>
        </div>
    </div>
    
    <div class="content-section">
        <h2>Government Support</h2>
        <p>
            As a government-aided school, Nyakabingo Primary School operates under the Ministry of Education's guidelines
            and standards. We receive government support for teachers' salaries and operational expenses, which allows us to
            provide affordable primary education to children in our community. Our commitment to the national curriculum and
            education standards ensures that our graduates are well-prepared for further education.
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
