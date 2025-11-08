<?php
session_start();
require_once(__DIR__ . "/backend/db.php");

// Debugging enable
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['email']) || ($_SESSION['role'] !== 'user' && $_SESSION['role'] !== 'company' && $_SESSION['role'] !== 'admin')) {
    header("Location: templates/user_login.html");
    exit();
}

// Get user info
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? ($_SESSION['username'] ?? 'User');
$safeUser = preg_replace('/[^a-zA-Z0-9_-]+/','_', $user_name);
$cvFile = "/uploads/" . $safeUser . ".pdf";

// Fetch job suggestions - get all available jobs for now
$job_suggestions = [];
$stmt = $conn->prepare("SELECT id, title, company_id FROM jobs WHERE status='open' LIMIT 5");
if($stmt){
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $job_suggestions[] = $row;
    }
    $stmt->close();
}

foreach ($job_suggestions as &$job) {
    $stmt = $conn->prepare("SELECT company_name FROM companies WHERE id=?");
    if($stmt){
        $stmt->bind_param("i", $job['company_id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $company = $res->fetch_assoc();
        $job['company_name'] = $company['company_name'] ?? 'Unknown';
        $stmt->close();
    }
}
$conn->close();

// Load HTML template
$template = file_get_contents(__DIR__ . "/templates/user_dashboard.html");

// Replace placeholders in template
$template = str_replace("{{user_name}}", htmlspecialchars($user_name), $template);

// CV section for overview
if (file_exists(__DIR__ . $cvFile)) {
    $cvSection = "<p>Your professional CV is ready!</p>" .
                 "<iframe src='backend/download_cv.php?inline=1&file=uploads/" . $safeUser . ".pdf'></iframe>" .
                 "<div style='margin-top: 1rem;'>" .
                 "<a href='backend/download_cv.php?file=uploads/" . $safeUser . ".pdf' download class='btn btn-primary'><i class='fas fa-download'></i> Download CV</a>" .
                 " <button onclick=\"loadPage('cvbuilder')\" class='btn btn-success' style='margin-left: 1rem;'><i class='fas fa-edit'></i> Update CV</button>" .
                 "</div>";
} else {
    $cvSection = "<p>You haven't created a CV yet. Let's build your professional profile!</p>" .
                 "<button onclick=\"loadPage('cvbuilder')\" class='btn btn-success'><i class='fas fa-plus'></i> Create My CV</button>";
}
$template = str_replace("{{cv_section}}", $cvSection, $template);

// Job suggestions
if(count($job_suggestions) > 0){
    $jobsHtml = "<ul class='list-group'>";
    foreach($job_suggestions as $job){
        $jobsHtml .= "<li class='list-group-item'><strong>" 
            . htmlspecialchars($job['title']) . "</strong> at " 
            . htmlspecialchars($job['company_name']) . "</li>";
    }
    $jobsHtml .= "</ul>";
} else {
    $jobsHtml = "<p class='text-muted'>No job suggestions right now.</p>";
}
$template = str_replace("{{job_suggestions}}", $jobsHtml, $template);

// Output the final HTML
echo $template;
