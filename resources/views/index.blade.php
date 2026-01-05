<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SofaHub Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .logo {
            margin-bottom: 2rem;
            animation: fadeIn 1s ease-in-out;
        }

        .logo-container {
            width: 180px;
            height: 180px;
            background: linear-gradient(135deg, #3498db, #2c3e50);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            margin: 0 auto 1rem;
            position: relative;
            overflow: hidden;
        }

        .logo-icon {
            color: white;
            font-size: 5rem;
            position: relative;
            z-index: 2;
        }

        .logo-container:before {
            content: '';
            position: absolute;
            width: 120%;
            height: 120%;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            z-index: 1;
        }

        .logo-text {
            font-size: 2.2rem;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 1px;
        }

        .logo-text span {
            color: #3498db;
        }

        h1 {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            color: #2c3e50;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.1);
            animation: slideIn 1s ease-out;
        }

        p {
            font-size: 1.2rem;
            max-width: 700px;
            margin-bottom: 2.5rem;
            color: #555;
            animation: fadeIn 1.5s ease-in-out;
        }

        .btn-group {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
            animation: fadeIn 2s ease-in-out;
        }

        .btn {
            display: inline-block;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.5s;
            z-index: -1;
        }

        .btn:hover:before {
            width: 100%;
        }

        .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-admin {
            background: linear-gradient(45deg, #3498db, #2980b9);
            color: white;
        }

        .btn-showroom {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: white;
        }

        .btn-login {
            background: linear-gradient(45deg, #9b59b6, #8e44ad);
            color: white;
        }

        .btn-register {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }

        .features {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 4rem;
            gap: 2rem;
            animation: fadeIn 2.5s ease-in-out;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            width: 280px;
            box-shadow: 0 10px 30px rgba(255, 255, 255, 0.08);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #3498db;
        }

        .feature-card h3 {
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .feature-card p {
            font-size: 1rem;
            margin-bottom: 0;
            color: #666;
        }

        footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2.2rem;
            }
            
            p {
                font-size: 1.1rem;
            }
            
            .btn {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
            }
            
            .features {
                flex-direction: column;
                align-items: center;
            }
            
            .logo-container {
                width: 150px;
                height: 150px;
            }
            
            .logo-icon {
                font-size: 4rem;
            }
            
            .logo-text {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <div class="logo-container">
                <i class="fas fa-couch logo-icon"></i>
            </div>
            <div class="logo-text">Sofa<span>Hub</span></div>
        </div>

        <h1>SofaHub Management System</h1>
        <p>Access your workspace below to manage operations, production, and sales efficiently.</p>
    

    


          <div class="btn-group">
                <a href="{{route('login')}}" class="btn btn-login">Login</a>
                <a href="{{route('register')}}" class="btn btn-register">Register</a>
    </div>

</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
