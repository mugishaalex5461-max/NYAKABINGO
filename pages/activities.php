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
    
    .activities-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 30px;
    }
    
    .activity-card {
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .activity-card:hover {
        border-color: #3b82f6;
        box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        transform: translateY(-5px);
    }
    
    .activity-card .header {
        background: linear-gradient(135deg, #3b82f6, #1e3a8a);
        color: white;
        padding: 30px;
        text-align: center;
        font-size: 50px;
    }
    
    .activity-card .content {
        padding: 20px;
    }
    
    .activity-card h3 {
        color: #1e3a8a;
        margin-bottom: 10px;
        font-size: 22px;
    }
    
    .activity-card p {
        color: #666;
        font-size: 15px;
        line-height: 1.6;
    }
    
    .events-section {
        background: #f9fafb;
        border-radius: 10px;
        padding: 30px;
        margin-top: 30px;
    }
    
    .event-item {
        background: white;
        padding: 20px;
        margin-bottom: 15px;
        border-radius: 8px;
        border-left: 4px solid #3b82f6;
    }
    
    .event-item:last-child {
        margin-bottom: 0;
    }
    
    .event-item h4 {
        color: #1e3a8a;
        margin-bottom: 8px;
    }
    
    .event-item p {
        color: #666;
        margin-bottom: 5px;
    }
    
    .event-date {
        color: #f59e0b;
        font-weight: bold;
    }
</style>

<div class="page-header">
    <h1>Co-Curricular Activities</h1>
    <p>Developing Well-Rounded Citizens</p>
</div>

<div class="page-content">
    <div class="content-section">
        <h2>Our Commitment to Holistic Development</h2>
        <p>
            At Nyakabingo Primary School, we believe that education goes beyond the classroom. Our co-curricular 
            activities program is designed to develop students' talents, interests, and character. Through sports, 
            cultural activities, and practical experiences, we help students build confidence, leadership skills, 
            and a sense of belonging to our school community.
        </p>
    </div>
    
    <div class="content-section">
        <h2>Our Activities</h2>
        <div class="activities-grid">
            <div class="activity-card">
                <div class="header">⚽</div>
                <div class="content">
                    <h3>Sports & Athletics</h3>
                    <p>
                        Students participate in football, netball, volleyball, running races, and other sports. 
                        Sports build teamwork, discipline, and physical fitness.
                    </p>
                </div>
            </div>
            
            <div class="activity-card">
                <div class="header">🎨</div>
                <div class="content">
                    <h3>Creative Arts & Crafts</h3>
                    <p>
                        Students explore painting, drawing, sculpture, and other creative art forms. 
                        This encourages imagination and self-expression.
                    </p>
                </div>
            </div>
            
            <div class="activity-card">
                <div class="header">🎭</div>
                <div class="content">
                    <h3>Drama & Performance</h3>
                    <p>
                        Students participate in plays, skits, and performances during school events. 
                        This builds confidence in public speaking and performance.
                    </p>
                </div>
            </div>
            
            <div class="activity-card">
                <div class="header">🎵</div>
                <div class="content">
                    <h3>Music & Choir</h3>
                    <p>
                        Our school choir performs at events and teaches students singing, music theory, and appreciation 
                        of our cultural heritage.
                    </p>
                </div>
            </div>
            
            <div class="activity-card">
                <div class="header">📚</div>
                <div class="content">
                    <h3>Debates & Clubs</h3>
                    <p>
                        Students participate in debating competitions and join various clubs like science club, 
                        environmental club, and reading clubs.
                    </p>
                </div>
            </div>
            
            <div class="activity-card">
                <div class="header">🌱</div>
                <div class="content">
                    <h3>Environmental Activities</h3>
                    <p>
                        Students engage in environmental conservation, tree planting, and waste management programs 
                        to develop environmental awareness.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="content-section">
        <h2>School Events & Calendar</h2>
        <div class="events-section">
            <h3 style="color: #1e3a8a; margin-bottom: 20px;">Key Events Throughout the Year</h3>
            
            <div class="event-item">
                <h4>School Opening Ceremony</h4>
                <p><span class="event-date">Every Beginning of Term</span></p>
                <p>Welcome assembly for all students and staff marking the start of a new term.</p>
            </div>
            
            <div class="event-item">
                <h4>Sports Day</h4>
                <p><span class="event-date">Mid-Term (Every Term)</span></p>
                <p>A day of athletic competitions, races, and team events celebrating student talent in sports.</p>
            </div>
            
            <div class="event-item">
                <h4>End of Term Prize Giving</h4>
                <p><span class="event-date">End of Each Term</span></p>
                <p>Recognition ceremony celebrating top students and award winners.</p>
            </div>
            
            <div class="event-item">
                <h4>Cultural Day</h4>
                <p><span class="event-date">Once Per Year</span></p>
                <p>Students showcase school culture through music, dance, drama, and traditional performances.</p>
            </div>
            
            <div class="event-item">
                <h4>Environmental Day</h4>
                <p><span class="event-date">Campaign Throughout Year</span></p>
                <p>Tree planting and environmental conservation activities to protect our surroundings.</p>
            </div>
            
            <div class="event-item">
                <h4>Inter-House Competitions</h4>
                <p><span class="event-date">Throughout The Year</span></p>
                <p>House competitions in sports, academics, and cultural activities building school spirit.</p>
            </div>
        </div>
    </div>
    
    <div class="content-section">
        <h2>Benefits of Co-Curricular Activities</h2>
        <p>
            Our co-curricular program helps students:
        </p>
        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; border-radius: 5px; margin-top: 15px;">
            <ul style="list-style: none;">
                <li style="margin-bottom: 10px;">✓ Develop confidence and self-esteem</li>
                <li style="margin-bottom: 10px;">✓ Build teamwork and leadership skills</li>
                <li style="margin-bottom: 10px;">✓ Discover and nurture talents and interests</li>
                <li style="margin-bottom: 10px;">✓ Improve social interaction and friendship</li>
                <li style="margin-bottom: 10px;">✓ Maintain physical fitness and health</li>
                <li style="margin-bottom: 10px;">✓ Develop discipline and responsibility</li>
                <li style="margin-bottom: 10px;">✓ Participate in community service</li>
            </ul>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
