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
            case 'add':
                // Add new education
                $title = trim($_POST['title']);
                $institution = trim($_POST['institution']);
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                $description = trim($_POST['description']);
                $highlights = $_POST['highlights'] ?? [];
                $status = $_POST['status'] ?? 'active';
                $display_order = (int)($_POST['display_order'] ?? 1);
                
                if (empty($title) || empty($institution)) {
                    $error = 'Title and institution are required.';
                } else {
                    // Convert highlights array to JSON
                    $highlights_json = json_encode(array_filter($highlights));
                    
                    $stmt = $pdo->prepare("INSERT INTO education (title, institution, start_date, end_date, description, highlights, status, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$title, $institution, $start_date, $end_date, $description, $highlights_json, $status, $display_order])) {
                        $message = 'Education record added successfully!';
                    } else {
                        $error = 'Failed to add education record.';
                    }
                }
                break;
                
            case 'edit':
                // Edit existing education
                $id = (int)$_POST['id'];
                $title = trim($_POST['title']);
                $institution = trim($_POST['institution']);
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                $description = trim($_POST['description']);
                $highlights = $_POST['highlights'] ?? [];
                $status = $_POST['status'] ?? 'active';
                $display_order = (int)($_POST['display_order'] ?? 1);
                
                if (empty($title) || empty($institution)) {
                    $error = 'Title and institution are required.';
                } else {
                    // Convert highlights array to JSON
                    $highlights_json = json_encode(array_filter($highlights));
                    
                    $stmt = $pdo->prepare("UPDATE education SET title = ?, institution = ?, start_date = ?, end_date = ?, description = ?, highlights = ?, status = ?, display_order = ?, updated_at = NOW() WHERE id = ?");
                    if ($stmt->execute([$title, $institution, $start_date, $end_date, $description, $highlights_json, $status, $display_order, $id])) {
                        $message = 'Education record updated successfully!';
                    } else {
                        $error = 'Failed to update education record.';
                    }
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT * FROM education WHERE id = ?");
                $stmt->execute([$id]);
                $education = $stmt->fetch();
                
                if ($education) {
                    $stmt = $pdo->prepare("DELETE FROM education WHERE id = ?");
                    if ($stmt->execute([$id])) {
                        $message = 'Education record deleted successfully!';
                    } else {
                        $error = 'Failed to delete education record.';
                    }
                }
                break;
        }
    }
}

// Get all education records
$stmt = $pdo->query("SELECT * FROM education ORDER BY start_date DESC");
$education_records = $stmt->fetchAll();

