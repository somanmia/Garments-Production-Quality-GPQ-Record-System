<?php
require_once('config/db_connect.php');

// Default empty values
$row = [
    'user_id' => '', 'factory_name' => '', 'contact_am_london' => '', 'sample_date' => '', 
    'style_id' => '', 'po_number' => '', 'shipment_date' => '', 'style_description' => '', 
    'colours_ordered' => '', 'sample_for' => '', 'samples_requested' => '', 'sample_size' => '', 
    'color' => '', 'main_fabric' => '', 'any_contrast_fabric' => '', 'main_colour' => '', 
    'any_contrast_colours' => '', 'trims' => '', 'main_print' => '', 'any_contrast_print' => '', 
    'embroidery' => '', 'back_neck_labels' => '', 'size_tab' => '', 'care_label' => '', 
    'swing_ticket' => '', 'barcode_labels' => '', 'hanger_sticker' => '', 'packaging' => '', 
    'fit' => '', 'trims_specify' => '', 'fabric_composition' => '', 'weight_spec' => '', 
    'test_reports' => '', 'comments' => ''
];

$search_done = false;

if (isset($_GET['search']) && !empty($_GET['search_id'])) {
    $search_id = trim($_GET['search_id']);
    
    $stmt = $conn->prepare("SELECT * FROM submission_form WHERE style_id = ? OR user_id = ? LIMIT 1");
    $stmt->bind_param("ss", $search_id, $search_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = array_merge($row, $result->fetch_assoc());
        $search_done = true;
    }
    $stmt->close();
}

function checkOption($dbVal, $targetVal) {
    return (strtoupper(trim((string)$dbVal)) === strtoupper(trim((string)$targetVal))) ? 'X' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SAMPLE SUBMIT FORM - View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
<style>
/* =========================================================
   SAMPLE SUBMIT FORM
   FULL CSS - SCREEN + A4 PRINT
   ========================================================= */

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
    background-color: #e9ecef;
    font-family: Arial, Helvetica, sans-serif;
    color: #000;
}

/* =========================================================
   TOP NAVIGATION BAR
   ========================================================= */

.top-nav-bar {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 10px 20px;
    color: #fff;
}

/* =========================================================
   MAIN A4 CONTAINER
   ========================================================= */

.form-container {
    width: 210mm;
    min-height: 280mm;
    padding: 10mm;
    margin: 10mm auto 20mm auto;
    background: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
}

/* =========================================================
   OFFICIAL FORM TABLE
   ========================================================= */

table.official-form {
    width: 100%;
    border-collapse: collapse !important;
    table-layout: fixed;
    border: 2px solid #000 !important;
    margin-bottom: 15px;
}

/* Column Width */

table.official-form colgroup col:nth-child(1) {
    width: 38%;
}

table.official-form colgroup col:nth-child(2) {
    width: 31%;
}

table.official-form colgroup col:nth-child(3) {
    width: 31%;
}

/* =========================================================
   TABLE CELLS
   ========================================================= */

table.official-form td {
    border: 1px solid #000 !important;
    padding: 6px 8px;
    font-size: 10px;
    line-height: 1.2;
    vertical-align: middle;
    color: #000;
    background-color: #fff;
    word-break: break-word;
}

/* =========================================================
   HEADER FIX (ভিতরের ভার্টিক্যাল বর্ডার সম্পূর্ণ মুছে ফেলা হয়েছে)
   ========================================================= */

tr#header {
    height: 110px !important;
}

tr#header td {
    border-left: none !important;
    border-right: none !important;
    border-bottom: 1px solid #000 !important;
    text-align: center !important;
    vertical-align: middle !important;
    padding: 5px !important;
}

/* শুধু বাইরের প্রান্তের দাগগুলো ঠিক রাখার জন্য */
tr#header td:first-child {
    border-left: 2px solid #000 !important;
}

tr#header td:last-child {
    border-right: 2px solid #000 !important;
}

.header-logo-1,
.header-logo-2 {
    display: block;
    max-height: 65px !important;
    max-width: 90% !important;
    width: auto !important;
    height: auto !important;
    object-fit: contain !important;
    margin: 0 auto !important;
}

