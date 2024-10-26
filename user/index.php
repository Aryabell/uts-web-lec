<?php
session_start();

// Include koneksi database
include '../config/database.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data pengguna dari sesi
$user_id = $_SESSION['user_id'];
$user_name =  htmlspecialchars($_SESSION['user_name']);

$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_info = $user_result->fetch_assoc();
$profile_picture = $user_info['profile_picture'];

// Inisialisasi variabel
$event_id = "";
$success = "";
$error = "";

// Cek apakah ada event_id yang diterima dari URL untuk pendaftaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);

    // Cek apakah pengguna sudah terdaftar untuk event ini
    $stmt = $conn->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->bind_param("ii", $user_id, $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "You are already registered for this event.";
    } else {
        // Daftarkan pengguna untuk event
        $stmt = $conn->prepare("INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $event_id);

        if ($stmt->execute()) {
            $success = "Registration for the event was successful!";
        } else {
            $error = "An error occurred. Please try again.";
        }
    }
}

// Ambil data acara dari database
$query = "SELECT * FROM events WHERE status = 'open'";
$events = $conn->query($query);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <title>Daftar Acara</title>
    <style>
        .card {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
        }

        .modal {
            transition: opacity 0.3s ease-in-out, transform 0.3s ease-in-out;
            transform: scale(0.9);
            opacity: 0;
        }

        .modal.show {
            opacity: 1;
            transform: scale(1);
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes pop-in {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            60% {
                transform: scale(1.2);
                opacity: 1;
            }

            100% {
                transform: scale(1);
            }
        }

        .icon-animate {
            animation: pop-in 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: white;
            font-size: 1.25rem;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            padding: 30px;
            width: 45px;
            height: 45px;
            transition: background-color 0.3s ease;
        }

        .swiper-button-next::after,
        .swiper-button-prev::after {
            font-size: 1.4rem;
        }

        .swiper-button-next:hover,
        .swiper-button-prev:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

        .swiper-container {
            position: relative;
            max-width: 100%;
            margin: auto;
            overflow: hidden;
        }

        .swiper-slide img {
            transition: transform 0.4s ease;
        }

        .swiper-slide img:hover {
            transform: scale(1.05);
        }

        .swiper-pagination-bullet {
            background-color: #fff;
            opacity: 0.7;
            transition: opacity 0.3s ease;
        }

        .swiper-pagination-bullet-active {
            background-color: #007bff;
            opacity: 1;
        }

        .swiper-scrollbar-drag {
            background-color: #007bff;
        }

        .swiper-slide {
            width: 100% !important;
        }

        body {
            overflow-x: hidden;
            /* Mencegah scroll horizontal */
        }
    </style>
</head>

<body class="bg-white min-h-screen flex flex-col fade-in">
    <!-- Navbar -->
    <nav class="bg-gray-800 shadow-md">
        <div class="container mx-auto flex items-center justify-between p-4">
            <a class="text-2xl font-bold text-white hover:text-gray-200 transition duration-300" href="index.php">Eventify</a>
            <!-- Desktop menu -->
            <div class="hidden md:flex space-x-4 items-center">
                <a class="text-gray-200 hover:text-white transition duration-300 flex items-center space-x-2" href="profile.php">
                    <?php if (!empty($user_info['profile_picture'])): ?>
                        <img src="../profile/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" alt="Profile Picture" class="w-8 h-8 rounded-full">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                    <span><?php echo htmlspecialchars($user_name); ?></span>
                </a>
                <a class="bg-green-500 text-white px-3 py-2 rounded hover:bg-green-600 transition duration-300" href="registered_events.php">
                    <i class="fas fa-calendar-check"></i> My Events
                </a>
                <a class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600 transition duration-300" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            <!-- Burger button -->
            <div class="relative md:hidden">
                <button id="navbar-toggle" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        <!-- Mobile menu (hidden by default) -->
        <div id="navbar-menu" class="hidden md:hidden bg-gray-800">
            <a class="block px-4 py-2 text-gray-200 hover:bg-gray-700 flex items-center space-x-2" href="profile.php">
                <?php if (!empty($user_info['profile_picture'])): ?>
                    <img src="../profile/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" alt="Profile Picture" class="w-8 h-8 rounded-full">
                <?php else: ?>
                    <i class="fas fa-user w-8 h-8 rounded-full text-gray-200 bg-gray-500 flex items-center justify-center"></i>
                <?php endif; ?>
                <span><?php echo htmlspecialchars($user_name); ?></span>
            </a>
            <a class="block px-4 py-2 bg-green-500 text-white hover:bg-green-600" href="registered_events.php">
                <i class="fas fa-calendar-check"></i> My Events
            </a>
            <a class="block px-4 py-2 bg-red-500 text-white hover:bg-red-600" href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="relative mt-0 fade-in">
        <!-- Background Section -->
        <div class="absolute inset-0 bg-cover bg-center z-0 opacity-70"></div>

        <!-- Content Section -->
        <div class="swiper-container relative z-10 w-full">
            <div class="swiper-wrapper w-full">
                <?php
                // Query untuk mengambil top events
                $featured_events_query = "SELECT * FROM events WHERE featured = 1 AND status = 'open' LIMIT 10";
                $featured_events = $conn->query($featured_events_query);

                if ($featured_events->num_rows > 0):
                    while ($event = $featured_events->fetch_assoc()):
                ?>
                        <div class="swiper-slide w-full h-auto">
                            <div class="bg-gray-50 h-80 w-full shadow-md fade-in relative overflow-hidden">
                                <img src="../uploads/<?php echo $event['banner_image']; ?>" alt="Featured Events"
                                    class="w-full h-full object-fit transition duration-300 hover:scale-105">
                                <div class="absolute bottom-0 left-0 w-full bg-black bg-opacity-50 text-white p-2">
                                    <h2 class="text-lg font-bold"><?php echo htmlspecialchars($event['name']); ?></h2>
                                    <h3 class="text-sm"><?php echo htmlspecialchars($event['description']); ?></h3>
                                </div>
                            </div>
                        </div>
                    <?php
                    endwhile;
                else:
                    ?>
                    <div class="swiper-slide text-center py-8">
                        <div class="text-blue-600 bg-blue-50 px-4 py-3 rounded-lg shadow-md">No featured events available.</div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tambahkan navigasi -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
        </div>
    </div>

    <!-- Content -->
    <div class="container mx-auto mt-10 px-4 flex-grow">
        <div class="text-center mb-6 fade-in">
            <h1 class="text-4xl font-bold text-black">All Events</h1>
        </div>

        <!-- Modal for Confirmation -->
        <div id="modal" class="fixed top-0 left-0 w-full h-full flex items-center justify-center bg-gray-900 bg-opacity-50 hidden z-50">
            <div class="bg-white w-11/12 md:w-1/3 p-6 rounded-lg shadow-lg relative">
                <h2 class="text-2xl font-bold mb-4 text-center">Registration Confirmation</h2>
                <form id="registration-form" method="POST" action="">
                    <input type="hidden" name="event_id" id="event_id">
                    <p class="text-center text-gray-700 mb-4">
                        Are you sure you want to register for this event?</p>
                    <div class="flex justify-center space-x-4">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-300">Yes</button>
                        <button type="button" onclick="closeModal('modal')" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition duration-300">No</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal for Success -->
        <div id="modal-success" class="fixed top-0 left-0 w-full h-full flex items-center justify-center bg-gray-900 bg-opacity-50 hidden z-50">
            <div class="bg-white w-11/12 md:w-1/3 p-6 rounded-lg shadow-lg relative">
                <button onclick="closeModal('modal-success')" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
                <div class="text-center">
                    <i class="fas fa-check-circle text-green-500 text-4xl mb-4 icon-animate"></i>
                    <h2 class="text-2xl font-bold mb-4 text-center text-green-600">Register Success!</h2>
                    <p class="text-center text-gray-700">You have successfully registered for this event.</p>
                    <button onclick="closeModal('modal-success')" class="bg-green-600 text-white px-4 py-2 mt-4 rounded hover:bg-green-700 transition duration-300">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Modal for Error -->
        <div id="modal-error" class="fixed top-0 left-0 w-full h-full flex items-center justify-center bg-gray-900 bg-opacity-50 hidden z-50">
            <div class="bg-white w-11/12 md:w-1/3 p-6 rounded-lg shadow-lg relative">
                <button onclick="closeModal('modal-error')" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
                <div class="text-center">
                    <i class="fas fa-times-circle text-red-500 text-4xl mb-4 icon-animate"></i>
                    <h2 class="text-2xl font-bold mb-4 text-center text-red-600">Register Failed!</h2>
                    <p class="text-center text-gray-700"><?php echo $error; ?></p>
                    <button onclick="closeModal('modal-error')" class="bg-red-600 text-white px-4 py-2 mt-4 rounded hover:bg-red-700 transition duration-300">Cancel</button>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-lg p-6 fade-in">
            <?php if ($events->num_rows > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php while ($event = $events->fetch_assoc()): ?>
                        <div id="event-<?php echo $event['id']; ?>" class="bg-gray-50 p-6 rounded-lg shadow-md card cursor-pointer fade-in" onclick="toggleDetails(<?php echo $event['id']; ?>)">
                            <img src="../uploads/<?php echo $event['banner_image']; ?>" alt="Banner" class="w-full h-48 object-fit object-center transition duration-300 hover:opacity-90">
                            <h2 class="mt-4 text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($event['name']); ?></h2>
                            <div id="details-<?php echo $event['id']; ?>" class="hidden mt-4">
                                <div class="text-left text-base font-bold text-black">
                                    <p class="flex items-center space-x-3 text-gray-700">
                                        <i class="fas fa-info-circle text-green-500 m-1"></i>
                                        <span><?php echo htmlspecialchars($event['description']); ?></span>
                                    </p>

                                    <p class="flex items-center space-x-3 text-gray-700">
                                        <i class="fas fa-calendar-alt text-red-500 m-1"></i>
                                        <span><?php echo htmlspecialchars($event['event_date']); ?></span>
                                    </p>

                                    <p class="flex items-center space-x-3 text-gray-700">
                                        <i class="fas fa-clock text-yellow-500 m-1"></i>
                                        <span><?php echo htmlspecialchars($event['event_time']); ?></span>
                                    </p>

                                    <p class="flex items-center space-x-3 text-gray-700">
                                        <i class="fas fa-map-marker-alt text-blue-500 m-2"></i>
                                        <span><?php echo htmlspecialchars($event['location']); ?></span>
                                    </p>
                                </div>

                                <div class="mt-4">
                                    <?php
                                    $event_id = $event['id'];
                                    $registrants = $conn->query("SELECT COUNT(*) as total FROM registrations WHERE event_id = $event_id");
                                    $total_registrants = $registrants->fetch_assoc()['total'];
                                    ?>
                                    <?php if ($total_registrants < $event['max_participants']): ?>
                                        <button onclick="openModal('<?php echo $event['id']; ?>')" class="bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600 transition duration-300">
                                            <i class="fas fa-check-circle"></i> Register
                                        </button>
                                    <?php else: ?>
                                        <span class="text-red-500"><i class="fas fa-times-circle"></i> Event is Full</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <div class="text-blue-600 bg-blue-50 px-4 py-3 rounded-lg shadow-md">No events available.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>


    <script>
        // Inisialisasi Swiper.js
        var swiper = new Swiper('.swiper-container', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            effect: 'fade', // Menggunakan efek fade
            fadeEffect: {
                crossFade: true
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
        });

        document.getElementById("navbar-toggle").onclick = function() {
            var menu = document.getElementById("navbar-menu");
            menu.classList.toggle("hidden");
        };

        // Toggle details visibility
        function toggleDetails(eventId) {
            var details = document.getElementById('details-' + eventId);
            details.classList.toggle('hidden');
        }


        function openModal(eventId) {
            document.getElementById('event_id').value = eventId;
            document.getElementById('modal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        <?php if ($success): ?>
            document.getElementById('modal-success').classList.remove('hidden');
        <?php elseif ($error): ?>
            document.getElementById('modal-error').classList.remove('hidden');
        <?php endif; ?>
    </script>
    </script>
</body>

</html>