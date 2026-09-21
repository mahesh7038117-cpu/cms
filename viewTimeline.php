<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'];

// Fetch timelines that target everyone ('all') OR match the user's specific session profile role
$query = "SELECT * FROM ScheduleTimeline WHERE target_role = 'all' OR target_role = ? ORDER BY schedule_date ASC, start_time ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $role);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Schedule Timeline</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background: linear-gradient(135deg, #31e4cc, #4a90e2); padding: 40px 20px; min-height: 100vh; }
        .timeline-container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; margin-bottom: 10px; }
        .subtitle { text-align: center; color: #666; font-size: 14px; margin-bottom: 30px; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; }
        
        /* Vertical Line Structure Layout */
        .timeline { position: relative; border-left: 3px solid #4a90e2; margin-left: 20px; padding-left: 30px; }
        .timeline-item { position: relative; margin-bottom: 30px; }
        
        /* Circular Nodes */
        .timeline-dot { position: absolute; left: -41px; top: 5px; width: 18px; height: 18px; border-radius: 50%; background: #4a90e2; border: 3px solid #fff; box-shadow: 0 0 0 3px #4a90e2; }
        
        .timeline-content { background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #e2e8f0; }
        .timeline-date { font-size: 13px; font-weight: bold; color: #4a90e2; margin-bottom: 5px; }
        .timeline-title { font-size: 18px; color: #1e293b; margin-bottom: 8px; }
        .timeline-desc { font-size: 14px; color: #475569; line-height: 1.5; margin-bottom: 10px; }
        
        /* Interactive JavaScript Live Badge */
        .status-badge { inline-size: max-content; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; background: #cbd5e1; color: #475569; }
        .status-upcoming { background: #dbeafe; color: #1e40af; }
        .status-active { background: #dcfce7; color: #166534; animation: pulse 2s infinite; }
        .status-expired { background: #f1f5f9; color: #94a3b8; }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #4a90e2; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

    <div class="timeline-container">
        <h2>📅 System Schedule Timeline</h2>
        <div class="subtitle"><?php echo $role; ?> Feed</div>
        
        <div class="timeline">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="timeline-item" 
                         data-date="<?php echo $row['schedule_date']; ?>" 
                         data-start="<?php echo $row['start_time']; ?>" 
                         data-end="<?php echo $row['end_time']; ?>">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-date">
                                📆 <?php echo date('M d, Y', strtotime($row['schedule_date'])); ?> | ⏰ <?php echo date('h:i A', strtotime($row['start_time'])); ?> - <?php echo date('h:i A', strtotime($row['end_time'])); ?>
                            </div>
                            <div class="timeline-title"><?php echo htmlspecialchars($row['title']); ?></div>
                            <div class="timeline-desc"><?php echo nl2br(htmlspecialchars($row['description'])); ?></div>
                            <div class="status-badge">Checking...</div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:#666;">No scheduled events listed for your profile view tier yet.</p>
            <?php endif; ?>
        </div>
        
        <a class="back-link" href="dashboards/dashboard.php">⬅️ Back to Dashboard</a>
    </div>

    <!-- Live Status Evaluation Automation Script Engine -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            function updateTimelineBadges() {
                const now = new Date();
                const items = document.querySelectorAll('.timeline-item');
                
                items.forEach(item => {
                    const badge = item.querySelector('.status-badge');
                    const eventDateStr = item.getAttribute('data-date');
                    const startTimeStr = item.getAttribute('data-start');
                    const endTimeStr = item.getAttribute('data-end');
                    
                    // Construct absolute processing date references
                    const eventStart = new Date(`${eventDateStr}T${startTimeStr}`);
                    const eventEnd = new Date(`${eventDateStr}T${endTimeStr}`);
                    
                    badge.className = "status-badge"; // Reset styles
                    
                    if (now < eventStart) {
                        badge.textContent = "Upcoming";
                        badge.classList.add("status-upcoming");
                    } else if (now >= eventStart && now <= eventEnd) {
                        badge.textContent = "Live Now";
                        badge.classList.add("status-active");
                    } else {
                        badge.textContent = "Completed";
                        badge.classList.add("status-expired");
                    }
                });
            }
            
            updateTimelineBadges();
            setInterval(updateTimelineBadges, 30000); // Re-evaluate timestamps every 30 seconds
        });
    </script>
</body>
</html>