.header-title-box {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    font-size: 16px !important;
    font-weight: 800 !important;
    text-decoration: underline;
    letter-spacing: 0.3px;
    text-align: center;
}

/* =========================================================
   THICK BORDERS
   ========================================================= */

.border-top-thick {
    border-top: 2px solid #000 !important;
}

.border-bottom-thick {
    border-bottom: 2px solid #000 !important;
}

/* =========================================================
   DATABASE DATA
   ========================================================= */

.data-display {
    font-size: 10px;
    font-weight: bold;
    line-height: 1.2;
    color: #000;
    text-transform: uppercase;
    display: block;
    margin-top: 2px;
}

/* =========================================================
   SECTION TITLES & SPACING
   ========================================================= */

.section-title-1 {
    font-size: 12px;
    font-weight: bold;
    text-align: center;
    letter-spacing: 0.2px;
    line-height: 1.2;
    height:80px;
    border:none;

}
.section-title {
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    letter-spacing: 0.2px;
    line-height: 1.2;
height:35px;

}
.sample-for-grid {
    display: flex;
    width: 100%;
    justify-content: space-between;
    font-size: 10px;
    font-weight: bold;
    text-align: center;
    margin-top: 10px;
}

.sample-for-grid div {
    flex: 1;
    white-space: nowrap;
}

.sample-for-values {
    display: flex;
    width: 100%;
    justify-content: space-between;
    font-size: 9px;
    text-align: center;
    font-weight: bold;
    margin-top: 4px;
}

.sample-for-values div {
    flex: 1;
    min-height: 12px;
}

.comments-td {
    vertical-align: top !important;
    padding-top: 8px !important;
    padding-bottom: 12px !important;
}

/* =========================================================
   PRINT SETTINGS
   ========================================================= */

@media print {
    @page {
        size: A4 portrait;
        margin: 5mm;
    }

    html, body {
        width: 210mm;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .no-print {
        display: none !important;
    }

    .form-container {
        width: 200mm !important;
        margin: 0 auto !important;
        padding: 2mm !important;
        box-shadow: none !important;
    }

    table.official-form {
        width: 100% !important;
        border: 2px solid #000 !important;
    }

    table.official-form td {
        padding: 5px 4px !important;
        font-size: 10px !important;
    }

    tr#header {
        height: 110px !important;
    }

    /* প্রিন্ট লেআউটেও মাঝের বর্ডার বন্ধ রাখা হলো */
    tr#header td {
        border-left: none !important;
        border-right: none !important;
        border-bottom: 1px solid #000 !important;
        front-size:18px;
    }

    tr#header td:first-child {
        border-left: 2px solid #000 !important;
    }

    tr#header td:last-child {
        border-right: 2px solid #000 !important;
    }

    .header-logo-1,
    .header-logo-2 {
        max-height: 60px !important;
    }

    .header-title-box {
        font-size: 16px !important;
    }
}

@media screen and (max-width: 900px) {
    .form-container {
        width: 100%;
        margin: 10px auto;
        padding: 10px;
        overflow-x: auto;
    }
    table.official-form {
        min-width: 700px;
    }
}
</style>
</head>
<body>

<div class="top-nav-bar no-print">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-file-lines text-info fs-5"></i>
            <span class="fw-bold fs-6">Official Sample Submit Form</span>
        </div>
        
        <form method="GET" action="" class="d-flex gap-2 m-0">
            <input type="text" name="search_id" class="form-control bg-light text-dark px-2 py-1 rounded" placeholder="Style ID / User ID" value="<?php echo htmlspecialchars($_GET['search_id'] ?? ''); ?>" required style="width: 200px; background: #fff !important;">
            <button type="submit" name="search" class="btn btn-info btn-sm fw-semibold text-dark px-3">
                <i class="fa-solid fa-magnifying-glass me-1"></i> Search
            </button>
        </form>

        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-outline-light btn-sm px-3 rounded-pill fw-semibold">
                <i class="fa-solid fa-print me-1"></i> Print / PDF
            </button>
            <a href="index.php" class="btn btn-outline-light btn-sm px-3 rounded-pill fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Dashboard
            </a>
        </div>
    </div>
