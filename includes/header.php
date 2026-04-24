<?php
include 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $school_config['name']; ?> - <?php echo $school_config['motto']; ?></title>
    <link rel="stylesheet" href="/NYAKABINGO_PRIMARY/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        /* Header Styles */
        header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .header-hero {
            position: relative;
            padding: 60px 20px;
            text-align: center;
            background:
                linear-gradient(135deg, rgba(30, 58, 138, 0.6) 0%, rgba(59, 130, 246, 0.55) 100%),
                url('/NYAKABINGO_PRIMARY/images/uploads/school_bag.jpg') center/cover no-repeat;
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .header-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 20%, rgba(255, 255, 255, 0.25), transparent 45%);
            z-index: 1;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(-50%, -50%) translateY(0px); }
            50% { transform: translate(-50%, -50%) translateY(-15px); }
        }
        
        .header-hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
            animation: float 3.2s ease-in-out infinite;
        }
        
        .header-hero h1 {
            font-size: 42px;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            animation: slideDown 0.8s ease-out;
        }
        
        .header-hero p {
            font-size: 18px;
            opacity: 0.95;
            margin-bottom: 15px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }
        
        .header-contact-hero {
            background: transparent;
            padding: 0;
            border-radius: 0;
            display: inline-block;
            margin-top: 15px;
            border: none;
        }
        
        .header-contact-hero p {
            margin: 5px 0;
            font-size: 16px;
        }
        
        .header-contact-hero strong {
            display: block;
            font-size: 20px;
            margin-top: 8px;
            color: #fbbf24;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Navigation */
        nav {
            background: #1e3a8a;
            padding: 0;
        }
        
        nav ul {
            max-width: 1200px;
            margin: 0 auto;
            list-style: none;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        nav ul li {
            margin: 0;
        }
        
        nav ul li a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            transition: background 0.3s;
        }
        
        nav ul li a:hover {
            background: #3b82f6;
        }
        
        nav ul li a.active {
            background: #60a5fa;
            border-bottom: 3px solid #fbbf24;
        }
    </style>
</head>
<body>

<header>
    <div class="header-hero">
        <div class="header-hero-content">
            <h1><?php echo $school_config['name']; ?></h1>
            <p><?php echo $school_config['motto']; ?></p>
            <div class="header-contact-hero">
                <p><?php echo $school_config['location']; ?></p>
                <strong><?php echo $school_config['contact_phone']; ?></strong>
            </div>
        </div>
    </div>
    
    <nav>
        <ul>
            <li><a href="/NYAKABINGO_PRIMARY/index.php">Home</a></li>
            <li><a href="/NYAKABINGO_PRIMARY/pages/about.php">About Us</a></li>
            <li><a href="/NYAKABINGO_PRIMARY/pages/academics.php">Academics</a></li>
            <li><a href="/NYAKABINGO_PRIMARY/pages/activities.php">Activities</a></li>
            <li><a href="/NYAKABINGO_PRIMARY/pages/gallery.php">Gallery</a></li>
            <li><a href="/NYAKABINGO_PRIMARY/pages/contact.php">Contact</a></li>
        </ul>
    </nav>
</header>
