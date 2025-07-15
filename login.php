<?php
session_start();
require_once 'config/database.php';

$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // Redirect by role
        switch ($user['role']) {
            case 'admin':
                header("Location: admin/dashboard.php");
                break;
            case 'institution':
                header("Location: institution/dashboard.php");
                break;
            case 'farmer':
                header("Location: farmer/dashboard.php");
                break;
        }
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HingaDatabank Platform</title>
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
        
        .login-bg {
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
    </style>
</head>
<body class="font-inter min-h-screen login-bg flex items-center justify-center p-4">
    <!-- Background Animation Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-20 left-10 w-20 h-20 bg-white bg-opacity-10 rounded-full float-animation"></div>
        <div class="absolute top-40 right-20 w-16 h-16 bg-white bg-opacity-10 rounded-full float-animation" style="animation-delay: -2s;"></div>
        <div class="absolute bottom-20 left-1/4 w-24 h-24 bg-white bg-opacity-10 rounded-full float-animation" style="animation-delay: -4s;"></div>
        <div class="absolute bottom-40 right-1/3 w-12 h-12 bg-white bg-opacity-10 rounded-full float-animation" style="animation-delay: -1s;"></div>
    </div>

    <div class="w-full max-w-6xl mx-auto grid lg:grid-cols-2 gap-8 items-center relative z-10">
        <!-- Left Side - Welcome Content -->
        <div class="text-white slide-in hidden lg:block">
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
                    Welcome Back to Your <span class="text-agri-gold">Agricultural</span> Journey
                </h2>
                <p class="text-xl text-gray-100 mb-8 leading-relaxed">
                    Access your personalized dashboard to manage your farming operations, track financial progress, and connect with opportunities.
                </p>
            </div>
            
            <!-- Feature Highlights -->
            <div class="grid grid-cols-1 gap-4 mb-8">
                <div class="flex items-center space-x-4 bg-white bg-opacity-10 rounded-lg p-4">
                    <div class="w-10 h-10 bg-agri-gold rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Secure Financial Management</h3>
                        <p class="text-sm text-gray-200">Your financial data is protected with enterprise-grade security</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4 bg-white bg-opacity-10 rounded-lg p-4">
                    <div class="w-10 h-10 bg-agri-gold rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Real-time Analytics</h3>
                        <p class="text-sm text-gray-200">Track your progress with comprehensive dashboards</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4 bg-white bg-opacity-10 rounded-lg p-4">
                    <div class="w-10 h-10 bg-agri-gold rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">Expert Support</h3>
                        <p class="text-sm text-gray-200">Get help from agricultural and financial experts</p>
                    </div>
                </div>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-agri-gold">10K+</div>
                    <div class="text-sm text-gray-200">Active Users</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-agri-gold">$50M+</div>
                    <div class="text-sm text-gray-200">Loans Processed</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-agri-gold">99.9%</div>
                    <div class="text-sm text-gray-200">Uptime</div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full max-w-md mx-auto lg:mx-0">
            <div class="form-container rounded-2xl shadow-2xl p-8 slide-in">
                <!-- Header -->
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-agri-green rounded-full flex items-center justify-center mx-auto mb-4 pulse-green">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome Back!</h2>
                    <p class="text-gray-600">Sign in to access your HingaDatabank dashboard</p>
                </div>

                <!-- Error Message -->
                <?php if (!empty($error)): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg error-shake">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-red-700 font-medium"><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST" class="space-y-6">
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
                                placeholder="Enter your email" 
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
                                placeholder="Enter your password" 
                                required 
                                class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:outline-none input-focus transition-all duration-300"
                            >
                            <button 
                                type="button" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                onclick="togglePassword()"
                            >
                                <svg id="eye-open" class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                </svg>
                                <svg id="eye-closed" class="w-5 h-5 text-gray-400 hover:text-gray-600 hidden" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zm4.261 4.26l1.514 1.515a2.003 2.003 0 012.45 2.45l1.514 1.514a4 4 0 00-5.478-5.478z" clip-rule="evenodd"/>
                                    <path d="M12.454 16.697L9.75 13.992a4 4 0 01-3.742-3.741L2.335 6.578A9.98 9.98 0 00.458 10c1.274 4.057 5.065 7 9.542 7 .847 0 1.669-.105 2.454-.303z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                id="remember" 
                                name="remember" 
                                type="checkbox" 
                                class="w-4 h-4 text-agri-green border-gray-300 rounded focus:ring-agri-green focus:ring-2"
                            >
                            <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                        </div>
                        <a href="#" class="text-sm text-agri-green hover:text-agri-dark-green font-medium">Forgot password?</a>
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-agri-green hover:bg-agri-dark-green text-white font-semibold py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl"
                    >
                        Sign In
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-8 text-center">
                    <p class="text-gray-600">
                        Don't have an account? 
                        <a href="register.php" class="text-agri-green hover:text-agri-dark-green font-medium">Sign up here</a>
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
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            
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

        // Add loading state to submit button
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Signing In...
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

        // Auto-focus on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>