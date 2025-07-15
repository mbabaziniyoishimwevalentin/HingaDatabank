<?php
session_start();

// Redirect logged in users to their dashboard
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            exit();
        case 'institution':
            header("Location: institution/dashboard.php");
            exit();
        case 'farmer':
            header("Location: farmer/dashboard.php");
            exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HingaDatabank Platform - Empowering Agricultural Growth</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'agri-green': '#22c55e',
                        'agri-dark-green': '#16a34a',
                        'agri-light-green': '#bbf7d0',
                        'agri-brown': '#a3a3a3',
                        'agri-gold': '#fbbf24'
                    },
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        .hero-bg {
            background: linear-gradient(135deg, #059669 0%, #16a34a 50%, #22c55e 100%);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .fade-in {
            animation: fadeIn 1s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .pulse-green {
            animation: pulseGreen 2s ease-in-out infinite;
        }
        
        @keyframes pulseGreen {
            0%, 100% { background-color: #22c55e; }
            50% { background-color: #16a34a; }
        }
        
        .gradient-text {
            background: linear-gradient(45deg, #22c55e, #16a34a);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="font-inter bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="w-8 h-8 bg-agri-green rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2L3 7v11h4v-6h6v6h4V7l-7-5z"/>
                            </svg>
                        </div>
                        <span class="text-2xl font-bold gradient-text">HingaDatabank</span>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="#features" class="text-gray-600 hover:text-agri-green px-3 py-2 rounded-md text-sm font-medium transition-colors">Features</a>
                        <a href="#how-it-works" class="text-gray-600 hover:text-agri-green px-3 py-2 rounded-md text-sm font-medium transition-colors">How It Works</a>
                        <a href="#testimonials" class="text-gray-600 hover:text-agri-green px-3 py-2 rounded-md text-sm font-medium transition-colors">Testimonials</a>
                        <a href="#contact" class="text-gray-600 hover:text-agri-green px-3 py-2 rounded-md text-sm font-medium transition-colors">Contact</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="login.php" class="text-agri-green hover:text-agri-dark-green font-medium transition-colors">Login</a>
                    <a href="register.php" class="bg-agri-green hover:bg-agri-dark-green text-white px-4 py-2 rounded-lg font-medium transition-all duration-300 transform hover:scale-105">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg min-h-screen flex items-center relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 left-10 w-20 h-20 bg-white bg-opacity-10 rounded-full float-animation"></div>
            <div class="absolute top-40 right-20 w-16 h-16 bg-white bg-opacity-10 rounded-full float-animation" style="animation-delay: -2s;"></div>
            <div class="absolute bottom-20 left-1/4 w-24 h-24 bg-white bg-opacity-10 rounded-full float-animation" style="animation-delay: -4s;"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="text-white fade-in">
                    <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">
                        Empowering <span class="text-agri-gold">Agricultural</span> Growth
                    </h1>
                    <p class="text-xl mb-8 text-gray-100 leading-relaxed">
                        Connect farmers with financial institutions, manage agricultural activities, and drive sustainable farming practices with our comprehensive platform.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <a href="register.php" class="bg-white text-agri-green px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            Start Your Journey
                        </a>
                        <a href="login.php" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-agri-green transition-all duration-300 transform hover:scale-105">
                            Sign In
                        </a>
                    </div>
                    <div class="flex items-center space-x-8">
                        <div class="text-center">
                            <div class="text-2xl font-bold">10,000+</div>
                            <div class="text-sm text-gray-200">Active Farmers</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold">500+</div>
                            <div class="text-sm text-gray-200">Partner Institutions</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold">$50M+</div>
                            <div class="text-sm text-gray-200">Loans Facilitated</div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-3xl p-8 shadow-2xl">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="bg-white bg-opacity-20 rounded-xl p-6 text-center">
                                <div class="w-12 h-12 bg-agri-gold rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h3 class="text-white font-semibold">Easy Financing</h3>
                                <p class="text-gray-200 text-sm mt-2">Quick loan applications</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-xl p-6 text-center">
                                <div class="w-12 h-12 bg-agri-gold rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h3 class="text-white font-semibold">Farm Management</h3>
                                <p class="text-gray-200 text-sm mt-2">Track your activities</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-xl p-6 text-center">
                                <div class="w-12 h-12 bg-agri-gold rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 2C5.589 2 2 5.589 2 10s3.589 8 8 8 8-3.589 8-8-3.589-8-8-8zm3.707 9.293a1 1 0 00-1.414-1.414L9 13.172 7.707 11.88a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h3 class="text-white font-semibold">Expert Support</h3>
                                <p class="text-gray-200 text-sm mt-2">24/7 assistance</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-xl p-6 text-center">
                                <div class="w-12 h-12 bg-agri-gold rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h3 class="text-white font-semibold">Growth Analytics</h3>
                                <p class="text-gray-200 text-sm mt-2">Performance insights</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Why Choose HingaDatabank?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Our platform bridges the gap between farmers and financial institutions, providing tools and resources for sustainable agricultural growth.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="card-hover bg-gradient-to-br from-agri-light-green to-white rounded-2xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-agri-green rounded-full flex items-center justify-center mb-6 pulse-green">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Smart Financing</h3>
                    <p class="text-gray-600 mb-6">Access tailored financial solutions with competitive rates and flexible repayment terms designed for agricultural cycles.</p>
                    <ul class="space-y-2">
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-agri-green mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Quick loan approval process
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-agri-green mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Seasonal payment options
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-agri-green mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Competitive interest rates
                        </li>
                    </ul>
                </div>
                
                <div class="card-hover bg-gradient-to-br from-blue-50 to-white rounded-2xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Farm Management</h3>
                    <p class="text-gray-600 mb-6">Comprehensive tools to track, monitor, and optimize your agricultural operations for maximum productivity.</p>
                    <ul class="space-y-2">
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Crop planning & scheduling
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Inventory management
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Expense tracking
                        </li>
                    </ul>
                </div>
                
                <div class="card-hover bg-gradient-to-br from-yellow-50 to-white rounded-2xl p-8 shadow-lg">
                    <div class="w-16 h-16 bg-agri-gold rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Analytics & Insights</h3>
                    <p class="text-gray-600 mb-6">Data-driven insights to help you make informed decisions and maximize your agricultural returns.</p>
                    <ul class="space-y-2">
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-agri-gold mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Performance analytics
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-agri-gold mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Market predictions
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-agri-gold mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Financial reports
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Get started with HingaDatabank in three simple steps and transform your agricultural operations.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 bg-agri-green rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-2xl font-bold text-white">1</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Sign Up & Verify</h3>
                    <p class="text-gray-600">Create your account and verify your identity to access our platform's features and financial services.</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 bg-agri-green rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-2xl font-bold text-white">2</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Connect & Apply</h3>
                    <p class="text-gray-600">Connect with partner financial institutions and apply for loans or financial products that suit your needs.</p>
                </div>
                
                <div class="text-center">
                    <div class="w-20 h-20 bg-agri-green rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                        <span class="text-2xl font-bold text-white">3</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Manage & Grow</h3>
                    <p class="text-gray-600">Use our tools to manage your farm operations, track progress, and grow your agricultural business successfully.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-agri-green to-agri-dark-green">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-4">Ready to Transform Your Farm?</h2>
            <p class="text-xl text-gray-100 mb-8 max-w-2xl mx-auto">
                Join thousands of farmers who have already improved their operations and secured financing through HingaDatabank.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="register.php" class="bg-white text-agri-green px-8 py-4 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Get Started Today
                </a>
                <a href="login.php" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-agri-green transition-all duration-300 transform hover:scale-105">
                    Sign In
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-agri-green rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2L3 7v11h4v-6h6v6h4V7l-7-5z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold">HingaDatabank</span>
                    </div>
                    <p class="text-gray-400">Empowering farmers through smart financial solutions and modern farm management tools.</p>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Platform</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#features" class="hover:text-agri-green transition-colors">Features</a></li>
                        <li><a href="#how-it-works" class="hover:text-agri-green transition-colors">How It Works</a></li>
                        <li><a href="#" class="hover:text-agri-green transition-colors">Pricing</a></li>
                        <li><a href="#" class="hover:text-agri-green transition-colors">Resources</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-agri-green transition-colors">Help Center</a></li>
                        <li><a href="#contact" class="hover:text-agri-green transition-colors">Contact Us</a></li>
                        <li><a href="#" class="hover:text-agri-green transition-colors">Documentation</a></li>
                        <li><a href="#" class="hover:text-agri-green transition-colors">API</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Connect</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-agri-green transition-colors">Twitter</a></li>
                        <li><a href="#" class="hover:text-agri-green transition-colors">LinkedIn</a></li>
                        <li><a href="#" class="hover:text-agri-green transition-colors">Facebook</a></li>
                        <li><a href="#" class="hover:text-agri-green transition-colors">Instagram</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 HingaDatabank Platform. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Add smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add fade-in animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards and sections
        document.querySelectorAll('.card-hover, section').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });

        // Add mobile menu toggle functionality
        const mobileMenuButton = document.createElement('button');
        mobileMenuButton.innerHTML = `
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        `;
        mobileMenuButton.className = 'md:hidden text-gray-600 hover:text-agri-green focus:outline-none';
        
        // Add mobile menu functionality
        const nav = document.querySelector('nav .max-w-7xl');
        const navItems = nav.querySelector('.hidden.md\\:block');
        const navButtons = nav.querySelector('.flex.items-center.space-x-4');
        
        // Insert mobile menu button
        nav.querySelector('.flex.justify-between').appendChild(mobileMenuButton);
        
        let mobileMenuOpen = false;
        mobileMenuButton.addEventListener('click', () => {
            mobileMenuOpen = !mobileMenuOpen;
            if (mobileMenuOpen) {
                const mobileMenu = document.createElement('div');
                mobileMenu.className = 'md:hidden absolute top-16 left-0 right-0 bg-white shadow-lg z-50';
                mobileMenu.innerHTML = `
                    <div class="px-4 py-2 space-y-2">
                        <a href="#features" class="block px-3 py-2 text-gray-600 hover:text-agri-green">Features</a>
                        <a href="#how-it-works" class="block px-3 py-2 text-gray-600 hover:text-agri-green">How It Works</a>
                        <a href="#testimonials" class="block px-3 py-2 text-gray-600 hover:text-agri-green">Testimonials</a>
                        <a href="#contact" class="block px-3 py-2 text-gray-600 hover:text-agri-green">Contact</a>
                        <div class="border-t pt-2">
                            <a href="login.php" class="block px-3 py-2 text-agri-green hover:text-agri-dark-green">Login</a>
                            <a href="register.php" class="block px-3 py-2 bg-agri-green text-white rounded-lg text-center">Get Started</a>
                        </div>
                    </div>
                `;
                nav.appendChild(mobileMenu);
            } else {
                nav.querySelector('.md\\:hidden.absolute')?.remove();
            }
        });

        // PHP session redirect functionality
        // This is handled by the PHP code at the top of the file
        // The JavaScript below was just for demo purposes and is now removed
        
        // Add mobile menu toggle functionality
</body>
</html>