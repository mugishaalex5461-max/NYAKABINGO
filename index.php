<?php
require_once __DIR__ . '/includes/gallery_admin_auth.php';
gallery_admin_bootstrap();

$auth_error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['gallery_auth_action'])) {
    $auth_action = (string) $_POST['gallery_auth_action'];

    if ($auth_action === 'login') {
        $email_input = trim((string) ($_POST['admin_email'] ?? ''));
        $password_input = (string) ($_POST['admin_password'] ?? '');

        if (!gallery_admin_login($email_input, $password_input)) {
            $auth_error = 'Invalid admin credentials.';
        }
    } elseif ($auth_action === 'logout') {
        gallery_admin_logout();
    }
}

$is_gallery_admin = gallery_is_admin();
$base_url = '/NYAKABINGO_PRIMARY';
$uploads_dir = __DIR__ . '/images/uploads/';

function render_home_gallery_slot(string $label, string $filename, bool $isAdmin, string $uploadsDir, string $baseUrl): void
{
    $imagePath = $uploadsDir . $filename;
    $imageExists = is_file($imagePath);
    $imageVersion = $imageExists ? (filemtime($imagePath) ?: time()) : 0;
    $imageUrl = $baseUrl . '/images/uploads/' . rawurlencode($filename) . ($imageExists ? ('?v=' . $imageVersion) : '');
    ?>
    <div class="gallery-image-item home-gallery-slot" data-filename="<?php echo htmlspecialchars($filename); ?>" data-has-image="<?php echo $imageExists ? '1' : '0'; ?>">
        <div class="home-slot-preview <?php echo $isAdmin ? 'is-clickable' : ''; ?>" title="<?php echo $isAdmin ? 'Click to upload or replace image' : ''; ?>">
            <?php if ($imageExists): ?>
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="<?php echo htmlspecialchars($label); ?>">
            <?php else: ?>
                <div class="home-slot-placeholder">📸 <?php echo htmlspecialchars($label); ?><br><small>(upload: <?php echo htmlspecialchars($filename); ?>)</small></div>
            <?php endif; ?>
        </div>
        <div class="home-slot-meta"><?php echo htmlspecialchars($label); ?></div>
        <?php if ($isAdmin): ?>
            <div class="home-slot-actions">
                <button type="button" class="home-slot-btn upload home-slot-upload" data-filename="<?php echo htmlspecialchars($filename); ?>"><?php echo $imageExists ? 'Replace' : 'Upload'; ?></button>
                <button type="button" class="home-slot-btn delete home-slot-delete" data-filename="<?php echo htmlspecialchars($filename); ?>" <?php echo $imageExists ? '' : 'disabled'; ?>>Delete</button>
                <input type="file" class="home-slot-input" accept="image/*" hidden>
            </div>
            <div class="home-slot-hint">Click image or Upload/Replace to manage this slot.</div>
        <?php else: ?>
            <div class="home-slot-note">View only</div>
        <?php endif; ?>
    </div>
    <?php
}

include 'includes/header.php';
?>

