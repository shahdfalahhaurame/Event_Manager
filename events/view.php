<?php

$page_title = 'View Event';
require_once '../includes/header.php';
require_once '../includes/db.php';

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$event_id) {
    echo "<p style='color: red;'>Invalid event ID.</p>";
    exit;
}

// Fetch event info
$stmt = $conn->prepare("SELECT e.*, u.name AS creator_name FROM events e JOIN users u ON e.created_by = u.id WHERE e.id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event_result = $stmt->get_result();
$event = $event_result->fetch_assoc();

if (!$event) {
    echo "<p style='color: red;'>Event not found.</p>";
    exit;
}

// Fetch event needs
$needs_stmt = $conn->prepare("SELECT * FROM needs WHERE event_id = ?");
$needs_stmt->bind_param("i", $event_id);
$needs_stmt->execute();
$needs_result = $needs_stmt->get_result();
$needs = $needs_result->fetch_all(MYSQLI_ASSOC);
?>

<!-- Responsive Event Info Layout -->
<style>
    .event-container {
        margin: auto;
        color: white;
    }

    .event-card {
        background-color: #1e1e1e;
        border-radius: 12px;
        padding: 2rem;
    }

    .event-section {
        margin-bottom: 1.25rem;
        width: 100%;
        height: fit-content;
    }

    .event-section strong {
        display: inline-block;
        width: 140px;
        font-weight: 600;
    }

    .badge {
        display: inline-block;
        padding: 0.3rem 0.7rem;
        border-radius: 6px;
        color: white;
        font-size: 0.85rem;
    }

    .badge.draft { background-color: #555; }
    .badge.active { background-color: #007bff; }
    .badge.full { background-color: #ffa000; }
    .badge.cancelled { background-color: #e53935; }
    .badge.completed { background-color: #43a047; }

    .priority-low { background-color: #00c853; }
    .priority-medium { background-color: #ffb300; }
    .priority-high { background-color: #e53935; }

    @media (max-width: 600px) {
        .event-section strong {
            display: block;
            width: auto;
            margin-bottom: 0.3rem;
        }
    }
</style>

<div class="event-container md:p-[2em] p-[0.8em]">
    <div class="flex flex-col items-start mb-6">
        <h1 class="text-2xl font-size font-semibold">Events</h1>
        <div class="flex flex-row items-center space-x-2 mt-1">
            <p class="text-gray-400 text-sm">Dashboard</p>
            <img class="w-4" src="../assets/icons/arrow-solid-Right.svg" alt="" srcset="">
            <p class="text-gray-400 text-sm">Events</p>
            <img class="w-4" src="../assets/icons/arrow-solid-Right.svg" alt="" srcset="">
            <p class="text-[#1A71F6] text-sm font-bold">View: <?= htmlspecialchars($event['title']) ?></p>
        </div>
    </div>

    <div class="event-card">
        <div class="flex flex-col gap-[8px] mb-[16px] relative">
            <h2 class="text-[22px]">Event Information</h2>
            <p class="text-[#B0B0B0]">Information about this specific Event.</p>
            <a href="./index.php" class="text-[#B0B0B0] hover:text-[#00aeef] flex items-center gap-2">
                <img class="absolute right-0 top-0 w-8" src="../assets/icons/arrow-back-outline.svg" alt="Back Icon">
            </a>
        </div>
        
        <div class="event-section flex gap-2 items-start md:flex-row flex-col">
            <div class="flex gap-2 items-center ">
                <img src="../assets/icons/GroupIcon/filter-outline.svg" alt="" srcset="">
                <strong>Title:</strong>
            </div>
            <?= htmlspecialchars($event['title']) ?>
        </div>
        <div class="event-section flex gap-2 items-center flex-wrap">
            <img src="../assets/icons/GroupIcon/float-right.svg" alt="" srcset="">
            <strong>Description:</strong> <?= nl2br(htmlspecialchars($event['description'])) ?>
        </div>
        <div class="event-section flex gap-2 items-center flex-wrap">
            <img src="../assets/icons/GroupIcon/today-outline.svg" alt="" srcset="">
            <strong>Date:</strong> <?= date("m/d/y", strtotime($event['date'])) ?> at <?= date("g:i A", strtotime($event['time'])) ?>
        </div>
        <div class="event-section flex gap-2 items-center flex-wrap">
            <img src="../assets/icons/GroupIcon/area-search.svg" alt="" srcset="">
            <strong>State:</strong> <span class="badge <?= htmlspecialchars($event['state']) ?>"><?= ucfirst($event['state']) ?></span>
        </div>
        <div class="event-section flex gap-2 items-center flex-wrap">
            <img src="../assets/icons/GroupIcon/golf-outline.svg" alt="" srcset="">
            <strong>Location:</strong> <a href="<?= htmlspecialchars($event['location']) ?>" target="_blank" style="color: #00aeef; word-break: break-all;"><?= htmlspecialchars($event['location']) ?></a>
        </div>

        <?php if ($needs): ?>
            <div class="event-section flex gap-2 items-center flex-wrap">
                <img src="../assets/icons/GroupIcon/receipt.svg" alt="" srcset="">
                <strong>Needs:</strong> <?= implode(', ', array_column($needs, 'title')) ?>
            </div>
            <div class="event-section flex gap-x-2 items-center flex-wrap">
                <img src="../assets/icons/GroupIcon/float-right.svg" alt="" srcset="">
                <strong>Description / Needs:</strong>
                <?php foreach ($needs as $need): ?>
                    <div>
                        <?= htmlspecialchars($need['description']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="event-section flex gap-2 items-center flex-wrap">
                <strong>Priority:</strong>
                <?php foreach ($needs as $need): ?>
                    <span class="badge priority-<?= strtolower($need['priority']) ?>"><?= $need['priority'] ?></span>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="event-section flex gap-2 items-center flex-wrap">

                <strong>Needs:</strong> None
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
