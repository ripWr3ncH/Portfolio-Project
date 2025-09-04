<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_status':
                $id = (int)$_POST['id'];
                $status = $_POST['status'];
                
                $stmt = $pdo->prepare("UPDATE contact_messages SET status = ?, updated_at = NOW() WHERE id = ?");
                if ($stmt->execute([$status, $id])) {
                    $message = 'Message status updated successfully!';
                } else {
                    $error = 'Failed to update message status.';
                }
                break;
                
            case 'bulk_action':
                $selected_ids = $_POST['selected_messages'] ?? [];
                $bulk_action = $_POST['bulk_action'];
                
                if (!empty($selected_ids)) {
                    $placeholders = str_repeat('?,', count($selected_ids) - 1) . '?';
                    
                    if ($bulk_action === 'delete') {
                        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id IN ($placeholders)");
                        if ($stmt->execute($selected_ids)) {
                            $message = count($selected_ids) . ' messages deleted successfully!';
                        }
                    } elseif (in_array($bulk_action, ['read', 'unread', 'replied', 'archived'])) {
                        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ?, updated_at = NOW() WHERE id IN ($placeholders)");
                        $params = array_merge([$bulk_action], $selected_ids);
                        if ($stmt->execute($params)) {
                            $message = count($selected_ids) . ' messages updated successfully!';
                        }
                    }
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $message = 'Message deleted successfully!';
                } else {
                    $error = 'Failed to delete message.';
                }
                break;
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$page = (int)($_GET['page'] ?? 1);
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Build query
$where = "WHERE 1=1";
$params = [];

if ($status_filter !== 'all') {
    $where .= " AND status = ?";
    $params[] = $status_filter;
}

// Get total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM contact_messages $where");
$countStmt->execute($params);
$total_messages = $countStmt->fetchColumn();
$total_pages = ceil($total_messages / $per_page);

