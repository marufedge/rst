<?php
require_once 'config.php';

if (isset($_POST['upload_monthly_report'])) {
    $uploads = [];
    
    // Handle multiple file uploads
    $file_fields = ['bank_statement', 'salary_slip', 'loan_deduction', 'office_paid'];
    
    foreach ($file_fields as $field) {
        if (!empty($_FILES[$field]['name'])) {
            $upload = uploadFile($_FILES[$field], 'monthly_reports');
            if ($upload['success']) {
                $uploads[$field . '_file'] = $upload['filename'];
            }
        }
    }
    
    if (!empty($uploads)) {
        $stmt = $pdo->prepare("INSERT INTO monthly_reports 
            (report_month, bank_statement_file, salary_slip_file, loan_deduction_file, office_paid_file) 
            VALUES (?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $_POST['report_month'] . '-01',
            $uploads['bank_statement_file'] ?? null,
            $uploads['salary_slip_file'] ?? null,
            $uploads['loan_deduction_file'] ?? null,
            $uploads['office_paid_file'] ?? null
        ]);
        
        $message = "Monthly report uploaded successfully!";
    }
}

// Get existing reports
$reports = $pdo->query("SELECT * FROM monthly_reports ORDER BY report_month DESC")->fetchAll();
?>

<!-- Add this HTML to your monthly reports page -->
<div class="container-fluid mt-4">
    <h2>Monthly Reports</h2>
    
    <div class="card">
        <div class="card-header">
            <h5>Upload Monthly Report</h5>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Report Month</label>
                        <input type="month" name="report_month" class="form-control" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label>Bank Statement</label>
                        <input type="file" name="bank_statement" class="form-control" accept=".pdf,.jpg,.png">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Salary Slip</label>
                        <input type="file" name="salary_slip" class="form-control" accept=".pdf,.jpg,.png">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Loan Deduction</label>
                        <input type="file" name="loan_deduction" class="form-control" accept=".pdf,.jpg,.png">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label>Office Paid</label>
                        <input type="file" name="office_paid" class="form-control" accept=".pdf,.jpg,.png">
                    </div>
                </div>
                
                <button type="submit" name="upload_monthly_report" class="btn btn-primary">
                    Upload Report
                </button>
            </form>
        </div>
    </div>
    
    <!-- Display existing reports -->
    <div class="card mt-4">
        <div class="card-header">
            <h5>Previous Reports</h5>
        </div>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Bank Statement</th>
                        <th>Salary Slip</th>
                        <th>Loan Deduction</th>
                        <th>Office Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?php echo date('F Y', strtotime($report['report_month'])); ?></td>
                        <td>
                            <?php if ($report['bank_statement_file']): ?>
                                <a href="uploads/monthly_reports/<?php echo $report['bank_statement_file']; ?>" target="_blank">
                                    <i class="bi bi-file-pdf"></i> View
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($report['salary_slip_file']): ?>
                                <a href="uploads/monthly_reports/<?php echo $report['salary_slip_file']; ?>" target="_blank">
                                    <i class="bi bi-file-pdf"></i> View
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($report['loan_deduction_file']): ?>
                                <a href="uploads/monthly_reports/<?php echo $report['loan_deduction_file']; ?>" target="_blank">
                                    <i class="bi bi-file-image"></i> View
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($report['office_paid_file']): ?>
                                <a href="uploads/monthly_reports/<?php echo $report['office_paid_file']; ?>" target="_blank">
                                    <i class="bi bi-file-pdf"></i> View
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>