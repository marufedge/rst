<?php
require_once 'config.php';
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get statistics
$stats = [];

// Total sales today
$stmt = $pdo->query("SELECT COUNT(*) as count, COALESCE(SUM(net_amount), 0) as total FROM daily_sales WHERE sale_date = CURDATE()");
$stats['today_sales'] = $stmt->fetch();

// Total pending deliveries
$stmt = $pdo->query("SELECT COUNT(*) as count FROM delivery_payments WHERE status = 'pending'");
$stats['pending_deliveries'] = $stmt->fetchColumn();

// Total bank deposits this month
$stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total FROM bank_deposits WHERE MONTH(deposit_date) = MONTH(CURDATE())");
$stats['month_deposits'] = $stmt->fetchColumn();

// Active salesmen
$stmt = $pdo->query("SELECT COUNT(*) as count FROM salesmen WHERE status = 'active'");
$stats['active_salesmen'] = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2>Dashboard</h2>
                <hr>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Today's Sales</h6>
                                <h3><?php echo number_format($stats['today_sales']['count']); ?></h3>
                                <p class="mb-0">Amount: $<?php echo number_format($stats['today_sales']['total'], 2); ?></p>
                            </div>
                            <i class="bi bi-cart-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Pending Deliveries</h6>
                                <h3><?php echo $stats['pending_deliveries']; ?></h3>
                            </div>
                            <i class="bi bi-truck fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Monthly Deposits</h6>
                                <h3>$<?php echo number_format($stats['month_deposits'], 2); ?></h3>
                            </div>
                            <i class="bi bi-bank fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Active Salesmen</h6>
                                <h3><?php echo $stats['active_salesmen']; ?></h3>
                            </div>
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Sales</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT ds.*, s.full_name as salesman_name 
                                                     FROM daily_sales ds 
                                                     JOIN salesmen s ON ds.salesman_id = s.id 
                                                     ORDER BY ds.created_at DESC LIMIT 5");
                                while ($row = $stmt->fetch()):
                                ?>
                                <tr>
                                    <td><?php echo $row['invoice_number']; ?></td>
                                    <td><?php echo $row['customer_name']; ?></td>
                                    <td>$<?php echo number_format($row['net_amount'], 2); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['sale_date'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Bank Deposits</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Bank</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT * FROM bank_deposits ORDER BY created_at DESC LIMIT 5");
                                while ($row = $stmt->fetch()):
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($row['deposit_date'])); ?></td>
                                    <td><?php echo $row['bank_name']; ?></td>
                                    <td>$<?php echo number_format($row['amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>