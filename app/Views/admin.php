<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bug Tracker - Admin</title>
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

        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
        }
        .password-requirements {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
        }
        .invalid {
            color: #e74c3c;
        }
        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        .checkbox-container input[type="checkbox"] {
            margin-right: 0.5rem;
        }

        #exportModal .modal-content {
            max-width: 400px;
        }

        #exportModal .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        #exportModal .checkbox-group label {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        #exportModal .checkbox-group input[type="checkbox"] {
            margin: 0;
        }

        .export-button {
            background-color: #f39c12;
            margin-right: 10px;
        }

        .export-button:hover {
            background-color: #e67e22;
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
        <h1>Admin Page - Welcome, <?php echo htmlspecialchars($WelcomeUser->Name); ?>!</h1>

        <?php if ($userRole == 1): ?>
            <section id="user-management">
                <h2>User Management</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Project</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            // Separate users by role
                            $admins = [];
                            $managers = [];
                            $regularUsers = [];

                            foreach ($users as $user) {
                                if ($user->RoleID == 1) {
                                    $admins[] = $user;
                                } elseif ($user->RoleID == 2) {
                                    $managers[] = $user;
                                } else {
                                    $regularUsers[] = $user;
                                }
                            }

                            // Sort admins and managers by username
                            usort($admins, function($a, $b) { return strcasecmp($a->Username, $b->Username); });
                            usort($managers, function($a, $b) { return strcasecmp($a->Username, $b->Username); });

                            // Sort regular users by project and then by username
                            usort($regularUsers, function($a, $b) {
                                $projectCompare = strcasecmp(
                                    $a->ProjectId ? App\Models\Project::findById($a->ProjectId)->Project : '',
                                    $b->ProjectId ? App\Models\Project::findById($b->ProjectId)->Project : ''
                                );
                                return $projectCompare === 0 ? strcasecmp($a->Username, $b->Username) : $projectCompare;
                            });

                            // Function to display user row
                            function displayUserRow($user) {
                                $projectName = $user->ProjectId ? htmlspecialchars(App\Models\Project::findById($user->ProjectId)->Project) : 'N/A';
                                $roleName = $user->RoleID == 1 ? 'Admin' : ($user->RoleID == 2 ? 'Manager' : 'User');
                                ?>
                                <tr>
                                    <td><?php echo $user->Id; ?></td>
                                    <td><?php echo htmlspecialchars($user->Username); ?></td>
                                    <td><?php echo htmlspecialchars($user->Name);?></td>
                                    <td><?php echo $roleName; ?></td>
                                    <td><?php echo $projectName; ?></td>
                                    <td>
                                        <button onclick="openEditUserModal(<?php echo htmlspecialchars(json_encode($user)); ?>)">Edit</button>
                                        <button onclick="deleteUser(<?php echo $user->Id; ?>)">Delete</button>
                                    </td>
                                </tr>
                                <?php
                            }

                            // Display admins
                            if (!empty($admins)) {
                                echo "<tr><td colspan='7'><h3>Administrators</h3></td></tr>";
                                foreach ($admins as $admin) {
                                    displayUserRow($admin);
                                }
                            }

                            // Display managers
                            if (!empty($managers)) {
                                echo "<tr><td colspan='7'><h3>Managers</h3></td></tr>";
                                foreach ($managers as $manager) {
                                    displayUserRow($manager);
                                }
                            }

                            // Display regular users
                            if (!empty($regularUsers)) {
                                echo "<tr><td colspan='7'><h3>Users</h3></td></tr>";
                                $currentProject = null;
                                foreach ($regularUsers as $user) {
                                    $userProject = $user->ProjectId ? App\Models\Project::findById($user->ProjectId)->Project : 'No Project';
                                    if ($userProject !== $currentProject) {
                                        echo "<tr><td colspan='7'><h4>" . htmlspecialchars($userProject) . "</h4></td></tr>";
                                        $currentProject = $userProject;
                                    }
                                    displayUserRow($user);
                                }
                            }
                        ?>
                    </tbody>
                </table>
                <button onclick="openAddUserModal()">Add New User</button>
            </section>
        <?php endif; ?>

        <?php if ($userRole == 2): ?>
            <section id="manager-user-assignment" class="manager-user-assignment">
                <h2>User Project Assignment</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Current Project</th>
                            <th>Assign to Project</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $regularUsers = array_filter($users, function($user) {
                            return $user->RoleID == 3;
                        });
                        usort($regularUsers, function($a, $b) {
                            return strcmp($a->Username, $b->Username);
                        });
                        foreach ($regularUsers as $user):
                        ?>
                        <tr>
                            <td><?php echo $user->Id; ?></td>
                            <td><?php echo htmlspecialchars($user->Username); ?></td>
                            <td><?php echo htmlspecialchars($user->Name); ?></td>
                            <td><?php echo $user->ProjectId ? htmlspecialchars(App\Models\Project::findById($user->ProjectId)->Project) : 'None'; ?></td>
                            <td>
                                <select onchange="updateUserProject(<?php echo $user->Id; ?>, this.value, this)">
                                    <option value="">None</option>
                                    <?php foreach ($projects as $project): ?>
                                        <option value="<?php echo $project->Id; ?>" <?php echo $user->ProjectId == $project->Id ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($project->Project); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        <?php endif; ?>

        <section id="project-management">
            <h2>Project Management</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Project Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo $project->Id; ?></td>
                        <td><?php echo $project->Project; ?></td>
                        <td><button onclick="openEditProjectModal(<?php echo htmlspecialchars(json_encode($project)); ?>)">Edit</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button onclick="openAddProjectModal()">Add New Project</button>
        </section>

        <section id="bug-management">
            <h2>Bug Management</h2>
            <h2>All Bugs</h2>
            <?php
            // Group bugs by project
            $bugsByProject = [];
            $bugsByProject = [];
            foreach ($bugs as $bug) {
                $projectId = $bug->projectId;
                if (!isset($bugsByProject[$projectId])) {
                    $bugsByProject[$projectId] = [];
                }
                $bugsByProject[$projectId][] = $bug;
            }
        
            // Display bugs grouped by project
            foreach ($projects as $project):
                $projectId = $project->Id;
                $projectName = htmlspecialchars($project->Project);
                $projectBugs = $bugsByProject[$projectId] ?? [];
            ?>
                <h3><?php echo $projectName; ?></h3>
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
                        <?php if (empty($projectBugs)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No bugs reported for this project.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($projectBugs as $bug): ?>
                            <tr>
                                <td><?php echo $bug->id; ?></td>
                                <td><?php echo htmlspecialchars($bug->summary); ?></td>
                                <td><span class="bug-status" data-status-id="<?php echo $bug->statusId; ?>"><?php echo $bug->statusId; ?></span></td>
                                <td><span class="bug-priority" data-priority-id="<?php echo $bug->priorityId; ?>"><?php echo $bug->priorityId; ?></span></td>
                                <td><?php echo $bug->assignedToId ? htmlspecialchars(App\Models\User::findById($bug->assignedToId)->Name) : 'Unassigned'; ?></td>
                                <td>
                                    <button onclick="viewBugDetails(<?php echo htmlspecialchars(json_encode($bug)); ?>)">View</button>
                                    <button onclick="openEditBugModal(<?php echo htmlspecialchars(json_encode($bug)); ?>)">Edit</button>

                                    <?php if ($userRole == 1): ?>
                                        <button onclick="deleteBug(<?php echo $bug->id; ?>)">Delete</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>

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
                                <button onclick="viewBugDetails(<?php echo htmlspecialchars(json_encode($bug)); ?>)">View</button>
                                <button onclick="openEditBugModal(<?php echo htmlspecialchars(json_encode($bug)); ?>)">Edit</button>
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
                                <button onclick="viewBugDetails(<?php echo htmlspecialchars(json_encode($bug)); ?>)">View</button>
                                <button onclick="openEditBugModal(<?php echo htmlspecialchars(json_encode($bug)); ?>)">Edit</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($userRole <= 2): ?>
            <h3>Unassigned Bugs</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Project</th>
                        <th>Summary</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Date Raised</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($unassignedBugs)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No unassigned bugs at the moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($unassignedBugs as $bug): ?>
                        <tr>
                            <td><?php echo $bug->id; ?></td>
                            <td><?php echo isset($projectsById[$bug->projectId]) ? htmlspecialchars($projectsById[$bug->projectId]->Project) : 'Unknown Project'; ?></td>
                            <td><?php echo htmlspecialchars($bug->summary); ?></td>
                            <td><span class="bug-status" data-status-id="<?php echo $bug->statusId; ?>"><?php echo $bug->statusId; ?></span></td>
                            <td><span class="bug-priority" data-priority-id="<?php echo $bug->priorityId; ?>"><?php echo $bug->priorityId; ?></span></td>
                            <td><?php echo $bug->dateRaised; ?></td>
                            <td>
                                <button onclick="viewBugDetails(<?php echo htmlspecialchars(json_encode($bug)); ?>)">View</button>
                                <button onclick="openEditBugModal(<?php echo htmlspecialchars(json_encode($bug)); ?>)">Edit</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <?php endif; ?>
            <button onclick="openAddBugModal()">Add New Bug</button>
        </section>

        <button onclick="openExportModal()" class="button export-button">Export Data</button>
        <a href="<?php echo url('logout'); ?>" class="button">Logout</a>
    </div>

    <!-- Modals -->
    <!-- User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="userModalTitle">User</h2>
            <form id="userForm">
                <input type="hidden" id="userId" name="userId">
                
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                
                <label for="roleId">Role:</label>
                <select id="roleId" name="roleId" required onchange="updateProjectSelect()">
                    <option value="1">Admin</option>
                    <option value="2">Manager</option>
                    <option value="3">User</option>
                </select>
                
                <label for="projectId">Project:</label>
                <select id="projectId" name="projectId" onchange="confirmProjectChange(this)">
                    <option value="">None</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project->Id; ?>"><?php echo htmlspecialchars($project->Project); ?></option>
                    <?php endforeach; ?>
                </select>

                <div id="passwordFields">
                    <label for="password">Password:</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" required>
                        <button type="button" id="password-toggle" class="password-toggle">👓</button>
                    </div>

                    <label for="confirm-password">Confirm Password:</label>
                    <div class="password-container">
                        <input type="password" id="confirm-password" name="confirm-password" required>
                        <button type="button" id="confirm-password-toggle" class="password-toggle">👓</button>
                    </div>

                    <div id="password-requirements" class="password-requirements">
                        Password must contain:
                        <ul>
                            <li id="length">At least 8 characters</li>
                            <li id="uppercase">At least one uppercase letter</li>
                            <li id="lowercase">At least one lowercase letter</li>
                            <li id="number">At least one number</li>
                            <li id="special">At least one special character</li>
                            <li id="match">Passwords must match</li>
                        </ul>
                    </div>
                </div>
                
                <button type="submit">Save</button>
            </form>
        </div>
    </div>

    <!-- Project Modal -->
    <div id="projectModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="projectModalTitle">Project</h2>
            <form id="projectForm">
                <input type="hidden" id="projectId" name="projectId">
                
                <label for="projectName">Project Name:</label>
                <input type="text" id="projectName" name="projectName" required>
                
                <button type="submit">Save</button>
            </form>
        </div>
    </div>

    <!-- Bug Modal -->
    <div id="bugModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="bugModalTitle">Bug</h2>
            <form id="bugForm">
                <input type="hidden" id="bugId" name="bugId">
                
                <label for="bugProjectId">Project:</label>
                <select id="bugProjectId" name="bugProjectId" required <?php echo ($userRole > 2) ? 'disabled' : ''; ?>>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project->Id; ?>"><?php echo htmlspecialchars($project->Project); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <label for="summary">Summary:</label>
                <input type="text" id="summary" name="summary" required maxlength="255">
                
                <label for="description">Description:</label>
                <textarea id="description" name="description" required maxlength="1000"></textarea>
                
                <label for="assignedToId">Assigned To:</label>
                <select id="assignedToId" name="assignedToId">
                    <option value="">Unassigned</option>
                    <?php if ($userRole <= 2): ?>
                        <?php foreach ($users as $u): ?>
                            <?php if ($u->RoleID == 3): ?>
                                <option value="<?php echo $u->Id; ?>">
                                    <?php echo htmlspecialchars($u->Name); ?>
                                    <?php echo ($u->Id == $userId) ? ' (Me)' : ''; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="<?php echo $userId; ?>"><?php echo htmlspecialchars($WelcomeUser->Name); ?> (Me)</option>
                    <?php endif; ?>
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

    <!-- Export Data Modal -->
    <div id="exportModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Export Data</h2>
            <form id="exportForm">
                <div class="checkbox-group">
                    <?php if ($userRole == 1): ?>
                        <label>
                            <input type="checkbox" name="exportUsers" value="1">
                            Users
                        </label>
                    <?php endif; ?>
                    <label>
                        <input type="checkbox" name="exportProjects" value="1">
                        Projects
                    </label>
                    <label>
                        <input type="checkbox" name="exportBugs" value="1">
                        Bugs
                    </label>
                </div>
                <button type="submit" class="button">Export Selected Data</button>
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

        // Password visibility toggle
        function togglePasswordVisibility(inputId, toggleId) {
            var input = document.getElementById(inputId);
            var toggle = document.getElementById(toggleId);
            var type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            toggle.textContent = type === 'password' ? '👓' : '🕶️';
        }

        document.getElementById('password-toggle').addEventListener('click', function() {
            togglePasswordVisibility('password', 'password-toggle');
        });

        document.getElementById('confirm-password-toggle').addEventListener('click', function() {
            togglePasswordVisibility('confirm-password', 'confirm-password-toggle');
        });

        // Password requirements check
        function checkPasswordRequirements() {
            var password = document.getElementById('password').value;
            var confirmPassword = document.getElementById('confirm-password').value;
            var isNewUser = document.getElementById('userId').value === "";

            var requirements = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password),
                match: password === confirmPassword && password !== ''
            };

            for (var req in requirements) {
                var element = document.getElementById(req);
                if (requirements[req]) {
                    element.classList.remove('invalid');
                } else {
                    element.classList.add('invalid');
                }
            }

            return Object.values(requirements).every(Boolean);
        }

        document.getElementById('password').addEventListener('input', checkPasswordRequirements);
        document.getElementById('confirm-password').addEventListener('input', checkPasswordRequirements);

        // Fix Description handling functions
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

        // User management
        function updateProjectSelect() {
            var roleSelect = document.getElementById('roleId');
            var projectSelect = document.getElementById('projectId');
            
            if (roleSelect.value === '1' || roleSelect.value === '2') {
                projectSelect.value = '';
                projectSelect.disabled = true;
            } else {
                projectSelect.disabled = false;
            }
        }

        function openAddUserModal() {
            document.getElementById("userModalTitle").innerText = "Add User";
            document.getElementById("userForm").reset();
            document.getElementById("userId").value = "";
            updateProjectSelect();
            document.getElementById("userForm").dataset.isNewUser = "true";
            openModal("userModal");
        }

        function openEditUserModal(user) {
            document.getElementById("userModalTitle").innerText = "Edit User";
            document.getElementById("userId").value = user.Id;
            document.getElementById("username").value = user.Username;
            document.getElementById("roleId").value = user.RoleID;
            document.getElementById("projectId").value = user.ProjectId || "";
            document.getElementById("projectId").setAttribute('data-current-project', user.ProjectId || "");
            document.getElementById("name").value = user.Name;
            document.getElementById("password").value = "";
            document.getElementById("confirm-password").value = "";
            updateProjectSelect();
            document.getElementById("userForm").dataset.isNewUser = "false";
            openModal("userModal");
        }

        function updateUserProject(userId, projectId, selectElement) {
            const originalValue = selectElement.value;
            const confirmMessage = projectId 
                ? "Are you sure you want to change this user's project? They will be unassigned from all bugs in their current project before being assigned to the new project."
                : "Are you sure you want to remove this user from their current project? They will be unassigned from all bugs in their current project.";

            if (confirm(confirmMessage)) {
                fetch('<?php echo url('/admin/updateUserProject'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ userId: userId, projectId: projectId }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("User project updated successfully");
                        window.location.reload();
                    } else {
                        alert(data.message || "Error updating user project");
                        selectElement.value = originalValue; // Reset to original value on error
                        window.location.reload();
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert("An error occurred while updating the user project");
                    selectElement.value = originalValue; // Reset to original value on error
                    window.location.reload();
                });
            } else {
                selectElement.value = originalValue; // Reset to original value when cancelled
                window.location.reload();
            }
        }

        function confirmProjectChange(selectElement) {
            const newProjectId = selectElement.value;
            const currentProjectId = selectElement.getAttribute('data-current-project');
            const isNewUser = document.getElementById("userForm").dataset.isNewUser === "true";
            
            if (!isNewUser && newProjectId !== currentProjectId) {
                const confirmMessage = newProjectId
                    ? "Are you sure you want to change this user's project? They will be unassigned from all bugs in their current project before being assigned to the new project."
                    : "Are you sure you want to remove this user from their current project? They will be unassigned from all bugs in their current project.";

                if (!confirm(confirmMessage)) {
                    selectElement.value = currentProjectId;
                } else {
                    selectElement.setAttribute('data-current-project', newProjectId);
                }
            }
        }

        function deleteUser(userId) {
            var currentUserId = <?php echo json_encode(App\Core\SessionManager::get('user_id')); ?>;
            if (userId == currentUserId) {
                if (confirm("Are you sure you want to delete your own account? This action will log you out and delete all your data. This cannot be undone.")) {
                    performDeleteUser(userId, true);
                }
            } else {
                if (confirm("Are you sure you want to delete this user?")) {
                    performDeleteUser(userId, false);
                }
            }
        }

        function performDeleteUser(userId, isSelf) {
            fetch('<?php echo url('/admin/deleteUser'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ userId: userId, isSelf: isSelf }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("User deleted successfully");
                    if (isSelf) {
                        window.location.href = '<?php echo url('logout'); ?>';
                    } else {
                        location.reload();
                    }
                } else {
                    alert(data.message || "Error deleting user");
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                alert("An error occurred while deleting the user");
            });
        }

        // Project management
        function openAddProjectModal() {
            document.getElementById("projectModalTitle").innerText = "Add Project";
            document.getElementById("projectForm").reset();
            document.getElementById("projectId").value = "";
            openModal("projectModal");
        }

        function openEditProjectModal(project) {
            document.getElementById("projectModalTitle").innerText = "Edit Project";
            document.getElementById("projectId").value = project.Id;
            document.getElementById("projectName").value = project.Project;
            openModal("projectModal");
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

        // Session management initialization
        function initializeSessionManagement() {
            const sessionExpirationBanner = document.getElementById('session-expiration-banner');
            const sessionCountdown = document.getElementById('session-countdown');
            let sessionExpirationTime = <?php echo json_encode(App\Core\SessionManager::getSessionExpirationTime()); ?>;
            let countdownInterval;

            function updateSessionCountdown() {
                let now = Math.floor(Date.now() / 1000);
                let timeLeft = sessionExpirationTime - now;

                if (timeLeft <= 900) { // Show banner when 15 minutes or less remain
                    sessionExpirationBanner.style.display = 'block';
                }

                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = '<?php echo url('logout'); ?>';
                    return;
                }

                let minutes = Math.floor(timeLeft / 60);
                let seconds = timeLeft % 60;
                sessionCountdown.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
            }

            function refreshSession() {
                fetch('<?php echo url('refresh-session'); ?>', { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            sessionExpirationTime = data.newExpirationTime;
                            sessionExpirationBanner.style.display = 'none';
                        }
                    });
            }

            // Start countdown immediately
            countdownInterval = setInterval(updateSessionCountdown, 1000);

            // Set up session refresh events
            document.addEventListener('click', refreshSession);
            document.addEventListener('keypress', refreshSession);

            // Debounced scroll handler
            let scrollTimeout;
            window.addEventListener('scroll', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(refreshSession, 0);
            });
        }

        // Call this function when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            initializeData();
            updateStatusAndPriority();
            initializeSessionManagement();
        });

        function viewBugDetails(bug) {
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
            document.getElementById("assignedToId").value = "";
            
            handleFixDescriptionField("1"); // Default to Unassigned status
            
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
            
            handleFixDescriptionField(bug.statusId.toString());
            
            openModal("bugModal");
        }

        function openExportModal() {
            openModal("exportModal");
        }

        // Form submissions
        document.getElementById("exportForm").onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            fetch('<?php echo url('/admin/exportData'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.blob())
            .then(blob => {
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'exported_data.zip';
                document.body.appendChild(a);
                a.click();
                a.remove();
            })
            .catch((error) => {
                console.error('Error:', error);
                alert("An error occurred while exporting the data");
            });

            closeModal("exportModal");
        };

        document.getElementById("userForm").onsubmit = function(e) {
            e.preventDefault();
            if (checkPasswordRequirements()) {
                var formData = new FormData(this);
                fetch('<?php echo url('/admin/saveUser'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("User saved successfully");
                        location.reload();
                    } else {
                        alert("Error saving user: " + data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert("An error occurred while saving the user");
                });
            } else {
                alert("Please meet all password requirements before submitting.");
            }
        };

        document.getElementById("projectForm").onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            fetch('<?php echo url('/admin/saveProject'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Project saved successfully");
                    location.reload();
                } else {
                    alert("Error saving project: " + data.message);
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                alert("An error occurred while saving the project");
            });
        };

        document.getElementById("bugForm").onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            fetch('<?php echo url('/admin/saveBug'); ?>', {
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

        document.getElementById("updatePassword").addEventListener("change", function() {
            var passwordFields = document.getElementById("passwordFields");
            var passwordInput = document.getElementById("password");
            var confirmPasswordInput = document.getElementById("confirm-password");
            
            if (this.checked) {
                passwordFields.style.display = "block";
                passwordInput.required = true;
                confirmPasswordInput.required = true;
            } else {
                passwordFields.style.display = "none";
                passwordInput.required = false;
                confirmPasswordInput.required = false;
                passwordInput.value = "";
                confirmPasswordInput.value = "";
            }
        });

        function deleteProject(projectId, projectName) {
            if (confirm(`Are you sure you want to delete the project "${projectName}" (ID: ${projectId})? This will also delete all associated bugs. This action cannot be undone.`)) {
                fetch('<?php echo url('/admin/deleteProject'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ projectId: projectId }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Project "${projectName}" (ID: ${projectId}) deleted successfully`);
                        location.reload();
                    } else {
                        alert("Error deleting project: " + data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert("An error occurred while deleting the project");
                });
            }
        }

        function deleteBug(bugId) {
            if (confirm(`Are you sure you want to delete the bug (ID: ${bugId})? This action cannot be undone.`)) {
                fetch('<?php echo url('/admin/deleteBug'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ bugId: bugId }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Bug (ID: ${bugId}) deleted successfully`);
                        location.reload();
                    } else {
                        alert("Error deleting bug: " + data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert("An error occurred while deleting the bug");
                });
            }
        }

        // Add event listeners for user modal
        document.addEventListener('DOMContentLoaded', function() {
            var roleSelect = document.getElementById('roleId');
            roleSelect.addEventListener('change', updateProjectSelect);

            var projectSelect = document.getElementById('projectId');
            projectSelect.addEventListener('change', function() {
                confirmProjectChange(this);
            });

            var userModal = document.getElementById('userModal');
            userModal.addEventListener('show', updateProjectSelect);
        });

        // Bug form functionality
        document.addEventListener('DOMContentLoaded', function() {
            const bugProjectIdSelect = document.getElementById('bugProjectId');
            const assignedToSelect = document.getElementById('assignedToId');
            const statusSelect = document.getElementById('statusId');

            // Create an object to store users by project
            const usersByProject = <?php 
                $usersByProject = [];
                foreach ($users as $user) {
                    if ($user->RoleID == 3 && $user->ProjectId) {
                        $usersByProject[$user->ProjectId][] = [
                            'id' => $user->Id,
                            'name' => htmlspecialchars($user->Name)
                        ];
                    }
                }
                echo json_encode($usersByProject);
            ?>;

            function updateAssignedToOptions() {
                const projectId = bugProjectIdSelect.value;
                assignedToSelect.innerHTML = '<option value="">Unassigned</option>';

                if (usersByProject[projectId]) {
                    usersByProject[projectId].forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        assignedToSelect.appendChild(option);
                    });
                }
            }

            bugProjectIdSelect.addEventListener('change', function() {
                // Reset assigned user and status when project changes
                assignedToSelect.value = "";
                statusSelect.value = "1"; // Set to "Unassigned"
                updateAssignedToOptions();
            });

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

            // Call updateAssignedToOptions when opening the modal to ensure correct initial state
            window.openAddBugModal = function() {
                document.getElementById("bugModalTitle").innerText = "Add Bug";
                document.getElementById("bugForm").reset();
                document.getElementById("bugId").value = "";
                updateAssignedToOptions();
                openModal("bugModal");
            };

            window.openEditBugModal = function(bug) {
                document.getElementById("bugModalTitle").innerText = "Edit Bug";
                // Populate form fields with bug data
                document.getElementById("bugId").value = bug.id;
                document.getElementById("bugProjectId").value = bug.projectId;
                document.getElementById("summary").value = bug.summary;
                document.getElementById("description").value = bug.description;
                document.getElementById("statusId").value = bug.statusId;
                document.getElementById("priorityId").value = bug.priorityId;
                document.getElementById("targetDate").value = bug.targetDate;
                
                updateAssignedToOptions();
                document.getElementById("assignedToId").value = bug.assignedToId || "";
                
                openModal("bugModal");
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
                
                fetch('<?php echo url('/admin/saveBug'); ?>', {
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