<?php
session_start();
include('config.php');

// Fetch featured business ideas (latest 6 published ideas)
$ideas_query = "SELECT r.title, r.description, r.type, r.file_url, r.link, c.name AS category_name, u.full_name AS agent_name
                FROM agent_business_resources r
                LEFT JOIN business_categories c ON r.category_id = c.id
                LEFT JOIN users u ON r.agent_id = u.id
                WHERE r.status = 'published' AND r.type = 'idea'
                ORDER BY r.created_at DESC LIMIT 6";
$ideas_result = mysqli_query($conn, $ideas_query);

// Fetch approved mentors (with profile pictures)
$mentors_query = "SELECT u.id, u.full_name, a.expertise, a.company_name, a.profile_picture
                  FROM users u
                  JOIN agent_profiles a ON u.id = a.user_id
                  WHERE u.role = 'agent' AND u.status = 'approved'
                  ORDER BY u.id DESC LIMIT 6";
$mentors_result = mysqli_query($conn, $mentors_query);

// Fetch recent educational resources / events (latest 3)
$edu_query = "SELECT title, description, type, video_url, event_date, location, link
              FROM educational_resources
              ORDER BY created_at DESC LIMIT 3";
$edu_result = mysqli_query($conn, $edu_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrepreneur Portal - Launch Your Startup Journey</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary: #0d6efd;
            --success: #198754;
            --accent: #198754;
        }
        .hero {
            height: 80vh;
            min-height: 500px;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=1600&q=80') center/cover no-repeat;
            color: white;
            display: flex;
            align-items: center;
            text-align: center;
        }
        .hero h1 { font-size: 4rem; font-weight: 700; }
        .hero p { font-size: 1.4rem; max-width: 700px; margin: 0 auto 30px; }
        .btn-hero { padding: 14px 40px; font-size: 1.3rem; border-radius: 50px; }
        .card-hover:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(0,0,0,0.15); transition: all 0.3s; }
        .mentor-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .section-title { position: relative; margin-bottom: 50px; font-weight: 700; }
        .section-title::after { content: ''; width: 80px; height: 5px; background: var(--accent); position: absolute; bottom: -15px; left: 50%; transform: translateX(-50%); }
        footer { background: #212529; color: white; padding: 40px 0; }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1 class="display-4 fw-bold text-white">Launch Your Startup Journey</h1>
        <p class="lead">Access curated business ideas, templates, mentors, financial resources, and a vibrant community to turn your vision into reality.</p>
        <?php if (isset($_SESSION['id'])): ?>
            <a href="<?php echo ($_SESSION['role'] === 'user') ? 'user/user_dashboard.php' : (($_SESSION['role'] === 'agent') ? 'agent/agent_dashboard.php' : 'admin/admin_dashboard.php'); ?>" 
               class="btn btn-success btn-lg btn-hero">
                <i class="fas fa-tachometer-alt mr-2"></i> Go to Dashboard
            </a>
        <?php else: ?>
            <div class="mt-4">
                <a href="register.php" class="btn btn-success btn-lg btn-hero mr-3">Get Started Free</a>
                <a href="login.php" class="btn btn-outline-light btn-lg btn-hero">Login</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Featured Business Ideas -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center section-title">Featured Business Ideas</h2>
        <div class="row">
            <?php if (mysqli_num_rows($ideas_result) > 0): ?>
                <?php while ($idea = mysqli_fetch_assoc($ideas_result)): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 card-hover">
                            <div class="card-body">
                                <span class="badge badge-success mb-2"><?= ucfirst($idea['type']) ?></span>
                                <h5 class="card-title"><?= htmlspecialchars($idea['title']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars(substr($idea['description'], 0, 120)) ?>...</p>
                                <p class="small"><strong>Category:</strong> <?= htmlspecialchars($idea['category_name'] ?: 'General') ?></p>
                                <p class="small"><strong>By:</strong> <?= htmlspecialchars($idea['agent_name']) ?></p>
                                <a href="login.php" class="btn btn-sm btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center"><p class="text-muted">No featured ideas yet.</p></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h2 class="text-center section-title">Meet Our Mentors</h2>
        <div class="row justify-content-center">
            <?php if (mysqli_num_rows($mentors_result) > 0): ?>
                <?php while ($mentor = mysqli_fetch_assoc($mentors_result)): ?>
                    <div class="col-md-4 col-lg-2 mb-4 text-center">

                        <?php
                        $profile_pic = $mentor['profile_picture']
                            ? htmlspecialchars($mentor['profile_picture'])
                            : 'images/default-profile.png'; 
                        ?>

                        <img src="<?= $profile_pic ?>"
                             class="rounded-circle mentor-img mb-3"
                             alt="<?= htmlspecialchars($mentor['full_name']) ?>">

                        <h6 class="mb-1"><?= htmlspecialchars($mentor['full_name']) ?></h6>
                        <p class="small text-muted"><?= htmlspecialchars($mentor['expertise'] ?: 'Entrepreneur') ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center"><p class="text-muted">No mentors yet.</p></div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <a href="login.php" class="btn btn-outline-primary btn-lg">Connect with Mentors</a>
        </div>
    </div>
</section>

<!-- Educational Resources & Events -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center section-title">Learn & Grow</h2>
        <div class="row">
            <?php if (mysqli_num_rows($edu_result) > 0): ?>
                <?php while ($edu = mysqli_fetch_assoc($edu_result)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 card-hover">
                            <div class="card-body">
                                <span class="badge badge-info mb-2"><?= ucfirst($edu['type']) ?></span>
                                <h5 class="card-title"><?= htmlspecialchars($edu['title']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars(substr($edu['description'], 0, 100)) ?>...</p>
                                <?php if ($edu['type'] === 'event' && $edu['event_date']): ?>
                                    <p class="small"><strong>Date:</strong> <?= date('M d, Y', strtotime($edu['event_date'])) ?></p>
                                <?php endif; ?>
                                <a href="login.php" class="btn btn-sm btn-success">Learn More</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center"><p class="text-muted">No educational content yet.</p></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Why Join Us -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center section-title">Why Join the Entrepreneur Portal?</h2>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-lightbulb fa-4x text-success mb-3"></i>
                        <h5>Curated Business Ideas</h5>
                        <p>Discover proven ideas and frameworks from experienced entrepreneurs.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-user-tie fa-4x text-primary mb-3"></i>
                        <h5>Expert Mentorship</h5>
                        <p>Connect with approved mentors who can guide your startup journey.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-comments fa-4x text-info mb-3"></i>
                        <h5>Community & Forums</h5>
                        <p>Discuss ideas, get feedback, and build valuable connections.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="text-center">
    <div class="container">
        <p class="mb-0">© <?= date('Y') ?> Entrepreneur Portal. All rights reserved.</p>
        <p class="mt-2"><a href="about.php" class="text-white">About</a> | <a href="contact.php" class="text-white">Contact</a></p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>