<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Administrative Court | Easeloader System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1E40AF', // Blue
                        secondary: '#F97316', // Orange
                    },
                    boxShadow: {
                        'card': '0 4px 20px rgba(0, 0, 0, 0.08)',
                        'card-hover': '0 10px 30px rgba(0, 0, 0, 0.12)',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-slide {
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transform: translateY(18px);
            transition: opacity 0.6s ease, transform 0.6s ease, max-height 0.6s ease;
            pointer-events: none;
        }
        .hero-slide.active {
            opacity: 1;
            max-height: 1400px;
            transform: translateY(0);
            pointer-events: auto;
        }
        .hero-dot {
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .hero-dot.active {
            transform: scaleX(1.35);
        }
        .hero-slider-glow {
            position: absolute;
            inset: -60px;
            background: radial-gradient(circle at top right, rgba(59,130,246,0.3), transparent 55%);
            filter: blur(10px);
            pointer-events: none;
            z-index: 0;
        }
        .stat-card {
            position: relative;
            overflow: hidden;
            background-color: #f8fafc;
        }
        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: -30%;
            width: 160%;
            height: 120%;
            background: radial-gradient(circle at 20% 20%, rgba(59,130,246,0.25), transparent 55%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        .stat-card-blue::after {
            background: radial-gradient(circle at 20% 30%, rgba(30,64,175,0.25), transparent 55%);
        }
        .stat-card-orange::after {
            background: radial-gradient(circle at 80% 10%, rgba(249,115,22,0.3), transparent 60%);
        }
        .stat-card-green::after {
            background: radial-gradient(circle at 80% 20%, rgba(5,150,105,0.25), transparent 60%);
        }
        .stat-card-purple::after {
            background: radial-gradient(circle at 20% 80%, rgba(124,58,237,0.25), transparent 60%);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
            box-shadow: 0 20px 35px rgba(15, 23, 42, 0.2);
        }
        }
        .service-card:hover {
            transform: translateY(-8px);
            transition: all 0.3s ease;
        }
        .process-step {
            position: relative;
        }
        .process-step:not(:last-child):after {
            content: '';
            position: absolute;
            top: 50%;
            right: 0;
            width: 100%;
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }
        @media (max-width: 768px) {
            .process-step:not(:last-child):after {
                display: none;
            }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- Header -->
    <header class="bg-gradient-to-r from-primary to-blue-900 text-white sticky top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-balance-scale text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold">Administrative Court</h1>
                    <p class="text-xs text-blue-100">Easeloader System</p>
                </div>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-6">
                <nav class="flex items-center gap-6 text-sm" aria-label="Primary navigation">
                    <a href="#" class="hover:text-secondary font-medium transition-colors"><i class="fas fa-home mr-1"></i> Home</a>
                    <a href="#" class="hover:text-secondary font-medium transition-colors"><i class="fas fa-gavel mr-1"></i> Cases</a>
                    <a href="#" class="hover:text-secondary font-medium transition-colors"><i class="fas fa-file-contract mr-1"></i> Appeals</a>
                    <a href="#" class="hover:text-secondary font-medium transition-colors"><i class="fas fa-calendar-alt mr-1"></i> Calendar</a>
                </nav>
                <a href="#" class="inline-flex items-center bg-white text-primary px-4 py-2 rounded-lg hover:bg-gray-100 font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                    <i class="fas fa-sign-in-alt mr-1"></i> Login
                </a>
            </div>

            <!-- Mobile menu button -->
            <button id="mobile-menu-button" class="md:hidden text-white" aria-controls="mobile-menu" aria-expanded="false" aria-label="Open main menu">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <div id="mobile-menu" class="md:hidden bg-blue-900 px-4 py-4 hidden rounded-b-2xl shadow-lg border-t border-blue-800">
            <div class="flex flex-col space-y-4">
                <a href="#" class="text-white hover:text-secondary transition"><i class="fas fa-home mr-2"></i> Home</a>
                <a href="#" class="text-white hover:text-secondary transition"><i class="fas fa-gavel mr-2"></i> Cases</a>
                <a href="#" class="text-white hover:text-secondary transition"><i class="fas fa-file-contract mr-2"></i> Appeals</a>
                <a href="#" class="text-white hover:text-secondary transition"><i class="fas fa-calendar-alt mr-2"></i> Calendar</a>
                <a href="#" class="text-white hover:text-secondary transition"><i class="fas fa-sign-in-alt mr-2"></i> Login</a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="bg-white relative overflow-hidden">
        <div id="hero-slider" class="relative hero-slider" role="region" aria-live="polite" aria-label="Administrative Court highlights">
            <div class="hero-slider-glow"></div>
            <!-- Slide 1 -->
            <div class="hero-slide active" role="group" aria-label="Digital Access to Administrative Justice">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16 md:py-24 grid md:grid-cols-2 gap-12 items-center relative z-10">
                    <div>
                        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-primary mb-6 leading-tight">
                            Digital Access to Administrative Justice
                        </h2>
                        <p class="text-gray-600 mb-8 text-lg">
                            Submit appeals, track cases, and manage court processes efficiently through our easeloader system. Streamlined, transparent, and accessible.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="#" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-800 transition flex items-center justify-center shadow-md">
                                <i class="fas fa-file-upload mr-2"></i> File an Appeal
                            </a>
                            <a href="#" class="border border-secondary text-secondary px-6 py-3 rounded-lg hover:bg-secondary hover:text-white transition flex items-center justify-center">
                                <i class="fas fa-search mr-2"></i> Track Case
                            </a>
                        </div>
                    </div>
                    <div class="hidden md:block relative">
                        <div class="w-full h-72 bg-gradient-to-br from-primary to-secondary rounded-2xl shadow-2xl flex items-center justify-center">
                            <div class="text-white text-center p-6">
                                <i class="fas fa-balance-scale text-6xl opacity-80 mb-4"></i>
                                <p class="text-xl font-bold">E-Justice Portal</p>
                                <p class="text-blue-100">Secure &bull; Efficient &bull; Transparent</p>
                            </div>
                        </div>
                        <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-secondary/20 rounded-2xl -z-10"></div>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="hero-slide" role="group" aria-label="Transparent & Efficient Case Management">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16 md:py-24 grid md:grid-cols-2 gap-12 items-center relative z-10">
                    <div>
                        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-primary mb-6 leading-tight">
                            Transparent & Efficient Case Management
                        </h2>
                        <p class="text-gray-600 mb-8 text-lg">
                            Judges, clerks, and citizens collaborate securely in one centralized system. Real-time updates and comprehensive tracking.
                        </p>
                        <a href="#" class="bg-secondary text-white px-6 py-3 rounded-lg hover:bg-orange-600 transition inline-flex items-center shadow-md">
                            <i class="fas fa-info-circle mr-2"></i> Learn More
                        </a>
                    </div>
                    <div class="hidden md:block relative">
                        <div class="w-full h-72 bg-gradient-to-br from-secondary to-primary rounded-2xl shadow-2xl flex items-center justify-center">
                            <div class="text-white text-center p-6">
                                <i class="fas fa-chart-line text-6xl opacity-80 mb-4"></i>
                                <p class="text-xl font-bold">Real-Time Analytics</p>
                                <p class="text-blue-100">Monitor &bull; Analyze &bull; Decide</p>
                            </div>
                        </div>
                        <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-primary/20 rounded-2xl -z-10"></div>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="hero-slide" role="group" aria-label="Secure & Role-Based Court Access">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 py-16 md:py-24 grid md:grid-cols-2 gap-12 items-center relative z-10">
                    <div>
                        <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-primary mb-6 leading-tight">
                            Secure & Role-Based Court Access
                        </h2>
                        <p class="text-gray-600 mb-8 text-lg">
                            Ensuring confidentiality, accountability, and lawful digital procedures with advanced security protocols.
                        </p>
                        <a href="#" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-blue-800 transition inline-flex items-center shadow-md">
                            <i class="fas fa-lock mr-2"></i> System Login
                        </a>
                    </div>
                    <div class="hidden md:block relative">
                        <div class="w-full h-72 bg-gradient-to-br from-primary to-secondary rounded-2xl shadow-2xl flex items-center justify-center">
                            <div class="text-white text-center p-6">
                                <i class="fas fa-shield-alt text-6xl opacity-80 mb-4"></i>
                                <p class="text-xl font-bold">Secure Access</p>
                                <p class="text-blue-100">Protected &bull; Role-Based &bull; Compliant</p>
                            </div>
                        </div>
                        <div class="absolute -top-4 -right-4 w-32 h-32 bg-secondary/20 rounded-2xl -z-10"></div>
                    </div>
                </div>
            </div>

            <!-- Slider Controls -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-3">
                <div class="flex items-center gap-3 rounded-full bg-white/90 backdrop-blur-md px-4 py-2 shadow-xl">
                    <button type="button" class="dot hero-dot w-10 h-2 rounded-full bg-primary" data-index="0" aria-label="Go to slide 1: Digital Access to Administrative Justice" aria-pressed="true"></button>
                    <button type="button" class="dot hero-dot w-10 h-2 rounded-full bg-gray-300 hover:bg-gray-400" data-index="1" aria-label="Go to slide 2: Transparent & Efficient Case Management" aria-pressed="false"></button>
                    <button type="button" class="dot hero-dot w-10 h-2 rounded-full bg-gray-300 hover:bg-gray-400" data-index="2" aria-label="Go to slide 3: Secure & Role-Based Court Access" aria-pressed="false"></button>
                </div>
                <span id="hero-slide-status" class="sr-only">Slide 1 of 3</span>
            </div>
            
            <!-- Manual Slider Controls -->
            <button id="prev-slide" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 text-primary w-10 h-10 rounded-full shadow-md hidden md:flex items-center justify-center hover:bg-white" aria-label="Previous slide">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button id="next-slide" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 text-primary w-10 h-10 rounded-full shadow-md hidden md:flex items-center justify-center hover:bg-white" aria-label="Next slide">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </section>

    <!-- Insights -->
    <section class="bg-gradient-to-b from-blue-50 to-white py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-10">
                <div>
                    <p class="text-sm uppercase tracking-wider text-gray-400">Court Intelligence</p>
                    <h3 class="text-2xl md:text-3xl font-bold text-primary">Operational Insights</h3>
                </div>
                <a href="#" class="text-primary font-medium hover:text-blue-800 inline-flex items-center gap-2">
                    View dashboards <i class="fas fa-arrow-right text-sm"></i>
                </a>
            </div>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-white p-8 rounded-3xl shadow-card border border-gray-100">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-primary/10 text-primary rounded-2xl flex items-center justify-center">
                                <i class="fas fa-tachometer-alt text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm uppercase tracking-wide text-gray-400">Resolution Pace</p>
                                <p class="text-3xl font-bold text-primary">32 days</p>
                            </div>
                        </div>
                        <div class="text-xs font-semibold inline-flex items-center gap-1 text-green-600 bg-green-100 px-3 py-1 rounded-full">
                            <i class="fas fa-arrow-down"></i> -6%
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm">Accelerated workflows and hearing automation keep cases moving.</p>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-card border border-gray-100">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-secondary/10 text-secondary rounded-2xl flex items-center justify-center">
                                <i class="fas fa-file-invoice text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm uppercase tracking-wide text-gray-400">Digital Confidence</p>
                                <p class="text-3xl font-bold text-secondary">76%</p>
                            </div>
                        </div>
                        <div class="text-xs font-semibold inline-flex items-center gap-1 text-primary bg-primary/10 px-3 py-1 rounded-full">
                            <i class="fas fa-check-circle"></i> 98% verified
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm">Paperless submissions and live audit trails keep verification instant.</p>
                </div>
                <div class="bg-white p-8 rounded-3xl shadow-card border border-gray-100">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm uppercase tracking-wide text-gray-400">Citizen Trust</p>
                                <p class="text-3xl font-bold text-green-600">4.9 / 5</p>
                            </div>
                        </div>
                        <div class="text-xs font-semibold inline-flex items-center gap-1 text-blue-600 bg-blue-100 px-3 py-1 rounded-full">
                            <i class="fas fa-award"></i> 180+ partners
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm">Judges, clerks, and appellants rate the experience as reliable and fair.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Case Statistics -->
    <section class="bg-white py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <h3 class="text-2xl md:text-3xl font-bold text-primary mb-10 text-center">Court Case Overview</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="stat-card stat-card-blue border-l-4 border-primary p-6 rounded-xl shadow-card hover:shadow-card-hover transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Total Cases</p>
                            <p class="text-3xl font-bold text-primary mt-2">1,248</p>
                        </div>
                        <i class="fas fa-folder-open text-primary text-2xl"></i>
                    </div>
                    <p class="text-xs text-gray-500 mt-4">+12% from last month</p>
                </div>
                <div class="stat-card stat-card-orange border-l-4 border-secondary p-6 rounded-xl shadow-card hover:shadow-card-hover transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Pending</p>
                            <p class="text-3xl font-bold text-secondary mt-2">342</p>
                        </div>
                        <i class="fas fa-clock text-secondary text-2xl"></i>
                    </div>
                    <p class="text-xs text-gray-500 mt-4">-8% from last month</p>
                </div>
                <div class="stat-card stat-card-green border-l-4 border-green-600 p-6 rounded-xl shadow-card hover:shadow-card-hover transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Resolved</p>
                            <p class="text-3xl font-bold text-green-600 mt-2">876</p>
                        </div>
                        <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                    </div>
                    <p class="text-xs text-gray-500 mt-4">+15% from last month</p>
                </div>
                <div class="stat-card stat-card-purple border-l-4 border-purple-600 p-6 rounded-xl shadow-card hover:shadow-card-hover transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500">Appeals Filed</p>
                            <p class="text-3xl font-bold text-purple-600 mt-2">210</p>
                        </div>
                        <i class="fas fa-file-alt text-purple-600 text-2xl"></i>
                    </div>
                    <p class="text-xs text-gray-500 mt-4">+22% from last month</p>
                </div>
            </div>
            <div class="mt-10 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between text-sm text-gray-500">
                <p>Updated 2 minutes ago • Live data from the Easeloader registry.</p>
                <a href="#" class="text-primary font-medium inline-flex items-center gap-2 hover:text-blue-800">
                    Download trend report <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Services -->
    <section class="bg-gray-100 py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <h3 class="text-2xl md:text-3xl font-bold text-primary mb-12 text-center">Tribunal Services</h3>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="service-card bg-white p-8 rounded-2xl shadow-card hover:shadow-card-hover transition-all">
                    <div class="w-12 h-12 bg-blue-100 text-primary rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-file-upload text-xl"></i>
                    </div>
                    <h4 class="text-xl font-semibold mb-3">Appeal Submission</h4>
                    <p class="text-gray-600 mb-6">Secure digital submission of administrative appeals with document upload and verification.</p>
                    <a href="#" class="text-primary font-medium hover:text-blue-800 inline-flex items-center">
                        Learn more <i class="fas fa-arrow-right ml-2 text-sm"></i>
                    </a>
                </div>
                <div class="service-card bg-white p-8 rounded-2xl shadow-card hover:shadow-card-hover transition-all">
                    <div class="w-12 h-12 bg-orange-100 text-secondary rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-gavel text-xl"></i>
                    </div>
                    <h4 class="text-xl font-semibold mb-3">Hearing Management</h4>
                    <p class="text-gray-600 mb-6">Scheduling, notices, and hearing documentation with automated reminders.</p>
                    <a href="#" class="text-primary font-medium hover:text-blue-800 inline-flex items-center">
                        Learn more <i class="fas fa-arrow-right ml-2 text-sm"></i>
                    </a>
                </div>
                <div class="service-card bg-white p-8 rounded-2xl shadow-card hover:shadow-card-hover transition-all">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mb-6">
                        <i class="fas fa-bullhorn text-xl"></i>
                    </div>
                    <h4 class="text-xl font-semibold mb-3">Decision Publishing</h4>
                    <p class="text-gray-600 mb-6">Transparent and timely publication of tribunal decisions with secure access controls.</p>
                    <a href="#" class="text-primary font-medium hover:text-blue-800 inline-flex items-center">
                        Learn more <i class="fas fa-arrow-right ml-2 text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Process -->
    <section class="bg-white py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <h3 class="text-2xl md:text-3xl font-bold text-primary mb-12 text-center">Appeal Process</h3>
            <div class="grid md:grid-cols-4 gap-6 relative">
                <div class="process-step p-6 text-center relative z-10">
                    <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 shadow-md">1</div>
                    <p class="font-bold text-lg mb-2">File Appeal</p>
                    <p class="text-gray-600 text-sm">Submit appeal with required documentation</p>
                </div>
                <div class="process-step p-6 text-center relative z-10">
                    <div class="w-16 h-16 bg-secondary text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 shadow-md">2</div>
                    <p class="font-bold text-lg mb-2">Review & Validation</p>
                    <p class="text-gray-600 text-sm">Court reviews and validates submitted appeal</p>
                </div>
                <div class="process-step p-6 text-center relative z-10">
                    <div class="w-16 h-16 bg-primary text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 shadow-md">3</div>
                    <p class="font-bold text-lg mb-2">Hearing</p>
                    <p class="text-gray-600 text-sm">Scheduled hearing with all parties</p>
                </div>
                <div class="process-step p-6 text-center relative z-10">
                    <div class="w-16 h-16 bg-secondary text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4 shadow-md">4</div>
                    <p class="font-bold text-lg mb-2">Decision</p>
                    <p class="text-gray-600 text-sm">Final decision issued and published</p>
                </div>
            </div>
            <div class="text-center mt-10">
                <a href="#" class="inline-flex items-center text-primary font-medium hover:text-blue-800">
                    <i class="fas fa-book-open mr-2"></i> View detailed process guide
                </a>
            </div>
        </div>
    </section>

    <!-- Today Hearings -->
    <section class="bg-gray-100 py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8">
                <div>
                    <h3 class="text-2xl md:text-3xl font-bold text-primary">Today's Hearings</h3>
                    <p class="text-gray-600 mt-2">Hearings scheduled for <span class="font-medium">January 17, 2026</span></p>
                </div>
                <a href="#" class="text-secondary font-medium hover:text-orange-700 inline-flex items-center mt-4 md:mt-0">
                    View full schedule <i class="fas fa-arrow-right ml-2 text-sm"></i>
                </a>
            </div>

            <div class="overflow-x-auto bg-white rounded-2xl shadow-card">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-6 py-4 text-left font-medium">Case No</th>
                            <th class="px-6 py-4 text-left font-medium">Parties</th>
                            <th class="px-6 py-4 text-left font-medium">Hearing Type</th>
                            <th class="px-6 py-4 text-left font-medium">Time</th>
                            <th class="px-6 py-4 text-left font-medium">Court Room</th>
                            <th class="px-6 py-4 text-left font-medium">Status</th>
                            <th class="px-6 py-4 text-left font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold text-primary">ATC-2026-014</td>
                            <td class="px-6 py-4">Abebe Kebede vs Ministry of Revenue</td>
                            <td class="px-6 py-4">Merit Hearing</td>
                            <td class="px-6 py-4 font-medium">09:00 AM</td>
                            <td class="px-6 py-4">Room 2</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-medium">Scheduled</span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="#" class="text-primary hover:text-blue-800 text-sm font-medium">View Details</a>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold text-primary">ATC-2026-021</td>
                            <td class="px-6 py-4">Selam PLC vs City Administration</td>
                            <td class="px-6 py-4">Preliminary</td>
                            <td class="px-6 py-4 font-medium">11:30 AM</td>
                            <td class="px-6 py-4">Room 1</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-medium">Pending</span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="#" class="text-primary hover:text-blue-800 text-sm font-medium">View Details</a>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-bold text-primary">ATC-2026-033</td>
                            <td class="px-6 py-4">Hana Tesfaye vs Immigration Authority</td>
                            <td class="px-6 py-4">Decision Hearing</td>
                            <td class="px-6 py-4 font-medium">02:00 PM</td>
                            <td class="px-6 py-4">Room 3</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium">In Progress</span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="#" class="text-primary hover:text-blue-800 text-sm font-medium">View Details</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-8 text-center">
                <a href="#" class="inline-flex items-center text-primary font-medium hover:text-blue-800">
                    <i class="fas fa-video mr-2"></i> Access Virtual Hearing Portal
                </a>
            </div>
        </div>
    </section>

    <!-- Trust & Authority -->
    <section class="bg-gradient-to-r from-primary to-blue-800 text-white py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <h3 class="text-2xl md:text-3xl font-bold text-center mb-12">Why Choose Easeloader System</h3>
            <div class="grid md:grid-cols-3 gap-8 text-center">
                <div class="p-6">
                    <i class="fas fa-shield-alt text-4xl mb-6 opacity-90"></i>
                    <p class="text-3xl font-bold mb-2">100%</p>
                    <p class="text-lg opacity-90">Secure Digital Records</p>
                    <p class="text-sm opacity-80 mt-2">End-to-end encrypted with blockchain verification</p>
                </div>
                <div class="p-6">
                    <i class="fas fa-clock text-4xl mb-6 opacity-90"></i>
                    <p class="text-3xl font-bold mb-2">24/7</p>
                    <p class="text-lg opacity-90">Online Access</p>
                    <p class="text-sm opacity-80 mt-2">Access case files and submit documents anytime</p>
                </div>
                <div class="p-6">
                    <i class="fas fa-award text-4xl mb-6 opacity-90"></i>
                    <p class="text-3xl font-bold mb-2">Certified</p>
                    <p class="text-lg opacity-90">Legal Compliance</p>
                    <p class="text-sm opacity-80 mt-2">Fully compliant with national digital justice standards</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="bg-white py-12 md:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <h3 class="text-2xl md:text-3xl font-bold text-center text-primary mb-12">System Features</h3>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl shadow-card border border-gray-100">
                    <i class="fas fa-cloud-upload-alt text-primary text-3xl mb-6"></i>
                    <h4 class="font-bold text-xl mb-3">Online Filing</h4>
                    <p class="text-gray-600">Submit administrative appeals securely without visiting the court. Upload documents, pay fees, and get instant confirmation.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-card border border-gray-100">
                    <i class="fas fa-chart-bar text-primary text-3xl mb-6"></i>
                    <h4 class="font-bold text-xl mb-3">Case Tracking</h4>
                    <p class="text-gray-600">Monitor case status, hearings, and decisions in real time. Get notifications for important updates and deadlines.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl shadow-card border border-gray-100">
                    <i class="fas fa-user-shield text-primary text-3xl mb-6"></i>
                    <h4 class="font-bold text-xl mb-3">Role-Based Access</h4>
                    <p class="text-gray-600">Judges, clerks, and appellants access only what they need. Multi-level authentication and audit trails.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-primary text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-balance-scale text-white text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-lg">Administrative Court</h4>
                            <p class="text-blue-100 text-sm">Easeloader System</p>
                        </div>
                    </div>
                    <p class="text-sm text-blue-100">A digital platform for transparent and efficient administrative justice.</p>
                </div>
                
                <div>
                    <h5 class="font-bold text-lg mb-6">Quick Links</h5>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="text-blue-100 hover:text-white transition">File an Appeal</a></li>
                        <li><a href="#" class="text-blue-100 hover:text-white transition">Track Case Status</a></li>
                        <li><a href="#" class="text-blue-100 hover:text-white transition">Court Calendar</a></li>
                        <li><a href="#" class="text-blue-100 hover:text-white transition">Legal Resources</a></li>
                    </ul>
                </div>
                
                <div>
                    <h5 class="font-bold text-lg mb-6">Support</h5>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="text-blue-100 hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="text-blue-100 hover:text-white transition">FAQs</a></li>
                        <li><a href="#" class="text-blue-100 hover:text-white transition">Contact Us</a></li>
                        <li><a href="#" class="text-blue-100 hover:text-white transition">System Status</a></li>
                    </ul>
                </div>
                
                <div>
                    <h5 class="font-bold text-lg mb-6">Contact Info</h5>
                    <ul class="space-y-3 text-sm text-blue-100">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mr-3 mt-1"></i>
                            <span>Administrative Court Building, Justice Avenue, Addis Ababa</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-3"></i>
                            <span>+251 11 123 4567</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3"></i>
                            <span>support@court-easeloader.gov.et</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-blue-800 mt-12 pt-8 text-center text-sm text-blue-100">
                <p>&copy; 2026 Administrative Court Easeloader System. All rights reserved. | <a href="#" class="hover:text-white transition">Privacy Policy</a> | <a href="#" class="hover:text-white transition">Terms of Use</a></p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            mobileMenuButton.setAttribute('aria-expanded', (!isExpanded).toString());
        });

        // Hero slider functionality
        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.dot');
        const prevBtn = document.getElementById('prev-slide');
        const nextBtn = document.getElementById('next-slide');
        const heroStatus = document.getElementById('hero-slide-status');
        let currentSlide = 0;
        
        // Function to show a specific slide
        function showSlide(index) {
            slides.forEach((slide, idx) => {
                const isActive = idx === index;
                slide.classList.toggle('active', isActive);
                slide.setAttribute('aria-hidden', (!isActive).toString());
            });
            
            dots.forEach((dot, idx) => {
                const isActive = idx === index;
                dot.classList.toggle('bg-primary', isActive);
                dot.classList.toggle('bg-gray-300', !isActive);
                dot.setAttribute('aria-pressed', isActive.toString());
            });

            if (heroStatus) {
                heroStatus.textContent = `Slide ${index + 1} of ${slides.length}`;
            }
            
            currentSlide = index;
        }
        
        // Initialize the slider
        showSlide(0);
        
        // Dot click events
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                showSlide(index);
            });
        });
        
        // Previous button
        prevBtn.addEventListener('click', () => {
            let newIndex = currentSlide - 1;
            if (newIndex < 0) newIndex = slides.length - 1;
            showSlide(newIndex);
        });
        
        // Next button
        nextBtn.addEventListener('click', () => {
            let newIndex = currentSlide + 1;
            if (newIndex >= slides.length) newIndex = 0;
            showSlide(newIndex);
        });
        
        // Auto slide every 5 seconds
        setInterval(() => {
            let nextIndex = currentSlide + 1;
            if (nextIndex >= slides.length) nextIndex = 0;
            showSlide(nextIndex);
        }, 5000);
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
                if (!mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                }
                mobileMenuButton.setAttribute('aria-expanded', 'false');
            }
        });
    </script>
</body>
</html>
