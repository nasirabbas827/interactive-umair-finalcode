<?php
session_start();
include('../config.php');

if (!isset($_SESSION["id"]) || $_SESSION["role"] !== 'agent') {
    header("location: ../login.php");
    exit;
}

$agent_id = $_SESSION["id"];
$success_msg = "";

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    if (!empty($message)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $agent_id, $receiver_id, $message);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $success_msg = "Message sent successfully!";
        // Redirect to clear POST data and show the updated conversation
        header("Location: agent_messages.php?with_user=" . $receiver_id);
        exit;
    }
}

// Fetch all unique users who sent messages to this agent or received from agent
$users = [];
$result = mysqli_query($conn, "
    SELECT DISTINCT u.id, u.full_name
    FROM users u
    INNER JOIN messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
    WHERE u.id != $agent_id AND (m.sender_id = $agent_id OR m.receiver_id = $agent_id)
    ORDER BY u.full_name ASC
");
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

// Get selected conversation
$conversation = [];
$selected_user_name = "Select a User";
if (isset($_GET['with_user'])) {
    $with_user = intval($_GET['with_user']);
    // Fetch selected user's name
    $user_res = mysqli_query($conn, "SELECT full_name FROM users WHERE id = $with_user LIMIT 1");
    if ($user_row = mysqli_fetch_assoc($user_res)) {
        $selected_user_name = $user_row['full_name'];
    }

    $stmt = mysqli_prepare($conn, "
        SELECT m.*, u.full_name AS sender_name
        FROM messages m
        INNER JOIN users u ON m.sender_id = u.id
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC
    ");
    mysqli_stmt_bind_param($stmt, "iiii", $agent_id, $with_user, $with_user, $agent_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $conversation[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Messages</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css"> 


    <style>
         .container.py-5 {
            padding-top: 3rem !important;
            padding-bottom: 3rem !important;
        }
        .chat-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Users List (Sidebar) */
        .user-list-card {
            min-height: 500px; /* Ensure chat and list have similar height */
        }
        .user-list { 
            max-height: 440px; 
            overflow-y: auto; 
            border-right: 1px solid #dee2e6; /* Separator line */
            padding-right: 0;
        }
        .list-group-item {
            border-left: none;
            border-right: none;
            cursor: pointer;
        }
        .list-group-item.active {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            font-weight: bold;
        }
        
        /* Conversation Area */
        .conversation-area {
            height: 400px;
            overflow-y: auto;
            display: flex; /* For chat flow at the bottom */
            flex-direction: column-reverse; /* To keep the latest message in view */
            padding: 15px;
            background-color: #e9ecef; /* Subtle background for chat box */
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .message-wrapper {
            width: 100%;
            display: flex;
            margin-bottom: 10px;
        }
        .message-sent { 
            justify-content: flex-end; /* Align right for agent/self messages */
        }
        .message-received { 
            justify-content: flex-start; /* Align left for other user messages */
        }

        .message-box { 
            max-width: 75%;
            border-radius: 15px; 
            padding: 10px 15px; 
            word-wrap: break-word; /* Essential for long messages */
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        /* Message Bubbles */
        .message-sent .message-box { 
            background-color: #007bff; /* Primary color for agent messages */
            color: white;
            border-bottom-right-radius: 0; /* Tail effect */
        }
        .message-received .message-box { 
            background-color: #f1f0f0; /* Light grey for user messages */
            color: #343a40;
            border-bottom-left-radius: 0; /* Tail effect */
        }
        
        .message-box strong {
            font-size: 0.8rem;
            margin-bottom: 2px;
            display: block;
        }
        .message-box p {
            margin: 0;
            white-space: pre-wrap; /* Preserve formatting like line breaks */
        }
        .message-box small.text-muted {
            font-size: 0.7rem;
            /* Invert text color for sent message timestamps */
            color: rgba(255, 255, 255, 0.7) !important; 
            display: block;
            text-align: right;
            margin-top: 5px;
        }
        .message-received .message-box small.text-muted {
            color: #6c757d !important;
        }
        
        /* Input area */
        .reply-form-container {
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container py-5">
    <div class="row chat-container">
        <div class="col-md-4 p-0 user-list-card">
            <h5 class="p-3 mb-0 border-bottom bg-light rounded-top">
                <i class="fas fa-users mr-2 text-primary"></i>Conversations
            </h5>
            <div class="list-group user-list">
                <?php if (empty($users)): ?>
                    <p class="text-center text-muted mt-3">No active conversations yet.</p>
                <?php endif; ?>
                <?php foreach ($users as $user): ?>
                    <a href="?with_user=<?= $user['id'] ?>" class="list-group-item list-group-item-action <?= (isset($with_user) && $with_user == $user['id']) ? 'active' : '' ?>">
                        <i class="fas fa-user-circle mr-2"></i>
                        <?= htmlspecialchars($user['full_name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-md-8 p-0">
            <h5 class="p-3 mb-0 border-bottom bg-light rounded-top">
                <i class="fas fa-comment-dots mr-2 text-primary"></i>Chat with:<?= htmlspecialchars($selected_user_name) ?>
            </h5>
            <?php if (isset($with_user)): ?>
                <div class="conversation-area">
                    <?php foreach (array_reverse($conversation) as $msg): ?>
                        <div class="message-wrapper <?= $msg['sender_id'] == $agent_id ? 'message-sent' : 'message-received' ?>">
                            <div class="message-box">
                                <p><?= htmlspecialchars($msg['message']) ?></p>
                                <small class="text-muted"><?= date('H:i:s, M j', strtotime($msg['created_at'])) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="reply-form-container p-3">
                    <form method="post" class="d-flex">
                        <input type="hidden" name="receiver_id" value="<?= $with_user ?>">
                        <textarea name="message" class="form-control mr-2" placeholder="Type your message..." required rows="1" style="resize: none;"></textarea>
                        <button type="submit" name="send_message" class="btn btn-primary">
                             <i class="fas fa-paper-plane"></i> Send
                        </button>
                    </form>
                </div>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success mt-2 mx-3"><?= $success_msg ?></div>
                <?php endif; ?>

            <?php else: ?>
                <div class="p-4 text-center">
                    <p class="text-muted mt-5">
                        <i class="fas fa-hand-point-left fa-2x d-block mb-3 text-secondary"></i>
                        Select a user from the left to begin or view a conversation.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Scroll the conversation area to the bottom (for the initial load)
    $(document).ready(function() {
        const conversationArea = $('.conversation-area');
        // Because we are using column-reverse, the latest message is already on view at the bottom
        // conversationArea.scrollTop(conversationArea[0].scrollHeight);
    });
</script>
</body>
</html>