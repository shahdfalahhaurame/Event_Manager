<?php
$page_title = 'Events';

require_once '../includes/header.php';

require_once '../includes/db.php';

$error_message = '';
$success_message = '';

if (isset($_GET['success'])) {
    $success_code = $_GET['success'];
    switch ($success_code) {
        case 'event_added':
            $success_message = 'Event created successfully!';
            break;
        case 'event_updated':
            $success_message = 'Event updated successfully!';
            break;
        case 'event_deleted':
            $success_message = 'Event deleted successfully!';
            break;
        default:
            $success_message = 'Operation successful.';
            break;
    }
}

if (isset($_GET['error'])) {
    $error_code = $_GET['error'];
    switch ($error_code) {
        case 'dbconnection':
            $error_message = 'A database connection error occurred. Please try again later.';
            break;
        case 'queryfailed':
            $error_message = 'Error fetching events. Please try again.';
            break;
        case 'invalid_id':
            $error_message = 'Invalid event ID provided.';
            break;
        case 'event_not_found':
            $error_message = 'Event not found.';
            break;
        case 'deletion_failed':
            $error_message = 'Failed to delete the event. Please try again.';
            break;
        case 'no_id_provided':
             $error_message = 'No event ID provided.';
             break;
         case 'unauthorized':
              $error_message = 'You are not authorized to perform this action.';
              break;
         case 'update_failed':
              $error_message = 'Failed to update the event. Please try again.';
              break;
         case 'unknown_error':
              $error_message = 'An unknown error occurred.';
              break;
        default:
            $error_message = 'An unknown error occurred.';
            break;
    }
}


// --- Fetch Event Data from Database ---
$events = [];

$logged_in_user_id = $_SESSION['user_id'] ?? null;

if ($logged_in_user_id !== null && isset($conn) && $conn !== null) {
    try {
        $sql = "SELECT
                    id,
                    title,
                    DATE_FORMAT(date, '%Y-%m-%d') as event_date,
                    time,
                    location,
                    state,
                    (SELECT COUNT(*) FROM needs WHERE event_id = events.id) as needs_count,
                    created_at
                FROM events
                WHERE created_by = ?
                ORDER BY date ASC";

        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            error_log("Events list SQL Prepare failed: (" . $conn->errno . ") " . $conn->error);
            $error_message = 'An application error occurred while preparing to fetch events.';
        } else {
            $stmt->bind_param("i", $logged_in_user_id);

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result) {
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $events[] = $row;
                    }
                }
                $result->free();
            } else {
                error_log("Events list SQL Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                $error_message = 'Error fetching events. Please try again.';
            }

            $stmt->close();
        }

    } catch (Exception $e) {
        error_log("Exception when fetching events in events/index.php: " . $e->getMessage());
        $error_message = 'An unexpected error occurred while loading events.';
    }

    if ($conn !== null) {
        $conn->close();
    }

} else {
    if (!isset($conn) || !($conn instanceof mysqli)) {
        $error_message = 'Database connection failed. Unable to load events.';
    } else if ($logged_in_user_id === null) {
        $error_message = 'You must be logged in to view events.';
    }
}

?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
    <div>
        <h1 class="text-2xl font-semibold">Events</h1>
        <div class="flex flex-row items-center space-x-2 mt-1">
            <p class="text-gray-400 text-sm">Dashboard</p>
            <img class="w-4" src="../assets/icons/arrow-solid-Right.svg" alt="" srcset="">
            <p class="text-[#1A71F6] text-sm font-bold">Events</p>
        </div>
    </div>
    <div class="flex items-center space-x-4 mt-4 sm:mt-0 w-full sm:w-auto justify-end">
        <a href="./add.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md flex items-center whitespace-nowrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            New Event
        </a>
    </div>
</div>

<?php if ($success_message): ?>
    <div class="bg-green-500 text-white p-3 rounded mb-4 text-center"><?php echo htmlspecialchars($success_message); ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="bg-red-600 text-white p-3 rounded mb-4 text-center"><?php echo htmlspecialchars($error_message); ?></div>
