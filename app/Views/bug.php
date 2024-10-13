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

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bug Management</h1>

        <section id="bug-management">
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
                    <?php foreach ($bugs as $bug): ?>
                    <tr>
                        <td><?php echo $bug->id; ?></td>
                        <td><?php echo isset($projectsById[$bug->projectId]) ? htmlspecialchars($projectsById[$bug->projectId]->Project) : 'Unknown Project'; ?></td>
                        <td><?php echo htmlspecialchars($bug->summary); ?></td>
                        <td><?php echo $bug->statusId; ?></td>
                        <td><?php echo $bug->priorityId; ?></td>
                        <td><?php echo $bug->assignedToId ? htmlspecialchars(App\Models\User::findById($bug->assignedToId)->Name) : 'Unassigned'; ?></td>
                        <td>
                            <button onclick="openEditBugModal(<?php echo htmlspecialchars(json_encode($bug)); ?>)">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
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
                    <?php foreach ($openBugs as $bug): ?>
                    <tr>
                        <td><?php echo $bug->id; ?></td>
                        <td><?php echo isset($projectsById[$bug->projectId]) ? htmlspecialchars($projectsById[$bug->projectId]->Project) : 'Unknown Project'; ?></td>
                        <td><?php echo htmlspecialchars($bug->summary); ?></td>
                        <td><?php echo $bug->statusId; ?></td>
                        <td><?php echo $bug->priorityId; ?></td>
                        <td><?php echo $bug->assignedToId ? htmlspecialchars(App\Models\User::findById($bug->assignedToId)->Name) : 'Unassigned'; ?></td>
                        <td>
                            <?php if ($userRole <= 2 || $bug->assignedToId == App\Core\SessionManager::get('user_id')): ?>
                                <button onclick="openEditBugModal(<?php echo htmlspecialchars(json_encode($bug)); ?>)">Edit</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
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
                    <?php foreach ($overdueBugs as $bug): ?>
                    <tr>
                        <td><?php echo $bug->id; ?></td>
                        <td><?php echo isset($projectsById[$bug->projectId]) ? htmlspecialchars($projectsById[$bug->projectId]->Project) : 'Unknown Project'; ?></td>
                        <td><?php echo htmlspecialchars($bug->summary); ?></td>
                        <td><?php echo $bug->statusId; ?></td>
                        <td><?php echo $bug->priorityId; ?></td>
                        <td><?php echo $bug->assignedToId ? htmlspecialchars(App\Models\User::findById($bug->assignedToId)->Name) : 'Unassigned'; ?></td>
                        <td><?php echo $bug->targetDate; ?></td>
                        <td>
                            <?php if ($userRole <= 2 || $bug->assignedToId == App\Core\SessionManager::get('user_id')): ?>
                                <button onclick="openEditBugModal(<?php echo htmlspecialchars(json_encode($bug)); ?>)">Edit</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button onclick="openAddBugModal()">Add New Bug</button>
        </section>

        <a href="/logout" class="button">Logout</a>
    </div>

    <!-- Bug Modal -->
    <div id="bugModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="bugModalTitle">Bug</h2>
            <form id="bugForm">
                <input type="hidden" id="bugId" name="bugId">
                <label for="bugProjectId">Project:</label>
                <select id="bugProjectId" name="bugProjectId" required>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project->Id; ?>"><?php echo $project->Project; ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="summary">Summary:</label>
                <input type="text" id="summary" name="summary" required>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
                <label for="assignedToId">Assigned To:</label>
                <select id="assignedToId" name="assignedToId">
                    <option value="">Unassigned</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user->Id; ?>"><?php echo $user->Name; ?></option>
                    <?php endforeach; ?>
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
                <button type="submit">Save</button>
            </form>
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
        function openAddBugModal() {
            document.getElementById("bugModalTitle").innerText = "Add Bug";
            document.getElementById("bugForm").reset();
            document.getElementById("bugId").value = "";
            openModal("bugModal");
        }

        function openEditBugModal(bug) {
            document.getElementById("bugModalTitle").innerText = "Edit Bug";
            // Populate form fields with bug data
            document.getElementById("bugId").value = bug.id;
            document.getElementById("bugProjectId").value = bug.projectId;
            document.getElementById("summary").value = bug.summary;
            document.getElementById("description").value = bug.description;
            document.getElementById("assignedToId").value = bug.assignedToId || "";
            document.getElementById("statusId").value = bug.statusId;
            document.getElementById("priorityId").value = bug.priorityId;
            document.getElementById("targetDate").value = bug.targetDate;
            openModal("bugModal");
        }

        // Form submission
        document.getElementById("bugForm").onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            fetch('/bug/saveBug', {
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
    </script>
</body>
</html>