// Get messages - using separate parameter binding for LIMIT/OFFSET
$stmt = $pdo->prepare("
    SELECT * FROM contact_messages 
    $where 
    ORDER BY created_at DESC 
    LIMIT :limit OFFSET :offset
");

// Bind the filter parameters first
$paramIndex = 1;
foreach ($params as $param) {
    $stmt->bindValue($paramIndex, $param);
    $paramIndex++;
}

// Bind LIMIT and OFFSET as integers
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$messages = $stmt->fetchAll();

// Get message counts by status
$statusCounts = $pdo->query("
    SELECT 
        status,
        COUNT(*) as count 
    FROM contact_messages 
    GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$unreadCount = $statusCounts['unread'] ?? 0;
$totalCount = array_sum($statusCounts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin Panel</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <?php include 'includes/header.php'; ?>
            
            <div class="dashboard-content">
                <div class="page-header">
                    <h1><i class="fas fa-envelope"></i> Contact Messages</h1>
                    <p>Manage and respond to visitor messages</p>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Message Stats -->
                <div class="stats-grid" style="margin-bottom: 2rem;">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $totalCount; ?></div>
                        <div class="stat-label">Total Messages</div>
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $unreadCount; ?></div>
                        <div class="stat-label">Unread Messages</div>
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $statusCounts['replied'] ?? 0; ?></div>
                        <div class="stat-label">Replied</div>
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $statusCounts['archived'] ?? 0; ?></div>
                        <div class="stat-label">Archived</div>
                        <div class="stat-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="filters-container" style="margin-bottom: 2rem;">
                    <div class="filter-tabs">
                        <a href="?status=all" class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                            All (<?php echo $totalCount; ?>)
                        </a>
                        <a href="?status=unread" class="filter-tab <?php echo $status_filter === 'unread' ? 'active' : ''; ?>">
                            Unread (<?php echo $statusCounts['unread'] ?? 0; ?>)
                        </a>
                        <a href="?status=read" class="filter-tab <?php echo $status_filter === 'read' ? 'active' : ''; ?>">
                            Read (<?php echo $statusCounts['read'] ?? 0; ?>)
                        </a>
                        <a href="?status=replied" class="filter-tab <?php echo $status_filter === 'replied' ? 'active' : ''; ?>">
                            Replied (<?php echo $statusCounts['replied'] ?? 0; ?>)
                        </a>
                        <a href="?status=archived" class="filter-tab <?php echo $status_filter === 'archived' ? 'active' : ''; ?>">
                            Archived (<?php echo $statusCounts['archived'] ?? 0; ?>)
                        </a>
                    </div>
                </div>
                
                <?php if (empty($messages)): ?>
                    <div style="padding: 3rem; text-align: center; color: #666;">
                        <i class="fas fa-inbox" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                        <h3>No messages found</h3>
                        <p>No contact messages match your current filter.</p>
                    </div>
                <?php else: ?>
                    <!-- Bulk Actions -->
                    <form method="POST" id="bulk-form">
                        <div class="bulk-actions" style="margin-bottom: 1rem;">
                            <select name="bulk_action" required>
                                <option value="">Bulk Actions</option>
                                <option value="read">Mark as Read</option>
                                <option value="unread">Mark as Unread</option>
                                <option value="replied">Mark as Replied</option>
                                <option value="archived">Archive</option>
                                <option value="delete">Delete</option>
                            </select>
                            <button type="submit" name="action" value="bulk_action" class="btn btn-secondary">Apply</button>
                        </div>
                        
                        <!-- Messages Table -->
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th>
                                        <th>Sender</th>
                                        <th>Subject/Message</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($messages as $msg): ?>
                                        <tr class="message-row <?php echo $msg['status']; ?>">
                                            <td>
                                                <input type="checkbox" name="selected_messages[]" value="<?php echo $msg['id']; ?>" class="message-checkbox">
                                            </td>
                                            <td>
                                                <div class="sender-info">
                                                    <strong><?php echo htmlspecialchars($msg['name']); ?></strong>
                                                    <br>
                                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="email-link">
                                                        <?php echo htmlspecialchars($msg['email']); ?>
                                                    </a>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="message-preview">
                                                    <?php if ($msg['subject']): ?>
                                                        <div class="subject">
                                                            <strong><?php echo htmlspecialchars($msg['subject']); ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="message-text">
                                                        <?php echo htmlspecialchars(substr($msg['message'], 0, 100)) . (strlen($msg['message']) > 100 ? '...' : ''); ?>
                                                    </div>
                                                    <?php if ($msg['is_spam']): ?>
                                                        <span class="spam-badge">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 12px; height: 12px; display: inline;">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                                                            </svg>
                                                            Potential Spam
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="date-info">
                                                    <?php echo date('M j, Y', strtotime($msg['created_at'])); ?>
                                                    <br>
                                                    <small><?php echo date('g:i A', strtotime($msg['created_at'])); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                                    <select name="status" onchange="this.form.submit()" class="status-select">
                                                        <option value="unread" <?php echo $msg['status'] === 'unread' ? 'selected' : ''; ?>>Unread</option>
                                                        <option value="read" <?php echo $msg['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                                        <option value="replied" <?php echo $msg['status'] === 'replied' ? 'selected' : ''; ?>>Replied</option>
                                                        <option value="archived" <?php echo $msg['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button onclick="viewMessage(<?php echo $msg['id']; ?>)" class="btn btn-sm btn-primary" title="View Full Message">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="btn btn-sm btn-success" title="Reply via Email">
                                                        <i class="fas fa-reply"></i>
                                                    </a>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>" 
                                   class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Message Modal -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
    
    <style>
        .message-row.unread {
            background-color: #fff8e1;
            font-weight: 600;
        }
        
        .filter-tabs {
            display: flex;
            gap: 1rem;
            border-bottom: 1px solid #ddd;
        }
        
        .filter-tab {
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: #666;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .filter-tab.active,
        .filter-tab:hover {
            color: #007bff;
            border-bottom-color: #007bff;
        }
        
        .sender-info strong {
            color: #333;
        }
        
        .email-link {
            color: #007bff;
            text-decoration: none;
        }
        
        .email-link:hover {
            text-decoration: underline;
        }
        
        .message-preview {
            max-width: 300px;
        }
        
        .subject {
            margin-bottom: 0.25rem;
        }
        
        .message-text {
            color: #666;
            font-size: 0.9rem;
        }
        
        .spam-badge {
            display: inline-block;
            background: #ff4444;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-top: 0.25rem;
        }
        
        .status-select {
            padding: 0.25rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.25rem;
        }
        
        .bulk-actions {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .bulk-actions select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .page-link {
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: #007bff;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .page-link.active,
        .page-link:hover {
            background: #007bff;
            color: white;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
    </style>
    
    <script>
        // Select all functionality
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.message-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
        
        // Modal functionality
        const modal = document.getElementById('messageModal');
        const span = document.getElementsByClassName('close')[0];
        
        span.onclick = function() {
            modal.style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        function viewMessage(id) {
            // You can implement AJAX to load full message content
            fetch(`../api/contact.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const msg = data.message;
                        document.getElementById('modalContent').innerHTML = `
                            <h3>Message Details</h3>
                            <p><strong>From:</strong> ${msg.name} (${msg.email})</p>
                            ${msg.subject ? `<p><strong>Subject:</strong> ${msg.subject}</p>` : ''}
                            <p><strong>Date:</strong> ${new Date(msg.created_at).toLocaleString()}</p>
                            <p><strong>Message:</strong></p>
                            <div style="background: #f5f5f5; padding: 1rem; border-radius: 4px; white-space: pre-wrap;">${msg.message}</div>
                        `;
                        modal.style.display = 'block';
                    }
                });
        }
    </script>
</body>
</html>
