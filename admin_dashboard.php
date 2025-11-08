<?php
// admin_dashboard.php
session_start();

require_once(__DIR__ . "/backend/db.php");

// Require admin session: accept either dedicated flag or role=admin
if (!((isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'))) {
    header("Location: backend/admin_login.php");
    exit;
}

// Aggregate counts using existing schema
$fetchCount = function(mysqli $conn, string $sql): int {
    $res = $conn->query($sql);
    if ($res && ($row = $res->fetch_assoc())) {
        return (int)array_values($row)[0];
    }
    return 0;
};

$total_users = $fetchCount($conn, "SELECT COUNT(*) FROM users");
$total_jobs = $fetchCount($conn, "SELECT COUNT(*) FROM jobs");
$total_companies = $fetchCount($conn, "SELECT COUNT(*) FROM companies");
$cv_downloads = $fetchCount($conn, "SELECT COUNT(*) FROM cv_downloads");

// Load template and inject runtime counts into placeholders
$html = file_get_contents(__DIR__ . "/templates/admin_dashboard.html");
if ($html !== false) {
    $inject = "<script>\n" .
              "document.addEventListener('DOMContentLoaded',function(){\n" .
              "  var u=document.getElementById('totalUsers'); if(u) u.textContent='" . $total_users . "';\n" .
              "  var j=document.getElementById('totalJobs'); if(j) j.textContent='" . $total_jobs . "';\n" .
              "  var c=document.getElementById('totalCompanies'); if(c) c.textContent='" . $total_companies . "';\n" .
              "  var d=document.getElementById('cvDownloads'); if(d) d.textContent='" . $cv_downloads . "';\n" .
              "});\n" .
              "</script>";
    // append injection before closing body if present
    if (strpos($html, '</body>') !== false) {
        $html = str_replace('</body>', $inject . '</body>', $html);
    } else {
        $html .= $inject;
    }
    echo $html;
} else {
    echo "Failed to load admin dashboard template.";
}

// keep connection for potential further use or close
$conn->close();
?>