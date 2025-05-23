<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SalvaVidas - Prevención de Suicidio')</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- App CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --sidebar-width: 260px;
            --navbar-height: 60px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
            overflow-x: hidden;
        }
        
        .app-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--secondary-color);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 100;
            transition: all 0.3s;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar-collapsed .sidebar {
            width: 60px;
        }
        
        .sidebar-header {
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .sidebar-logo i {
            font-size: 1.5rem;
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .sidebar-collapse-btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 0;
        }
        
        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .menu-link:hover, .menu-link.active {
            background-color: rgba(0, 0, 0, 0.1);
            color: white;
            border-left-color: var(--primary-color);
        }
        
        .menu-icon {
            margin-right: 15px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .menu-text {
            transition: opacity 0.3s;
        }
        
        .sidebar-collapsed .menu-text {
            opacity: 0;
            width: 0;
            display: none;
        }
        
        .menu-badge {
            margin-left: auto;
            background-color: var(--danger-color);
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
        }
        
        /* Menu categoría */
        .menu-category {
            padding: 10px 20px;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 1px;
        }
        
        .sidebar-collapsed .menu-category {
            opacity: 0;
            display: none;
        }
        
        /* Navbar */
        .navbar {
            height: var(--navbar-height);
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 99;
            transition: all 0.3s;
        }
        
        .sidebar-collapsed .navbar {
            left: 60px;
        }
        
        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            padding: 0 20px;
        }
        
        .navbar-left {
            display: flex;
            align-items: center;
        }
        
        .navbar-right {
            display: flex;
            align-items: center;
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
            margin-left: 20px;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: 500;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .user-role {
            font-size: 0.7rem;
            color: #6c757d;
        }
        
        /* Main content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 20px;
            transition: all 0.3s;
        }
        
        .sidebar-collapsed .main-content {
            margin-left: 60px;
        }
        
        /* Utilities */
        .navbar-icon {
            font-size: 1.2rem;
            color: #6c757d;
            margin-left: 15px;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .navbar-icon:hover {
            color: var(--primary-color);
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                left: -280px;
            }
            
            .sidebar-collapsed .sidebar {
                left: 0;
                width: var(--sidebar-width);
            }
            
            .navbar, .main-content {
                left: 0;
                margin-left: 0;
            }
            
            .sidebar-collapsed .navbar,
            .sidebar-collapsed .main-content {
                left: 0;
                margin-left: 0;
            }
            
            .menu-text {
                display: inline;
                opacity: 1;
                width: auto;
            }
            
            .menu-category {
                display: block;
                opacity: 1;
            }
            
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 99;
                display: none;
            }
            
            .sidebar-collapsed .sidebar-overlay {
                display: block;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div id="app" class="app-container" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <a href="{{ route('dashboard') }}" class="sidebar-logo">
                    <i class="fas fa-heart-pulse"></i>
                    <span class="menu-text">SalvaVidas</span>
                </a>
                <button class="sidebar-collapse-btn" @click="toggleSidebar">
                    <i class="fas" :class="sidebarCollapsed ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
                </button>
            </div>
            
            <div class="sidebar-menu">
                <div class="menu-category">Principal</div>
                <ul class="menu-item list-unstyled">
                    <li>
                        <a href="{{ route('dashboard') }}" class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="menu-icon fas fa-tachometer-alt"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>
                </ul>
                
                <div class="menu-category">Asistente IA</div>
                <ul class="menu-item list-unstyled">
                    <li>
                        <a href="{{ route('chat.index') }}" class="menu-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
                            <i class="menu-icon fas fa-comments"></i>
                            <span class="menu-text">Chat</span>
                        </a>
                    </li>
                </ul>
                
                <div class="menu-category">Evaluación de Riesgo</div>
                <ul class="menu-item list-unstyled">
                    <li>
                        <a href="{{ route('risk-assessment.index') }}" class="menu-link {{ request()->routeIs('risk-assessment.*') ? 'active' : '' }}">
                            <i class="menu-icon fas fa-exclamation-triangle"></i>
                            <span class="menu-text">Evaluaciones de Riesgo</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('risk.index') }}" class="menu-link {{ request()->routeIs('risk.index') ? 'active' : '' }}">
                            <i class="menu-icon fas fa-clipboard-list"></i>
                            <span class="menu-text">Evaluaciones</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('risk.create') }}" class="menu-link {{ request()->routeIs('risk.create') ? 'active' : '' }}">
                            <i class="menu-icon fas fa-plus-circle"></i>
                            <span class="menu-text">Nueva Evaluación</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('risk.dashboard') }}" class="menu-link {{ request()->routeIs('risk.dashboard') ? 'active' : '' }}">
                            <i class="menu-icon fas fa-chart-line"></i>
                            <span class="menu-text">Estadísticas</span>
                        </a>
                    </li>
                </ul>
                
                <div class="menu-category">Pacientes</div>
                <ul class="menu-item list-unstyled">
                    <li>
                        <a href="{{ url('/pacientes') }}" class="menu-link">
                            <i class="menu-icon fas fa-user-injured"></i>
                            <span class="menu-text">Todos los Pacientes</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ url('/pacientes/alto-riesgo') }}" class="menu-link">
                            <i class="menu-icon fas fa-exclamation-triangle"></i>
                            <span class="menu-text">Alto Riesgo</span>
                            <span class="menu-badge">5</span>
                        </a>
                    </li>
                </ul>
                
                <div class="menu-category">Sistema</div>
                <ul class="menu-item list-unstyled">
                    <li>
                        <a href="{{ url('/configuracion') }}" class="menu-link">
                            <i class="menu-icon fas fa-cog"></i>
                            <span class="menu-text">Configuración</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="menu-link">
                            <i class="menu-icon fas fa-sign-out-alt"></i>
                            <span class="menu-text">Cerrar Sesión</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Navbar -->
        <nav class="navbar">
            <div class="navbar-content">
                <div class="navbar-left">
                    <div class="page-title">@yield('page-title', 'Dashboard')</div>
                </div>
                
                <div class="navbar-right">
                    <div class="navbar-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    
                    <div class="navbar-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    
                    <div class="user-dropdown">
                        <div class="user-avatar">
                            U
                        </div>
                        <div class="user-info">
                            <div class="user-name">Usuario Demo</div>
                            <div class="user-role">Profesional</div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="main-content">
            @yield('content')
        </div>
        
        <!-- Sidebar Overlay para dispositivos móviles -->
        <div class="sidebar-overlay" @click="toggleSidebar"></div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Si no hay Vue inicializado, agregar funcionalidad básica para el sidebar
            if (typeof app === 'undefined') {
                window.sidebarCollapsed = false;
                
                document.querySelectorAll('.sidebar-collapse-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        document.querySelector('.app-container').classList.toggle('sidebar-collapsed');
                        window.sidebarCollapsed = !window.sidebarCollapsed;
                    });
                });
                
                document.querySelector('.sidebar-overlay').addEventListener('click', function() {
                    document.querySelector('.app-container').classList.toggle('sidebar-collapsed');
                    window.sidebarCollapsed = !window.sidebarCollapsed;
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