// Get education for editing if specified
$edit_education = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM education WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_education = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Education - Admin Panel</title>
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
                    <h1><i class="fas fa-graduation-cap"></i> Manage Education</h1>
                    <p>Add, edit, and manage your educational background</p>
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
                
                <!-- Add/Edit Education Form -->
                <div class="form-container">
                    <h2><?php echo $edit_education ? 'Edit Education' : 'Add New Education'; ?></h2>
                    
                    <form method="POST" accept-charset="UTF-8" enctype="application/x-www-form-urlencoded">
                        <input type="hidden" name="action" value="<?php echo $edit_education ? 'edit' : 'add'; ?>">
                        <?php if ($edit_education): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_education['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="institution">Institution *</label>
                                <input type="text" id="institution" name="institution" value="<?php echo $edit_education ? htmlspecialchars($edit_education['institution']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="title">Education Title *</label>
                                <input type="text" id="title" name="title" value="<?php echo $edit_education ? htmlspecialchars($edit_education['title']) : ''; ?>" placeholder="e.g., Bachelor of Computer Science & Engineering" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="text" id="start_date" name="start_date" value="<?php echo $edit_education ? htmlspecialchars($edit_education['start_date']) : ''; ?>" placeholder="e.g., 2022">
                            </div>
                            
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="text" id="end_date" name="end_date" value="<?php echo $edit_education ? htmlspecialchars($edit_education['end_date']) : ''; ?>" placeholder="e.g., Present or 2026">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="active" <?php echo (!$edit_education || $edit_education['status'] === 'active') ? 'selected' : ''; ?>>Active (Published)</option>
                                    <option value="inactive" <?php echo ($edit_education && $edit_education['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive (Hidden)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="display_order">Display Order</label>
                                <input type="number" id="display_order" name="display_order" value="<?php echo $edit_education ? htmlspecialchars($edit_education['display_order']) : '1'; ?>" min="1" placeholder="1">
                                <small>Lower numbers appear first</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" placeholder="Additional details about your education, focus areas, coursework, etc."><?php echo $edit_education ? htmlspecialchars($edit_education['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Highlights (Optional)</label>
                            <div id="highlights-container">
                                <?php 
                                $highlights = [];
                                if ($edit_education && $edit_education['highlights']) {
                                    $highlights = json_decode($edit_education['highlights'], true) ?: [];
                                }
                                
                                if (empty($highlights)) {
                                    $highlights = [''];
                                }
                                
                                foreach ($highlights as $index => $highlight): ?>
                                    <div class="highlight-input-group">
                                        <input type="text" name="highlights[]" value="<?php echo htmlspecialchars($highlight); ?>" placeholder="e.g., GPA: 3.76/4.00">
                                        <button type="button" onclick="removeHighlight(this)" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" onclick="addHighlight()" class="btn btn-sm btn-secondary">
                                <i class="fas fa-plus"></i> Add Highlight
                            </button>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $edit_education ? 'Update Education' : 'Add Education'; ?>
                            </button>
                            <?php if ($edit_education): ?>
                                <a href="education.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Education List -->
                <div class="table-container" style="margin-top: 2rem;">
                    <div class="table-header">
                        <h2>All Education Records (<?php echo count($education_records); ?>)</h2>
                    </div>
                    
                    <?php if (empty($education_records)): ?>
                        <div style="padding: 2rem; text-align: center; color: #666;">
                            <i class="fas fa-graduation-cap" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>No education records found. Add your first education above!</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Institution</th>
                                    <th>Title</th>
                                    <th>Duration</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($education_records as $education): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($education['institution']); ?></strong>
                                            <?php if ($education['description']): ?>
                                                <br>
                                                <small style="color: #666;"><?php echo htmlspecialchars(substr($education['description'], 0, 60)) . (strlen($education['description']) > 60 ? '...' : ''); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($education['title']); ?></td>
                                        <td>
                                            <?php 
                                            $start = $education['start_date'];
                                            $end = $education['end_date'];
                                            
                                            if ($start && $end) {
                                                echo htmlspecialchars($start . ' - ' . $end);
                                            } elseif ($start) {
                                                echo htmlspecialchars($start . ' - Present');
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-order"><?php echo $education['display_order'] ?? 1; ?></span>
                                        </td>
                                        <td>
                                            <?php if ($education['status'] === 'active'): ?>
                                                <span class="badge badge-current">
                                                    <i class="fas fa-check"></i> Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge badge-completed">
                                                    <i class="fas fa-times"></i> Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="education.php?edit=<?php echo $education['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this education record?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $education['id']; ?>">
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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .badge-current {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-completed {
            background: #d4edda;
            color: #155724;
        }
    </style>
    
    <style>
        .highlight-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        
        .highlight-input-group input {
            flex: 1;
        }
        
        .highlight-input-group .btn {
            padding: 8px 12px;
            min-width: auto;
        }
        
        #highlights-container {
            margin-bottom: 15px;
        }
    </style>
    
    <script>
        // Add new highlight input
        function addHighlight() {
            const container = document.getElementById('highlights-container');
            const div = document.createElement('div');
            div.className = 'highlight-input-group';
            div.innerHTML = `
                <input type="text" name="highlights[]" placeholder="e.g., Dean's List">
                <button type="button" onclick="removeHighlight(this)" class="btn btn-sm btn-danger">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            container.appendChild(div);
        }
        
        // Remove highlight input
        function removeHighlight(button) {
            const container = document.getElementById('highlights-container');
            if (container.children.length > 1) {
                button.parentElement.remove();
            }
        }
        
        // Initialize highlights container
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('highlights-container');
            if (container.children.length === 0) {
                addHighlight();
            }
        });
    </script>
</body>
</html>
