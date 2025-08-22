<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kader') {
    header("Location: login.php");
    exit();
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$desa_kader = isset($_SESSION['desa']) ? $_SESSION['desa'] : '';

include 'db.php';


// Desa kader yang login
$desa_kader = $_SESSION['desa'];

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Cek dulu apakah data anak dengan id ini memang dari desa kader yang login
    $stmt = $conn->prepare("SELECT desa FROM anak WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && $row['desa'] === $desa_kader) {
        // Kalau desa cocok, boleh hapus
        $stmt_del = $conn->prepare("DELETE FROM anak WHERE id = ?");
        $stmt_del->bind_param("i", $id);
        $stmt_del->execute();
        $stmt_del->close();
    } else {
        // Kalau tidak cocok, tolak hapus
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Anda tidak berhak menghapus data dari desa lain.'
                });
              </script>";
        exit();
    }

    $stmt->close();
    header("Location: anak_list.php");
    exit();
}


?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kader Posyandu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Dashboard Section Styles */
        #dashboard {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .section-header h2 {
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.8rem;
        }

        .divider {
            border: 0;
            height: 1px;
            background-image: linear-gradient(to right, rgba(0, 0, 0, 0), rgba(44, 62, 80, 0.75), rgba(0, 0, 0, 0));
            width: 60%;
            margin: 1.5rem auto;
        }

        /* Card Styles */
        .feature-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            height: 100%;
            background: linear-gradient(135deg, rgb(253, 105, 235) 0%, rgb(255, 255, 255) 100%);
            position: relative;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #4e73df, #224abe);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }

        .card-body {
            padding: 2rem;
        }

        .card-title {
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            color: #2c3e50;
        }

        .card-text {
            color: #6c757d;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        /* Feature List Styles */
        .feature-list {
            list-style: none;
            padding-left: 0;
            margin-top: 1.5rem;
        }

        .feature-list li {
            padding: 0.75rem 0;
            font-size: 1rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
        }

        .feature-list li:last-child {
            border-bottom: none;
        }

        .feature-list li:hover {
            transform: translateX(5px);
            color: #4e73df;
        }

        .feature-list i {
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
            margin-right: 12px;
        }

        /* Color Variants */
        .text-primary {
            color: #4e73df !important;
        }

        .text-success {
            color: #1cc88a !important;
        }

        .text-warning {
            color: #f6c23e !important;
        }

        .text-danger {
            color: #e74a3b !important;
        }

        .text-info {
            color: #36b9cc !important;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .section-header h2 {
                font-size: 1.8rem;
                flex-direction: column;
            }

            .divider {
                width: 80%;
            }

            .card-body {
                padding: 1.5rem;
            }
        }

        .dropdown {
            position: relative;
        }

        .dropdown-header {
            padding: 12px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }

        .dropdown-header:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .dropdown-header i:first-child {
            margin-right: 10px;
        }

        .dropdown-icon {
            margin-left: auto;
            transition: transform 0.3s;
        }

        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            background-color: rgba(0, 0, 0, 0.2);
        }

        .dropdown.active .dropdown-content {
            max-height: 300px;
            /* Sesuaikan dengan kebutuhan */
        }

        .dropdown.active .dropdown-icon {
            transform: rotate(180deg);
        }

        .dropdown-content a {
            display: flex;
            align-items: center;
            padding: 10px 15px 10px 40px;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
        }

        .dropdown-content a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .dropdown-content a i {
            margin-right: 10px;
            font-size: 0.9em;
        }

        .lansia-tab {
            transition: opacity 0.3s ease;
        }

        .lansia-tab.hidden {
            display: none;
            opacity: 0;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-row {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .btn-row button {
            flex: 1;
            min-width: 120px;
            white-space: nowrap;
        }

        .btn-add {
            margin-top: 10px;
            align-self: flex-start;
        }

        /* If you want the add button to be full width on mobile */
        @media (max-width: 768px) {
            .btn-row button {
                min-width: calc(50% - 5px);
            }

            .btn-add {
                width: 100%;
            }

            .btn-add a {
                width: 100%;
                text-align: center;
            }
        }

        .anak-tab {
            transition: all 0.3s ease;
        }

        .anak-tab.hidden {
            display: none;
        }

        :root {
            --primary: rgb(214, 51, 189);
            --primary-dark: rgb(156, 35, 130);
            --secondary: #e599f7;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #f8c4ec;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --white: #ffffff;
            --sidebar-width: 260px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: rgb(214, 51, 189);
            color: white;
            position: fixed;
            width: 250px;
        }

        .sidebar-header {
            text-align: center;
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-scrollable {
            flex: 1;
            overflow-y: auto;
            padding: 10px 0;

            /* Tambahan untuk sembunyikan scrollbar */
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* Internet Explorer 10+ */
        }

        .sidebar-scrollable::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }

        .scrollable-hidden-scrollbar {
            overflow: auto;
            /* aktifkan scroll 2 arah */
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE */
        }

        .scrollable-hidden-scrollbar::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari, Opera */
        }


        .sidebar-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .sidebar-header p {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 0 10px;
        }

        .sidebar-footer {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        /* Gaya untuk submenu */
        .has-submenu {
            display: flex;
            flex-direction: column;
        }

        .submenu-header {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submenu-header:hover {
            background-color: rgb(197, 7, 184);
            border-radius: 10px;
        }

        .submenu-icon {
            margin-left: auto;
            transition: transform 0.3s;
            font-size: 0.8em;
        }

        .submenu-items {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
            background-color: rgba(192, 37, 161, 0.1);
        }

        .has-submenu.active .submenu-items {
            max-height: 500px;
            /* Sesuaikan dengan kebutuhan */
        }

        .has-submenu.active .submenu-icon {
            transform: rotate(180deg);
        }

        .submenu-item {
            display: flex;
            align-items: center;
            padding: 10px 20px 10px 50px;
            color: rgb(255, 255, 255);
            text-decoration: none;
            transition: all 0.3s;
            border-radius: 10px;
        }

        .submenu-item:hover {
            background-color: rgba(179, 0, 255, 0.1);
            color: #fff;
        }

        .submenu-item i {
            font-size: 0.9em;
            margin-right: 8px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            margin: 0.3rem 0;
            color: var(--white);
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: var(--transition);
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--white);
        }

        .menu-item.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 3px solid var(--white);
        }

        .menu-item i {
            margin-right: 0.8rem;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .menu-item span {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .logout-btn {
            position: absolute;
            bottom: 1.5rem;
            left: 0;
            right: 0;
            width: calc(100% - 3rem);
            margin: 0 1.5rem;
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 5px;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: var(--transition);
            overflow: scroll;
            /* scroll aktif */

            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE 10+ */
        }

        .main-content::-webkit-scrollbar {
            width: 0px;
            height: 0px;
            background: transparent;
            /* opsional */
        }



        /* Welcome Card */
        .welcome-card {
            background: linear-gradient(135deg, var(--primary), var(--info));
            color: var(--white);
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.2);
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .welcome-card h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .welcome-card p {
            font-size: 1rem;
            opacity: 0.9;
            max-width: 600px;
        }

        /* Section Styles */
        .section {
            background-color: var(--white);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .section.hidden {
            display: none;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--light-gray);
        }

        .section-header h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
        }

        .section-header h3 i {
            margin-right: 0.5rem;
        }

        /* Button Styles */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            outline: none;
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(67, 97, 238, 0.2);
        }

        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #e51773;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(247, 37, 133, 0.2);
        }

        .btn-success {
            background-color: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background-color: #3ab5d9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(76, 201, 240, 0.2);
        }

        /* Table Styles */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th,
        .table td {
            padding: 0.8rem;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }

        .table th {
            background-color: var(--light);
            color: var(--dark);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table tr:hover td {
            background-color: rgba(67, 97, 238, 0.05);
        }

        .table .actions {
            display: flex;
            gap: 0.5rem;
        }

        /* Untuk HP */
        /* ==================== */
        /* RESPONSIVE BREAKPOINTS */
        /* ==================== */

        /* Mobile (Portrait) - Up to 480px */
        @media (max-width: 480px) {
            .sidebar-menu {
                width: 100%;
                height: auto;
                position: relative;
                padding: 10px 0;
            }

            .menu-item,
            .submenu-header {
                padding: 10px 15px;
            }

            .submenu-item {
                padding: 8px 15px 8px 40px;
            }

            .container {
                padding: 8px;
                margin-left: 0;
            }

            .section-header h3 {
                font-size: 1.2rem;
            }

            .table-responsive {
                font-size: 12px;
            }

            .btn {
                padding: 5px 8px;
                font-size: 12px;
            }
        }

        /* Mobile (Landscape) - 481px to 600px */
        @media (min-width: 481px) and (max-width: 600px) {
            .sidebar-menu {
                width: 180px;
                position: fixed;
                z-index: 1000;
                transform: translateX(-160px);
                transition: transform 0.3s ease;
            }

            .sidebar-menu:hover {
                transform: translateX(0);
            }

            .container {
                margin-left: 20px;
                padding: 10px;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                margin-top: 10px;
            }
        }

        /* Tablet - 601px to 768px */
        @media (min-width: 601px) and (max-width: 768px) {
            .sidebar-menu {
                width: 200px;
            }

            .container {
                margin-left: 200px;
                padding: 15px;
            }

            .menu-item,
            .submenu-header {
                padding: 12px 15px;
            }

            .table-responsive {
                font-size: 14px;
            }
        }

        /* Small Laptop - 769px to 1024px */
        @media (min-width: 769px) and (max-width: 1024px) {
            .sidebar-menu {
                width: 220px;
            }

            .container {
                margin-left: 220px;
                padding: 20px;
            }

            .section-header {
                flex-direction: row;
            }
        }

        /* Desktop - Above 1024px */
        @media (min-width: 1025px) {
            .sidebar-menu {
                width: 250px;
            }

            .container {
                margin-left: 250px;
                padding: 25px 40px;
            }

            .stats-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        /* ==================== */
        /* SPECIAL MOBILE FEATURES */
        /* ==================== */

        /* Mobile menu toggle */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1100;
            background: #2c3e50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            font-size: 1.2rem;
        }

        @media (max-width: 600px) {
            .mobile-menu-toggle {
                display: block;
            }

            .sidebar-menu {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar-menu.active {
                transform: translateX(0);
            }

            .container {
                margin-left: 0;
            }
        }

        /* ==================== */
        /* IMPROVED TABLE RESPONSIVENESS */
        /* ==================== */

        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .table-responsive {
                border: none;
            }

            th,
            td {
                min-width: 120px;
                padding: 8px 10px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
        }

        /* ==================== */
        /* UTILITY CLASSES */
        /* ==================== */

        .hidden-mobile {
            display: block;
        }

        @media (max-width: 600px) {
            .hidden-mobile {
                display: none;
            }

            .visible-mobile {
                display: block;
            }
        }


        /* Animation */
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

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Posyandu Sehat</h2>
                <p class="sidebar-subtitle">Dashboard Kader</p>
            </div>

            <!-- Scrollable Menu Area -->
            <div class="sidebar-scrollable">
                <nav class="sidebar-menu" aria-label="Main navigation">
                    <ul class="menu-list">
                        <li class="menu-item">
                            <a href="#dashboard" class="menu-link" onclick="showSection('dashboard')" aria-current="page">
                                <i class="fas fa-book" aria-hidden="true"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <!-- Age Category Menu -->
                        <li class="menu-item has-submenu">
                            <button class="submenu-header" aria-expanded="false" aria-controls="age-category-submenu">
                                <i class="fas fa-child" aria-hidden="true"></i>
                                <span>Kategori Umur</span>
                                <i class="fas fa-chevron-down submenu-icon" aria-hidden="true"></i>
                            </button>
                            <ul id="age-category-submenu" class="submenu-items">
                                <li>
                                    <a href="#anak" class="submenu-item" onclick="showSection('anak')">
                                        <i class="fas fa-baby" aria-hidden="true"></i>
                                        <span>Balita</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#remaja" class="submenu-item" onclick="showSection('remaja')">
                                        <i class="fas fa-user" aria-hidden="true"></i>
                                        <span>Remaja</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#lansia" class="submenu-item" onclick="showSection('lansia')">
                                        <i class="fas fa-user-tie" aria-hidden="true"></i>
                                        <span>Lansia</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="menu-item has-submenu">
                            <button class="submenu-header" aria-expanded="false" aria-controls="village-list-submenu">
                                <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                                <span>Daftar Desa</span>
                                <i class="fas fa-chevron-down submenu-icon" aria-hidden="true"></i>
                            </button>
                            <div id="village-list-submenu" class="submenu-items scrollable-desa">
                                <ul class="village-list">
                                    <li>
                                        <a href="#kedai-kandang" class="submenu-item" onclick="showDesaDetail('Kedai Kandang', 'Ali', 'al@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Kedai Kandang</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#ujung" class="submenu-item" onclick="showDesaDetail('Ujung', 'Andi', 'andi@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Ujung</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#pulo-ie" class="submenu-item" onclick="showDesaDetail('Pulo Ie', 'Ari', 'ari@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Pulo Ie</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#barat-daya" class="submenu-item" onclick="showDesaDetail('Barat Daya', 'Bariah', 'bariah@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Barat Daya</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#pasi-meurapat" class="submenu-item" onclick="showDesaDetail('Pasi Meurapat', 'Budi', 'budi@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Pasi Meurapat</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#indra-damal" class="submenu-item" onclick="showDesaDetail('Indra Damal', 'Dedi', 'dedi@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Indra Damal</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#kedai-runding" class="submenu-item" onclick="showDesaDetail('Kedai Runding', 'Farmala', 'farmala@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Kedai Runding</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#ujung-pasir" class="submenu-item" onclick="showDesaDetail('Ujung Pasir', 'Fitri', 'fitri@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Ujung Pasir</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#geulumbuk" class="submenu-item" onclick="showDesaDetail('Geulumbuk', 'Hasan', 'hasan@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Geulumbuk</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#pasi-lembang" class="submenu-item" onclick="showDesaDetail('Pasi Lembang', 'Lina', 'lina@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Pasi Lembang</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#rantau-binuang" class="submenu-item" onclick="showDesaDetail('Rantau Binuang', 'Maulana', 'maulana@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Rantau Binuang</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#kapeh" class="submenu-item" onclick="showDesaDetail('Kapeh', 'Nawawi', 'nawawi@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Kapeh</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#luar" class="submenu-item" onclick="showDesaDetail('Luar', 'Rafif', 'rafif@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Luar</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#suaq-bakung" class="submenu-item" onclick="showDesaDetail('Suaq Bakung', 'Rahmad', 'rahmad@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Suaq Bakung</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#ujung-padang" class="submenu-item" onclick="showDesaDetail('Ujung Padang', 'Rian', 'nofriansafutra@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Ujung Padang</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#jua" class="submenu-item" onclick="showDesaDetail('Jua', 'Siti', 'siti@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Jua</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#sialang" class="submenu-item" onclick="showDesaDetail('Sialang', 'Yanti', 'yanti@gmail.com')">
                                            <i class="fas fa-map-pin" aria-hidden="true"></i>
                                            <span>Desa Sialang</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="sidebar-footer">
                <button id="logoutBtn" class="btn btn-primary logout-btn" aria-label="Logout">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>Logout</span>
                </button>
            </div>
        </aside>

        <!-- Village Detail Modal -->
        <dialog id="desaDetailModal" class="modal" aria-labelledby="desaTitle">
            <div class="modal-content">
                <button class="close" onclick="closeModal()" aria-label="Close modal">&times;</button>
                <h3 id="desaTitle">Detail Desa</h3>
                <div class="desa-info">
                    <p><strong>Nama Desa:</strong> <span id="detailNamaDesa"></span></p>
                    <p><strong>Kader Penanggung Jawab:</strong> <span id="detailKader"></span></p>
                    <p><strong>Email Kader:</strong> <span id="detailEmail"></span></p>
                </div>
                <button class="btn btn-primary" onclick="closeModal()">Tutup</button>
            </div>
        </dialog>

        <style>
            .menu-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .menu-item {
                position: relative;
            }

            .menu-link,
            .submenu-header {
                display: flex;
                align-items: center;
                width: 100%;
                padding: 0.75rem 1rem;
                color: var(--sidebar-text);
                text-decoration: none;
                background: transparent;
                border: none;
                text-align: left;
                cursor: pointer;
                transition: background var(--transition-speed) ease;
            }

            .menu-link:hover,
            .submenu-header:hover {
                background: var(--sidebar-hover);
            }

            .menu-link[aria-current="page"] {
                background: var(--sidebar-active);
            }

            .menu-link i,
            .submenu-header i {
                margin-right: 0.75rem;
                width: 1.25rem;
                text-align: center;
            }

            /* Submenu Styles */
            .submenu-items {
                display: none;
                background: rgba(0, 0, 0, 0.1);
                transition: all var(--transition-speed) ease;
            }

            .submenu-items.active {
                display: block;
            }

            .submenu-item {
                display: flex;
                align-items: center;
                padding: 0.6rem 1rem 0.6rem 2.5rem;
                color: var(--sidebar-text);
                text-decoration: none;
                transition: background var(--transition-speed) ease;
            }

            .submenu-item:hover {
                background: var(--sidebar-hover);
            }

            .submenu-icon {
                margin-left: auto;
                transition: transform var(--transition-speed) ease;
            }

            .submenu-icon.rotate {
                transform: rotate(180deg);
            }

            /* Scrollable Village List */
            .scrollable-desa {
                max-height: 300px;
                overflow-y: auto;
            }

            .village-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            /* Custom Scrollbar */
            .scrollable-desa {
                scrollbar-width: thin;
                scrollbar-color: var(--sidebar-hover) var(--sidebar-border);
            }

            .scrollable-desa::-webkit-scrollbar {
                width: 6px;
            }

            .scrollable-desa::-webkit-scrollbar-track {
                background: var(--sidebar-border);
            }

            .scrollable-desa::-webkit-scrollbar-thumb {
                background-color: var(--sidebar-hover);
                border-radius: 6px;
            }

            /* Sidebar Footer */
            .sidebar-footer {
                padding: 1rem;
                border-top: 1px solid var(--sidebar-border);
            }


            /* Modal Styles */
            .modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }

            .modal[open] {
                opacity: 1;
                visibility: visible;
            }

            .modal-content {
                background-color: #fff;
                padding: 2rem;
                border-radius: 8px;
                width: 90%;
                max-width: 500px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                position: relative;
            }

            .close {
                position: absolute;
                top: 1rem;
                right: 1rem;
                font-size: 1.5rem;
                line-height: 1;
                background: none;
                border: none;
                cursor: pointer;
                color: #666;
            }

            .close:hover {
                color: #333;
            }

            .desa-info {
                margin: 1.5rem 0;
            }

            .desa-info p {
                margin-bottom: 0.75rem;
            }

            /* Responsive Adjustments */
            @media (max-width: 768px) {
                .sidebar {
                    width: 100%;
                    height: auto;
                    position: relative;
                }

                .sidebar-scrollable {
                    max-height: 60vh;
                }
            }
        </style>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Toggle submenus
                document.querySelectorAll(".submenu-header").forEach(header => {
                    header.addEventListener("click", function() {
                        const isExpanded = this.getAttribute("aria-expanded") === "true";
                        const submenuId = this.getAttribute("aria-controls");
                        const submenu = document.getElementById(submenuId);
                        const icon = this.querySelector(".submenu-icon");

                        // Toggle state
                        this.setAttribute("aria-expanded", !isExpanded);
                        submenu.classList.toggle("active");
                        icon.classList.toggle("rotate");
                    });
                });

                // Close submenus when clicking outside
                document.addEventListener("click", function(event) {
                    if (!event.target.closest(".has-submenu")) {
                        document.querySelectorAll(".submenu-items").forEach(menu => {
                            menu.classList.remove("active");
                        });
                        document.querySelectorAll(".submenu-icon").forEach(icon => {
                            icon.classList.remove("rotate");
                        });
                        document.querySelectorAll(".submenu-header").forEach(header => {
                            header.setAttribute("aria-expanded", "false");
                        });
                    }
                });

                // Logout button handler
                document.getElementById("logoutBtn").addEventListener("click", function() {
                    // Add logout functionality here
                    console.log("Logout clicked");
                });
            });

            // Show village details
            function showDesaDetail(namaDesa, kader, email) {
                const modal = document.getElementById('desaDetailModal');
                document.getElementById('detailNamaDesa').textContent = namaDesa;
                document.getElementById('detailKader').textContent = kader;
                document.getElementById('detailEmail').textContent = email;
                document.getElementById('desaTitle').textContent = 'Detail Desa ' + namaDesa;
                modal.showModal();
            }

            // Close modal
            function closeModal() {
                document.getElementById('desaDetailModal').close();
            }

            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('desaDetailModal');
                if (event.target === modal) {
                    closeModal();
                }
            });
        </script>
        <!-- Data Anak Section -->


        <!-- Main Content -->
        <main class="main-content scrollable-hidden-scrollbar">
            <!-- Welcome Section -->
            <div class="welcome-card fade-in text-center" id="welcome" style="background: linear-gradient(135deg, #f8e1ff, #e1e9ff); padding: 25px; border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <h2 style="color: #6a0dad; font-weight: bold;"> <i class="fas fa-hand-holding-heart"></i> Selamat datang, <?php echo htmlspecialchars($username); ?>! </h2>
                <p style="font-size: 16px; color: #555;"> Anda login sebagai <b>Kader Posyandu</b>. Gunakan menu di samping untuk mengelola dan memantau data posyandu sesuai desa Anda. </p>
            </div>
            <!-- Dashboard Kader -->
            <div class="section fade-in mt-4" id="dashboard">
                <div class="section-header">
                    <h2 style="color: #6a0dad; font-weight: bold;">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard Kader
                    </h2>
                    <hr class="divider" style="border: 1px solid #6a0dad;">
                </div>

                <div class="section-content p-3 kader-feature-container">
                    <div class="kader-feature-card" style="position: relative; overflow: hidden;">
                        <!-- Background Animation Elements -->
                        <div class="kader-feature-bg-animation">
                            <div class="kader-feature-dot dot-1"></div>
                            <div class="kader-feature-dot dot-2"></div>
                            <div class="kader-feature-dot dot-3"></div>
                            <div class="kader-feature-dot dot-4"></div>
                        </div>

                        <!-- Card Header with Gradient -->
                        <div class="kader-feature-header">
                            <div class="kader-feature-icon-wrapper">
                                <div class="kader-feature-pulsing-icon">
                                    <i class="fas fa-users-cog"></i>
                                </div>
                                <div class="kader-feature-ripple-effect"></div>
                            </div>
                            <div class="kader-feature-header-content">
                                <h4 class="kader-feature-title">Fitur Utama Kader</h4>
                                <div class="kader-feature-decoration"></div>
                            </div>
                        </div>

                        <!-- Card Body with Glass Effect -->
                        <div class="kader-feature-body">
                            <!-- Description with Modern Styling -->
                            <div class="kader-feature-description-container">
                                <div class="kader-feature-info-badge">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Informasi Sistem</span>
                                </div>
                                <p class="kader-feature-text">
                                    Sebagai kader, Anda memiliki hak untuk mengelola data sesuai desa Anda.
                                    Data yang dikelola otomatis akan masuk ke database desa Anda, dan Anda juga bisa melihat data dari desa lain.
                                </p>
                            </div>

                            <!-- Feature List with Modern Cards -->
                            <div class="kader-feature-items">
                                <div class="kader-feature-item kader-feature-balita">
                                    <div class="kader-feature-icon-box">
                                        <div class="kader-feature-icon-bg balita-bg">
                                            <i class="fas fa-baby"></i>
                                        </div>
                                        <div class="kader-feature-icon-glow balita-glow"></div>
                                    </div>
                                    <div class="kader-feature-content">
                                        <h6 class="kader-feature-item-title">Data Anak Balita</h6>
                                        <p class="kader-feature-item-text">Tambah, edit, dan hapus data anak balita di desa Anda</p>
                                        <div class="kader-feature-badge balita-badge">0-5 Tahun</div>
                                    </div>
                                    <div class="kader-feature-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>

                                <div class="kader-feature-item kader-feature-remaja">
                                    <div class="kader-feature-icon-box">
                                        <div class="kader-feature-icon-bg remaja-bg">
                                            <i class="fas fa-child"></i>
                                        </div>
                                        <div class="kader-feature-icon-glow remaja-glow"></div>
                                    </div>
                                    <div class="kader-feature-content">
                                        <h6 class="kader-feature-item-title">Data Anak Remaja</h6>
                                        <p class="kader-feature-item-text">Kelola informasi anak remaja di desa Anda</p>
                                        <div class="kader-feature-badge remaja-badge">6-17 Tahun</div>
                                    </div>
                                    <div class="kader-feature-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>

                                <div class="kader-feature-item kader-feature-lansia">
                                    <div class="kader-feature-icon-box">
                                        <div class="kader-feature-icon-bg lansia-bg">
                                            <i class="fas fa-user-clock"></i>
                                        </div>
                                        <div class="kader-feature-icon-glow lansia-glow"></div>
                                    </div>
                                    <div class="kader-feature-content">
                                        <h6 class="kader-feature-item-title">Data Lansia</h6>
                                        <p class="kader-feature-item-text">Catat dan pantau data lansia di wilayah kerja Anda</p>
                                        <div class="kader-feature-badge lansia-badge">60+ Tahun</div>
                                    </div>
                                    <div class="kader-feature-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>

                                <div class="kader-feature-item kader-feature-coordination">
                                    <div class="kader-feature-icon-box">
                                        <div class="kader-feature-icon-bg coordination-bg">
                                            <i class="fas fa-network-wired"></i>
                                        </div>
                                        <div class="kader-feature-icon-glow coordination-glow"></div>
                                    </div>
                                    <div class="kader-feature-content">
                                        <h6 class="kader-feature-item-title">Lihat Data Desa Lain</h6>
                                        <p class="kader-feature-item-text">Akses data dari desa lain untuk keperluan koordinasi</p>
                                        <div class="kader-feature-badge coordination-badge">Multi Desa</div>
                                    </div>
                                    <div class="kader-feature-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer with Stats -->
                        <div class="kader-feature-footer">
                            <div class="kader-feature-stats">
                                <div class="kader-feature-stat">
                                    <i class="fas fa-shield-check"></i>
                                    <span>Data Aman</span>
                                </div>
                                <div class="kader-feature-divider"></div>
                                <div class="kader-feature-stat">
                                    <i class="fas fa-sync-alt"></i>
                                    <span>Real-time</span>
                                </div>
                                <div class="kader-feature-divider"></div>
                                <div class="kader-feature-stat">
                                    <i class="fas fa-mobile-alt"></i>
                                    <span>Mobile Friendly</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    /* Scoped CSS untuk fitur kader saja */
                    .kader-feature-container {
                        --kf-primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        --kf-balita-gradient: linear-gradient(135deg, #ff6b9d 0%, #ff8ba7 100%);
                        --kf-remaja-gradient: linear-gradient(135deg, #ffa726 0%, #ffb74d 100%);
                        --kf-lansia-gradient: linear-gradient(135deg, #26a69a 0%, #4db6ac 100%);
                        --kf-coordination-gradient: linear-gradient(135deg, #29b6f6 0%, #4fc3f7 100%);
                        --kf-glass-bg: rgba(255, 255, 255, 0.95);
                        --kf-shadow-light: 0 10px 40px rgba(0, 0, 0, 0.1);
                        --kf-shadow-medium: 0 20px 60px rgba(0, 0, 0, 0.15);
                        --kf-border-radius: 24px;
                    }

                    /* Main Card Container */
                    .kader-feature-card {
                        background: var(--kf-glass-bg);
                        backdrop-filter: blur(20px);
                        border: 1px solid rgba(255, 255, 255, 0.3);
                        border-radius: var(--kf-border-radius);
                        box-shadow: var(--kf-shadow-medium);
                        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                        font-family: 'Inter', sans-serif;
                        max-width: 900px;
                        margin: 0 auto;
                    }

                    .kader-feature-card:hover {
                        transform: translateY(-5px);
                        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.2);
                    }

                    /* Background Animation */
                    .kader-feature-bg-animation {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        pointer-events: none;
                        z-index: 0;
                    }

                    .kader-feature-dot {
                        position: absolute;
                        width: 6px;
                        height: 6px;
                        background: linear-gradient(45deg, #667eea, #764ba2);
                        border-radius: 50%;
                        opacity: 0.3;
                        animation: kf-floatDot 8s ease-in-out infinite;
                    }

                    .kader-feature-dot.dot-1 {
                        top: 20%;
                        left: 10%;
                        animation-delay: 0s;
                    }

                    .kader-feature-dot.dot-2 {
                        top: 60%;
                        right: 15%;
                        animation-delay: 2s;
                    }

                    .kader-feature-dot.dot-3 {
                        bottom: 30%;
                        left: 20%;
                        animation-delay: 4s;
                    }

                    .kader-feature-dot.dot-4 {
                        top: 40%;
                        right: 30%;
                        animation-delay: 6s;
                    }

                    @keyframes kf-floatDot {

                        0%,
                        100% {
                            transform: translateY(0px) scale(1);
                        }

                        50% {
                            transform: translateY(-20px) scale(1.2);
                        }
                    }

                    /* Card Header */
                    .kader-feature-header {
                        background: var(--kf-primary-gradient);
                        padding: 2.5rem 2rem;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        position: relative;
                        overflow: hidden;
                        z-index: 1;
                    }

                    .kader-feature-header::before {
                        content: '';
                        position: absolute;
                        top: -50%;
                        left: -50%;
                        width: 200%;
                        height: 200%;
                        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
                        transform: rotate(45deg);
                        animation: kf-headerShimmer 4s infinite;
                    }

                    @keyframes kf-headerShimmer {
                        0% {
                            transform: translateX(-100%) rotate(45deg);
                        }

                        100% {
                            transform: translateX(100%) rotate(45deg);
                        }
                    }

                    .kader-feature-icon-wrapper {
                        position: relative;
                        margin-right: 1.5rem;
                    }

                    .kader-feature-pulsing-icon {
                        width: 70px;
                        height: 70px;
                        background: rgba(255, 255, 255, 0.2);
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        backdrop-filter: blur(10px);
                        border: 1px solid rgba(255, 255, 255, 0.3);
                        position: relative;
                        z-index: 1;
                    }

                    .kader-feature-pulsing-icon i {
                        color: white;
                        font-size: 1.8rem;
                        animation: kf-iconPulse 2s ease-in-out infinite;
                    }

                    @keyframes kf-iconPulse {

                        0%,
                        100% {
                            transform: scale(1);
                        }

                        50% {
                            transform: scale(1.1);
                        }
                    }

                    .kader-feature-ripple-effect {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        width: 70px;
                        height: 70px;
                        border: 2px solid rgba(255, 255, 255, 0.5);
                        border-radius: 50%;
                        transform: translate(-50%, -50%);
                        animation: kf-ripple 2s infinite;
                    }

                    @keyframes kf-ripple {
                        0% {
                            transform: translate(-50%, -50%) scale(1);
                            opacity: 1;
                        }

                        100% {
                            transform: translate(-50%, -50%) scale(1.8);
                            opacity: 0;
                        }
                    }

                    .kader-feature-header-content {
                        text-align: center;
                    }

                    .kader-feature-title {
                        color: white;
                        font-size: 1.8rem;
                        font-weight: 800;
                        margin: 0;
                        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
                    }

                    .kader-feature-decoration {
                        width: 60px;
                        height: 4px;
                        background: rgba(255, 255, 255, 0.5);
                        border-radius: 2px;
                        margin: 0.5rem auto 0;
                    }

                    /* Glass Card Body */
                    .kader-feature-body {
                        padding: 2.5rem;
                        position: relative;
                        z-index: 1;
                    }

                    /* Description Container */
                    .kader-feature-description-container {
                        margin-bottom: 2rem;
                    }

                    .kader-feature-info-badge {
                        display: inline-flex;
                        align-items: center;
                        background: linear-gradient(135deg, #e3f2fd, #f0f8ff);
                        color: #1976d2;
                        padding: 0.5rem 1rem;
                        border-radius: 20px;
                        font-size: 0.85rem;
                        font-weight: 500;
                        margin-bottom: 1rem;
                        border: 1px solid rgba(25, 118, 210, 0.2);
                    }

                    .kader-feature-info-badge i {
                        margin-right: 0.5rem;
                        font-size: 0.9rem;
                    }

                    .kader-feature-text {
                        color: #4a5568;
                        line-height: 1.7;
                        font-size: 1rem;
                        margin: 0;
                        text-align: justify;
                    }

                    /* Features Container */
                    .kader-feature-items {
                        display: flex;
                        flex-direction: column;
                        gap: 1rem;
                    }

                    .kader-feature-item {
                        display: flex;
                        align-items: center;
                        background: white;
                        padding: 1.5rem;
                        border-radius: 16px;
                        border: 1px solid rgba(0, 0, 0, 0.05);
                        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                        cursor: pointer;
                        position: relative;
                        overflow: hidden;
                    }

                    .kader-feature-item::before {
                        content: '';
                        position: absolute;
                        top: 0;
                        left: -100%;
                        width: 100%;
                        height: 100%;
                        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
                        transition: all 0.5s ease;
                    }

                    .kader-feature-item:hover::before {
                        left: 100%;
                    }

                    .kader-feature-item:hover {
                        transform: translateX(8px);
                        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
                    }

                    .kader-feature-icon-box {
                        position: relative;
                        margin-right: 1.5rem;
                    }

                    .kader-feature-icon-bg {
                        width: 55px;
                        height: 55px;
                        border-radius: 14px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        position: relative;
                        z-index: 1;
                        transition: all 0.3s ease;
                    }

                    .kader-feature-icon-bg i {
                        color: white;
                        font-size: 1.3rem;
                    }

                    .kader-feature-icon-glow {
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        width: 55px;
                        height: 55px;
                        border-radius: 14px;
                        transform: translate(-50%, -50%);
                        opacity: 0;
                        transition: all 0.3s ease;
                    }

                    .kader-feature-item:hover .kader-feature-icon-glow {
                        opacity: 0.3;
                        transform: translate(-50%, -50%) scale(1.3);
                    }

                    .kader-feature-item:hover .kader-feature-icon-bg {
                        transform: scale(1.1) rotate(5deg);
                    }

                    /* Feature Colors */
                    .kader-feature-icon-bg.balita-bg {
                        background: var(--kf-balita-gradient);
                    }

                    .kader-feature-icon-glow.balita-glow {
                        background: var(--kf-balita-gradient);
                    }

                    .kader-feature-icon-bg.remaja-bg {
                        background: var(--kf-remaja-gradient);
                    }

                    .kader-feature-icon-glow.remaja-glow {
                        background: var(--kf-remaja-gradient);
                    }

                    .kader-feature-icon-bg.lansia-bg {
                        background: var(--kf-lansia-gradient);
                    }

                    .kader-feature-icon-glow.lansia-glow {
                        background: var(--kf-lansia-gradient);
                    }

                    .kader-feature-icon-bg.coordination-bg {
                        background: var(--kf-coordination-gradient);
                    }

                    .kader-feature-icon-glow.coordination-glow {
                        background: var(--kf-coordination-gradient);
                    }

                    .kader-feature-content {
                        flex: 1;
                    }

                    .kader-feature-item-title {
                        color: #2d3748;
                        font-size: 1.1rem;
                        font-weight: 700;
                        margin: 0 0 0.5rem 0;
                    }

                    .kader-feature-item-text {
                        color: #718096;
                        font-size: 0.9rem;
                        line-height: 1.5;
                        margin: 0 0 0.5rem 0;
                    }

                    .kader-feature-badge {
                        display: inline-block;
                        padding: 0.25rem 0.75rem;
                        border-radius: 12px;
                        font-size: 0.75rem;
                        font-weight: 500;
                        color: white;
                    }

                    .kader-feature-badge.balita-badge {
                        background: linear-gradient(135deg, #ff6b9d, #ff8ba7);
                    }

                    .kader-feature-badge.remaja-badge {
                        background: linear-gradient(135deg, #ffa726, #ffb74d);
                    }

                    .kader-feature-badge.lansia-badge {
                        background: linear-gradient(135deg, #26a69a, #4db6ac);
                    }

                    .kader-feature-badge.coordination-badge {
                        background: linear-gradient(135deg, #29b6f6, #4fc3f7);
                    }

                    .kader-feature-arrow {
                        color: #cbd5e0;
                        font-size: 0.9rem;
                        transition: all 0.3s ease;
                        margin-left: 1rem;
                    }

                    .kader-feature-item:hover .kader-feature-arrow {
                        color: #4a5568;
                        transform: translateX(3px);
                    }

                    /* Card Footer */
                    .kader-feature-footer {
                        background: linear-gradient(135deg, #f7fafc, #edf2f7);
                        padding: 1.5rem 2rem;
                        border-top: 1px solid rgba(0, 0, 0, 0.05);
                    }

                    .kader-feature-stats {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 1.5rem;
                    }

                    .kader-feature-stat {
                        display: flex;
                        align-items: center;
                        color: #718096;
                        font-size: 0.85rem;
                        font-weight: 500;
                    }

                    .kader-feature-stat i {
                        margin-right: 0.5rem;
                        font-size: 0.9rem;
                        color: #4a5568;
                    }

                    .kader-feature-divider {
                        width: 1px;
                        height: 20px;
                        background: rgba(0, 0, 0, 0.1);
                    }

                    /* Responsive */
                    @media (max-width: 768px) {
                        .kader-feature-header {
                            flex-direction: column;
                            text-align: center;
                            padding: 2rem 1.5rem;
                        }

                        .kader-feature-icon-wrapper {
                            margin-right: 0;
                            margin-bottom: 1rem;
                        }

                        .kader-feature-body {
                            padding: 1.5rem;
                        }

                        .kader-feature-item {
                            padding: 1rem;
                        }

                        .kader-feature-icon-box {
                            margin-right: 1rem;
                        }

                        .kader-feature-stats {
                            flex-direction: column;
                            gap: 1rem;
                        }

                        .kader-feature-divider {
                            width: 30px;
                            height: 1px;
                            transform: rotate(90deg);
                        }
                    }

                    @media (max-width: 480px) {
                        .kader-feature-card {
                            margin: 0 0.5rem;
                        }

                        .kader-feature-title {
                            font-size: 1.5rem;
                        }

                        .kader-feature-pulsing-icon {
                            width: 60px;
                            height: 60px;
                        }

                        .kader-feature-pulsing-icon i {
                            font-size: 1.5rem;
                        }

                        .kader-feature-item {
                            flex-direction: column;
                            text-align: center;
                        }

                        .kader-feature-icon-box {
                            margin-right: 0;
                            margin-bottom: 1rem;
                        }

                        .kader-feature-arrow {
                            margin-left: 0;
                            margin-top: 0.5rem;
                            transform: rotate(90deg);
                        }
                    }
                </style>
            </div>

            <!-- Data Anak Section -->
            <div class="section hidden fade-in" id="anak">
                <div class="section-header">
                    <h3><i class="fas fa-baby"></i> Kelola Data Balita</h3>
                    <div class="btn-group">
                        <!-- First Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary active" onclick="showAnakTab('sialang', event)">Desa Sialang</button>
                            <button class="btn btn-primary" onclick="showAnakTab('ujungpadang', event)">Desa Ujung Padang</button>
                            <button class="btn btn-primary" onclick="showAnakTab('baratdaya', event)">Desa Barat Daya</button>
                            <button class="btn btn-primary" onclick="showAnakTab('kapeh', event)">Desa Kapeh</button>
                            <button class="btn btn-primary" onclick="showAnakTab('kedaikandang', event)">Desa Kedai Kandang</button>
                            <button class="btn btn-primary" onclick="showAnakTab('kedairunding', event)">Desa Kedai Runding</button>
                            <button class="btn btn-primary" onclick="showAnakTab('suaqbakung', event)">Desa Suaq Bakung</button>
                        </div>

                        <!-- Second Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary" onclick="showAnakTab('rantaubinuang', event)">Desa Rantau Binuang</button>
                            <button class="btn btn-primary" onclick="showAnakTab('pulauie', event)">Desa Pulau Ie</button>
                            <button class="btn btn-primary" onclick="showAnakTab('luar', event)">Desa Luar</button>
                            <button class="btn btn-primary" onclick="showAnakTab('ujung', event)">Desa Ujung</button>
                            <button class="btn btn-primary" onclick="showAnakTab('jua', event)">Desa Jua</button>
                            <button class="btn btn-primary" onclick="showAnakTab('pasimeurapat', event)">Desa Pasi Meurapat</button>
                            <button class="btn btn-primary" onclick="showAnakTab('ujungpasir', event)">Desa Ujung Pasir</button>
                        </div>

                        <!-- Third Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary" onclick="showAnakTab('geulumbuk', event)">Desa Geulumbuk</button>
                            <button class="btn btn-primary" onclick="showAnakTab('pasilembang', event)">Desa Pasie Lembang</button>
                            <button class="btn btn-primary" onclick="showAnakTab('indradamal', event)">Desa Indra Damal</button>
                        </div>

                        <!-- Add Child Button -->
                        <div class="btn-add">
                            <a href="anak_add.php" class="btn btn-success">
                                <i class="fas fa-plus"></i> Tambah Data Anak Balita
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tab Sialang -->
                <div id="anak-sialang-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_yanti ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit_satu.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>

                                            <a href="anak_hapus/anak_delete_satu.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Ujung Padang -->
                <div id="anak-ujungpadang-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Ujung Padang -->
                <div id="anak-baratdaya-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_bariahh ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit_dua.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete_dua.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab kapeh -->
                <div id="anak-kapeh-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_nawawi ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit_empat.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete_empat.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Tab kedai kandang -->
                <div id="anak-kedaikandang-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_al ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit_lima.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete_lima.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-kedairunding-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_farmala ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit_enam.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete_enam.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <div id="anak-suaqbakung-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_rahmad ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit_tiga.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete_tiga.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-rantaubinuang-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_maulana ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit_tujuh.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete_tujuh.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-pulauie-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_ari ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit_lapan.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete_lapan.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-luar-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_rafif ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit9.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete9.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-ujung-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_andi ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit10.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete10.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-jua-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_siti ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit11.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete11.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-pasimeurapat-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_budi ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit12.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete12.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-ujungpasir-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_fitri ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit13.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete13.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-geulumbuk-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_hasan ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit14.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete14.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-pasilembang-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_lina ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit15.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete15.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="anak-indradamal-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Anak</th>
                                    <th>Umur (bulan)</th>
                                    <th>Nama Orang Tua</th>
                                    <th>Alamat</th>
                                    <th>BB Lalu</th>
                                    <th>BB Ini</th>
                                    <th>PB/TB Lalu</th>
                                    <th>PB/TB Ini</th>
                                    <th>LK Lalu</th>
                                    <th>LK Ini</th>
                                    <th>Status Gizi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $result = $conn->query("SELECT * FROM anak_dedi ORDER BY id DESC");
                                while ($row = $result->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['bb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['pbtb_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_lalu']); ?></td>
                                        <td><?= htmlspecialchars($row['lk_ini']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="anak_edit/anak_edit16.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="anak_hapus/anak_delete16.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>





            </div>

            <!-- Data Remaja Section -->
            <div class="section hidden fade-in" id="remaja">
                <div class="section-header">
                    <h3><i class="fas fa-user"></i> Kelola Data Remaja</h3>
                    <div class="btn-group">
                        <!-- First Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary active" onclick="showRemajaTab('sialang', event)">Desa Sialang</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('ujungpadang', event)">Desa Ujung Padang</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('baratdaya', event)">Desa Barat Daya</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('kapeh', event)">Desa Kapeh</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('kedaikandang', event)">Desa Kedai Kandang</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('kedairunding', event)">Desa Kedai Runding</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('suaqbakung', event)">Desa Suaq Bakung</button>
                        </div>

                        <!-- Second Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary" onclick="showRemajaTab('rantaubinuang', event)">Desa Rantau Binuang</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('pulauie', event)">Desa Pulau Ie</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('luar', event)">Desa Luar</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('ujung', event)">Desa Ujung</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('jua', event)">Desa Jua</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('pasimeurapat', event)">Desa Pasi Meurapat</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('ujungpasir', event)">Desa Ujung Pasir</button>
                        </div>

                        <!-- Third Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary" onclick="showRemajaTab('geulumbuk', event)">Desa Geulumbuk</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('pasilembang', event)">Desa Pasi Lembang</button>
                            <button class="btn btn-primary" onclick="showRemajaTab('indradamal', event)">Desa Indra Damal</button>
                        </div>

                        <!-- Add Remaja Button -->
                        <div class="btn-add">
                            <a href="anak_remaja_add.php" class="btn btn-success">
                                <i class="fas fa-plus"></i> Tambah Data Remaja
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tab Sialang untuk Remaja -->
                <div id="remaja-sialang-tab" class="anak-tab">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_yanti WHERE alamat = 'Sialang' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit1.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete1.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tab Ujung Padang untuk Remaja -->
                <div id="remaja-ujungpadang-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_rian WHERE alamat = 'Ujung Padang' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit2.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete2.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-kedaikandang-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_ali WHERE alamat = 'Kedai Kandang' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit5.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete5.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-kedairunding-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_farmala WHERE alamat = 'Kedai Runding' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit6.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete6.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-kapeh-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_nawawi WHERE alamat = 'Kapeh' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit4.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete4.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-baratdaya-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_bariah WHERE alamat = 'Barat Daya' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit3.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete3.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <div id="remaja-suaqbakung-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_rahmad WHERE alamat = 'Suaq Bakung' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit7.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete7.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-rantaubinuang-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_maulana WHERE alamat = 'Rantau Binuang' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit8.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete8.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-pulauie-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_ari WHERE alamat = 'pulo ie' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit9.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete9.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-luar-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_rafif WHERE alamat = 'Luar' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit10.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete10.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-ujung-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_andi WHERE alamat = 'Ujung' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit11.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete11.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-jua-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_siti WHERE alamat = 'Jua' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit12.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete12.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-pasimeurapat-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_budi WHERE alamat = 'Pasi Meurapat' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit13.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete13.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-ujungpasir-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_fitri WHERE alamat = 'Ujung Pasir' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit14.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete14.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-geulumbuk-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_hasan WHERE alamat = 'Geulumbuk' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit15.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete15.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-pasilembang-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_lina WHERE alamat = 'Pasilembang' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit16.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete16.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="remaja-indradamal-tab" class="anak-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Remaja</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>Orang Tua</th>
                                    <th>BB (kg)</th>
                                    <th>TB (cm)</th>
                                    <th>Status Gizi</th>
                                    <th>Status Pubertas</th>
                                    <th>Menstruasi Pertama</th>
                                    <th>Status Reproduksi</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM remaja_dedi WHERE alamat = 'Indradamal' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['orang_tua']); ?></td>
                                        <td><?= htmlspecialchars($row['bb']); ?></td>
                                        <td><?= htmlspecialchars($row['tb']); ?></td>
                                        <td><?= htmlspecialchars($row['status_gizi']); ?></td>
                                        <td><?= htmlspecialchars($row['status_pubertas']); ?></td>
                                        <td><?= $row['menstruasi_pertama'] ? htmlspecialchars($row['menstruasi_pertama']) : '-'; ?></td>
                                        <td><?= htmlspecialchars($row['status_reproduksi']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="remaja_edit/remaja_edit17.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="remaja_hapus/remaja_delete17.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>







            </div>


            <div class="section hidden fade-in" id="lansia">
                <div class="section-header">
                    <h3><i class="fas fa-user-tie"></i> Kelola Data Lansia</h3>
                    <div class="btn-group">
                        <!-- First Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary active" onclick="showLansiaTab('sialang', event)">Desa Sialang</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('ujungpadang', event)">Desa Ujung Padang</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('baratdaya', event)">Desa Barat Daya</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('kapeh', event)">Desa Kapeh</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('kedaikandang', event)">Desa Kedai Kandang</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('kedairunding', event)">Desa Kedai Runding</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('suaqbakung', event)">Desa Suaq Bakung</button>
                        </div>

                        <!-- Second Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary" onclick="showLansiaTab('rantaubinuang', event)">Desa Rantau Binuang</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('pulauie', event)">Desa Pulau Ie</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('luar', event)">Desa Luar</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('ujung', event)">Desa Ujung</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('jua', event)">Desa Jua</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('ujungpasir', event)">Desa Pasi Meurapat</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('ujung', event)">Desa Ujung Pasir</button>
                        </div>

                        <!-- Third Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary" onclick="showLansiaTab('Geulumbuk', event)">Desa Geulumbuk</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('pasilembang', event)">Desa Pasie Lembang</button>
                            <button class="btn btn-primary" onclick="showLansiaTab('indradamai', event)">Desa Pasie Indra Damai</button>
                        </div>

                        <!-- Add Lansia Button -->
                        <div class="btn-add">
                            <a href="lansia_add.php" class="btn btn-success">
                                <i class="fas fa-plus"></i> Tambah Data Lansia
                            </a>
                        </div>
                    </div>
                </div>
                <div id="lansia-sialang-tab" class="lansia-tab">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Lansia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>No. HP/Kontak</th>
                                    <th>Nama Keluarga</th>
                                    <th>No. HP Keluarga</th>
                                    <th>Riwayat Penyakit</th>
                                    <th>Obat Rutin</th>
                                    <th>Status Kesehatan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM lansia_yanti WHERE alamat = 'Sialang' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nik']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['riwayat_penyakit']); ?></td>
                                        <td><?= htmlspecialchars($row['obat_rutin']); ?></td>
                                        <td><?= htmlspecialchars($row['status_kesehatan']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="lansia_edit/lansia_edit_satu.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="lansia_hapus/lansia_delete_satu.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="lansia-ujungpadang-tab" class="lansia-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Lansia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>No. HP/Kontak</th>
                                    <th>Nama Keluarga</th>
                                    <th>No. HP Keluarga</th>
                                    <th>Riwayat Penyakit</th>
                                    <th>Obat Rutin</th>
                                    <th>Status Kesehatan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM lansia_rian WHERE alamat = 'Ujung Padang' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nik']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['riwayat_penyakit']); ?></td>
                                        <td><?= htmlspecialchars($row['obat_rutin']); ?></td>
                                        <td><?= htmlspecialchars($row['status_kesehatan']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="lansia_edit/lansia_edit2.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="lansia_hapus/lansia_delete2.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


                <div id="lansia-baratdaya-tab" class="lansia-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Lansia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>No. HP/Kontak</th>
                                    <th>Nama Keluarga</th>
                                    <th>No. HP Keluarga</th>
                                    <th>Riwayat Penyakit</th>
                                    <th>Obat Rutin</th>
                                    <th>Status Kesehatan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM lansia_bariah WHERE alamat = 'Barat Daya' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nik']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['riwayat_penyakit']); ?></td>
                                        <td><?= htmlspecialchars($row['obat_rutin']); ?></td>
                                        <td><?= htmlspecialchars($row['status_kesehatan']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="lansia_edit/lansia_edit3.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="lansia_hapus/lansia_delete3.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="lansia-kapeh-tab" class="lansia-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Lansia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>No. HP/Kontak</th>
                                    <th>Nama Keluarga</th>
                                    <th>No. HP Keluarga</th>
                                    <th>Riwayat Penyakit</th>
                                    <th>Obat Rutin</th>
                                    <th>Status Kesehatan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM lansia_nawawi WHERE alamat = 'kapeh' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nik']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['riwayat_penyakit']); ?></td>
                                        <td><?= htmlspecialchars($row['obat_rutin']); ?></td>
                                        <td><?= htmlspecialchars($row['status_kesehatan']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="lansia_edit/lansia_edit4.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="lansia_hapus/lansia_delete4.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="lansia-kapeh-tab" class="lansia-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Lansia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>No. HP/Kontak</th>
                                    <th>Nama Keluarga</th>
                                    <th>No. HP Keluarga</th>
                                    <th>Riwayat Penyakit</th>
                                    <th>Obat Rutin</th>
                                    <th>Status Kesehatan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM lansia_nawawi WHERE alamat = 'kapeh' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nik']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['riwayat_penyakit']); ?></td>
                                        <td><?= htmlspecialchars($row['obat_rutin']); ?></td>
                                        <td><?= htmlspecialchars($row['status_kesehatan']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="lansia_edit/lansia_edit4.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="lansia_hapus/lansia_delete4.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="lansia-kedaikandang-tab" class="lansia-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Lansia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>No. HP/Kontak</th>
                                    <th>Nama Keluarga</th>
                                    <th>No. HP Keluarga</th>
                                    <th>Riwayat Penyakit</th>
                                    <th>Obat Rutin</th>
                                    <th>Status Kesehatan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM lansia_al WHERE alamat = 'Kedai Kandang' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nik']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['riwayat_penyakit']); ?></td>
                                        <td><?= htmlspecialchars($row['obat_rutin']); ?></td>
                                        <td><?= htmlspecialchars($row['status_kesehatan']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="lansia_edit/lansia_edit5.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="lansia_hapus/lansia_delete5.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>


             <div id="lansia-kedairunding-tab" class="lansia-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Lansia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>No. HP/Kontak</th>
                                    <th>Nama Keluarga</th>
                                    <th>No. HP Keluarga</th>
                                    <th>Riwayat Penyakit</th>
                                    <th>Obat Rutin</th>
                                    <th>Status Kesehatan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM lansia_Farmala WHERE alamat = 'Kedai Runding' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nik']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['riwayat_penyakit']); ?></td>
                                        <td><?= htmlspecialchars($row['obat_rutin']); ?></td>
                                        <td><?= htmlspecialchars($row['status_kesehatan']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="lansia_edit/lansia_edit6.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="lansia_hapus/lansia_delete6.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="lansia-suaqbakung-tab" class="lansia-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Lansia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>No. HP/Kontak</th>
                                    <th>Nama Keluarga</th>
                                    <th>No. HP Keluarga</th>
                                    <th>Riwayat Penyakit</th>
                                    <th>Obat Rutin</th>
                                    <th>Status Kesehatan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM lansia_rahmad WHERE alamat = 'Suaq Bakung' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nik']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['riwayat_penyakit']); ?></td>
                                        <td><?= htmlspecialchars($row['obat_rutin']); ?></td>
                                        <td><?= htmlspecialchars($row['status_kesehatan']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="lansia_edit/lansia_edit7.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="lansia_hapus/lansia_delete7.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                   <div id="lansia-rantaubinuang-tab" class="lansia-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Lansia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>No. HP/Kontak</th>
                                    <th>Nama Keluarga</th>
                                    <th>No. HP Keluarga</th>
                                    <th>Riwayat Penyakit</th>
                                    <th>Obat Rutin</th>
                                    <th>Status Kesehatan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM lansia_maulana WHERE alamat = 'Rantau Binuang' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nik']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['riwayat_penyakit']); ?></td>
                                        <td><?= htmlspecialchars($row['obat_rutin']); ?></td>
                                        <td><?= htmlspecialchars($row['status_kesehatan']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="lansia_edit/lansia_edit8.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="lansia_hapus/lansia_delete8.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                   <div id="lansia-pulauie-tab" class="lansia-tab hidden">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIK</th>
                                    <th>Nama Lansia</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tgl Lahir</th>
                                    <th>Umur</th>
                                    <th>Alamat</th>
                                    <th>No. HP/Kontak</th>
                                    <th>Nama Keluarga</th>
                                    <th>No. HP Keluarga</th>
                                    <th>Riwayat Penyakit</th>
                                    <th>Obat Rutin</th>
                                    <th>Status Kesehatan</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                $query = mysqli_query($conn, "SELECT * FROM lansia_ari WHERE alamat = 'pulau ie' ORDER BY id DESC");
                                while ($row = mysqli_fetch_assoc($query)):
                                ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($row['nik']); ?></td>
                                        <td><?= htmlspecialchars($row['nama']); ?></td>
                                        <td><?= htmlspecialchars($row['jk']); ?></td>
                                        <td><?= htmlspecialchars($row['tgl_lahir']); ?></td>
                                        <td><?= (int)$row['umur']; ?></td>
                                        <td><?= htmlspecialchars($row['alamat']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['no_telepon_keluarga']); ?></td>
                                        <td><?= htmlspecialchars($row['riwayat_penyakit']); ?></td>
                                        <td><?= htmlspecialchars($row['obat_rutin']); ?></td>
                                        <td><?= htmlspecialchars($row['status_kesehatan']); ?></td>
                                        <td><?= htmlspecialchars($row['ket']); ?></td>
                                        <td class="actions">
                                            <a href="lansia_edit/lansia_edit9.php?id=<?= $row['id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="lansia_hapus/lansia_delete9.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete()">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>





            </div>

            <div class="section hidden fade-in" id="petugas">
                <div class="section-header">
                    <h3><i class="fas fa-baby"></i> Kelola informasi</h3>
                    <div class="btn-group">
                        <!-- First Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary active" onclick="showAnakTab('sialang', event)">Desa Sialang</button>
                            <button class="btn btn-primary" onclick="showAnakTab('ujungpadang', event)">Desa Ujung Padang</button>
                            <button class="btn btn-primary" onclick="showAnakTab('baratdaya', event)">Desa Barat Daya</button>
                            <button class="btn btn-primary" onclick="showAnakTab('kapeh', event)">Desa Kapeh</button>
                            <button class="btn btn-primary" onclick="showAnakTab('kedaikandang', event)">Desa Kedai Kandang</button>
                            <button class="btn btn-primary" onclick="showAnakTab('kedairunding', event)">Desa Kedai Runding</button>
                            <button class="btn btn-primary" onclick="showAnakTab('suaqbakung', event)">Desa Suaq Bakung</button>
                        </div>

                        <!-- Second Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary" onclick="showAnakTab('rantaubinuang', event)">Desa Rantau Binuang</button>
                            <button class="btn btn-primary" onclick="showAnakTab('pulauie', event)">Desa Pulau Ie</button>
                            <button class="btn btn-primary" onclick="showAnakTab('luar', event)">Desa Luar</button>
                            <button class="btn btn-primary" onclick="showAnakTab('ujung', event)">Desa Ujung</button>
                            <button class="btn btn-primary" onclick="showAnakTab('jua', event)">Desa Jua</button>
                            <button class="btn btn-primary" onclick="showAnakTab('ujungpasir', event)">Desa Pasi Meurapat</button>
                            <button class="btn btn-primary" onclick="showAnakTab('ujung', event)">Desa Ujung Pasir</button>
                        </div>

                        <!-- Third Row of Village Buttons -->
                        <div class="btn-row">
                            <button class="btn btn-primary" onclick="showAnakTab('Geulumbuk', event)">Desa Geulumbuk</button>
                            <button class="btn btn-primary" onclick="showAnakTab('pasilembang', event)">Desa Pasie Lembang</button>
                            <button class="btn btn-primary" onclick="showAnakTab('indradamai', event)">Desa Indra Damai</button>
                        </div>

                        <!-- Add Child Button -->
                        <div class="btn-add">
                            <a href="anak_add.php" class="btn btn-success">
                                <i class="fas fa-plus"></i> Update Informasi
                            </a>
                        </div>
                    </div>
                </div>
                <script>
                    // Menampilkan section berdasarkan ID
                    function showSection(sectionId) {
                        // Hapus class 'active' dari semua menu
                        document.querySelectorAll('.menu-item').forEach(item => {
                            item.classList.remove('active');
                        });

                        // Tambahkan class 'active' ke menu yang diklik
                        document.querySelector(`[onclick="showSection('${sectionId}')"]`).classList.add('active');

                        // Sembunyikan semua section
                        document.querySelectorAll('.section').forEach(section => {
                            section.classList.add('hidden');
                        });

                        // Tampilkan section yang dipilih
                        const selectedSection = document.getElementById(sectionId);
                        if (selectedSection) {
                            selectedSection.classList.remove('hidden');
                            selectedSection.classList.add('fade-in');

                            // Jika section-nya adalah anak, tampilkan tab default 'sialang'
                            if (sectionId === 'anak') {
                                showAnakTab('sialang', null);
                            }
                            // Jika section-nya adalah remaja, tampilkan tab default 'sialang'
                            else if (sectionId === 'remaja') {
                                showRemajaTab('sialang', null);
                            }
                        }
                    }

                    // Menampilkan tab anak berdasarkan desa yang dipilih
                    function showAnakTab(desa, event) {
                        // Sembunyikan semua tab anak
                        document.querySelectorAll('#anak .anak-tab').forEach(tab => {
                            tab.classList.add('hidden');
                        });

                        // Tampilkan tab sesuai desa
                        const targetTab = document.getElementById(`anak-${desa}-tab`);
                        if (targetTab) {
                            targetTab.classList.remove('hidden');
                        }

                        // Update tombol aktif
                        if (event) {
                            const btnGroup = event.target.closest('.btn-group');
                            if (btnGroup) {
                                btnGroup.querySelectorAll('button').forEach(btn => {
                                    btn.classList.remove('active');
                                });
                                event.target.classList.add('active');
                            }
                        }
                    }

                    // Menampilkan tab remaja berdasarkan desa yang dipilih
                    function showRemajaTab(desa, event) {
                        // Sembunyikan semua tab remaja
                        document.querySelectorAll('#remaja .anak-tab').forEach(tab => {
                            tab.classList.add('hidden');
                        });

                        // Tampilkan tab sesuai desa
                        const targetTab = document.getElementById(`remaja-${desa}-tab`);
                        if (targetTab) {
                            targetTab.classList.remove('hidden');
                        }

                        // Update tombol aktif
                        if (event) {
                            const btnGroup = event.target.closest('.btn-group');
                            if (btnGroup) {
                                btnGroup.querySelectorAll('button').forEach(btn => {
                                    btn.classList.remove('active');
                                });
                                event.target.classList.add('active');
                            }
                        }
                    }

                    function showLansiaTab(tabName, event) {
                        // Sembunyikan semua tab lansia
                        document.querySelectorAll('.lansia-tab').forEach(tab => {
                            tab.classList.add('hidden');
                        });

                        // Tampilkan tab yang dipilih
                        const targetTab = document.getElementById(`lansia-${tabName}-tab`);
                        if (targetTab) {
                            targetTab.classList.remove('hidden');
                        }

                        // Update tombol aktif
                        if (event) {
                            const btnGroup = event.target.closest('.btn-group');
                            if (btnGroup) {
                                btnGroup.querySelectorAll('button').forEach(btn => {
                                    btn.classList.remove('active');
                                });
                                event.target.classList.add('active');
                            }
                        }
                    }

                    // Panggil fungsi ini saat halaman load untuk menampilkan tab default
                    document.addEventListener('DOMContentLoaded', function() {
                        showLansiaTab('sialang', null);
                    });

                    function confirmDelete(event, element) {
                        event.preventDefault(); // mencegah langsung pergi ke link

                        Swal.fire({
                            title: 'Apakah Anda yakin?',
                            text: "Data yang dihapus tidak dapat dikembalikan!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d63384',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = element.getAttribute('href'); // lanjut ke link hapus
                            }
                        });
                    }
                    document.getElementById('logoutBtn').addEventListener('click', function(event) {
                        event.preventDefault(); // cegah langsung logout

                        Swal.fire({
                            title: 'Apakah Anda yakin ingin logout?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#d63384',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, logout',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'logout.php'; // arahkan ke logout.php
                            }
                        });
                    });

                    // Inisialisasi halaman
                    document.addEventListener('DOMContentLoaded', function() {
                        // Tampilkan section welcome secara default
                        showSection('welcome');
                    });
                </script>

                <script>
                    // Tambahkan script untuk dropdown
                    document.addEventListener('DOMContentLoaded', function() {
                        const dropdowns = document.querySelectorAll('.dropdown');

                        dropdowns.forEach(dropdown => {
                            const header = dropdown.querySelector('.dropdown-header');

                            header.addEventListener('click', function() {
                                dropdown.classList.toggle('active');

                                // Tutup dropdown lainnya yang terbuka
                                dropdowns.forEach(otherDropdown => {
                                    if (otherDropdown !== dropdown && otherDropdown.classList.contains('active')) {
                                        otherDropdown.classList.remove('active');
                                    }
                                });
                            });
                        });

                        // Tetap pertahankan fungsi showSection yang sudah ada
                    });

                    document.addEventListener('DOMContentLoaded', function() {
                        // Inisialisasi submenu
                        const submenuHeaders = document.querySelectorAll('.submenu-header');

                        submenuHeaders.forEach(header => {
                            header.addEventListener('click', function() {
                                const parent = this.parentElement;
                                parent.classList.toggle('active');

                                // Tutup submenu lainnya yang terbuka
                                document.querySelectorAll('.has-submenu').forEach(menu => {
                                    if (menu !== parent && menu.classList.contains('active')) {
                                        menu.classList.remove('active');
                                    }
                                });
                            });
                        });

                        // Aktifkan submenu jika section yang terkait sedang aktif
                        const activeSection = document.querySelector('.section:not(.hidden)');
                        if (activeSection) {
                            const sectionId = activeSection.id;
                            if (['anak', 'remaja', 'lansia'].includes(sectionId)) {
                                document.querySelector('.has-submenu').classList.add('active');
                            }
                        }
                    });

                    // Fungsi showSection yang sudah ada
                    function showSection(sectionId) {
                        // Hapus kelas aktif dari semua menu
                        document.querySelectorAll('.menu-item').forEach(item => {
                            item.classList.remove('active');
                        });

                        // Tambahkan kelas aktif ke menu yang sesuai
                        if (['anak', 'remaja', 'lansia'].includes(sectionId)) {
                            document.querySelector('.has-submenu').classList.add('active');
                        }

                        // Sembunyikan semua section
                        document.querySelectorAll('.section').forEach(section => {
                            section.classList.add('hidden');
                        });

                        // Tampilkan section yang dipilih
                        const selectedSection = document.getElementById(sectionId);
                        if (selectedSection) {
                            selectedSection.classList.remove('hidden');
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        // Mobile menu toggle functionality
                        const mobileToggle = document.createElement('button');
                        mobileToggle.className = 'mobile-menu-toggle';
                        mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
                        document.body.prepend(mobileToggle);

                        const sidebar = document.querySelector('.sidebar-menu');

                        mobileToggle.addEventListener('click', function() {
                            sidebar.classList.toggle('active');
                        });

                        // Close menu when clicking outside on mobile
                        document.addEventListener('click', function(e) {
                            if (window.innerWidth <= 600 &&
                                !sidebar.contains(e.target) &&
                                e.target !== mobileToggle) {
                                sidebar.classList.remove('active');
                            }
                        });
                    });

                    // Script untuk konfirmasi hapus dengan SweetAlert
                    function confirmDelete(event, element) {
                        event.preventDefault(); // Mencegah link langsung dijalankan

                        const deleteUrl = element.href;

                        Swal.fire({
                            title: 'Konfirmasi Hapus Data',
                            text: 'Apakah Anda yakin ingin menghapus data anak ini?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
                            cancelButtonText: '<i class="fas fa-times"></i> Batal',
                            reverseButtons: true,
                            focusCancel: true,
                            backdrop: true,
                            allowOutsideClick: false,
                            customClass: {
                                confirmButton: 'btn-delete-confirm',
                                cancelButton: 'btn-delete-cancel'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Tampilkan loading
                                Swal.fire({
                                    title: 'Menghapus Data...',
                                    text: 'Mohon tunggu sebentar',
                                    icon: 'info',
                                    allowOutsideClick: false,
                                    showConfirmButton: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                // Redirect ke URL hapus
                                window.location.href = deleteUrl;
                            }
                        });
                    }



                    // Inject CSS ke head
                    document.head.insertAdjacentHTML('beforeend', additionalCSS);
                </script>

                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

                <script>
                    setTimeout(function() {
                        const alertBox = document.getElementById('alert');
                        if (alertBox) {
                            alertBox.style.transition = 'opacity 1s ease';
                            alertBox.style.opacity = '0';
                            setTimeout(() => alertBox.remove(), 1000);
                        }
                    }, 2000); // alert hilang setelah 2 detik
                </script>



</body>

</html>