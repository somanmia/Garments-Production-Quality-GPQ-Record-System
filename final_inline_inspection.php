<?php
// ১. সেশন এবং ডাটাবেস কানেকশন স্টার্ট
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once('config/db_connect.php');

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// ক্যারেক্টার সেট এনকোডিং ঠিক করা (বাংলা ডাটার জন্য গুরুত্বপূর্ণ)
mysqli_set_charset($conn, "utf8mb4");

$logged_in_buyer_id = 2026; // আপনার সেট করা বায়ার আইডি
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Inline Inspection Report List</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <link rel="stylesheet" href="dist/css/admin.css">
</head>
<body>

    <div class="container-fluid py-3">
        
        <div class="candidate-header mb-4 d-flex align-items-center justify-content-between">
            <h5 class="m-0 fw-bold text-secondary"><i class="fa fa-clipboard-check me-2 text-primary"></i>Final Inline Inspection Report List</h5>
        </div>

        <div class="table-card shadow-sm">
            <div class="table-responsive">
                <table class="table custom-table align-middle" id="myTable">
                    <thead>
                        <tr>
                            <th>Created Date</th>
                            <th>Style Number</th>
                            <th>Comments</th>
                            <th class="text-center" style="width: 200px;">PDF File</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // final_inline_inspection টেবিল এবং Comments কলাম অনুযায়ী সিলেক্ট কুয়েরি আপডেট করা হয়েছে
                        $select_stmt = $conn->prepare("SELECT id, style_name, Comments, drive_link, created_at FROM final_inline_inspection WHERE buyer_id = ? ORDER BY id DESC");
                        $select_stmt->bind_param("i", $logged_in_buyer_id);
                        $select_stmt->execute();
                        $records = $select_stmt->get_result();
                        
                        if ($records) {
                            while ($row = $records->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td class="text-muted">
                                        <i class="far fa-calendar-alt me-1"></i> 
                                        <?php echo (!empty($row['created_at'])) ? date('d M, Y', strtotime($row['created_at'])) : 'N/A'; ?>
                                    </td>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($row['style_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Comments']); ?></td>
                                    
                                    <td class="text-center">
                                        <?php 
                                        if (!empty($row['drive_link'])): 
                                            $raw_link = $row['drive_link'];
                                            $download_link = $raw_link;

                                            if (strpos($raw_link, 'drive.google.com') !== false) {
                                                if (preg_match('/\/d\/([a-zA-Z0-9-_]+)/', $raw_link, $matches)) {
                                                    $file_id = $matches[1];
                                                    // ড্রাইভ পেজ লোড এড়াতে ডাইরেক্ট ডাউনলোড ইউআরএল
                                                    $download_link = "https://docs.google.com/uc?id=" . $file_id . "&export=download";
                                                }
                                            }
                                        ?>
                                            <a href="<?php echo htmlspecialchars($download_link); ?>" class="btn-pdf-download text-decoration-none px-3 py-1.5 rounded" download title="সরাসরি ফাইলটি ডাউনলোড করুন">
                                                <i class="fa-solid fa-cloud-arrow-down me-1"></i> Download PDF
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small px-2"><i class="fa-solid fa-circle-exclamation me-1"></i>No File</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                        $select_stmt->close();
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        // ডাটাটেবিল ইনিশিয়ালাইজেশন
        $('#myTable').DataTable({
            "responsive": true,
            "pageLength": 10,
            "order": [], 
            "language": {
                "search": "সার্চ ফিল্টার:",
                "lengthMenu": "দেখান _MENU_ টি রেকর্ড",
                "info": "মোট _TOTAL_ টি রেকর্ডের মধ্যে _START_ থেকে _END_ দেখানো হচ্ছে",
                "paginate": { "next": "<i class='fa fa-angle-right'></i>", "previous": "<i class='fa fa-angle-left'></i>" }
            }
        });
    });
    </script>
</body>
</html>