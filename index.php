<?php
require_once 'config.php';

// Handle form submissions
$message = '';
$error = '';

// Add Daily Entry
if (isset($_POST['add_daily_entry'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO daily_entries 
            (entry_date, sales, deposit, expense, short_deposit, owner_count, paid_clip_status, notes, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $_POST['entry_date'],
            $_POST['sales'] ?: 0,
            $_POST['deposit'] ?: 0,
            $_POST['expense'] ?: 0,
            $_POST['short_deposit'] ?: 0,
            $_POST['owner_count'] ?: 0,
            $_POST['paid_clip_status'],
            $_POST['notes'],
            $_SESSION['user_id'] ?? 1
        ]);
        
        $entry_id = $pdo->lastInsertId();
        
        // Handle file uploads
        if (!empty($_FILES['attachments']['name'][0])) {
            foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                if (!empty($tmp_name)) {
                    $file = [
                        'name' => $_FILES['attachments']['name'][$key],
                        'type' => $_FILES['attachments']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['attachments']['error'][$key],
                        'size' => $_FILES['attachments']['size'][$key]
                    ];
                    
                    $upload = uploadFile($file, 'daily_attachments');
                    if ($upload['success']) {
                        $stmt = $pdo->prepare("INSERT INTO attachments 
                            (reference_type, reference_id, file_name, file_path) 
                            VALUES ('daily', ?, ?, ?)");
                        $stmt->execute([$entry_id, $file['name'], $upload['filename']]);
                    }
                }
            }
        }
        
        $message = "Daily entry added successfully!";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get statistics
$stats = $pdo->query("SELECT 
    COALESCE(SUM(sales),0) as total_sales,
    COALESCE(SUM(deposit),0) as total_deposits,
    COALESCE(SUM(expense),0) as total_expenses,
    COALESCE(SUM(short_deposit),0) as total_short_deposit,
    AVG(owner_count) as avg_owners
    FROM daily_entries WHERE MONTH(entry_date) = MONTH(CURDATE())")->fetch();

// Get recent entries
$recent_entries = $pdo->query("SELECT * FROM daily_entries ORDER BY entry_date DESC LIMIT 10")->fetchAll();

// Get automation transactions
$automation = $pdo->query("SELECT * FROM automation_transactions ORDER BY transaction_date DESC LIMIT 5")->fetchAll();

// Get owners
$owners = $pdo->query("SELECT * FROM owners WHERE is_active = 1")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Safety System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .upload-area {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
        }
        .upload-area:hover {
            border-color: #667eea;
        }
        .file-list {
            margin-top: 10px;
        }
        .file-item {
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-shield-lock"></i> Financial Safety System
            </span>
            <div class="text-white">
                <i class="bi bi-person-circle"></i> Admin
                <button class="btn btn-sm btn-outline-light ms-3" onclick="location.href='logout.php'">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <h6>Total Sales (Month)</h6>
                    <h3>$<?php echo number_format($stats['total_sales'], 2); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h6>Total Deposits</h6>
                    <h3>$<?php echo number_format($stats['total_deposits'], 2); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <h6>Total Expenses</h6>
                    <h3>$<?php echo number_format($stats['total_expenses'], 2); ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <h6>Active Owners</h6>
                    <h3><?php echo count($owners); ?></h3>
                </div>
            </div>
        </div>

        <!-- Add Daily Entry Form -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Add New Daily Entry</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_daily_entry" value="1">
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Date</label>
                            <input type="date" name="entry_date" class="form-control" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Sales Amount ($)</label>
                            <input type="number" step="0.01" name="sales" class="form-control" 
                                   placeholder="0.00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Deposit Amount ($)</label>
                            <input type="number" step="0.01" name="deposit" class="form-control" 
                                   placeholder="0.00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Expense Amount ($)</label>
                            <input type="number" step="0.01" name="expense" class="form-control" 
                                   placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Short Deposit ($)</label>
                            <input type="number" step="0.01" name="short_deposit" class="form-control" 
                                   placeholder="0.00">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Owner Count</label>
                            <input type="number" name="owner_count" class="form-control" value="4">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Paid Clip Status</label>
                            <select name="paid_clip_status" class="form-control">
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Attachments</label>
                            <input type="file" name="attachments[]" class="form-control" multiple 
                                   accept=".pdf,.jpg,.jpeg,.png,.xlsx,.docx">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Entry
                    </button>
                </form>
            </div>
        </div>

        <!-- Recent Entries Table -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-table"></i> Recent Daily Entries</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Sales</th>
                                <th>Deposit</th>
                                <th>Expense</th>
                                <th>Short Deposit</th>
                                <th>Owners</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_entries as $entry): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($entry['entry_date'])); ?></td>
                                <td>$<?php echo number_format($entry['sales'], 2); ?></td>
                                <td>$<?php echo number_format($entry['deposit'], 2); ?></td>
                                <td>$<?php echo number_format($entry['expense'], 2); ?></td>
                                <td>$<?php echo number_format($entry['short_deposit'], 2); ?></td>
                                <td><?php echo $entry['owner_count']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $entry['paid_clip_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($entry['paid_clip_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewEntry(<?php echo $entry['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="editEntry(<?php echo $entry['id']; ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Automation & Owners Section -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-robot"></i> Automation Transactions</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php foreach ($automation as $auto): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?php echo $auto['category']; ?></strong>
                                        <br>
                                        <small><?php echo $auto['description']; ?></small>
                                    </div>
                                    <div class="text-<?php echo $auto['type'] == 'credit' ? 'success' : 'danger'; ?>">
                                        <?php echo $auto['type'] == 'credit' ? '+' : '-'; ?>
                                        $<?php echo number_format($auto['amount'], 2); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="bi bi-people"></i> Owners List</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($owners as $owner): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $owner['name']; ?>
                                <span class="badge bg-primary"><?php echo $owner['role']; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function viewEntry(id) {
        alert('View entry ' + id + ' - Implement view functionality');
    }
    
    function editEntry(id) {
        alert('Edit entry ' + id + ' - Implement edit functionality');
    }
    
    // Preview attachments before upload
    document.querySelector('input[type="file"]').addEventListener('change', function(e) {
        const fileList = document.createElement('div');
        fileList.className = 'file-list';
        
        for (let i = 0; i < this.files.length; i++) {
            const file = this.files[i];
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `<i class="bi bi-file-earmark"></i> ${file.name} (${(file.size/1024).toFixed(2)} KB)`;
            fileList.appendChild(fileItem);
        }
        
        const existingList = this.parentNode.querySelector('.file-list');
        if (existingList) existingList.remove();
        this.parentNode.appendChild(fileList);
    });
    </script>
</body>
</html>