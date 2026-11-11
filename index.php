<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// ১. ডাটাবেস কানেকশন ইনক্লুড করা
require_once('config/db_connect.php');
$conn = mysqli_connect($servername, $username, $password, $dbname);
if ($conn) {
    mysqli_set_charset($conn, "utf8mb4");
}

// সেশন থেকে লগইন থাকা বায়ার আইডি নেওয়া
$logged_in_buyer_id = 2026; 

// --- ডাটাবেস থেকে রিয়েল-টাইম ইউনিক ডাটা কাউন্ট করার ভেরিয়েবল ---
$total_pp_meetings   = 0;
$total_pp_samples    = 0;
$total_gs_samples    = 0;
$total_fit_samples   = 0;
$total_dev_samples   = 0;
$total_inspections   = 0;
$pending_inspections = 0;

if ($conn) {
    
    // ১. মোট পিপি মিটিং এর সংখ্যা (সুরক্ষিত কুয়েরি)
    try {
        $stmt1 = $conn->prepare("SELECT COUNT(DISTINCT style_name) as total FROM pp_meetings WHERE buyer_id = ?");
        if(!$stmt1) {
            $stmt1 = $conn->prepare("SELECT COUNT(DISTINCT style_no) as total FROM pp_meetings WHERE buyer_id = ?");
        }
        if($stmt1) {
            $stmt1->bind_param("i", $logged_in_buyer_id);
            $stmt1->execute();
            $res1 = $stmt1->get_result()->fetch_assoc();
            $total_pp_meetings = $res1['total'] ?? 0;
            $stmt1->close();
        }
    } catch (Exception $e) { $total_pp_meetings = 0; }

    // ২. পিপি স্যাম্পল (ইউনিক কাউন্ট - style_name দিয়ে ডুপ্লিকেট বাদ) 🔥
    try {
        $stmt2 = $conn->prepare("SELECT COUNT(DISTINCT style_name) as total FROM samples WHERE buyer_id = ? AND sample_type = 'PP Sample'");
        if($stmt2) {
            $stmt2->bind_param("i", $logged_in_buyer_id);
            $stmt2->execute();
            $res2 = $stmt2->get_result()->fetch_assoc();
            $total_pp_samples = $res2['total'] ?? 0;
            $stmt2->close();
        }
    } catch (Exception $e) { $total_pp_samples = 0; }

    // ৩. জিএস স্যাম্পল (style_name দিয়ে ডুপ্লিকেট বাদ)
    try {
        $stmt3 = $conn->prepare("SELECT COUNT(DISTINCT style_name) as total FROM samples WHERE buyer_id = ? AND sample_type = 'GS Sample'");
        if($stmt3) {
            $stmt3->bind_param("i", $logged_in_buyer_id);
            $stmt3->execute();
            $res3 = $stmt3->get_result()->fetch_assoc();
            $total_gs_samples = $res3['total'] ?? 0;
            $stmt3->close();
        }
    } catch (Exception $e) { $total_gs_samples = 0; }

    // ৪. ফিট স্যাম্পল (style_name দিয়ে ডুপ্লিকেট বাদ)
    try {
        $stmt4 = $conn->prepare("SELECT COUNT(DISTINCT style_name) as total FROM samples WHERE buyer_id = ? AND (sample_type = 'Fit-1' OR sample_type = 'Fit-2')");
        if($stmt4) {
            $stmt4->bind_param("i", $logged_in_buyer_id);
            $stmt4->execute();
            $res4 = $stmt4->get_result()->fetch_assoc();
            $total_fit_samples = $res4['total'] ?? 0;
            $stmt4->close();
        }
    } catch (Exception $e) { $total_fit_samples = 0; }

    // ৫. ডেভেলপমেন্ট স্যাম্পল (style_name দিয়ে ডুপ্লিকেট বাদ)
    try {
        $stmt5 = $conn->prepare("SELECT COUNT(DISTINCT style_name) as total FROM samples WHERE buyer_id = ? AND sample_type = 'Development'");
        if($stmt5) {
            $stmt5->bind_param("i", $logged_in_buyer_id);
            $stmt5->execute();
            $res5 = $stmt5->get_result()->fetch_assoc();
            $total_dev_samples = $res5['total'] ?? 0;
            $stmt5->close();
        }
    } catch (Exception $e) { $total_dev_samples = 0; }

    // ৬. ফাইনাল ইন্সপেকশন এর সংখ্যা (সুরক্ষিত কুয়েরি)
    $check_table4 = mysqli_query($conn, "SHOW TABLES LIKE 'final_inspections'");
    if($check_table4 && mysqli_num_rows($check_table4) > 0) {
        try {
            $stmt6 = $conn->prepare("SELECT COUNT(DISTINCT style_name) as total FROM final_inspections WHERE buyer_id = ?");
            if(!$stmt6) {
                $stmt6 = $conn->prepare("SELECT COUNT(DISTINCT style_no) as total FROM final_inspections WHERE buyer_id = ?");
            }
            if($stmt6) {
                $stmt6->bind_param("i", $logged_in_buyer_id);
                $stmt6->execute();
                $res6 = $stmt6->get_result()->fetch_assoc();
                $total_inspections = $res6['total'] ?? 0;
                $stmt6->close();
            }
        } catch (Exception $e) { $total_inspections = 0; }
    }

    // স্মার্ট পেন্ডিং ক্যালকুলেশন
    $pending_inspections = $total_pp_meetings - $total_inspections;
    if($pending_inspections < 0) { $pending_inspections = 0; }
}

