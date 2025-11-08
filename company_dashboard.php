<?php
session_start();
require_once(__DIR__ . "/backend/db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "company") {
    header("Location: templates/user_login.html");
    exit();
}

if (!isset($_SESSION['company_id'])) {
    header("Location: templates/user_login.html");
    exit();
}

$company_id = intval($_SESSION['company_id']);
$company_name = $_SESSION['company_name'] ?? "Company";

function fetch_stat($conn,$query,$company_id){
    $stmt=$conn->prepare($query);
    $stmt->bind_param("i",$company_id);
    $stmt->execute();
    $data=$stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $data ? array_values($data)[0] : 0;
}

$total_jobs = fetch_stat($conn,"SELECT COUNT(*) FROM jobs WHERE company_id=?",$company_id);
$total_applicants = fetch_stat($conn,"SELECT COUNT(*) FROM applications a INNER JOIN jobs j ON a.job_id=j.id WHERE j.company_id=?",$company_id);
$cv_downloads = fetch_stat($conn,"SELECT COUNT(*) FROM cv_downloads cd INNER JOIN jobs j ON cd.job_id=j.id WHERE j.company_id=?",$company_id);
$open_positions = fetch_stat($conn,"SELECT COUNT(*) FROM jobs WHERE company_id=? AND status='open'",$company_id);

$stmt = $conn->prepare("SELECT id AS job_id, title, created_at, status, (SELECT COUNT(*) FROM applications WHERE job_id=jobs.id) AS applicants FROM jobs WHERE company_id=? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i",$company_id);
$stmt->execute();
$recent_jobs_result=$stmt->get_result();
$stmt->close();
$conn->close();

$recent_jobs_html='';
while($job=$recent_jobs_result->fetch_assoc()){
    $recent_jobs_html.="<tr>
        <td>{$job['job_id']}</td>
        <td>".htmlspecialchars($job['title'])."</td>
        <td>{$job['applicants']}</td>
        <td>".ucfirst($job['status'])."</td>
        <td>".date("Y-m-d",strtotime($job['created_at']))."</td>
    </tr>";
}

$template=file_get_contents(__DIR__."/templates/company_dashboard.html");
$template=str_replace('{{COMPANY_NAME}}',htmlspecialchars($company_name),$template);
$template=str_replace('{{TOTAL_JOBS}}',$total_jobs,$template);
$template=str_replace('{{TOTAL_APPLICANTS}}',$total_applicants,$template);
$template=str_replace('{{CV_DOWNLOADS}}',$cv_downloads,$template);
$template=str_replace('{{OPEN_POSITIONS}}',$open_positions,$template);
$template=str_replace('{{RECENT_JOBS}}',$recent_jobs_html,$template);

echo $template;
