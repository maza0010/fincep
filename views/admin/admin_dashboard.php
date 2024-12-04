<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<!-- admin_dashboard.php -->

<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once('../../config/database.php');

// Fetch pending approvals
$stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE status = 'pending'");
$stmt->execute();
$pendingApprovals = $stmt->fetchColumn();

// Fetch new member registrations for the last 6 months
$stmt = $pdo->prepare("SELECT MONTH(created_at) AS month, COUNT(id) AS new_members
                       FROM members
                       WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                       GROUP BY MONTH(created_at)
                       ORDER BY MONTH(created_at)");
$stmt->execute();
$newMembers = $stmt->fetchAll();

// Prepare data for the chart
$months = [];
$newMemberCounts = [];
foreach ($newMembers as $member) {
    $months[] = date('M', mktime(0, 0, 0, $member['month'], 10)); // Format month as abbreviated name
    $newMemberCounts[] = $member['new_members'];
}


// Fetch total members
$stmt = $pdo->prepare("SELECT COUNT(*) FROM members");
$stmt->execute();
$totalMembers = $stmt->fetchColumn();

// Fetch total donations (sum of all donations)
$stmt = $pdo->prepare("SELECT SUM(total) FROM donations");
$stmt->execute();
$totalDonations = $stmt->fetchColumn();

// Fetch donation breakdown by type
$stmt = $pdo->prepare("SELECT type, SUM(total) AS total_amount
                       FROM donations
                       GROUP BY type");
$stmt->execute();
$donationBreakdown = $stmt->fetchAll();

// Prepare data for the pie chart
$donationTypes = [];
$donationAmounts = [];
foreach ($donationBreakdown as $donation) {
    $donationTypes[] = $donation['type'];
    $donationAmounts[] = $donation['total_amount'];
}

foreach ($donationBreakdown as $data) {
    $donationTypes[] = ucfirst($data['type']); // Capitalize first letter
    $donationAmounts[] = $data['total_amount'];
}

// Fetch top donors
$stmt = $pdo->prepare("SELECT first_name, last_name, SUM(total) AS total_donations
                       FROM donations
                       JOIN members ON donations.member_id = members.id
                       GROUP BY donations.member_id
                       ORDER BY total_donations DESC
                       LIMIT 5");
$stmt->execute();
$topDonors = $stmt->fetchAll();

// Fetch recent donations
$stmt = $pdo->prepare("SELECT first_name, last_name, total, type, donations.created_at AS donation_date
                       FROM donations
                       JOIN members ON donations.member_id = members.id
                       ORDER BY donations.created_at DESC
                       LIMIT 5");
$stmt->execute();
$recentDonations = $stmt->fetchAll();


// Fetch active members count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE status = 'active'");
$stmt->execute();
$activeMembers = $stmt->fetchColumn();

// Fetch recent activity (e.g., recent members added)
$stmt = $pdo->prepare("SELECT * FROM members ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recentMembers = $stmt->fetchAll();

// Fetch monthly donation data for the last 6 months
$stmt = $pdo->prepare("SELECT MONTH(created_at) AS month, SUM(total) AS donation_total
                        FROM donations
                        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                        GROUP BY MONTH(created_at)
                        ORDER BY MONTH(created_at)");
$stmt->execute();
$donations = $stmt->fetchAll();

// Prepare data for the chart
$months = [];
$donationAmounts = [];
foreach ($donations as $donation) {
    $months[] = date('M', mktime(0, 0, 0, $donation['month'], 10)); // Format month as abbreviated name
    $donationAmounts[] = $donation['donation_total'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />

<!-- jQuery (needed for Bootstrap's JavaScript plugins) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

<!-- Bootstrap JS (including popper.js for modals) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container mt-5">
        <?php include('header.php'); ?>
        <h1>CEPOG Finance Dashboard</h1>
        <!-- Send Message Button (Trigger the Modal) -->
<button type="button" class="btn btn-success mb-3" data-toggle="modal" data-target="#sendMessageModal">
    Send Message to Member
</button>

<!-- Modal for Sending Message -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" role="dialog" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendMessageModalLabel">Send Message to Member</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="send_message.php" method="POST">
                    <div class="form-group">
                        <label for="member_id">Select Member</label>
                        <select name="member_id" id="member_id" class="form-control">
                            <?php
                            // Fetch all members from the database
                            $stmt = $pdo->query("SELECT id, first_name, last_name FROM members WHERE status = 'active'");
                            while ($row = $stmt->fetch()) {
                                echo "<option value='{$row['id']}'>{$row['first_name']} {$row['last_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" class="form-control" rows="4" placeholder="Enter your message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>

        <!-- Show Pending Approvals Notification -->
        <?php if ($pendingApprovals > 0): ?>
            <div class="alert alert-warning" role="alert">
                You have <?php echo $pendingApprovals; ?> pending member approvals.
            </div>
        <?php endif; ?>

        <!-- Overview Stats Row -->
        <div class="row">
            <!-- Total Members -->
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Total Members</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $totalMembers; ?></h5>
                    </div>
                </div>
            </div>

            <!-- Total Donations -->
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Total Donations</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo number_format($totalDonations, 2); ?> CAD``</h5>
                    </div>
                </div>
            </div>
            
            <?php 
            // Pagination variables for members
// Fetch the current page number from the URL, default to 1 if not set
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 1;  // Define the number of items per page
$offset = ($page - 1) * $limit;  // Calculate the offset

// Query to fetch members with pagination
$sql = "SELECT * FROM members ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);  // Bind the limit as integer
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);  // Bind the offset as integer
$stmt->execute();
$members = $stmt->fetchAll();
?>
            <!-- Search Form -->
<form method="GET" action="admin_dashboard.php">
    <div class="form-group">
        <input type="text" name="search" class="form-control" placeholder="Search members" value="<?php echo $search; ?>">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
</form>

<!-- Members Table -->
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>#</th>
            <th>Member Number</th>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($members as $member): ?>
            <tr>
                <td><?php echo $member['id']; ?></td>
                <td><?php echo $member['member_number']; ?></td>
                <td><?php echo $member['first_name'] . ' ' . $member['last_name']; ?></td>
                <td><?php echo $member['email']; ?></td>
                <td><?php echo $member['status']; ?></td>
                <td>
                    <!-- Action buttons for editing and deleting -->
                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editMemberModal<?php echo $member['id']; ?>">Edit</button>
                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteMemberModal<?php echo $member['id']; ?>">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Pagination -->
<?php
// Get total members count for pagination
$stmt = $pdo->prepare("SELECT COUNT(id) FROM members WHERE first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR member_number LIKE :search");
$stmt->execute(['search' => "%$search%"]);
$totalMembers = $stmt->fetchColumn();
$totalPages = ceil($totalMembers / $limit);
?>
<div class="pagination">
    <ul class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item<?php echo $i === $page ? ' active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</div
            
            <h3>Top Donors</h3>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Name</th>
            <th>Total Donations</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($topDonors as $donor): ?>
            <tr>
                <td><?php echo $donor['first_name'] . ' ' . $donor['last_name']; ?></td>
                <td><?php echo number_format($donor['total_donations'], 2); ?> USD</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

            <h3>Recent Donations</h3>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Donor</th>
            <th>Amount</th>
            <th>Type</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($recentDonations as $donation): ?>
            <tr>
                <td><?php echo $donation['first_name'] . ' ' . $donation['last_name']; ?></td>
                <td><?php echo number_format($donation['total'], 2); ?> USD</td>
                <td><?php echo ucfirst($donation['type']); ?></td>
                <td><?php echo date('Y-m-d H:i', strtotime($donation['donation_date'])); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


            
            <!-- Active Members -->
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Active Members</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $activeMembers; ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Table -->
        <h3>Recent Members</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentMembers as $member): ?>
                    <tr>
                        <td><?php echo $member['id']; ?></td>
                        <td><?php echo $member['first_name'] . ' ' . $member['last_name']; ?></td>
                        <td><?php echo $member['email']; ?></td>
                        <td><?php echo $member['created_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Donation Trends Graph -->
        <h3>Donation Trends (Last 6 Months)</h3>
        <canvas id="donationChart" width="150" height="50"></canvas>
        
        <!-- Donation Breakdown by Type -->
<h3>Donation Breakdown by Type</h3>
<canvas id="donationBreakdownChart" width="100" height="100"></canvas>

<script>
    // Donation Breakdown Chart Data
    const ctx3 = document.getElementById('donationBreakdownChart').getContext('2d');
    const donationBreakdownChart = new Chart(ctx3, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($donationTypes); ?>, // donation types data
            datasets: [{
                label: 'Donation Amount',
                data: <?php echo json_encode($donationAmounts); ?>, // donation amounts data
                backgroundColor: ['rgb(255, 99, 132)', 'rgb(75, 192, 10)', 'rgb(153, 102, 255)', 'rgb(25, 159, 164)']
                
            }]
        },
        options: {
            responsive: true,
        }
    });
</script>


        <script>
            // Donation Chart Data
            const ctx = document.getElementById('donationChart').getContext('2d');
            const donationChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($months); ?>, // months data
                    datasets: [{
                        label: 'Total Donations (CAD)',
                        data: <?php echo json_encode($donationAmounts); ?>, // donation amounts data
                        borderColor: 'rgb(75, 192, 192)',
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Months'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Amount (USD)'
                            }
                        }
                    }
                }
            });
        </script>
        
        <!-- New Member Registrations Trend -->
<h3>New Member Registrations (Last 6 Months)</h3>
<canvas id="memberGrowthChart" width="150" height="50"></canvas>

<script>
    // Member Growth Chart Data
    const ctx2 = document.getElementById('memberGrowthChart').getContext('2d');
    const memberGrowthChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>, // months data
            datasets: [{
                label: 'New Members',
                data: <?php echo json_encode($newMemberCounts); ?>, // new member counts data
                borderColor: 'rgb(255, 99, 132)',
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Months'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Number of New Members'
                    }
                }
            }
        }
    });
</script>

    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