// --- গ্রাফ/চার্ট পারসেন্টেজ ক্যালকুলেশন ---
$max_value = max($total_pp_meetings, $total_pp_samples, $total_gs_samples, $total_fit_samples, $total_dev_samples, $pending_inspections, 1);
$pp_m_per   = ($total_pp_meetings / $max_value) * 100;
$pp_s_per   = ($total_pp_samples / $max_value) * 100;
$gs_per     = ($total_gs_samples / $max_value) * 100;
$fit_per    = ($total_fit_samples / $max_value) * 100;
$dev_per    = ($total_dev_samples / $max_value) * 100;
$pending_per = ($pending_inspections / $max_value) * 100;
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GPQ SMART RECORD SYSTEM</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dist/css/admin_home.css">
    
    <style>
        .chart-container { display: flex; flex-direction: column; gap: 16px; padding: 25px 15px; }
        .chart-row { display: flex; align-items: center; gap: 15px; }
        .chart-label { width: 190px; font-size: 14px; font-weight: 600; color: #444; }
        .chart-bar-wrapper { flex-grow: 1; background-color: #f1f3f5; border-radius: 4px; overflow: hidden; height: 26px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1); }
        .chart-bar { height: 100%; border-radius: 4px; transition: width 1s ease-in-out; display: flex; align-items: center; justify-content: flex-end; padding-right: 12px; color: white; font-size: 12px; font-weight: bold; min-width: 25px; }
        .bg-ppm { background: linear-gradient(90deg, #6c757d, #495057); }
        .bg-pps { background: linear-gradient(90deg, #28a745, #1e7e34); }
        .bg-gs  { background: linear-gradient(90deg, #17a2b8, #117a8b); }
        .bg-fit { background: linear-gradient(90deg, #007bff, #0056b3); }
        .bg-dev { background: linear-gradient(90deg, #fd7e14, #d94111); }
        .bg-pending { background: linear-gradient(90deg, #dc3545, #bd2130); }
    </style>
</head>
<body>

    <div class="wrapper">
        <nav class="main-header">
            <div class="nav-left" id="sidebarToggle"><i class="fas fa-bars"></i></div>
            <ul class="nav-right">
                <li class="user-profile-top">
                    <img src="https://adminlte.io/themes/AdminLTE/dist/img/user2-160x160.jpg" alt="Admin">
                    <span>GPQ RECORD</span>
                </li>
                <li><a href="logout.php" style="color: inherit;"><i class="fas fa-sign-out-alt"></i></a></li>
            </ul>
        </nav>

        <aside class="main-sidebar">
            <div class="brand-link" id="brandName">GPQ RECORD</div>
            <div class="user-panel">
                <img src="https://adminlte.io/themes/AdminLTE/dist/img/user2-160x160.jpg" alt="User Image">
                <div class="status-text">
                    <p>GPQ RECORD</p>
                    <small><i class="fa fa-circle text-success"></i> Online</small>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="menu-header">REPORTS</li>
                <li class="<?php echo (!isset($_GET['page']) || $_GET['page'] == '' || $_GET['page'] == 'dashboard') ? 'active' : ''; ?>">
                    <a href="index.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a>
                </li>
                <li class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'final_inline_inspection') ? 'active' : ''; ?>">
                    <a href="index.php?page=final_inline_inspection"><i class="fas fa-folder-open"></i> <span>INSPECTION REPORTS</span></a>
                </li>
                <li class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'pp_meeting') ? 'active' : ''; ?>">
                    <a href="index.php?page=pp_meeting"><i class="fa-solid fa-handshake"></i> <span>PP MEETING</span></a>
                </li>
                <li class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'final_inspection') ? 'active' : ''; ?>">
                    <a href="index.php?page=final_inspection"><i class="fas fa-check-double"></i> <span>FINAL INSPECTION</span></a>
                </li>
                
                <li class="menu-header">MANAGE</li>
                <li>
                    <a href="sample_card.php"><i class="fas fa-scroll"></i> <span>SAMPLE CARD.</span></a>
                </li>
                <li>
                    <a href="submission_form.php"><i class="fas fa-scroll"></i> <span>Submission Form.</span></a>
                </li>
                <li class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'development') ? 'active' : ''; ?>">
                    <a href="index.php?page=development"><i class="fas fa-code-branch"></i> <span>DEVELOPMENT</span></a>
                </li>
                <li class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'sample') ? 'active' : ''; ?>">
                    <a href="index.php?page=sample"><i class="fas fa-shirt"></i> <span>PP SAMPLE</span></a>
                </li>
                <li class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'gs_sample') ? 'active' : ''; ?>">
                    <a href="index.php?page=gs_sample"><i class="fas fa-tags"></i> <span>GS SAMPLE</span></a>
                </li>
                <li class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'fit_sample') ? 'active' : ''; ?>">
                    <a href="index.php?page=fit_sample"><i class="fas fa-ruler-combined"></i> <span>FIT SAMPLE</span></a>
                </li>
                
                <li class="menu-header">SETTINGS</li>
                <li><a href="login.php"><i class="fas fa-lock"></i> <span>Admin Login</span></a></li>
            </ul>
        </aside>

        <div class="content-wrapper">
            <?php 
                if (isset($_GET['page']) && $_GET['page'] == 'sample') { include 'pp_sample.php'; } 
                else if(isset($_GET['page']) && $_GET['page'] == 'gs_sample') { include 'gs_sample.php'; } 
                else if(isset($_GET['page']) && $_GET['page'] == 'fit_sample') { include 'fit_sample.php'; } 
                else if(isset($_GET['page']) && $_GET['page'] == 'development') { include 'development.php'; } 
                else if(isset($_GET['page']) && $_GET['page'] == 'sample_fabric') { include 'sample_fabric.php'; } 
                else if(isset($_GET['page']) && $_GET['page'] == 'final_inspection') { include 'final_inspection.php'; } 
                else if(isset($_GET['page']) && $_GET['page'] == 'pp_meeting') { include 'pp_meeting.php'; } 
                else if(isset($_GET['page']) && $_GET['page'] == 'final_inline_inspection') { include 'final_inline_inspection.php'; } 
                
                else {
            ?>
                <div class="content-header">
                    <h1>System Dashboard</h1>
                    <div class="breadcrumb">Home > Dashboard</div>
                </div>

                <div class="stats-grid">
                    <div class="small-box bg-green">
                        <div class="inner"><h3><?php echo $total_pp_samples; ?></h3><p>Unique PP Samples</p></div>
                        <div class="icon"><i class="fas fa-shirt"></i></div>
                        <a href="index.php?page=sample" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                    <div class="small-box bg-blue" style="background-color: #007bff !important; color: #fff;">
                        <div class="inner"><h3><?php echo $total_fit_samples; ?></h3><p>Unique Fit Samples</p></div>
                        <div class="icon"><i class="fas fa-ruler-combined"></i></div>
                        <a href="index.php?page=fit_sample" class="small-box-footer" style="color: rgba(255,255,255,0.8) !important;">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                    <div class="small-box bg-teal" style="background-color: #17a2b8 !important; color: #fff;">
                        <div class="inner"><h3><?php echo $total_gs_samples; ?></h3><p>Unique GS Samples</p></div>
                        <div class="icon"><i class="fas fa-tags"></i></div>
                        <a href="index.php?page=gs_sample" class="small-box-footer" style="color: rgba(255,255,255,0.8) !important;">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                    <div class="small-box bg-orange">
                        <div class="inner"><h3><?php echo $total_dev_samples; ?></h3><p>Unique Dev. Samples</p></div>
                        <div class="icon"><i class="fas fa-code-branch"></i></div>
                        <a href="index.php?page=development" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                    </div>
                </div>

                <div class="chart-box">
                    <div class="chart-title">
                        <span><i class="fa-solid fa-chart-bar me-2 text-primary"></i>Garments Quality & Sample Record Tally (Unique by Style Name)</span>
                        <button class="btn-print" onclick="window.print()"><i class="fa fa-print"></i> Print Analytics</button>
                    </div>
                    
                    <div class="chart-container">
                        <div class="chart-row">
                            <div class="chart-label"><i class="fa-solid fa-handshake me-2 text-secondary"></i> PP Meetings</div>
                            <div class="chart-bar-wrapper"><div class="chart-bar bg-ppm" style="width: <?php echo $pp_m_per; ?>%;"><?php echo $total_pp_meetings; ?></div></div>
                        </div>
                        <div class="chart-row">
                            <div class="chart-label"><i class="fas fa-shirt me-2 text-success"></i> PP Samples</div>
                            <div class="chart-bar-wrapper"><div class="chart-bar bg-pps" style="width: <?php echo $pp_s_per; ?>%;"><?php echo $total_pp_samples; ?></div></div>
                        </div>
                        <div class="chart-row">
                            <div class="chart-label"><i class="fas fa-tags me-2 text-info"></i> GS Samples</div>
                            <div class="chart-bar-wrapper"><div class="chart-bar bg-gs" style="width: <?php echo $gs_per; ?>%;"><?php echo $total_gs_samples; ?></div></div>
                        </div>
                        <div class="chart-row">
                            <div class="chart-label"><i class="fas fa-ruler-combined me-2 text-primary"></i> Fit Samples</div>
                            <div class="chart-bar-wrapper"><div class="chart-bar bg-fit" style="width: <?php echo $fit_per; ?>%;"><?php echo $total_fit_samples; ?></div></div>
                        </div>
                        <div class="chart-row">
                            <div class="chart-label"><i class="fas fa-code-branch me-2 text-warning"></i> Dev. Samples</div>
                            <div class="chart-bar-wrapper"><div class="chart-bar bg-dev" style="width: <?php echo $dev_per; ?>%;"><?php echo $total_dev_samples; ?></div></div>
                        </div>
                        <div class="chart-row">
                            <div class="chart-label"><i class="fas fa-clock me-2 text-danger"></i> Pending Inspect.</div>
                            <div class="chart-bar-wrapper"><div class="chart-bar bg-pending" style="width: <?php echo $pending_per; ?>%;"><?php echo $pending_inspections; ?></div></div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <footer style="margin-left: 230px; background: #fff; padding: 15px; color: #444; border-top: 1px solid #dee2e6; transition: margin-left 0.3s ease-in-out;" id="main-footer">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <strong>Copyright &copy; <?php echo date('Y'); ?> </strong> 
                    <a href="#" style="color: #3c8dbc; text-decoration: none; font-weight: 600;">GPQ Smart Record System</a>. 
                </div>
                <div style="font-size: 13px; color: #777;"><b>Version</b> 1.6.5</div>
            </div>
        </footer>
    </div>

    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const body = document.body;
        const brandName = document.getElementById('brandName');
        const footer = document.getElementById('main-footer');

        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (window.innerWidth <= 768) {
                body.classList.toggle('sidebar-open');
            } else {
                body.classList.toggle('sidebar-collapse');
                footer.style.marginLeft = body.classList.contains('sidebar-collapse') ? "50px" : "230px";
                brandName.innerText = body.classList.contains('sidebar-collapse') ? "GPQ" : "GPQ RECORD";
            }
        });
        if (window.innerWidth <= 768) { footer.style.marginLeft = "0"; }
    </script>
</body>
</html>