<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bug Tracker - Bug Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            width: 95%;
            max-width: 1200px;
            margin: 2rem auto;
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1, h2, h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        h1 {
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
        }

        section {
            margin-bottom: 2rem;
            padding: 1rem;
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .button {
            display: inline-block;
            background-color: #3498db;
            color: #fff;
            padding: 0.5rem 1rem;
            margin: 0.5rem 0;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .button:hover {
            background-color: #2980b9;
        }

        .logout-button {
            background-color: #e74c3c;
        }

        .logout-button:hover {
            background-color: #c0392b;
        }

        .add-button {
            background-color: #2ecc71;
        }

        .add-button:hover {
            background-color: #27ae60;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            transition: color 0.3s;
        }

        .close:hover,
        .close:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }

        .modal h2 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }

        .modal form {
            display: grid;
            gap: 15px;
        }

        .modal label {
            font-weight: bold;
            color: #34495e;
        }

        .modal input[type="text"],
        .modal input[type="password"],
        .modal select,
        .modal textarea {
            width: 97%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .modal textarea {
            resize: vertical;
            min-height: 100px;
        }

        .disabled-field {
            background-color: #e9ecef;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .modal textarea {
            resize: vertical;
            min-height: 100px;
            width: 97%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .modal button[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .modal button[type="submit"]:hover {
            background-color: #2980b9;
        }

        #session-expiration-banner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background-color: #f8d7da;
            color: #721c24;
            text-align: center;
            padding: 10px;
            z-index: 1000;
        }

        select:disabled {
            background-color: #e9ecef;
            cursor: not-allowed;
            opacity: 0.6;
        }

        @media (max-width: 768px) {
            .container {
                width: 100%;
                padding: 1rem;
            }

            table {
                font-size: 0.9rem;
            }

            th, td {
                padding: 0.5rem;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
</head>
<body>
    <div id="session-expiration-banner">
        Your session will expire in <span id="session-countdown"></span>
    </div>
    <div class="container">
        <h1>Bug Management - Welcome, <?php echo htmlspecialchars($WelcomeUser->Name); ?>!</h1>

        <section id="bug-management">
        <h2>Bugs in My Project</h2>
            <h3>All Bugs For <?php echo htmlspecialchars($projectsById[$user->ProjectId]->Project); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Summary</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allBugs)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">There are no bugs in this project at the moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($allBugs as $bug): ?>
                        <tr>
                            <td><?php echo $bug->id; ?></td>
                            <td><?php echo htmlspecialchars($bug->summary); ?></td>
                            <td><span class="bug-status" data-status-id="<?php echo $bug->statusId; ?>"><?php echo $bug->statusId; ?></span></td>
                            <td><span class="bug-priority" data-priority-id="<?php echo $bug->priorityId; ?>"><?php echo $bug->priorityId; ?></span></td>
                            <td><?php echo $bug->assignedToId ? htmlspecialchars(App\Models\User::findById($bug->assignedToId)->Name) : 'Unassigned'; ?></td>
                            <td>
                                <button onclick="viewBugDetails(<?php echo htmlspecialchars(json_encode($bug)); ?>)">View</button>
                                <?php if ($bug->ownerId === $user->Id || $bug->assignedToId === $user->Id): ?>
                                    <button onclick="openEditBugModal(<?php echo htmlspecialchars(json_encode($bug)); ?>)">Edit</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <h2>My Bugs</h2>
            <h3>All My Bugs For <?php echo htmlspecialchars($projectsById[$user->ProjectId]->Project); ?></h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Project</th>
                        <th>Summary</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bugs)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">You have no assigned bugs at the moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bugs as $bug): ?>
                        <tr>
                            <td><?php echo $bug->id; ?></td>
                            <td><?php echo isset($projectsById[$bug->projectId]) ? htmlspecialchars($projectsById[$bug->projectId]->Project) : 'Unknown Project'; ?></td>
                            <td><?php echo htmlspecialchars($bug->summary); ?></td>
                            <td><span class="bug-status" data-status-id="<?php echo $bug->statusId; ?>"><?php echo $bug->statusId; ?></span></td>
                            <td><span class="bug-priority" data-priority-id="<?php echo $bug->priorityId; ?>"><?php echo $bug->priorityId; ?></span></td>
                            <td><?php echo $bug->assignedToId ? htmlspecialchars(App\Models\User::findById($bug->assignedToId)->Name) : 'Unassigned'; ?></td>
                            <td>
                                <button onclick="viewBugDetails(<?php echo htmlspecialchars(json_encode($bug)); ?>)">View</button>
                                <button onclick="openEditBugModal(<?php echo htmlspecialchars(json_encode($bug)); ?>)">Edit</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3>Open Bugs</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Project</th>
                        <th>Summary</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($openBugs)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No open bugs at the moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($openBugs as $bug): ?>
                        <tr>
                            <td><?php echo $bug->id; ?></td>
                            <td><?php echo isset($projectsById[$bug->projectId]) ? htmlspecialchars($projectsById[$bug->projectId]->Project) : 'Unknown Project'; ?></td>
                            <td><?php echo htmlspecialchars($bug->summary); ?></td>
                            <td><span class="bug-status" data-status-id="<?php echo $bug->statusId; ?>"><?php echo $bug->statusId; ?></span></td>
                            <td><span class="bug-priority" data-priority-id="<?php echo $bug->priorityId; ?>"><?php echo $bug->priorityId; ?></span></td>
                            <td><?php echo $bug->assignedToId ? htmlspecialchars(App\Models\User::findById($bug->assignedToId)->Name) : 'Unassigned'; ?></td>
                            <td>
                                <?php if ($userRole <= 2 || $bug->assignedToId == App\Core\SessionManager::get('user_id')): ?>
                                    <button onclick="viewBugDetails(<?php echo htmlspecialchars(json_encode($bug)); ?>)">View</button>
                                    <button onclick="openEditBugModal(<?php echo htmlspecialchars(json_encode($bug)); ?>)">Edit</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <h3>Overdue Bugs</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Project</th>
                        <th>Summary</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Assigned To</th>
                        <th>Target Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($overdueBugs)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No overdue bugs at the moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($overdueBugs as $bug): ?>
                        <tr>
                            <td><?php echo $bug->id; ?></td>
                            <td><?php echo isset($projectsById[$bug->projectId]) ? htmlspecialchars($projectsById[$bug->projectId]->Project) : 'Unknown Project'; ?></td>
                            <td><?php echo htmlspecialchars($bug->summary); ?></td>
                            <td><span class="bug-status" data-status-id="<?php echo $bug->statusId; ?>"><?php echo $bug->statusId; ?></span></td>
                            <td><span class="bug-priority" data-priority-id="<?php echo $bug->priorityId; ?>"><?php echo $bug->priorityId; ?></span></td>
                            <td><?php echo $bug->assignedToId ? htmlspecialchars(App\Models\User::findById($bug->assignedToId)->Name) : 'Unassigned'; ?></td>
                            <td><?php echo $bug->targetDate; ?></td>
                            <td>
                                <?php if ($userRole <= 2 || $bug->assignedToId == App\Core\SessionManager::get('user_id')): ?>
                                    <button onclick="viewBugDetails(<?php echo htmlspecialchars(json_encode($bug)); ?>)">View</button>
                                    <button onclick="openEditBugModal(<?php echo htmlspecialchars(json_encode($bug)); ?>)">Edit</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <button onclick="openAddBugModal()">Add New Bug</button>
        </section>

        <a href="<?php echo url('logout'); ?>" class="button">Logout</a>
    </div>

    <!-- Bug Modal -->
    <div id="bugModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="bugModalTitle">Bug</h2>
            <form id="bugForm">
                <input type="hidden" id="bugId" name="bugId">
                
                <label for="bugProjectId">Project:</label>
                <select id="bugProjectId" name="bugProjectId" required disabled>
                    <option value="<?php echo $user->ProjectId; ?>"><?php echo htmlspecialchars($projectsById[$user->ProjectId]->Project); ?></option>
                </select>
                
                <label for="summary">Summary:</label>
                <input type="text" id="summary" name="summary" required>
                
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
                
                <label for="assignedToId">Assigned To:</label>
                <select id="assignedToId" name="assignedToId">
                    <option value="">Unassigned</option>
                    <option value="<?php echo $user->Id; ?>"><?php echo htmlspecialchars($user->Name); ?> (Me)</option>
                </select>
                
                <label for="statusId">Status:</label>
                <select id="statusId" name="statusId" required>
                    <option value="1">Unassigned</option>
                    <option value="2">Assigned</option>
                    <option value="3">Closed</option>
                </select>
                
                <label for="priorityId">Priority:</label>
                <select id="priorityId" name="priorityId" required>
                    <option value="1">Low</option>
                    <option value="2">Medium</option>
                    <option value="3">High</option>
                    <option value="4">Urgent</option>
                </select>
                
                <label for="targetDate">Target Date:</label>
                <input type="date" id="targetDate" name="targetDate">
                
                <label for="fixDescription">Fix Description:</label>
                <textarea 
                    id="fixDescription" 
                    name="fixDescription" 
                    class="disabled-field"
                    disabled
                    placeholder="Only available when bug is closed"
                    maxlength="1000"
                ></textarea>
                
                <button type="submit">Save</button>
            </form>
        </div>
    </div>

    <!-- Bug Details Modal -->
    <div id="bugDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Bug Details</h2>
            <div id="bugDetailsContent">
                <p><strong>ID:</strong> <span id="bugDetailId"></span></p>
                <p><strong>Project:</strong> <span id="bugDetailProject"></span></p>
                <p><strong>Summary:</strong> <span id="bugDetailSummary"></span></p>
                <p><strong>Description:</strong> <span id="bugDetailDescription"></span></p>
                <p><strong>Status:</strong> <span id="bugDetailStatus"></span></p>
                <p><strong>Priority:</strong> <span id="bugDetailPriority"></span></p>
                <p><strong>Assigned To:</strong> <span id="bugDetailAssignedTo"></span></p>
                <p><strong>Owner:</strong> <span id="bugDetailOwner"></span></p>
                <p><strong>Date Raised:</strong> <span id="bugDetailDateRaised"></span></p>
                <p><strong>Target Date:</strong> <span id="bugDetailTargetDate"></span></p>
                <p><strong>Date Closed:</strong> <span id="bugDetailDateClosed"></span></p>
                <p><strong>Fix Description:</strong> <span id="bugDetailFixDescription"></span></p>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).style.display = "block";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.className === "modal") {
                event.target.style.display = "none";
            }
        }

        // Close buttons
        var closeButtons = document.getElementsByClassName("close");
        for (var i = 0; i < closeButtons.length; i++) {
            closeButtons[i].onclick = function() {
                this.parentElement.parentElement.style.display = "none";
            }
        }

        // Bug management
        const projectNames = {};
        const userNames = {};

        // This function should be called when the page loads to populate the projectNames object
        function initializeData() {
            <?php foreach ($projects as $project): ?>
            projectNames[<?php echo $project->Id; ?>] = "<?php echo htmlspecialchars($project->Project, ENT_QUOTES, 'UTF-8'); ?>";
            <?php endforeach; ?>

            <?php foreach ($users as $user): ?>
            userNames[<?php echo $user->Id; ?>] = "<?php echo htmlspecialchars($user->Name, ENT_QUOTES, 'UTF-8'); ?>";
            <?php endforeach; ?>
        }

        const currentUserId = <?php echo json_encode(App\Core\SessionManager::get('user_id')); ?>;

        function getUserNameWithMeIndicator(userId) {
            let name = getUserName(userId);
            if (userId == currentUserId) {
                name += ' (Me)';
            }
            return name;
        }

        function updateStatusAndPriority() {
            document.querySelectorAll('.bug-status').forEach(element => {
                const statusId = element.getAttribute('data-status-id');
                element.textContent = getStatusText(statusId);
            });

            document.querySelectorAll('.bug-priority').forEach(element => {
                const priorityId = element.getAttribute('data-priority-id');
                element.textContent = getPriorityText(priorityId);
            });
        }

        // Call this function when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeData();
            updateStatusAndPriority();
        });

        function viewBugDetails(bug) {
            // Populate the modal with bug details
            document.getElementById('bugDetailId').textContent = bug.id;
            document.getElementById('bugDetailProject').textContent = getProjectName(bug.projectId);
            document.getElementById('bugDetailSummary').textContent = bug.summary;
            document.getElementById('bugDetailDescription').textContent = bug.description;
            document.getElementById('bugDetailStatus').textContent = getStatusText(bug.statusId);
            document.getElementById('bugDetailPriority').textContent = getPriorityText(bug.priorityId);
            document.getElementById('bugDetailAssignedTo').textContent = bug.assignedToId ? getUserNameWithMeIndicator(bug.assignedToId) : 'Unassigned';
            document.getElementById('bugDetailOwner').textContent = getUserNameWithMeIndicator(bug.ownerId);
            document.getElementById('bugDetailDateRaised').textContent = formatDate(bug.dateRaised);
            document.getElementById('bugDetailTargetDate').textContent = formatDate(bug.targetDate);
            document.getElementById('bugDetailDateClosed').textContent = formatDate(bug.dateClosed);

            // Open the modal
            document.getElementById('bugDetailFixDescription').textContent = bug.fixDescription || 'Not resolved yet';
            openModal('bugDetailsModal');
        }

        function getProjectName(projectId) {
            return projectNames[projectId] || `Unknown Project (ID: ${projectId})`;
        }

        function getStatusText(statusId) {
            const statuses = {
                1: 'Unassigned',
                2: 'Assigned',
                3: 'Closed'
            };
            return statuses[statusId] || 'Unknown';
        }

        function getPriorityText(priorityId) {
            const priorities = {
                1: 'Low',
                2: 'Medium',
                3: 'High',
                4: 'Urgent'
            };
            return priorities[priorityId] || 'Unknown';
        }

        function getUserName(userId) {
            return userNames[userId] || `Unknown User (ID: ${userId})`;
        }

        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString();
        }

        function openAddBugModal() {
            document.getElementById("bugModalTitle").innerText = "Add Bug";
            document.getElementById("bugForm").reset();
            document.getElementById("bugId").value = "";
            document.getElementById("bugProjectId").value = "<?php echo $user->ProjectId; ?>";
            document.getElementById("assignedToId").value = "";
            document.getElementById("fixDescription").value = "";
            
            // Initialize fix description field state
            handleFixDescriptionField("1"); // Default to Unassigned status
            
            // Ensure the project dropdown shows the correct project name
            document.getElementById("bugProjectId").selectedIndex = 0;
            openModal("bugModal");
        }

        function openEditBugModal(bug) {
            document.getElementById("bugModalTitle").innerText = "Edit Bug";
            document.getElementById("bugId").value = bug.id;
            document.getElementById("bugProjectId").value = bug.projectId;
            document.getElementById("summary").value = bug.summary;
            document.getElementById("description").value = bug.description;
            document.getElementById("assignedToId").value = bug.assignedToId || "";
            document.getElementById("statusId").value = bug.statusId;
            document.getElementById("priorityId").value = bug.priorityId;
            document.getElementById("targetDate").value = bug.targetDate || "";
            document.getElementById("fixDescription").value = bug.fixDescription || "";
            
            // Initialize fix description field state
            handleFixDescriptionField(bug.statusId.toString());
            
            openModal("bugModal");
        }
        
        function handleFixDescriptionField(statusId) {
            const fixDescriptionField = document.getElementById("fixDescription");
            if (statusId === "3") { // If status is Closed
                fixDescriptionField.disabled = false;
                fixDescriptionField.required = true;
                fixDescriptionField.classList.remove('disabled-field');
                fixDescriptionField.placeholder = "Required when closing a bug";
            } else {
                fixDescriptionField.disabled = true;
                fixDescriptionField.required = false;
                fixDescriptionField.classList.add('disabled-field');
                fixDescriptionField.placeholder = "Only available when bug is closed";
                fixDescriptionField.value = ''; // Clear the value when disabled
            }
        }

        // Form Submission
        document.getElementById("bugForm").onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            fetch('<?php echo url('bug/saveBug'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Bug saved successfully");
                    location.reload();
                } else {
                    alert("Error saving bug: " + data.message);
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                alert("An error occurred while saving the bug");
            });
        };

        // Session expiration countdown
        let sessionExpirationTime = <?php echo json_encode(App\Core\SessionManager::getSessionExpirationTime()); ?>;
        let countdownInterval;

        function updateSessionCountdown() {
            let now = Math.floor(Date.now() / 1000);
            let timeLeft = sessionExpirationTime - now;

            if (timeLeft <= 900) { // Show banner when 3 minutes or less remain
                document.getElementById('session-expiration-banner').style.display = 'block';
            }

            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                window.location.href = '/logout';
                return;
            }

            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            document.getElementById('session-countdown').textContent = 
                `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }

        function refreshSession() {
            fetch('<?php echo url('refresh-session'); ?>', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        sessionExpirationTime = data.newExpirationTime;
                        document.getElementById('session-expiration-banner').style.display = 'none';
                    }
                });
        }

        // Start countdown
        countdownInterval = setInterval(updateSessionCountdown, 1000);

        // Refresh session on user interaction
        document.addEventListener('click', refreshSession);
        document.addEventListener('keypress', refreshSession);

        let scrollTimeout;
        window.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(refreshSession, 0); // Debounce scroll events
        });

        // Add event listeners for user modal
        document.addEventListener('DOMContentLoaded', function() {
            var roleSelect = document.getElementById('roleId');
            roleSelect.addEventListener('change', updateProjectSelect);

            var userModal = document.getElementById('userModal');
            userModal.addEventListener('show', updateProjectSelect);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const assignedToSelect = document.getElementById('assignedToId');
            const statusSelect = document.getElementById('statusId');

            assignedToSelect.addEventListener('change', function() {
                if (this.value) {
                    // If a user is assigned, set status to "Assigned"
                    statusSelect.value = "2";
                } else {
                    // If unassigned, set status to "Unassigned"
                    statusSelect.value = "1";
                }
            });

            statusSelect.addEventListener('change', function() {
                if (this.value === "1") {
                    // If status is set to "Unassigned", clear the assigned user
                    assignedToSelect.value = "";
                } else if (this.value === "2" && !assignedToSelect.value) {
                    // If status is set to "Assigned" but no user is assigned, prompt to select a user
                    alert("Please assign a user to this bug.");
                    this.value = "1"; // Reset status to "Unassigned"
                }
                // Note: We don't change anything if status is set to "Closed" (3)
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const bugForm = document.getElementById('bugForm');
            const projectIdSelect = document.getElementById('bugProjectId');
            
            // Create a hidden input to store the project ID
            const hiddenProjectId = document.createElement('input');
            hiddenProjectId.type = 'hidden';
            hiddenProjectId.name = 'bugProjectId';
            hiddenProjectId.value = projectIdSelect.value;
            bugForm.appendChild(hiddenProjectId);
            
            // Update hidden input when the select changes (if it's ever re-enabled)
            projectIdSelect.addEventListener('change', function() {
                hiddenProjectId.value = this.value;
            });
            
            // Modify the form submission to include the project ID
            bugForm.onsubmit = function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                
                // Ensure the project ID is included in the form data
                formData.set('bugProjectId', hiddenProjectId.value);
                
                fetch('<?php echo url('bug/saveBug'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Bug saved successfully");
                        location.reload();
                    } else {
                        alert("Error saving bug: " + data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert("An error occurred while saving the bug");
                });
            };
        });

        // Add event listeners when the document loads
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('statusId');
            const bugForm = document.getElementById('bugForm');

            // Handle status changes
            statusSelect.addEventListener('change', function() {
                handleFixDescriptionField(this.value);
            });

            // Form submission handler
            bugForm.onsubmit = function(e) {
                e.preventDefault();
                
                // Validate fix description when status is Closed
                const status = document.getElementById('statusId').value;
                const fixDescription = document.getElementById('fixDescription').value;
                
                if (status === "3" && !fixDescription.trim()) {
                    alert("Fix description is required when closing a bug");
                    return;
                }

                var formData = new FormData(this);
                
                fetch('<?php echo url('bug/saveBug'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Bug saved successfully");
                        location.reload();
                    } else {
                        alert("Error saving bug: " + data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert("An error occurred while saving the bug");
                });
            };
        });
    </script>
</body>
</html>