<?php
// ১. ডাটাবেস কানেকশন


require_once('config/db_connect.php');

$logged_in_buyer_id = 2026;

// নিরাপত্তা নিশ্চিত করতে Prepared Statement ব্যবহার করা ভালো, 
// তবে আপাতত আপনার সহজ কুয়েরিটিই নিচে লুপে চালানো হয়েছে।
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Management</title>
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
           <link rel="stylesheet" href="dist/css/admin.css">

    </head>
<body>

    <div class="container-fluid">
        <div class="table-card">
            <h6 class="mb-4 text-dark"><i class="fas fa-list-alt me-2"></i> Sample List (GS)</h6>
            <div class="table-responsive">
                <table class="table table-hover custom-table" id="myTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Style Name</th>
                            <th>Sample Type</th>
                            <th>Quantity</th>
                            <th>Color</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // SQL Injection থেকে বাঁচতে ভালো প্র্যাকটিস
                        $stmt = $conn->prepare("SELECT entry_date, style_name, sample_type, qty, color, extra_send FROM samples WHERE buyer_id = ? AND sample_type = 'GS Sample' ORDER BY id DESC");
                        $stmt->bind_param("i", $logged_in_buyer_id);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td class="text-secondary"><?php echo date('d M, Y', strtotime($row['entry_date'])); ?></td>
                                    <td class="style-name"><?php echo htmlspecialchars($row['style_name']); ?></td>
                                    <td><span class="badge-info"><?php echo $row['sample_type']; ?></span></td>
                                    <td class="fw-bold"><?php echo (int)$row['qty']; ?></td>
                                    <td><?php echo htmlspecialchars($row['color']); ?></td>
                                    <td class="text-muted small"><?php echo htmlspecialchars($row['extra_send']); ?></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#myTable').DataTable({
            "responsive": true,
            "pageLength": 10,
            "language": {
                "search": "সার্চ:",
                "lengthMenu": "দেখান _MENU_ টি",
                "info": "মোট _TOTAL_ টি রেকর্ডের মধ্যে _START_ থেকে _END_ পর্যন্ত দেখাচ্ছেন",
                "paginate": { "next": "পরবর্তী", "previous": "আগের" },
                "emptyTable": "কোন ডাটা পাওয়া যায়নি"
            }
        });
    });
    </script>
</body>
</html>