<style>
    .hero-section {
        background: linear-gradient(rgba(30, 58, 138, 0.8), rgba(59, 130, 246, 0.8)), url('/NYAKABINGO_PRIMARY/images/hero-bg.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 100px 20px;
        text-align: center;
        min-height: 500px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .hero-section h2 {
        font-size: 48px;
        margin-bottom: 20px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }
    
    .hero-section p {
        font-size: 20px;
        max-width: 600px;
        margin: 0 auto 30px;
        line-height: 1.8;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }
    
    .cta-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .cta-buttons a {
        padding: 12px 30px;
        background: #fbbf24;
        color: #1e3a8a;
        text-decoration: none;
        border-radius: 5px;
        font-weight: bold;
        transition: background 0.3s;
    }
    
    .cta-buttons a:hover {
        background: #f59e0b;
    }
    
    .cta-buttons a.secondary {
        background: transparent;
        border: 2px solid white;
        color: white;
    }
    
    .cta-buttons a.secondary:hover {
        background: white;
        color: #1e3a8a;
    }
    
    .welcome-section {
        max-width: 1200px;
        margin: 60px auto;
        padding: 0 20px;
    }
    
    .welcome-section h2 {
        font-size: 36px;
        color: #1e3a8a;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .welcome-section p {
        font-size: 18px;
        color: #555;
        line-height: 1.8;
        margin-bottom: 20px;
        text-align: justify;
    }
    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }
    
    .feature-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .feature-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        transform: translateY(-5px);
    }
    
    .feature-card h3 {
        color: #1e3a8a;
        margin-bottom: 15px;
        font-size: 22px;
    }
    
    .feature-card p {
        color: #666;
    }
    
    .staff-section, .pupils-section, .compound-section {
        background: #fff;
        padding: 40px 20px;
        margin-top: 40px;
        border-radius: 10px;
        border: 2px solid #e5e7eb;
    }
    
    .staff-section h3, .pupils-section h3, .compound-section h3 {
        color: #1e3a8a;
        font-size: 24px;
        margin-bottom: 20px;
        border-bottom: 2px solid #3b82f6;
        padding-bottom: 10px;
    }
    
    .staff-section p, .pupils-section p, .compound-section p {
        color: #666;
        line-height: 1.8;
        margin-bottom: 15px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .stat-box {
        background: #f3f4f6;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        border-left: 4px solid #3b82f6;
    }
    
    .stat-box .number {
        font-size: 32px;
        font-weight: bold;
        color: #1e3a8a;
    }
    
    .stat-box .label {
        color: #666;
        font-size: 14px;
        margin-top: 5px;
    }
    
    .image-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 25px;
    }
    
    .gallery-image-item {
        background: #f3f4f6;
        border: 2px dashed #bfdbfe;
        border-radius: 8px;
        overflow: hidden;
        min-height: 200px;
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        color: #999;
        font-size: 14px;
        text-align: center;
        padding: 0;
    }
    
    .gallery-image-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .home-manager-panel {
        max-width: 1200px;
        margin: 20px auto 0;
        padding: 0 20px;
    }

    .home-manager-box {
        background: #eff6ff;
        border: 2px solid #bfdbfe;
        border-radius: 10px;
        padding: 18px;
    }

    .home-manager-box h3 {
        color: #1e3a8a;
        margin-bottom: 10px;
        font-size: 20px;
    }

    .home-manager-box p {
        color: #334155;
        margin-bottom: 12px;
    }

    .home-auth-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .home-auth-row input {
        flex: 1;
        min-width: 220px;
        padding: 10px;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
    }

    .home-auth-btn {
        padding: 10px 14px;
        border: 0;
        border-radius: 6px;
        background: #1d4ed8;
        color: #fff;
        font-weight: 600;
        cursor: pointer;
    }

    .home-auth-btn.logout {
        background: #b91c1c;
    }

    .home-auth-error {
        color: #b91c1c;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .home-manager-status {
        margin-top: 10px;
        display: none;
        padding: 10px;
        border-radius: 6px;
        font-size: 14px;
        border: 1px solid transparent;
    }

    .home-manager-status.success {
        display: block;
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }

    .home-manager-status.error {
        display: block;
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }

    .home-gallery-slot {
        border: 2px dashed #bfdbfe;
        background: #f8fafc;
    }

    .home-slot-preview {
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #eef2ff;
    }

    .home-slot-preview.is-clickable {
        cursor: pointer;
    }

    .home-slot-preview.is-clickable:hover {
        filter: brightness(0.98);
    }

    .home-slot-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .home-slot-placeholder {
        color: #64748b;
        padding: 12px;
        line-height: 1.4;
    }

    .home-slot-meta {
        padding: 8px 10px 4px;
        color: #1e3a8a;
        font-weight: 600;
        font-size: 13px;
    }

    .home-slot-actions {
        display: flex;
        gap: 8px;
        padding: 0 10px 10px;
    }

    .home-slot-btn {
        flex: 1;
        border: 0;
        border-radius: 6px;
        padding: 7px 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        color: #fff;
    }

    .home-slot-btn.upload {
        background: #2563eb;
    }

    .home-slot-btn.delete {
        background: #dc2626;
    }

    .home-slot-btn:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .home-slot-note {
        padding: 0 10px 10px;
        color: #64748b;
        font-size: 12px;
    }

    .home-slot-hint {
        padding: 0 10px 10px;
        color: #475569;
        font-size: 12px;
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
    
    .icon {
        font-size: 40px;
        margin-bottom: 15px;
    }
    
    .leadership-section {
        background: #f3f4f6;
        padding: 60px 20px;
        margin-top: 60px;
    }
    
    .leadership-section h2 {
        font-size: 36px;
        color: #1e3a8a;
        text-align: center;
        margin-bottom: 50px;
    }
    
    .leadership-grid {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 40px;
    }
    
    .leader-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .leader-photo {
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 80px;
        overflow: hidden;
    }
    
    .leader-photo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }
    
    .leader-info {
        padding: 20px;
    }
    
    .leader-info h3 {
        color: #1e3a8a;
        margin-bottom: 5px;
        font-size: 20px;
    }
    
    .leader-info p.title {
        color: #f59e0b;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .leader-info p.bio {
        color: #666;
        font-size: 14px;
    }
