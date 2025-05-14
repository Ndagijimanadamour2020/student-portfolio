<div class="sidebar bg-dark text-white p-3" style="width:250px;">
    <h4 class="text-center mb-4">Admin Panel</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="./dashboard.php" class="nav-link text-white">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
        </li>

        <li class="nav-item"><a href="../departments/departments.php" class="nav-link text-white"><i class="fas fa-building me-2"></i>Departments</a></li>
        <li class="nav-item"><a href="../courses/courses.php" class="nav-link text-white"><i class="fas fa-book me-2"></i>Courses</a></li>
        <li class="nav-item"><a href="../assignments/assign_courses.php" class="nav-link text-white"><i class="fas fa-link me-2"></i>Assign Course</a></li>

        <!-- Grades Dropdown -->
        <li class="nav-item">
            <a class="nav-link text-white" data-bs-toggle="collapse" href="#gradesDropdown" role="button" aria-expanded="false" aria-controls="gradesDropdown">
                <i class="fas fa-chart-line me-2"></i>Grades <i class="fas fa-caret-down float-end"></i>
            </a>
            <div class="collapse" id="gradesDropdown">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item"><a href="../grades/input_grades.php" class="nav-link text-white">Enter Grades</a></li>
                    <li class="nav-item"><a href="../grades/edit_delete_grades.php" class="nav-link text-white">Manage Grades</a></li>
                    <li class="nav-item"><a href="../grades/generate_reports.php" class="nav-link text-white">Generate Reports</a></li>
                    <li class="nav-item"><a href="../grades/grade_analytics.php" class="nav-link text-white">Grade Analytics</a></li>
                </ul>
            </div>
        </li>

        <li class="nav-item"><a href="../students/students.php" class="nav-link text-white"><i class="fas fa-user-graduate me-2"></i>Students</a></li>
        <li class="nav-item"><a href="../parents/parents.php" class="nav-link text-white"><i class="fas fa-users me-2"></i>Parents</a></li>
        <li class="nav-item"><a href="../teachers/teachers.php" class="nav-link text-white"><i class="fas fa-chalkboard-teacher me-2"></i>Teachers</a></li>
        <li class="nav-item"><a href="../announcements/announcements.php" class="nav-link text-white"><i class="fas fa-bullhorn me-2"></i>Announcements</a></li>
        <li class="nav-item"><a href="../terms/terms.php" class="nav-link text-white"><i class="fas fa-calendar-alt me-2"></i>Academic Terms</a></li>
        <li class="nav-item"><a href="../class_groups/class_groups.php" class="nav-link text-white"><i class="fas fa-layer-group me-2"></i>Class Groups</a></li>
        <li class="nav-item"><a href="../users/manage_users.php" class="nav-link text-white"><i class="fas fa-users-cog me-2"></i>Users</a></li>

        <!-- Portfolio Management -->
        <li class="nav-item">
            <a href="../portfolio/manage_portfolio.php" class="nav-link text-white">
                <i class="fas fa-briefcase me-2"></i>Manage Portfolios
            </a>
        </li>
        <!-- announcement Management -->
        <li class="nav-item">
            <a href="../announcements/post_announcement.php" class="nav-link text-white">
                <i class="fas fa-briefcase me-2"></i>Manage Announcement
            </a>
        </li>

        <!-- Reports Dropdown -->
        <li class="nav-item">
            <a class="nav-link text-white" data-bs-toggle="collapse" href="#reportsDropdown" role="button" aria-expanded="false" aria-controls="reportsDropdown">
                <i class="fas fa-file-alt me-2"></i>Reports <i class="fas fa-caret-down float-end"></i>
            </a>
            <div class="collapse" id="reportsDropdown">
                <ul class="nav flex-column ms-3">
                    <li class="nav-item"><a href="../reports/term1_reports.php" class="nav-link text-white">Term 1 Report</a></li>
                    <li class="nav-item"><a href="../reports/term2_reports.php" class="nav-link text-white">Term 2 Report</a></li>
                    <li class="nav-item"><a href="../reports/term3_reports.php" class="nav-link text-white">Term 3 Report</a></li>
                </ul>
            </div>
        </li>

        <li class="nav-item mt-3">
            <a href="logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </li>
    </ul>
</div>
