<?php
require_once 'config/database.php';
$db = (new Database())->getConnection();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Email already exists.";
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'farmer')");
        if ($stmt->execute([$username, $email, $hash])) {
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registration failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HingaDatabank Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'agri-green': '#22c55e',
                        'agri-dark-green': '#16a34a',
                        'agri-light-green': '#bbf7d0',
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
        
        .register-bg {
            background: linear-gradient(135deg, #059669 0%, #16a34a 50%, #22c55e 100%);
        }
        
        .form-container {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
            border-color: #22c55e;
        }
        
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .slide-in {
            animation: slideIn 0.8s ease-out;
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0; 
                transform: translateX(-30px); 
            }
            to { 
                opacity: 1; 
                transform: translateX(0); 
            }
        }
        
        .slide-in-right {
            animation: slideInRight 0.8s ease-out;
        }
        
        @keyframes slideInRight {
            from { 
                opacity: 0; 
                transform: translateX(30px); 
            }
            to { 
                opacity: 1; 
                transform: translateX(0); 
            }
        }
        
        .pulse-green {
            animation: pulseGreen 2s ease-in-out infinite;
        }
        
        @keyframes pulseGreen {
            0%, 100% { background-color: #22c55e; }
            50% { background-color: #16a34a; }
        }
        
        .error-shake {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .password-strength {
            transition: all 0.3s ease;
        }
        
        .strength-weak { background-color: #ef4444; }
        .strength-medium { background-color: #f59e0b; }
        .strength-strong { background-color: #22c55e; }
    </style>
</head>
<body class="font-inter min-h-screen register-bg flex items-center justify-center p-4">
    <!-- Background Animation Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-20 left-10 w-20 h-20 bg-white bg-opacity-10 rounded-full float-animation"></div>
        <div class="absolute top-40 right-20 w-16 h-16 bg-white bg-opacity-10 rounded-full float-animation" style="animation-delay: -2s;"></div>
        <div class="absolute bottom-20 left-1/4 w-24 h-24 bg-white bg-opacity-10 rounded-full float-animation" style="animation-delay: -4s;"></div>
        <div class="absolute bottom-40 right-1/3 w-12 h-12 bg-white bg-opacity-10 rounded-full float-animation" style="animation-delay: -1s;"></div>
    </div>

    <div class="w-full max-w-6xl mx-auto grid lg:grid-cols-2 gap-8 items-center relative z-10">
        <!-- Left Side - Registration Form -->
        <div class="w-full max-w-md mx-auto lg:mx-0 order-2 lg:order-1">
            <div class="form-container rounded-2xl shadow-2xl p-8 slide-in">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-agri-green rounded-full flex items-center justify-center mx-auto mb-4 pulse-green">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Join HingaDatabank</h2>
                    <p class="text-gray-600">Create your account and start your agricultural journey</p>
                </div>

                <!-- Error Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg error-shake">
                        <?php foreach ($errors as $error): ?>
                            <div class="flex items-center mb-2 last:mb-0">
                                <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-red-700 font-medium"><?php echo htmlspecialchars($error); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form method="POST" class="space-y-6">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <input 
                                id="username"
                                name="username" 
                                type="text" 
                                placeholder="Enter your username" 
                                required 
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none input-focus transition-all duration-300"
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                </svg>
                            </div>
                            <input 
                                id="email"
                                name="email" 
                                type="email" 
                                placeholder="Enter your email address" 
                                required 
                                class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none input-focus transition-all duration-300"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <input 
                                id="password"
                                name="password" 
                                type="password" 
                                placeholder="Create a password" 
                                required 
                                class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none input-focus transition-all duration-300"
                                oninput="checkPasswordStrength()"
                            >
                            <button 
                                type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                onclick="togglePassword('password')"
                            >
                                <svg id="eye-open-1" class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <svg id="eye-closed-1" class="w-5 h-5 text-gray-400 hover:text-gray-600 hidden" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Password Strength Indicator -->
                        <div class="mt-2">
                            <div class="flex space-x-1">
                                <div id="strength-1" class="h-2 w-1/3 bg-gray-200 rounded password-strength"></div>
                                <div id="strength-2" class="h-2 w-1/3 bg-gray-200 rounded password-strength"></div>
                                <div id="strength-3" class="h-2 w-1/3 bg-gray-200 rounded password-strength"></div>
                            </div>
                            <p id="strength-text" class="text-sm text-gray-500 mt-1">Password strength</p>
                        </div>
                    </div>

                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <input 
                                id="confirm_password"
                                name="confirm_password" 
                                type="password" 
                                placeholder="Confirm your password" 
                                required 
                                class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none input-focus transition-all duration-300"
                                oninput="checkPasswordMatch()"
                            >
                            <button 
                                type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                onclick="togglePassword('confirm_password')"
                            >
                                <svg id="eye-open-2" class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <svg id="eye-closed-2" class="w-5 h-5 text-gray-400 hover:text-gray-600 hidden" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                </svg>
                            </button>
                        </div>
                        <div id="password-match" class="mt-1 text-sm hidden">
                            <span id="match-text"></span>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input 
                            id="terms" 
                            name="terms" 
                            type="checkbox" 
                            required
                            class="w-4 h-4 text-agri-green border-gray-300 rounded focus:ring-agri-green focus:ring-2"
                        >
                        <label for="terms" class="ml-2 block text-sm text-gray-700">
                            I agree to the <a href="#" class="text-agri-green hover:text-agri-dark-green font-medium">Terms of Service</a> and <a href="#" class="text-agri-green hover:text-agri-dark-green font-medium">Privacy Policy</a>
                        </label>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-agri-green hover:bg-agri-dark-green text-white font-semibold py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl"
                    >
                        Create Account
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-gray-600">
                        Already have an account? 
                        <a href="login.php" class="text-agri-green hover:text-agri-dark-green font-medium">Sign in here</a>
                    </p>
                </div>

                <!-- Back to Home -->
                <div class="mt-4 text-center">
                    <a href="index.php" class="text-gray-500 hover:text-gray-700 text-sm flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                        </svg>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Side - Welcome Content -->
        <div class="text-white slide-in-right hidden lg:block order-1 lg:order-2">
            <div class="mb-8">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2L3 7v11h4v-6h6v6h4V7l-7-5z"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold">HingaDatabank</h1>
                </div>
                <h2 class="text-4xl font-bold mb-4 leading-tight">
                    Start Your <span class="text-agri-gold">Agricultural</span> Success Story
                </h2>
                <p class="text-xl text-gray-100 mb-8 leading-relaxed">
                    Join thousands of farmers who are transforming their agricultural operations with our comprehensive financial platform.
                </p>
            </div>
            
            <!-- Feature Highlights -->
            <div class="grid grid-cols-1 gap-4 mb-8">
                <div class="flex items-center space-x-4 bg-white bg-opacity-10 rounded-lg p-4">
                    <div class="w-10 h-10 bg-agri-gold rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Quick & Easy Setup</h3>
                        <p class="text-sm text-gray-200">Get started in minutes with our streamlined registration process</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4 bg-white bg-opacity-10 rounded-lg p-4">
                    <div class="w-10 h-10 bg-agri-gold rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Comprehensive Dashboard</h3>
                        <p class="text-sm text-gray-200">Access all your financial tools in one intuitive interface</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4 bg-white bg-opacity-10 rounded-lg p-4">
                    <div class="w-10 h-10 bg-agri-gold rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Trusted & Secure</h3>
                        <p class="text-sm text-gray-200">Your data is protected with bank-level security standards</p>
                    </div>
                </div>
            </div>
            
            <!-- Benefits List -->
            <div class="space-y-3 mb-8">
                <h3 class="text-xl font-semibold text-agri-gold mb-4">What You'll Get:</h3>
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-agri-gold" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Expert guidance from agricultural professionals</span>
                </div>
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-agri-gold" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Connection with other farmers and institutions</span>
                </div>
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-agri-gold" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>24/7 customer support and assistance</span>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-agri-gold">15K+</div>
                    <div class="text-sm text-gray-200">New Users Monthly</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-agri-gold">98%</div>
                    <div class="text-sm text-gray-200">Success Rate</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-agri-gold">5⭐</div>
                    <div class="text-sm text-gray-200">User Rating</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeOpen = document.getElementById('eye-open-' + (inputId === 'password' ? '1' : '2'));
            const eyeClosed = document.getElementById('eye-closed-' + (inputId === 'password' ? '1' : '2'));
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBars = [
                document.getElementById('strength-1'),
                document.getElementById('strength-2'),
                document.getElementById('strength-3')
            ];
            const strengthText = document.getElementById('strength-text');
            
            // Reset all bars
            strengthBars.forEach(bar => {
                bar.className = 'h-2 w-1/3 bg-gray-200 rounded password-strength';
            });
            
            let strength = 0;
            let strengthLabel = 'Very Weak';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            if (strength >= 4) {
                strengthLabel = 'Strong';
                strengthBars[0].classList.add('strength-strong');
                strengthBars[1].classList.add('strength-strong');
                strengthBars[2].classList.add('strength-strong');
            } else if (strength >= 2) {
                strengthLabel = 'Medium';
                strengthBars[0].classList.add('strength-medium');
                strengthBars[1].classList.add('strength-medium');
            } else if (strength >= 1) {
                strengthLabel = 'Weak';
                strengthBars[0].classList.add('strength-weak');
            }
            
            strengthText.textContent = strengthLabel;
            strengthText.className = 'text-sm mt-1 ' + 
                (strength >= 4 ? 'text-green-600' : 
                 strength >= 2 ? 'text-yellow-600' : 
                 strength >= 1 ? 'text-red-600' : 'text-gray-500');
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('password-match');
            const matchText = document.getElementById('match-text');
            
            if (confirmPassword.length > 0) {
                matchDiv.classList.remove('hidden');
                if (password === confirmPassword) {
                    matchText.textContent = '✓ Passwords match';
                    matchText.className = 'text-green-600 font-medium';
                } else {
                    matchText.textContent = '✗ Passwords do not match';
                    matchText.className = 'text-red-600 font-medium';
                }
            } else {
                matchDiv.classList.add('hidden');
            }
        }

        // Add loading state to submit button
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Creating Account...
            `;
            submitButton.disabled = true;
        });

        // Enhanced form validation
        document.getElementById('email').addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                this.classList.add('border-red-500');
                this.classList.remove('border-gray-300');
            } else {
                this.classList.remove('border-red-500');
                this.classList.add('border-gray-300');
            }
        });

        document.getElementById('username').addEventListener('input', function() {
            const username = this.value;
            if (username.length > 0 && username.length < 3) {
                this.classList.add('border-red-500');
                this.classList.remove('border-gray-300');
            } else {
                this.classList.remove('border-red-500');
                this.classList.add('border-gray-300');
            }
        });

        // Auto-focus on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
    </script>
</body>
