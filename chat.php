<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];
$partner = intval($_GET['user']);

if (!isset($_GET['user']) || empty($_GET['user'])) {
    die("Invalid user.");
}
$partner = intval($_GET['user']);

// Get partner info
$partner_info = $conn->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$partner_info->bind_param("i", $partner);
$partner_info->execute();
$partner_result = $partner_info->get_result();
$partner_data = $partner_result->fetch_assoc();
$partner_name = $partner_data['username'];
$partner_pic = $partner_data['profile_pic'];
$partner_info->close();

// Get current user info
$user_info = $conn->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$user_info->bind_param("i", $uid);
$user_info->execute();
$user_result = $user_info->get_result();
$user_data = $user_result->fetch_assoc();
$user_name = $user_data['username'];
$user_pic = $user_data['profile_pic'];
$user_info->close();

// // Mark partner's messages as seen
// $update_seen = $conn->prepare("UPDATE messages SET seen=1, is_read=1 WHERE receiver_id=? AND sender_id=?");
// $update_seen->bind_param("ii", $uid, $partner);
// $update_seen->execute();
// $update_seen->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat with <?php echo htmlspecialchars($partner_name); ?> - Anas Insta</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    :root {
      --primary: #8B5CF6;
      --primary-dark: #7C3AED;
      --secondary: #F59E0B;
      --accent: #10B981;
      --dark: #0F0F13;
      --darker: #09090B;
      --light: #F8FAFC;
      --gray: #6B7280;
      --card-bg: #1A1B23;
      --card-border: #2D2D3A;
      --me-bubble: #8B5CF6;
      --other-bubble: #2D2D3A;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', system-ui, sans-serif;
    }

    body {
      background: var(--dark);
      color: var(--light);
      height: 100vh;
      overflow: hidden;
    }

    .chat-container {
      display: flex;
      flex-direction: column;
      height: 100vh;
      max-width: 100%;
      margin: 0 auto;
      background: var(--dark);
    }

    /* Header Styles */
    .chat-header {
      background: var(--card-bg);
      padding: 12px 16px;
      border-bottom: 1px solid var(--card-border);
      display: flex;
      align-items: center;
      gap: 12px;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .back-btn {
      background: transparent;
      border: none;
      color: var(--light);
      font-size: 1.2rem;
      cursor: pointer;
      padding: 8px;
      border-radius: 50%;
      transition: all 0.3s ease;
    }

    .partner-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--primary);
    }

    .partner-info {
      flex: 1;
    }

    .partner-name {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 2px;
    }

    .partner-status {
      font-size: 0.8rem;
      color: var(--accent);
    }

    /* Chat Box Styles */
    .chat-box {
      flex: 1;
      padding: 16px;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 8px;
      background: var(--dark);
    }

    /* Message Styles */
    .message {
      display: flex;
      max-width: 70%;
      margin-bottom: 4px;
    }

    .message.me {
      align-self: flex-end;
      flex-direction: row-reverse;
    }

    .message.other {
      align-self: flex-start;
    }

    .message-avatar {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      object-fit: cover;
      margin: 0 8px;
      align-self: flex-end;
    }

    .message-content {
      display: flex;
      flex-direction: column;
      max-width: calc(100% - 44px);
    }

    .message-bubble {
      padding: 8px 12px;
      border-radius: 18px;
      word-wrap: break-word;
      line-height: 1.4;
      font-size: 0.9rem;
    }

    .message.other .message-bubble {
      background: var(--other-bubble);
      color: var(--light);
      border: 1px solid var(--card-border);
      border-bottom-left-radius: 4px;
    }

    .message.me .message-bubble {
      background: linear-gradient(135deg, var(--me-bubble), var(--primary-dark));
      color: white;
      border-bottom-right-radius: 4px;
    }

    .message-info {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-top: 2px;
      font-size: 0.7rem;
      color: var(--gray);
      padding: 0 4px;
    }

    .message.me .message-info {
      justify-content: flex-end;
    }

    .message.other .message-info {
      justify-content: flex-start;
    }

    /* Message Form Styles */
    .chat-form-container {
      background: var(--card-bg);
      padding: 12px 16px;
      border-top: 1px solid var(--card-border);
      position: sticky;
      bottom: 0;
    }

    .chat-form {
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .message-input {
      flex: 1;
      padding: 10px 16px;
      border-radius: 20px;
      border: 1px solid var(--card-border);
      background: var(--dark);
      color: var(--light);
      font-size: 0.9rem;
      outline: none;
      resize: none;
      height: 40px;
      max-height: 120px;
    }

    .message-input:focus {
      border-color: var(--primary);
    }

    .send-btn {
      background: var(--primary);
      border: none;
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .send-btn:hover {
      background: var(--primary-dark);
    }

    .send-btn:disabled {
      background: var(--gray);
      cursor: not-allowed;
    }

    /* Scrollbar */
    .chat-box::-webkit-scrollbar {
      width: 4px;
    }

    .chat-box::-webkit-scrollbar-track {
      background: transparent;
    }

    .chat-box::-webkit-scrollbar-thumb {
      background: var(--primary);
      border-radius: 2px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .chat-header {
        padding: 10px 12px;
      }
      
      .chat-box {
        padding: 12px;
      }
      
      .message {
        max-width: 85%;
      }
      
      .chat-form-container {
        padding: 10px 12px;
      }
    }
  </style>
</head>
<body>
<div class="chat-container">
  <!-- Chat Header -->
  <div class="chat-header">
    <button class="back-btn" onclick="window.history.back()">
      <i class="fas fa-arrow-left"></i>
    </button>
    
    <img src="<?php echo htmlspecialchars($partner_pic); ?>" class="partner-avatar" alt="<?php echo htmlspecialchars($partner_name); ?>">
    
    <div class="partner-info">
      <div class="partner-name"><?php echo htmlspecialchars($partner_name); ?></div>
      <div class="partner-status">Active now</div>
    </div>
  </div>

  <!-- Chat Messages -->
  <div class="chat-box" id="chat-box">
    <!-- Messages loaded via JavaScript -->
  </div>

  <!-- Message Input Form -->
  <div class="chat-form-container">
    <form id="chat-form" class="chat-form">
      <input type="hidden" name="receiver_id" value="<?php echo $partner; ?>">
      <textarea name="message_text" id="message" class="message-input" placeholder="Message..." rows="1" required></textarea>
      <button type="submit" class="send-btn" id="send-btn">
        <i class="fas fa-paper-plane"></i>
      </button>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Firebase SDK -->
<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/9.22.0/firebase-app.js";
  import { getDatabase, ref, push, onChildAdded, serverTimestamp, update } 
    from "https://www.gstatic.com/firebasejs/9.22.0/firebase-database.js";


// Your web app's Firebase configuration
const firebaseConfig = {
  apiKey: "AIzaSyDbiYcmMtGWm-qciK-G_vLRhkArKiEHTpE",
  authDomain: "anasinstachat.firebaseapp.com",
  projectId: "anasinstachat",
   storageBucket: "anasinstachat.appspot.com",

    // storageBucket: "anasinstachat.firebasestorage.app",
  messagingSenderId: "590636643155",
  appId: "1:590636643155:web:dce3266af3f067d24127a6"
};

// Initialize Firebase

  const app = initializeApp(firebaseConfig);
  const db = getDatabase(app);

  const sender = "<?php echo $uid; ?>";
  const receiver = "<?php echo $partner; ?>";
  const chatId = sender < receiver ? sender+"_"+receiver : receiver+"_"+sender;

  // Load messages live
  const chatBox = document.getElementById("chat-box");
  onChildAdded(ref(db, "chats/" + chatId + "/messages"), (snapshot) => {
    const msg = snapshot.val();
    const div = document.createElement("div");
    div.classList.add("message", msg.sender == sender ? "me" : "other");
    div.innerHTML = `
      <div class="message-content">
        <div class="message-bubble">${msg.text}</div>
        <div class="message-info">${new Date(msg.timestamp).toLocaleTimeString()}</div>
      </div>
    `;
    chatBox.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;
  });

  // Send message
  document.getElementById("chat-form").addEventListener("submit", function(e){
    e.preventDefault();
    const text = document.getElementById("message").value.trim();
    if (!text) return;

    push(ref(db, "chats/" + chatId + "/messages"), {
      sender: sender,
      receiver: receiver,
      text: text,
      timestamp: Date.now(),
      seen: false
    });

    document.getElementById("message").value = "";
  });
</script>
</body>
</html>