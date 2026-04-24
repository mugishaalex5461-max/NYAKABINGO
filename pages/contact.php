<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$form_data = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'subject' => '',
    'message' => ''
];

$form_errors = [];
$form_success = '';

$subject_options = [
    'admission',
    'academic',
    'facility',
    'event',
    'feedback',
    'other'
];

if (empty($_SESSION['contact_csrf_token'])) {
    $_SESSION['contact_csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['contact_csrf_token'], $submitted_token)) {
        $form_errors[] = 'Your session expired. Please refresh and try again.';
    }

    $form_data['name'] = trim($_POST['name'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['phone'] = trim($_POST['phone'] ?? '');
    $form_data['subject'] = trim($_POST['subject'] ?? '');
    $form_data['message'] = trim($_POST['message'] ?? '');

    if ($form_data['name'] === '' || strlen($form_data['name']) < 2) {
        $form_errors[] = 'Please provide a valid full name.';
    }

    if (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $form_errors[] = 'Please provide a valid email address.';
    }

    if ($form_data['phone'] !== '' && strlen($form_data['phone']) > 30) {
        $form_errors[] = 'Phone number is too long.';
    }

    if (!in_array($form_data['subject'], $subject_options, true)) {
        $form_errors[] = 'Please select a valid subject.';
    }

    if ($form_data['message'] === '' || strlen($form_data['message']) < 10) {
        $form_errors[] = 'Your message should be at least 10 characters long.';
    }

    if (empty($form_errors)) {
        $log_dir = realpath(__DIR__ . '/../media');
        $log_file = $log_dir ? $log_dir . '/contact_messages.log' : __DIR__ . '/../media/contact_messages.log';

        $entry = "Date: " . date('Y-m-d H:i:s') . PHP_EOL;
        $entry .= "Name: " . $form_data['name'] . PHP_EOL;
        $entry .= "Email: " . $form_data['email'] . PHP_EOL;
        $entry .= "Phone: " . ($form_data['phone'] !== '' ? $form_data['phone'] : 'N/A') . PHP_EOL;
        $entry .= "Subject: " . $form_data['subject'] . PHP_EOL;
        $entry .= "Message:" . PHP_EOL . $form_data['message'] . PHP_EOL;
        $entry .= str_repeat('-', 60) . PHP_EOL;

        $written = @file_put_contents($log_file, $entry, FILE_APPEND | LOCK_EX);

        if ($written === false) {
            $form_errors[] = 'We could not save your message right now. Please try again shortly.';
        } else {
            $form_success = 'Thank you for your message. Our team will get back to you shortly.';
            $form_data = [
                'name' => '',
                'email' => '',
                'phone' => '',
                'subject' => '',
                'message' => ''
            ];
            $_SESSION['contact_csrf_token'] = bin2hex(random_bytes(32));
        }
    }
}

include '../includes/header.php';

$whatsapp_number = $school_config['contact_whatsapp'] ?? $school_config['contact_phone'];
$whatsapp_digits = preg_replace('/\D+/', '', $whatsapp_number);
$whatsapp_link = $whatsapp_digits !== '' ? 'https://wa.me/' . $whatsapp_digits : '#';
?>

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
    
    .contact-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        margin-bottom: 60px;
    }
    
    @media (max-width: 768px) {
        .contact-container {
            grid-template-columns: 1fr;
        }
    }
    
    .contact-info {
        background: #f9fafb;
        border-radius: 10px;
        padding: 30px;
        border: 2px solid #e5e7eb;
    }
    
    .contact-info h2 {
        color: #1e3a8a;
        margin-bottom: 30px;
        font-size: 28px;
    }
    
    .info-item {
        margin-bottom: 25px;
        padding-bottom: 25px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-item h3 {
        color: #3b82f6;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .contact-icon {
        width: 20px;
        height: 20px;
        color: currentColor;
        flex-shrink: 0;
    }
    
    .info-item p {
        color: #666;
        line-height: 1.6;
    }
    
    .contact-form {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        padding: 30px;
    }
    
    .contact-form h2 {
        color: #1e3a8a;
        margin-bottom: 30px;
        font-size: 28px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        color: #1e3a8a;
        font-weight: bold;
        margin-bottom: 8px;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 5px;
        font-family: inherit;
        font-size: 14px;
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 150px;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .submit-btn {
        background: linear-gradient(135deg, #3b82f6, #1e3a8a);
        color: white;
        padding: 12px 30px;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        width: 100%;
        font-size: 16px;
    }
    
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
    }
    
    .map-section {
        margin-top: 60px;
        background: #f9fafb;
        border-radius: 10px;
        padding: 30px;
    }
    
    .map-section h2 {
        color: #1e3a8a;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .map-placeholder {
        background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        border-radius: 10px;
        padding: 60px 20px;
        text-align: center;
        color: #1e3a8a;
    }
    
    .map-placeholder p {
        font-size: 18px;
        margin-bottom: 10px;
    }
    
    .section-header {
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
        padding: 20px;
        border-radius: 5px;
        margin-bottom: 30px;
    }
    
    .section-header h3 {
        color: #1e3a8a;
        margin-bottom: 8px;
    }
    
    .section-header p {
        color: #666;
    }

    .form-alert {
        border-radius: 8px;
        padding: 12px 14px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .form-alert-success {
        background: #ecfdf5;
        border: 1px solid #10b981;
        color: #065f46;
    }

    .form-alert-error {
        background: #fef2f2;
        border: 1px solid #ef4444;
        color: #991b1b;
    }

    .form-alert ul {
        margin: 0;
        padding-left: 20px;
    }
</style>

<div class="page-header">
    <h1>Contact Us</h1>
    <p>Get in Touch With Our School</p>
</div>

<div class="page-content">
    <div class="section-header">
        <h3>We're Here to Help</h3>
        <p>Have questions about admissions, academics, or activities? Contact us using any of the methods below.</p>
    </div>
    
    <div class="contact-container">
        <div class="contact-info">
            <h2>Contact Information</h2>
            
            <div class="info-item">
                <h3>
                    <img class="contact-icon" src="/NYAKABINGO_PRIMARY/images/icons/location.svg" alt="Location icon" loading="lazy">
                    <span>Location</span>
                </h3>
                <p>
                    <?php echo $school_config['name']; ?><br>
                    <?php echo $school_config['location']; ?><br>
                    Kisoro District, Uganda
                </p>
            </div>
            
            <div class="info-item">
                <h3>
                    <img class="contact-icon" src="/NYAKABINGO_PRIMARY/images/icons/phone.svg" alt="Phone icon" loading="lazy">
                    <span>Phone</span>
                </h3>
                <p><?php echo $school_config['contact_phone']; ?></p>
            </div>
            
            <div class="info-item">
                <h3>
                    <svg class="contact-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                        <path d="m4 7 8 6 8-6" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>Email</span>
                </h3>
                <p><a href="mailto:<?php echo $school_config['contact_email']; ?>" style="color: #3b82f6; text-decoration: none;">
                    <?php echo $school_config['contact_email']; ?>
                </a></p>
            </div>

            <div class="info-item">
                <h3>
                    <img class="contact-icon" src="/NYAKABINGO_PRIMARY/images/icons/whatsapp.svg" alt="WhatsApp icon" loading="lazy">
                    <span>WhatsApp</span>
                </h3>
                <p>
                    <a href="<?php echo htmlspecialchars($whatsapp_link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" style="color: #16a34a; text-decoration: none; font-weight: 600;">
                        Chat on WhatsApp: <?php echo htmlspecialchars($whatsapp_number, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </p>
            </div>
            
            <div class="info-item">
                <h3>
                    <svg class="contact-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 7v5l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Office Hours</span>
                </h3>
                <p>
                    Monday - Friday: 7:30 AM - 4:30 PM<br>
                    Saturday: 9:00 AM - 12:00 PM<br>
                    Sunday: Closed
                </p>
            </div>
            
            <div class="info-item">
                <h3>
                    <svg class="contact-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M3 9 12 4l9 5-9 5-9-5Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M7 11v4.5c0 1.4 2.24 2.5 5 2.5s5-1.1 5-2.5V11" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    <span>School Classification</span>
                </h3>
                <p><?php echo $school_config['classification']; ?></p>
            </div>
        </div>
        
        <div class="contact-form">
            <h2>Send us a Message</h2>
            <?php if ($form_success !== ''): ?>
                <div class="form-alert form-alert-success">
                    <?php echo htmlspecialchars($form_success, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($form_errors)): ?>
                <div class="form-alert form-alert-error">
                    <ul>
                        <?php foreach ($form_errors as $error): ?>
                            <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['contact_csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($form_data['name'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($form_data['email'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($form_data['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject *</label>
                    <select id="subject" name="subject" required>
                        <option value="">-- Select a Subject --</option>
                        <option value="admission" <?php echo $form_data['subject'] === 'admission' ? 'selected' : ''; ?>>Admission Inquiry</option>
                        <option value="academic" <?php echo $form_data['subject'] === 'academic' ? 'selected' : ''; ?>>Academic Question</option>
                        <option value="facility" <?php echo $form_data['subject'] === 'facility' ? 'selected' : ''; ?>>Facility Tour</option>
                        <option value="event" <?php echo $form_data['subject'] === 'event' ? 'selected' : ''; ?>>Event Information</option>
                        <option value="feedback" <?php echo $form_data['subject'] === 'feedback' ? 'selected' : ''; ?>>Feedback</option>
                        <option value="other" <?php echo $form_data['subject'] === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="message">Message *</label>
                    <textarea id="message" name="message" required><?php echo htmlspecialchars($form_data['message'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Send Message</button>
            </form>
        </div>
    </div>
    
    <div class="map-section">
        <h2>School Location</h2>
        <div class="map-placeholder">
            <p>📍 Nyakabingo Primary School</p>
            <p>Nyakabingo Parish, Kisoro District, South Western Uganda</p>
            <p style="font-size: 14px; color: #666; margin-top: 20px;">Interactive map coming soon</p>
        </div>
    </div>
    
    <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; border-radius: 5px; margin-top: 40px;">
        <h3 style="color: #1e3a8a; margin-bottom: 10px;">🎓 Admission Information</h3>
        <p style="color: #666; margin-bottom: 10px;">
            Interested in joining Nyakabingo Primary School? We welcome inquiries from parents and guardians. 
            Please contact our office for:
        </p>
        <ul style="color: #666; margin-left: 20px;">
            <li>Admission procedures and requirements</li>
            <li>School prospectus and policies</li>
            <li>Facility tours and school visits</li>
            <li>Curriculum information</li>
        </ul>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