</div>

<div class="form-container">
    <table class="official-form">
        <colgroup>
            <col style="width: 38%;">
            <col style="width: 31%;">
            <col style="width: 31%;">
        </colgroup>

        <!-- Header -->
        <tr id="header">
            <td><img src="dist/images/2.png" alt="THREADBARE" class="header-logo-1"></td>
            <td><img src="dist/images/1.png" alt="AM LONDON" class="header-logo-2"></td>
            <td><div class="header-title-box">SAMPLE SUBMIT FORM</div></td>
        </tr>

        <!-- Form Basic Information -->
        <tr>
            <td><strong>FACTORY NAME:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['factory_name']); ?></span></td>
        </tr>
        <tr>
            <td><strong>CONTACT at AM LONDON BD OFFICE:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['contact_am_london']); ?></span></td>
        </tr>
        <tr>
            <td><strong>DATE:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['sample_date']); ?></span></td>
        </tr>
        <tr>
            <td><strong>STYLE NUMBER:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['style_id']); ?></span></td>
        </tr>
        <tr>
            <td><strong>PO NUMBER:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['po_number']); ?></span></td>
        </tr>
        <tr>
            <td><strong>SHIPMENT DATE:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['shipment_date']); ?></span></td>
        </tr>
        <tr>
            <td><strong>DESCRIPTION:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['style_description']); ?></span></td>
        </tr>
        <tr>
            <td><strong>COLOURS ORDERED:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['colours_ordered']); ?></span></td>
        </tr>

        <!-- What is Sample For Section -->
        <tr>
            <td colspan="3" class="section-title-1">
                <u>WHAT IS SAMPLE FOR - PLEASE CIRCLE ALL CORRECT?</u>
                <div class="sample-for-grid">
                    <div>1ST/INITIAL</div>
                    <div>SALES</div>
                    <div>1ST FIT</div>
                    <div>2ND FIT</div>
                    <div>PHOTOSHOOT</div>
                    <div>PRE PRODUCTION</div>
                    <div>PRE SHIP</div>
                </div>
                <div class="sample-for-values">
                    <div><?php echo checkOption($row['sample_for'], '1ST/INITIAL'); ?></div>
                    <div><?php echo checkOption($row['sample_for'], 'SALES'); ?></div>
                    <div><?php echo checkOption($row['sample_for'], '1ST FIT'); ?></div>
                    <div><?php echo checkOption($row['sample_for'], '2ND FIT'); ?></div>
                    <div><?php echo checkOption($row['sample_for'], 'PHOTOSHOOT'); ?></div>
                    <div><?php echo checkOption($row['sample_for'], 'PRE PRODUCTION'); ?></div>
                    <div><?php echo checkOption($row['sample_for'], 'PRE SHIP'); ?></div>
                </div>
            </td>
        </tr>

        <!-- Quantity, Size, Colours -->
        <tr>
            <td><strong>HOW MANY SAMPLES REQUESTED/ HOW MANY SENT:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['samples_requested']); ?></span></td>
        </tr>
        <tr>
            <td><strong>SAMPLE SIZES:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['sample_size']); ?></span></td>
        </tr>
        <tr>
            <td><strong>SAMPLE COLOURS:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['color']); ?></span></td>
        </tr>

        <!-- Checklist Title -->
        <tr>
            <td colspan="3" class="section-title border-top-thick border-bottom-thick">
                <u>THIS SAMPLE IS CORRECT FOR - PLEASE CIRCLE IF AVAILABLE OR CORRECT</u>
            </td>
        </tr>

        <!-- Checklist Grid -->
        <?php 
        $checklist = [
            'MAIN FABRIC:' => ['main_fabric', 'CORRECT', 'ACTUAL'],
            'ANY CONTRAST FABRIC:' => ['any_contrast_fabric', 'CORRECT', 'ACTUAL'],
            'MAIN COLOUR:' => ['main_colour', 'CORRECT', 'ACTUAL'],
            'ANY CONTRAST COLOURS:' => ['any_contrast_colours', 'CORRECT', 'ACTUAL'],
            'TRIMS:' => ['trims', 'CORRECT', 'ACTUAL'],
            'MAIN PRINT:' => ['main_print', 'CORRECT', 'N/A'],
            'ANY CONTRAST PRINT:' => ['any_contrast_print', 'CORRECT', 'N/A'],
            'EMBROIDERY:' => ['embroidery', 'CORRECT', 'N/A'],
            'BACK NECK/BRAND LABELS:' => ['back_neck_labels', 'CORRECT', 'ACTUAL'],
            'SIZE TAB:' => ['size_tab', 'CORRECT', 'ACTUAL'],
            'CARE LABEL:' => ['care_label', 'CORRECT', 'ACTUAL'],
            'SWING TICKET:' => ['swing_ticket', 'CORRECT', 'ACTUAL'],
            'BARCODE LABELS & STICKER:' => ['barcode_labels', 'CORRECT', 'ACTUAL'],
            'HANGER & HANGER STICKER:' => ['hanger_sticker', 'CORRECT', 'ACTUAL'],
            'PACKAGING:' => ['packaging', 'CORRECT', 'ACTUAL'],
            'FIT:' => ['fit', 'CORRECT', 'ACTUAL']
        ];

        foreach($checklist as $label => $info): 
            $dbVal = strtoupper(trim((string)($row[$info[0]] ?? '')));
            $opt1 = $info[1];
            $opt2 = $info[2];
        ?>
        <tr>
            <td><strong><?php echo $label; ?></strong></td>
            <td colspan="2">
                <span class="<?php echo ($dbVal === $opt1) ? 'fw-bold' : ''; ?>"><?php echo $opt1; ?></span>
                &nbsp; / &nbsp;
                <span class="<?php echo ($dbVal === $opt2) ? 'fw-bold' : ''; ?>"><?php echo $opt2; ?></span>
            </td>
        </tr>
        <?php endforeach; ?>

        <!-- Trims Detail Row -->
        <tr>
            <td colspan="3" class="border-top-thick" style="vertical-align: top; padding-top: 8px;">
                <strong>TRIMS - ANY TRIMS THAT ARE AVAILABLE & NOT CORRECT PLEASE LIST THEM HERE:</strong>
                <div class="data-display mt-1" style="font-weight: normal; margin-bottom: 6px;"><?php echo nl2br(htmlspecialchars($row['trims_specify'])); ?></div>
            </td>
        </tr>

        <!-- Specification Rows -->
        <tr>
            <td><strong>IF FABRIC IS CORRECT - PLEASE CONFIRM FABRIC COMPOSITION:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['fabric_composition']); ?></span></td>
        </tr>
        <tr>
            <td><strong>IF FABRIC IS CORRECT - PLEASE CONFIRM WEIGHT/SPEC:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['weight_spec']); ?></span></td>
        </tr>
        <tr>
            <td><strong>HAVE TEST REPORTS BEEN SENT - IF NOT WHEN CAN WE RECEIVE:</strong></td>
            <td colspan="2"><span class="data-display"><?php echo htmlspecialchars($row['test_reports']); ?></span></td>
        </tr>

        <!-- Comments Section -->
        <tr>
            <td colspan="3" class="comments-td border-top-thick">
                <strong>COMMENTS - PLEASE ADVISE ANY PROBLEMS WITH THIS SAMPLE HERE:</strong>
                <div class="data-display mt-1" style="font-weight: normal; margin-bottom: 8px;"><?php echo nl2br(htmlspecialchars($row['comments'])); ?></div>
            </td>
        </tr>

        <!-- Footer Note Row -->
        <tr>
            <td colspan="3" style="font-size: 7.5px; font-weight: bold; background-color: #fff; padding: 6px 4px;" class="border-top-thick">
                PLEASE CONFIRM THAT THE SIZE SPEC AND YOUR SAMPLE MEASUREMENTS ARE ATTACHED TO EACH SAMPLE:
            </td>
        </tr>
    </table>
</div>

</body>
</html>