<?php
// public/chat.php
require_once '../config/db.php';
require_once '../src/functions.php';
session_start();
requireLogin();

$user_id = $_SESSION['user_id'];
$receiver_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Check Verification
$stmt = $pdo->prepare("SELECT kyc_status, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user_details = $stmt->fetch();

$kyc_warning = false;
if ($current_user_details['role'] === 'ngo' && $current_user_details['kyc_status'] !== 'approved') {
    $kyc_warning = true;
}

// Fetch Receiver Details
$receiver = null;
if ($receiver_id) {
    $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    $receiver = $stmt->fetch();
}

// Handle Send Message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_msg'])) {
    $msg = sanitize($_POST['message']);
    $recv_id = (int)$_POST['receiver_id'];
    
    if (!empty($msg) && $recv_id) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $recv_id, $msg]);

        // Notify recipient about new message
        $sender_name = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $sender_name->execute([$user_id]);
        $name = $sender_name->fetchColumn();
        addNotification($pdo, 'New Message', "You have a new message from $name", $recv_id);
    }
    // AJAX handling would be better, but basic form submit works for v1
    header("Location: chat.php?user_id=" . $recv_id);
    exit;
}

// Fetch Messages
$messages = [];
if ($receiver_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
           OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC
    ");
    $stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
    $messages = $stmt->fetchAll();
    
    // Mark as read
    $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?")->execute([$receiver_id, $user_id]);
}

// Fetch Recent Contacts + Active Task Partners
$contacts_sql = "
    SELECT DISTINCT u.id, u.username, u.role
    FROM users u
    WHERE u.id IN (
        -- 1. People you have messaged
        SELECT sender_id FROM messages WHERE receiver_id = ?
        UNION
        SELECT receiver_id FROM messages WHERE sender_id = ?
        
        UNION
        
        -- 2. If you are Donor: Show NGOs/Volunteers involved in your listings
        SELECT c.ngo_id FROM claims c JOIN food_listings f ON c.listing_id = f.id WHERE f.donor_id = ?
        UNION
        SELECT c.volunteer_id FROM claims c JOIN food_listings f ON c.listing_id = f.id WHERE f.donor_id = ?
        
        UNION
        
        -- 3. If you are NGO: Show Donors/Volunteers involved in your claims
        SELECT f.donor_id FROM claims c JOIN food_listings f ON c.listing_id = f.id WHERE c.ngo_id = ?
        UNION
        SELECT c.volunteer_id FROM claims c WHERE c.ngo_id = ?
        
        UNION
        
        -- 4. If you are Volunteer: Show Donors/NGOs involved in your tasks
        SELECT f.donor_id FROM claims c JOIN food_listings f ON c.listing_id = f.id WHERE c.volunteer_id = ?
        UNION
        SELECT c.ngo_id FROM claims c WHERE c.volunteer_id = ?
    ) AND u.id != ?
