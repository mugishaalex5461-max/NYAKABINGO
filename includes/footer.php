<?php
include 'config.php';
?>

<footer>
    <div class="footer-content">
        <div class="footer-section">
            <h3><?php echo $school_config['name']; ?></h3>
            <p><?php echo $school_config['location']; ?></p>
            <p><strong>Classification:</strong> <?php echo $school_config['classification']; ?></p>
        </div>
        
        <div class="footer-section">
            <h3>Contact Information</h3>
            <p><strong>Email:</strong> <?php echo $school_config['contact_email']; ?></p>
            <p><strong>Phone:</strong> <?php echo $school_config['contact_phone']; ?></p>
            <p><strong>District:</strong> Kisoro District, Uganda</p>
        </div>
        
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="/NYAKABINGO_PRIMARY/index.php">Home</a></li>
                <li><a href="/NYAKABINGO_PRIMARY/pages/about.php">About</a></li>
                <li><a href="/NYAKABINGO_PRIMARY/pages/academics.php">Academics</a></li>
                <li><a href="/NYAKABINGO_PRIMARY/pages/gallery.php">Gallery</a></li>
                <li><a href="/NYAKABINGO_PRIMARY/pages/gallery.php?admin=1">Admin Login</a></li>
                <li><a href="/NYAKABINGO_PRIMARY/pages/contact.php">Contact</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h3>Follow Us</h3>
            <div class="social-links">
                <a href="https://facebook.com">Facebook</a> | 
                <a href="https://twitter.com">Twitter</a> | 
                <a href="https://instagram.com">Instagram</a>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; 2026 <?php echo $school_config['name']; ?> | All Rights Reserved | Government-Aided Primary School</p>
    </div>
</footer>

<style>
    footer {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        color: white;
        margin-top: 50px;
        padding: 40px 20px;
    }
    
    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        margin-bottom: 30px;
    }
    
    .footer-section h3 {
        margin-bottom: 15px;
        color: #fbbf24;
    }
    
    .footer-section p {
        margin-bottom: 10px;
        opacity: 0.9;
    }
    
    .footer-section ul {
        list-style: none;
    }
    
    .footer-section ul li {
        margin-bottom: 8px;
    }
    
    .footer-section a {
        color: #bfdbfe;
        text-decoration: none;
        transition: color 0.3s;
    }
    
    .footer-section a:hover {
        color: #fbbf24;
    }
    
    .social-links {
        margin-top: 10px;
    }
    
    .footer-bottom {
        max-width: 1200px;
        margin: 0 auto;
        padding-top: 20px;
        border-top: 1px solid rgba(255,255,255,0.2);
        text-align: center;
        opacity: 0.8;
    }
</style>

</body>
</html>
