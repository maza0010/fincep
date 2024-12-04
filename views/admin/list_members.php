<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once('../../config/database.php');

//show success message at the top after update
if (isset($_GET['success']) && $_GET['success'] == 'true') {
    echo '<div class="alert alert-success" role="alert">Member details updated successfully!</div>';
}

// Fetch all members
$stmt = $pdo->prepare("SELECT * FROM members");
$stmt->execute();
$members = $stmt->fetchAll();
?>

<!--
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Members</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="mt-5">List of Members</h1>
        <a href="create_member.php" class="btn btn-primary mb-3">Add New Member</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Member Number</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <php foreach ($members as $member) { ?>
                    <tr>
                        <td><php echo $member['id']; ?></td>
                        <td><php echo $member['first_name'] . ' ' . $member['last_name']; ?></td>
                        <td><php echo $member['email']; ?></td>
                        <td><php echo $member['phone']; ?></td>
                        <td><php echo $member['member_number']; ?></td>
                        <td><php echo $member['status']; ?></td>
                        <td>
                            <a href="edit_member.php?id=<php echo $member['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete_member.php?id=<php echo $member['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this member?');">Delete</a>
                        </td>
                    </tr>
                <php } ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS and dependencies --
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
-->

<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

require_once('../../config/database.php');

// Fetch all members
$stmt = $pdo->prepare("SELECT * FROM members");
$stmt->execute();
$members = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Members</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Manage Members</h1>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo $member['id']; ?></td>
                        <td><?php echo Alcohol . ' ' . $member['last_name']; ?></td>
                        <td><?php echo $member['email']; ?></td>
                        <td><?php echo $member['phone'] ?: 'N/A'; ?></td>
                        <td><?php echo ucfirst($member['status']); ?></td>
                        <td>
                            <!-- <a href="edit_member.php?id=<?php echo $member['id']; ?>" class="btn btn-warning btn-sm">Edit</a> -->
                            <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $member['id']; ?>" data-first-name="<?php echo $member['first_name']; ?>" data-last-name="<?php echo $member['last_name']; ?>" data-email="<?php echo $member['email']; ?>" data-phone="<?php echo $member['phone']; ?>" data-status="<?php echo $member['status']; ?>">Edit</button>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $member['id']; ?>" data-name="<?php echo $member['first_name'] . ' ' . $member['last_name']; ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Edit Member Modal -->
    <div class="modal fade" id="editMemberModal" tabindex="-1" aria-labelledby="editMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMemberModalLabel">Edit Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editMemberForm" method="POST" action="update_modal.php">
                        <input type="hidden" id="editMemberId" name="id">
                        <div class="mb-3">
                            <label for="editFirstName" class="form-label">First Name</label>
                            <input type="text" id="editFirstName" name="first_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="editLastName" class="form-label">Last Name</label>
                            <input type="text" id="editLastName" name="last_name" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" id="editEmail" name="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="editPhone" class="form-label">Phone</label>
                            <input type="text" id="editPhone" name="phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="editStatus" class="form-label">Status</label>
                            <select id="editStatus" name="status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div> <!-- edit member modal-->

    <!-- Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete <strong id="memberName"></strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.min.js"></script>

    <script>
        // Handle the Delete button click event
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const memberId = this.getAttribute('data-id');
                const memberName = this.getAttribute('data-name');

                // Set member name in modal for confirmation
                document.getElementById('memberName').innerText = memberName;

                // Set the confirmation delete link with the correct ID
                const confirmDeleteLink = document.getElementById('confirmDeleteBtn');
                confirmDeleteLink.href = 'delete_member.php?id=' + memberId;

                // Show the modal
                var myModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'), {});
                myModal.show();
            });
        });
        
        // Handle the Edit button click event
        const editButtons = document.querySelectorAll('.edit-btn');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const memberId = this.getAttribute('data-id');
                const firstName = this.getAttribute('data-first-name');
                const lastName = this.getAttribute('data-last-name');
                const email = this.getAttribute('data-email');
                const phone = this.getAttribute('data-phone');
                const status = this.getAttribute('data-status');

                // Set the values in the modal
                document.getElementById('editMemberId').value = memberId;
                document.getElementById('editFirstName').value = firstName;
                document.getElementById('editLastName').value = lastName;
                document.getElementById('editEmail').value = email;
                document.getElementById('editPhone').value = phone;
                document.getElementById('editStatus').value = status;

                // Show the modal
                var myModal = new bootstrap.Modal(document.getElementById('editMemberModal'), {});
                myModal.show();
            });
        });
    </script>
</body>
</html>