";
$contacts = $pdo->prepare($contacts_sql);
$contacts->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$contact_list = $contacts->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | ZeroWaste-ZeroHunger</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        .chat-container { display: grid; grid-template-columns: 280px 1fr 300px; gap: 20px; height: 75vh; }
        .contact-list { background: rgba(255,255,255,0.05); border-radius: 12px; padding: 15px; overflow-y: auto; }
        .chat-box { background: rgba(0,0,0,0.3); border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); }
        .messages-area { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }
        .message-bubble { padding: 10px 15px; border-radius: 15px; max-width: 85%; word-wrap: break-word; }
        .msg-sent { background: var(--primary); color: white; align-self: flex-end; border-bottom-right-radius: 2px; }
        .msg-received { background: rgba(255,255,255,0.1); color: var(--text-main); align-self: flex-start; border-bottom-left-radius: 2px; }
        .chat-input { padding: 15px; background: rgba(0,0,0,0.2); display: flex; gap: 10px; }
        .contact-item { padding: 12px; margin-bottom: 8px; border-radius: 10px; cursor: pointer; transition: 0.2s; display: block; text-decoration: none; color: white; border: 1px solid transparent; }
        .contact-item:hover, .contact-item.active { background: rgba(59, 130, 246, 0.2); border-color: rgba(59, 130, 246, 0.4); }
        #map { height: 100%; width: 100%; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); }
        .map-sidebar { display: flex; flex-direction: column; gap: 10px; }
        @media (max-width: 1024px) {
            .chat-container { grid-template-columns: 250px 1fr; }
            .map-sidebar { display: none; }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container" style="padding: 40px 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>💬 Secure Mission Messenger</h2>
            <?php if ($current_user_details['role'] === 'volunteer'): ?>
                <button id="trackLocationBtn" class="btn btn-outline" style="font-size: 0.8rem;">
                    📍 Share Live Location
                </button>
            <?php endif; ?>
        </div>
        
        <div class="chat-container">
            <!-- Contact List -->
            <div class="contact-list">
                <h4 style="margin-bottom: 15px; color: var(--text-muted); border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:10px;">Select Contact</h4>
                <?php foreach($contact_list as $c): ?>
                    <a href="chat.php?user_id=<?php echo $c['id']; ?>" class="contact-item <?php echo $receiver_id == $c['id'] ? 'active' : ''; ?>">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($c['username']); ?>&background=random" style="width: 32px; height: 32px; border-radius: 50%;">
                            <div>
                                <div style="font-weight: bold;"><?php echo htmlspecialchars($c['username']); ?></div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo ucfirst($c['role']); ?></div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                <?php if(empty($contact_list)): ?>
                    <p style="color:var(--text-muted); font-size:0.9rem;">No active chats.</p>
                <?php endif; ?>
            </div>

            <!-- Chat Area -->
            <div class="chat-box">
                <?php if($receiver): ?>
                    <div style="padding: 15px; background: rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?php echo htmlspecialchars($receiver['username']); ?></strong>
                            <span style="font-size: 0.75rem; color: var(--text-muted); margin-left: 10px;">
                                (<?php echo htmlspecialchars($receiver['role'] ?? 'User'); ?>)
                            </span>
                        </div>
                        <button onclick="document.querySelector('.map-sidebar').style.display = (document.querySelector('.map-sidebar').style.display === 'block' ? 'none' : 'block')" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.7rem; display: none;">Toggle Map</button>
                    </div>
                    
                    <?php if($kyc_warning): ?>
                        <div style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 10px 20px; font-size: 0.85rem; border-bottom: 1px solid rgba(245, 158, 11, 0.2);">
                            ⚠️ <strong>Verification Pending:</strong> You can coordinate active rescues, but some pro features are restricted until your KYC is approved.
                        </div>
                    <?php endif; ?>
                    
                    <div class="messages-area" id="msgArea">
                        <?php foreach($messages as $msg): ?>
                            <div class="message-bubble <?php echo $msg['sender_id'] == $user_id ? 'msg-sent' : 'msg-received'; ?>">
                                <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                <div style="font-size: 0.7rem; opacity: 0.7; margin-top: 5px; text-align: right;">
                                    <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if(empty($messages)): ?>
                            <div style="text-align:center; color:var(--text-muted); margin-top:50px;">
                                <div style="font-size: 3rem; margin-bottom: 10px;">👋</div>
                                <p>Start the conversation with <?php echo htmlspecialchars($receiver['username']); ?>!</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form method="POST" class="chat-input" id="chatForm">
                        <input type="hidden" name="receiver_id" value="<?php echo $receiver_id; ?>">
                        <input type="text" name="message" id="messageInput" class="form-input" style="margin-bottom:0;" placeholder="Type a message..." required autocomplete="off">
                        <button type="submit" name="send_msg" class="btn btn-primary" style="width: auto;">Send</button>
                    </form>
                <?php else: ?>
                    <div style="display:flex; flex-direction: column; align-items:center; justify-content:center; height:100%; color:var(--text-muted); padding: 20px; text-align: center;">
                        <img src="assets/images/chat_placeholder.svg" style="width: 150px; opacity: 0.2; margin-bottom: 20px;" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2462/2462719.png'">
                        <h3>Your Secure Inbox</h3>
                        <p>Select a person from the left to start coordinating your food rescue mission.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Map Area -->
            <div class="map-sidebar">
                <h4 style="color: var(--text-muted);">📍 Live Location</h4>
                <div id="map"></div>
                <div id="mapInfo" style="font-size: 0.8rem; color: var(--text-muted); padding: 5px;">
                    Select a contact to see their location (if shared)
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Auto scroll to bottom
        var msgArea = document.getElementById("msgArea");
        if(msgArea) msgArea.scrollTop = msgArea.scrollHeight;

        // Leaflet Map Initialization
        var map = L.map('map').setView([27.7172, 85.3240], 13); // Default to Kathmandu
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var userMarker = null;
        var receiver_id = <?php echo $receiver_id; ?>;
        var user_role = '<?php echo $current_user_details['role']; ?>';

        // Update Location Every 10 Seconds if sharing
        function updateMyLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    fetch('api/update_location.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        }),
                        headers: { 'Content-Type': 'application/json' }
                    });
                });
            }
        }

        let trackingInterval = null;
        const trackBtn = document.getElementById('trackLocationBtn');
        if (trackBtn) {
            trackBtn.addEventListener('click', function() {
                if (!trackingInterval) {
                    trackingInterval = setInterval(updateMyLocation, 10000);
                    updateMyLocation(); // Initial call
                    this.innerHTML = "🛑 Stop & Clear Location";
                    this.classList.add('btn-danger');
                    this.style.background = "#ef4444";
                } else {
                    clearInterval(trackingInterval);
                    trackingInterval = null;
                    
                    // Clear from DB
                    fetch('api/update_location.php', {
                        method: 'POST',
                        body: JSON.stringify({ action: 'clear' }),
                        headers: { 'Content-Type': 'application/json' }
                    });

                    this.innerHTML = "📍 Share Live Location";
                    this.classList.remove('btn-danger');
                    this.style.background = "";
                }
            });
        }

        // Fetch Receiver Location
        function fetchReceiverLocation() {
            if (!receiver_id) return;
            
            fetch(`api/get_location.php?user_id=${receiver_id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.latitude && data.longitude) {
                        const latlng = [data.latitude, data.longitude];
                        if (!userMarker) {
                            userMarker = L.marker(latlng).addTo(map)
                                .bindPopup('<?php echo $receiver ? htmlspecialchars($receiver['username']) : "Contact"; ?>')
                                .openPopup();
                        } else {
                            userMarker.setLatLng(latlng);
                        }
                        map.panTo(latlng);
                        document.getElementById('mapInfo').innerHTML = "🟢 Live Location (Updates every 10s)";
                    } else {
                        if (userMarker) {
                            map.removeLayer(userMarker);
                            userMarker = null;
                        }
                        document.getElementById('mapInfo').innerHTML = "🔴 User is currently Offline/Not sharing.";
                    }
                });
        }

        if (receiver_id) {
            setInterval(fetchReceiverLocation, 10000);
            fetchReceiverLocation();
        }
    </script>
</body>
</html>

