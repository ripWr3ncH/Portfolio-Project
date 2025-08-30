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
                // Add new project
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $technologies = trim($_POST['technologies']);
                $project_url = trim($_POST['project_url']);
                $github_url = trim($_POST['github_url']);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $status = $_POST['status'];
                
                if (empty($title) || empty($description)) {
                    $error = 'Title and description are required.';
                } else {
                    $image_path = null;
                    
                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../uploads/projects/';
                        $image_name = time() . '_' . basename($_FILES['image']['name']);
                        $image_path = $upload_dir . $image_name;
                        
                        if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                            $error = 'Failed to upload image.';
                        } else {
                            $image_path = 'uploads/projects/' . $image_name;
                        }
                    }
                    
                    if (empty($error)) {
                        $stmt = $pdo->prepare("INSERT INTO projects (title, description, technologies, image_path, project_url, github_url, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        if ($stmt->execute([$title, $description, $technologies, $image_path, $project_url, $github_url, $is_featured, $status])) {
                            $message = 'Project added successfully!';
                        } else {
                            $error = 'Failed to add project.';
                        }
                    }
                }
                break;
                
            case 'edit':
                // Edit existing project
                $id = (int)$_POST['id'];
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $technologies = trim($_POST['technologies']);
                $project_url = trim($_POST['project_url']);
                $github_url = trim($_POST['github_url']);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $status = $_POST['status'];
                
                if (empty($title) || empty($description)) {
                    $error = 'Title and description are required.';
                } else {
                    // Get current project data
                    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
                    $stmt->execute([$id]);
                    $current_project = $stmt->fetch();
                    
                    $image_path = $current_project['image_path'];
                    
                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $upload_dir = '../uploads/projects/';
                        $image_name = time() . '_' . basename($_FILES['image']['name']);
                        $new_image_path = $upload_dir . $image_name;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $new_image_path)) {
                            // Delete old image
                            if ($image_path && file_exists('../' . $image_path)) {
                                unlink('../' . $image_path);
                            }
                            $image_path = 'uploads/projects/' . $image_name;
                        }
                    }
                    
                    $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, technologies = ?, image_path = ?, project_url = ?, github_url = ?, is_featured = ?, status = ?, updated_at = NOW() WHERE id = ?");
                    if ($stmt->execute([$title, $description, $technologies, $image_path, $project_url, $github_url, $is_featured, $status, $id])) {
                        $message = 'Project updated successfully!';
                    } else {
                        $error = 'Failed to update project.';
                    }
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
                $stmt->execute([$id]);
                $project = $stmt->fetch();
                
                if ($project) {
                    // Delete image file
                    if ($project['image_path'] && file_exists('../../' . $project['image_path'])) {
                        unlink('../../' . $project['image_path']);
                    }
                    
                    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                    if ($stmt->execute([$id])) {
                        $message = 'Project deleted successfully!';
                    } else {
                        $error = 'Failed to delete project.';
                    }
                }
                break;
        }
    }
}

// Get all projects
$stmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC");
$projects = $stmt->fetchAll();

// Get project for editing if specified
$edit_project = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_project = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects - Admin Panel</title>
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
                    <h1><i class="fas fa-folder-open"></i> Manage Projects</h1>
                    <p>Add, edit, and manage your portfolio projects</p>
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
                
                <!-- Add/Edit Project Form -->
                <div class="form-container">
                    <h2><?php echo $edit_project ? 'Edit Project' : 'Add New Project'; ?></h2>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $edit_project ? 'edit' : 'add'; ?>">
                        <?php if ($edit_project): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_project['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title">Project Title *</label>
                                <input type="text" id="title" name="title" value="<?php echo $edit_project ? htmlspecialchars($edit_project['title']) : ''; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status">
                                    <option value="active" <?php echo (!$edit_project || $edit_project['status'] === 'active') ? 'selected' : ''; ?>>Active (Published)</option>
                                    <option value="completed" <?php echo ($edit_project && $edit_project['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                    <option value="in_progress" <?php echo ($edit_project && $edit_project['status'] === 'in_progress') ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="planned" <?php echo ($edit_project && $edit_project['status'] === 'planned') ? 'selected' : ''; ?>>Planned</option>
                                    <option value="draft" <?php echo ($edit_project && $edit_project['status'] === 'draft') ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" required><?php echo $edit_project ? htmlspecialchars($edit_project['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="technologies">Technologies Used</label>
                            <input type="text" id="technologies" name="technologies" value="<?php echo $edit_project ? htmlspecialchars($edit_project['technologies']) : ''; ?>" placeholder="e.g., HTML, CSS, JavaScript, PHP">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="project_url">Project URL</label>
                                <input type="url" id="project_url" name="project_url" value="<?php echo $edit_project ? htmlspecialchars($edit_project['project_url']) : ''; ?>" placeholder="https://example.com">
                            </div>
                            
                            <div class="form-group">
                                <label for="github_url">GitHub URL</label>
                                <input type="url" id="github_url" name="github_url" value="<?php echo $edit_project ? htmlspecialchars($edit_project['github_url']) : ''; ?>" placeholder="https://github.com/username/repo">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="image">Project Image</label>
                            <input type="file" id="image" name="image" accept="image/*">
                            <?php if ($edit_project && $edit_project['image_path']): ?>
                                <p>Current image: <img src="../../<?php echo htmlspecialchars($edit_project['image_path']); ?>" alt="Current image" style="max-width: 100px; height: auto; margin-top: 10px;"></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_featured" name="is_featured" <?php echo ($edit_project && $edit_project['is_featured']) ? 'checked' : ''; ?>>
                            <label for="is_featured">Featured Project</label>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> <?php echo $edit_project ? 'Update Project' : 'Add Project'; ?>
                            </button>
                            <?php if ($edit_project): ?>
                                <a href="projects.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Projects List -->
                <div class="table-container" style="margin-top: 2rem;">
                    <div class="table-header">
                        <h2>All Projects (<?php echo count($projects); ?>)</h2>
                    </div>
                    
                    <?php if (empty($projects)): ?>
                        <div style="padding: 2rem; text-align: center; color: #666;">
                            <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>No projects found. Add your first project above!</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Technologies</th>
                                    <th>Status</th>
                                    <th>Featured</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>
                                    <tr>
                                        <td>
                                            <?php if ($project['image_path']): ?>
                                                <img src="../../<?php echo htmlspecialchars($project['image_path']); ?>" alt="Project image" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 40px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image" style="color: #ccc;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($project['title']); ?></strong>
                                            <br>
                                            <small style="color: #666;"><?php echo htmlspecialchars(substr($project['description'], 0, 50)) . (strlen($project['description']) > 50 ? '...' : ''); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($project['technologies']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $project['status']; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($project['is_featured']): ?>
                                                <i class="fas fa-star" style="color: #ffc107;"></i>
                                            <?php else: ?>
                                                <i class="far fa-star" style="color: #ccc;"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($project['created_at'])); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.5rem;">
                                                <?php if ($project['project_url']): ?>
                                                    <a href="<?php echo htmlspecialchars($project['project_url']); ?>" target="_blank" class="btn btn-sm btn-secondary" title="View Project">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <?php if ($project['github_url']): ?>
                                                    <a href="<?php echo htmlspecialchars($project['github_url']); ?>" target="_blank" class="btn btn-sm btn-secondary" title="View Code">
                                                        <i class="fab fa-github"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="projects.php?edit=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this project?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
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
        }
        
        .badge-completed {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-in_progress {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-planned {
            background: #cce7ff;
            color: #004085;
        }
    </style>
</body>
</html>