</style>

<div class="hero-section">
    <h2>Welcome to <?php echo $school_config['name']; ?></h2>
    <p><?php echo $school_config['motto']; ?></p>
    <p>Excellence Through Education and Character Development</p>
    <div class="cta-buttons">
        <a href="/NYAKABINGO_PRIMARY/pages/about.php">Learn More About Us</a>
        <a href="/NYAKABINGO_PRIMARY/pages/contact.php" class="secondary">Get in Touch</a>
    </div>
</div>

<div class="welcome-section">
    <h2>Welcome to Our School Community</h2>
    <p>
        At Nyakabingo Primary School, we are committed to providing quality education to all our pupils. 
        As a government-aided institution located in Kisoro District, we serve the communities of Nyakabingo Parish 
        with dedicated teaching and support for holistic child development. Our focus is on academic excellence, 
        character building, and preparing our pupils to be responsible citizens.
    </p>
    <p>
        We provide a supportive and inspirational environment where young minds can learn, grow, and develop their 
        full potential. Our experienced staff work tirelessly to ensure that every child receives quality education 
        and guidance to succeed in their academic journey and beyond.
    </p>
    
    <div class="features-grid">
        <div class="feature-card">
            <div class="icon">📚</div>
            <h3>Quality Academics</h3>
            <p>Comprehensive curriculum focused on Primary Education Standards with emphasis on foundational skills</p>
        </div>
        <div class="feature-card">
            <div class="icon">🏆</div>
            <h3>Excellence</h3>
            <p>Dedicated to achieving outstanding results and preparing students for secondary education</p>
        </div>
        <div class="feature-card">
            <div class="icon">❤️</div>
            <h3>Holistic Development</h3>
            <p>Character building, sports, and co-curricular activities for balanced development</p>
        </div>
        <div class="feature-card">
            <div class="icon">👥</div>
            <h3>Community Focus</h3>
            <p>Active engagement with parents and community in supporting student growth</p>
        </div>
    </div>
</div>

<?php if ($is_gallery_admin): ?>
<div class="home-manager-panel">
    <div id="home-auth-box" class="home-manager-box">
        <h3>Home Page Photo Manager</h3>
        <p>You are logged in as admin. Click Upload or Delete on any Staff, Pupils, or Compound photo slot below.</p>
        <form method="post">
            <input type="hidden" name="gallery_auth_action" value="logout">
            <button type="submit" class="home-auth-btn logout">Logout Admin</button>
        </form>
        <div id="home-manager-status" class="home-manager-status"></div>
    </div>
</div>
<?php endif; ?>