<?php endif; ?>

<style>
    #myTable {
        width: 100% !important;
        border-collapse: collapse;
    }

    #myTable thead tr {
        background-color: #101011; /* Dark background for header */
        border-radius: 16px 16px 0 0; /* Rounded top corners */
        overflow: hidden; /* Hide overflow for rounded corners */
    }

    /* Header cell styling */
    #myTable thead th {
        color: white; /* White text color */
        border-bottom: 2px solid #3e3e3e; /* Bottom border */
        padding: 12px 15px; /* Padding */
        text-align: left; /* Left align text */
        font-weight: 600; /* Semi-bold font */
    }

    /* Body cell styling */
    #myTable tbody td {
        background-color: #1e1e1e; /* Dark background for body cells */
        color: white; /* White text color */
        padding: 12px 15px; /* Padding */
        border-bottom: 1px solid #3e3e3e; /* Bottom border */
        vertical-align: middle; /* Vertically align content */
    }

    /* Hover effect for body rows */
    #myTable tbody tr:hover td {
        background-color: #2a2a2a !important; /* Slightly lighter background on hover */
    }

    /* Footer row styling (for search inputs) */
     #myTable tfoot th {
         padding: 8px 15px; /* Adjust padding */
         border-top: 1px solid #3e3e3e; /* Top border */
     }

    /* Style for the state badges */
    .state-badge {
        padding: 3px 8px; /* Padding */
        border-radius: 4px; /* Rounded corners */
        font-size: 12px; /* Smaller font size */
        font-weight: 600; /* Semi-bold font */
        color: white; /* White text color for badges */
        display: inline-block; /* Allows padding and margins */
        text-align: center; /* Center text */
    }

    /* Background colors for state badges (using Tailwind-like classes) */
    .bg-blue-600 { background-color: #2563eb; } /* Tailwind blue-600 */
    .bg-green-600 { background-color: #16a34a; } /* Tailwind green-600 */
    .bg-purple-600 { background-color: #9333ea; } /* Tailwind purple-600 */
    .bg-red-600 { background-color: #dc2626; } /* Tailwind red-600 */
    .bg-yellow-600 { background-color: #ca8a04; } /* Tailwind yellow-600 */
    .bg-gray-600 { background-color: #4b5563; } /* Tailwind gray-600 (for default/unset) */
    /* Removed .bg-b as it seemed custom; using gray-600 for default state */


    /* Style for placeholder text in inputs */
    input::placeholder {
        color: #666; /* Lighter gray for placeholder */
    }

     /* Ensure DataTables controls are spaced nicely */
     .dataTables_wrapper .row {
         display: flex;
         flex-wrap: wrap; /* Allow wrapping on small screens */
         align-items: center;
         justify-content: space-between;
         padding: 10px 0; /* Add some padding */
     }

     .dataTables_length,
     .dataTables_filter,
     .dataTables_info,
     .dataTables_paginate {
         margin-bottom: 10px; /* Add margin below controls */
     }

     /* Style for DataTables pagination */
     .dataTables_wrapper .dataTables_paginate .paginate_button {
         padding: 5px 10px;
         margin: 0 2px;
         border: 1px solid #3e3e3e;
         border-radius: 4px;
         color: white !important; /* Ensure text is white */
         background-color: #1e1e1e; /* Dark background */
         cursor: pointer;
     }

     .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
         background-color: #2a2a2a !important; /* Hover effect */
         border-color: #5a5a5a;
     }

     .dataTables_wrapper .dataTables_paginate .paginate_button.current {
         background-color: #2563eb !important; /* Active page background */
         border-color: #2563eb;
         color: white !important;
     }

     .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
         color: #666 !important; /* Disabled button color */
         background-color: #1e1e1e;
         border-color: #3e3e3e;
         cursor: not-allowed;
     }


</style>

<div class="bg-[#1f1f1f] rounded-lg p-6 shadow-lg overflow-x-auto"> <table id="myTable" class="display w-full">
        <thead>
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Time</th>
                <th>State</th>
                <th>Location</th>
                <th>Needs</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['title']); ?></td>
                        <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                        <td><?php echo htmlspecialchars($event['time']); ?></td>
                        <td>
                            <?php
                            $state = strtolower($event['state'] ?? '');
                            $state_classes = [
                                'draft' => 'bg-yellow-600',
                                'active' => 'bg-green-600',
                                'full' => 'bg-blue-600',
                                'cancelled' => 'bg-red-600',
                                'completed' => 'bg-purple-600'
                            ];
                            $state_class = $state_classes[$state] ?? 'bg-gray-600';
                            ?>
                            <span class="state-badge <?php echo $state_class; ?>">
                                <?php
                                 echo htmlspecialchars(empty($event['state']) ? 'Unset' : ucfirst($event['state']));
                                ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($event['location']); ?></td>
                        <td><?php echo htmlspecialchars($event['needs_count']); ?> needs</td>
                        <td class="px-4 py-3 whitespace-nowrap"> <div class="flex space-x-2">
                                <a href="view.php?id=<?php echo htmlspecialchars($event['id']); ?>" class="text-blue-400 hover:text-blue-600" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </a>
                                <a href="edit.php?id=<?php echo htmlspecialchars($event['id']); ?>" class="text-yellow-400 hover:text-yellow-600" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                </a>
                                <a href="delete.php?id=<?php echo htmlspecialchars($event['id']); ?>" class="text-red-400 hover:text-red-600" title="Delete" onclick="return confirm('Are you sure you want to delete this event?');">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-4">No events found. <a href="./add.php" class="text-blue-500 hover:underline">Create a new event</a>.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                 <th>Title</th>
                <th>Date</th>
                <th>Time</th>
                <th>State</th>
                <th>Location</th>
                <th>Needs</th>
                <th></th> </tr>
        </tfoot>
    </table>
</div>

<script>
    $(document).ready(function() {
        $('#myTable thead th').each(function(i) {
            var title = $(this).text().trim();

            if (i < $('#myTable thead th').length - 1) {
                 $('#myTable tfoot th').eq(i).html(
                     '<input type="text" placeholder="Search ' + title + '" data-index="' + i + '" ' +
                     'class="w-full px-2 py-1 rounded-md bg-[#3D3D3D] text-white border border-[#3D3D3D] focus:outline-none focus:border-blue-500 text-sm" />'
                 );
            } else {
                 // For the last column (Actions), leave the footer cell empty
                 $('#myTable tfoot th').eq(i).html('');
            }
        });


        // --- Initialize DataTables ---
        var table = $('#myTable').DataTable({
            // DataTables options for responsiveness and features
            'searching': true,
            "responsive": true,
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100],
            "language": {
                 "search": "_INPUT_",
                 "searchPlaceholder": "Search events...",
            },
            columnDefs: [
                {
                    "targets": [6],
                    "orderable": false,
                    "searchable": false
                }
            ],

            initComplete: function() {
                 $('.dataTables_info, .dataTables_length label, .dataTables_filter label').css('color', 'white');

                 $('.dataTables_filter input').css('color', 'white')
                                            .css('background-color', 'transparent')
                                            .addClass('px-2 py-1 rounded-md border border-gray-600 focus:outline-none focus:border-blue-500 text-sm'); // Add Tailwind classes

                 $('.dataTables_wrapper').addClass('mb-4');

            }

        });

        $(table.table().container()).on('keyup', 'tfoot input', function() {
            var columnIndex = $(this).data('index');

            table
                .column(columnIndex)
                .search(this.value)
                .draw();
        });

        $('.dataTables_filter input').on('keyup', function() {
             if (this.value === '') {
                 // If global search is cleared, clear all column searches
                 table.columns().search('').draw();
                 // Also clear the text in the footer inputs
                 $('#myTable tfoot input').val('');
             }
        });

    });
</script>

<?php
require_once '../includes/footer.php';
?>
