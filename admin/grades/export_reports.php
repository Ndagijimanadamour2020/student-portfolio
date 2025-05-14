<?php
require_once '../config/database.php';
require_once '../vendor/autoload.php'; // Make sure Dompdf and PhpSpreadsheet are installed

use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_GET['term']) || !isset($_GET['type'])) {
    die("Missing parameters.");
}

$termId = $_GET['term'];
$type = $_GET['type'];

$stmt = $pdo->prepare("
    SELECT students.id AS student_id, students.first_name, students.last_name, courses.course_name, grades.grade
    FROM grades
    JOIN students ON grades.student_id = students.id
    JOIN courses ON grades.course_id = courses.id
    WHERE grades.term_id = ?
    ORDER BY students.first_name, courses.course_name
");
$stmt->execute([$termId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    die("No data found for export.");
}

// Structure data for export
$reportData = [];
foreach ($rows as $row) {
    $fullName = $row['first_name'] . ' ' . $row['last_name'];
    $reportData[$row['student_id']]['name'] = $fullName;
    $reportData[$row['student_id']]['grades'][] = [
        'course' => $row['course_name'],
        'grade' => $row['grade']
    ];
}

// Export to PDF
if ($type === 'pdf') {
    $html = "<h2>Student Grades Report (Term ID: {$termId})</h2>";
    foreach ($reportData as $student) {
        $html .= "<h4>{$student['name']}</h4><table border='1' width='100%' cellpadding='5'>
            <tr><th>Course</th><th>Grade</th></tr>";
        $total = 0;
        $count = 0;
        foreach ($student['grades'] as $g) {
            $html .= "<tr><td>{$g['course']}</td><td>{$g['grade']}</td></tr>";
            $total += $g['grade'];
            $count++;
        }
        $avg = $count ? round($total / $count, 2) : 0;
        $html .= "<tr><td><strong>Average</strong></td><td><strong>{$avg}</strong></td></tr>";
        $html .= "</table><br>";
    }

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream("grades_report_term_{$termId}.pdf");
    exit;

} elseif ($type === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $rowNum = 1;
    foreach ($reportData as $student) {
        $sheet->setCellValue("A{$rowNum}", "Student: {$student['name']}");
        $rowNum++;
        $sheet->setCellValue("A{$rowNum}", "Course");
        $sheet->setCellValue("B{$rowNum}", "Grade");
        $rowNum++;

        $total = 0;
        $count = 0;
        foreach ($student['grades'] as $g) {
            $sheet->setCellValue("A{$rowNum}", $g['course']);
            $sheet->setCellValue("B{$rowNum}", $g['grade']);
            $total += $g['grade'];
            $count++;
            $rowNum++;
        }

        $avg = $count ? round($total / $count, 2) : 0;
        $sheet->setCellValue("A{$rowNum}", "Average");
        $sheet->setCellValue("B{$rowNum}", $avg);
        $rowNum += 2;
    }

    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment;filename=\"grades_report_term_{$termId}.xlsx\"");
    header("Cache-Control: max-age=0");

    $writer = new Xlsx($spreadsheet);
    $writer->save("php://output");
    exit;

} else {
    die("Invalid export type.");
}