<div class="leadership-section">
    <h2>Our School Leadership</h2>
    <div class="leadership-grid">
        <div class="leader-card">
            <div class="leader-photo"><img src="/NYAKABINGO_PRIMARY/images/uploads/head_teacher.jpg" alt="Head Teacher"></div>
            <div class="leader-info">
                <h3><?php echo $school_config['principal']['name']; ?></h3>
                <p class="title"><?php echo $school_config['principal']['title']; ?></p>
                <p class="bio"><?php echo $school_config['principal']['bio']; ?></p>
            </div>
        </div>
        <div class="leader-card">
            <div class="leader-photo"><img src="/NYAKABINGO_PRIMARY/images/uploads/deputy-head-teacher.jpg" alt="Deputy Head Teacher"></div>
            <div class="leader-info">
                <h3><?php echo $school_config['deputy_principal']['name']; ?></h3>
                <p class="title"><?php echo $school_config['deputy_principal']['title']; ?></p>
                <p class="bio"><?php echo $school_config['deputy_principal']['bio']; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="leadership-section">
    <h2>Our School Staff Members</h2>
    <div class="staff-section">
        <h3>Professional Teaching & Support Staff</h3>
        <p>
            Our school is staffed by dedicated and qualified teachers and support staff committed to providing quality education
            and mentorship to all our pupils. Each staff member plays a crucial role in creating a supportive learning environment.
        </p>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="number"><?php echo isset($school_config['staff_count']) ? $school_config['staff_count'] : '20+'; ?></div>
                <div class="label">Total Staff</div>
            </div>
            <div class="stat-box">
                <div class="number">15+</div>
                <div class="label">Teachers</div>
            </div>
            <div class="stat-box">
                <div class="number">5+</div>
                <div class="label">Support Staff</div>
            </div>
        </div>
        <div class="image-gallery">
            <?php render_home_gallery_slot('Staff Photo 1', 'staff1.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
            <?php render_home_gallery_slot('Staff Photo 2', 'staff2.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
            <?php render_home_gallery_slot('Staff Photo 3', 'staff3.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
            <?php render_home_gallery_slot('Staff Photo 4', 'staff4.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
        </div>
    </div>
</div>

<div class="leadership-section">
    <h2>Our Pupils</h2>
    <div class="pupils-section">
        <h3>Brilliant Young Learners</h3>
        <p>
            Nyakabingo Primary School serves pupils from various backgrounds and communities. We provide a nurturing environment
            where each pupil can develop academically, socially, and physically. Our pupils come from families within Nyakabingo Parish
            and surrounding areas.
        </p>
        <div class="stats-grid">
            <div class="stat-box">
                <div class="number"><?php echo isset($school_config['students_count']) ? $school_config['students_count'] : '300+'; ?></div>
                <div class="label">Total Pupils</div>
            </div>
            <div class="stat-box">
                <div class="number">7</div>
                <div class="label">Classes</div>
            </div>
            <div class="stat-box">
                <div class="number">1:45</div>
                <div class="label">Pupil:Teacher Ratio</div>
            </div>
        </div>
        <div class="image-gallery">
            <?php render_home_gallery_slot('Pupils Photo 1', 'pupils1.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
            <?php render_home_gallery_slot('Pupils Photo 2', 'pupils2.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
            <?php render_home_gallery_slot('Pupils Photo 3', 'pupils3.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
            <?php render_home_gallery_slot('Pupils Photo 4', 'pupils4.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
        </div>
    </div>
</div>

<div class="leadership-section">
    <h2>School Compound & Facilities</h2>
    <div class="compound-section">
        <h3>Modern Learning Infrastructure</h3>
        <p>
            Our school compound spans a spacious area with well-maintained facilities designed to support effective learning and
            holistic development of pupils. We continuously invest in infrastructure to ensure a safe, clean, and conducive learning environment.
        </p>
        <div class="values-list">
            <ul>
                <li>7 Well-Lit Classrooms - Spacious and ventilated learning spaces with modern furniture</li>
                <li>Staff Office - Dedicated workspace for administrative and teaching staff</li>
                <li>Pupils' Common Room - Safe space for pupils during free periods and lunch breaks</li>
                <li>Multipurpose Hall - Used for assemblies, events, and indoor activities</li>
                <li>Sports Grounds - Football pitch, running tracks, and athletic facilities</li>
                <li>Playground - Safe recreational area for younger pupils</li>
                <li>Water & Sanitation - Modern toilet facilities, hand-washing stations, and clean water supply</li>
                <li>School Farm - Vegetable garden and agricultural learning space</li>
                <li>Library Corner - Reading resources and educational materials</li>
            </ul>
        </div>
        <div class="image-gallery">
            <?php render_home_gallery_slot('Compound Photo 1', 'compound1.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
            <?php render_home_gallery_slot('Compound Photo 2', 'compound2.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
            <?php render_home_gallery_slot('Compound Photo 3', 'compound3.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
            <?php render_home_gallery_slot('Compound Photo 4', 'compound4.jpg', $is_gallery_admin, $uploads_dir, $base_url); ?>
        </div>
    </div>
</div>

<script>
window.GALLERY_IS_ADMIN = <?php echo $is_gallery_admin ? 'true' : 'false'; ?>;

document.addEventListener('DOMContentLoaded', function () {
    const uploadEndpoint = '/NYAKABINGO_PRIMARY/pages/upload_images.php';
    const manageEndpoint = '/NYAKABINGO_PRIMARY/pages/manage_uploads.php';
    const statusEl = document.getElementById('home-manager-status');

    if (!window.GALLERY_IS_ADMIN) {
        return;
    }

    function showStatus(message, type) {
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.className = 'home-manager-status ' + (type || '');
    }

    function requireAdmin() {
        if (window.GALLERY_IS_ADMIN) {
            return true;
        }

        showStatus('Admin login required first.', 'error');
        const authBox = document.getElementById('home-auth-box');
        const emailInput = document.getElementById('admin-email-input');
        if (authBox) {
            authBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        if (emailInput) {
            emailInput.focus();
        }
        return false;
    }

    function setBusy(button, busy, text) {
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        if (!button.dataset.originalText) {
            button.dataset.originalText = button.textContent || '';
        }

        button.disabled = busy;
        button.textContent = busy ? (text || 'Working...') : button.dataset.originalText;
    }

    async function uploadToSlot(filename, file, button) {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('target_filename', filename);

        setBusy(button, true, 'Uploading...');
        showStatus('Uploading ' + filename + '...', '');

        try {
            const response = await fetch(uploadEndpoint, {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Server returned invalid response.');
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Upload failed.');
            }

            showStatus('Saved ' + filename + ' successfully.', 'success');
            setTimeout(function () {
                window.location.reload();
            }, 700);
        } catch (error) {
            showStatus('Upload failed: ' + error.message, 'error');
            setBusy(button, false);
        }
    }

    async function deleteSlot(filename, button) {
        const confirmDelete = window.confirm('Delete ' + filename + '?');
        if (!confirmDelete) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('media_type', 'images');
        formData.append('filename', filename);

        setBusy(button, true, 'Deleting...');
        showStatus('Deleting ' + filename + '...', '');

        try {
            const response = await fetch(manageEndpoint, {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Server returned invalid response.');
            }

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Delete failed.');
            }

            showStatus('Deleted ' + filename + ' successfully.', 'success');
            setTimeout(function () {
                window.location.reload();
            }, 700);
        } catch (error) {
            showStatus('Delete failed: ' + error.message, 'error');
            setBusy(button, false);
        }
    }

    document.querySelectorAll('.home-slot-upload').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!requireAdmin()) {
                return;
            }

            const slot = button.closest('.home-gallery-slot');
            const input = slot ? slot.querySelector('.home-slot-input') : null;
            if (input) {
                input.click();
            }
        });
    });

    document.querySelectorAll('.home-slot-preview').forEach(function (preview) {
        preview.addEventListener('click', function () {
            if (!requireAdmin()) {
                return;
            }

            const slot = preview.closest('.home-gallery-slot');
            const input = slot ? slot.querySelector('.home-slot-input') : null;
            if (input) {
                input.click();
            }
        });
    });

    document.querySelectorAll('.home-slot-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const file = input.files && input.files[0] ? input.files[0] : null;
            const slot = input.closest('.home-gallery-slot');
            const uploadBtn = slot ? slot.querySelector('.home-slot-upload') : null;
            const filename = uploadBtn ? uploadBtn.getAttribute('data-filename') : '';

            if (!file || !uploadBtn || !filename) {
                return;
            }

            if (!file.type.startsWith('image/')) {
                showStatus('Please choose a valid image file for ' + filename + '.', 'error');
                input.value = '';
                return;
            }

            if (file.size > (5 * 1024 * 1024)) {
                showStatus('Image is too large. Max allowed size is 5MB.', 'error');
                input.value = '';
                return;
            }

            uploadToSlot(filename, file, uploadBtn);
            input.value = '';
        });
    });

    document.querySelectorAll('.home-slot-delete').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!requireAdmin()) {
                return;
            }

            const filename = button.getAttribute('data-filename') || '';
            if (!filename) {
                return;
            }

            deleteSlot(filename, button);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
