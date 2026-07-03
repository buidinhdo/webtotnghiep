<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bảng điều khiển Admin') - GameStation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <style>
        .sidebar-nav .nav-link { padding: 0.75rem 1.5rem; }
        .sidebar-nav .nav-link.active { background-color: #007bff; }
        .main-sidebar .nav-pills .nav-link.no-active-bg.active,
        .main-sidebar .nav-pills .nav-link.no-active-bg.active:hover,
        .main-sidebar .nav-pills .nav-link.no-active-bg.active:focus {
            background: transparent !important;
            background-color: transparent !important;
            box-shadow: none !important;
            color: #ffffff !important;
        }

        .main-sidebar .nav-pills .nav-link.no-active-bg.active .nav-icon,
        .main-sidebar .nav-pills .nav-link.no-active-bg.active p,
        .main-sidebar .nav-pills .nav-link.no-active-bg.active i {
            color: #ffffff !important;
        }
        .main-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .card-title { font-size: 1rem; font-weight: 600; }
        .stat-box { border-left: 4px solid #007bff; padding: 1rem; }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            text-decoration: none;
            background: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0;
        }

        .brand-logo {
            width: 38px;
            height: 24px;
            border-radius: 999px;
            background: linear-gradient(180deg, #38bdf8 0%, #0ea5e9 100%);
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 -2px 0 rgba(0, 0, 0, 0.12);
            flex: 0 0 auto;
        }

        .brand-logo::before,
        .brand-logo::after {
            content: '';
            position: absolute;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #0f172a;
            top: 8px;
        }

        .brand-logo::before { left: 10px; }
        .brand-logo::after { right: 10px; }

        .brand-text {
            color: #111827;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1;
        }
    </style>
    @yield('extra_css')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-user-circle"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Hồ sơ
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('admin.store-info.edit') }}" class="dropdown-item">
                            <i class="fas fa-store mr-2"></i> Thông tin cửa hàng
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item" style="border: none; background: none; cursor: pointer; width: 100%; text-align: left;">
                                <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('home') }}" class="brand-link">
                <span class="brand-logo" aria-hidden="true"></span>
                <span class="brand-text">GameStation</span>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link no-active-bg {{ Route::currentRouteName() == 'admin.dashboard' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Tổng quan</p>
                            </a>
                        </li>

                        <li class="nav-header">QUẢN LÝ</li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'products') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'products') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-box"></i>
                                <p>Sản phẩm <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.products.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách sản phẩm</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.products.create') }}" class="nav-link {{ Route::currentRouteName() == 'admin.products.create' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thêm sản phẩm</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'categories') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'categories') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-list"></i>
                                <p>Danh mục <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.categories.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách danh mục</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.categories.create') }}" class="nav-link {{ Route::currentRouteName() == 'admin.categories.create' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thêm danh mục</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'genres') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'genres') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tags"></i>
                                <p>Thể loại <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.genres.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.genres.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách thể loại</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.genres.create') }}" class="nav-link {{ Route::currentRouteName() == 'admin.genres.create' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thêm thể loại</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'esrb') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'esrb') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-star"></i>
                                <p>ESRB <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.esrb.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.esrb.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách ESRB</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.esrb.create') }}" class="nav-link {{ Route::currentRouteName() == 'admin.esrb.create' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thêm ESRB</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'publishers') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'publishers') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-building"></i>
                                <p>Nhà phát hành <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.publishers.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.publishers.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách nhà phát hành</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.publishers.create') }}" class="nav-link {{ Route::currentRouteName() == 'admin.publishers.create' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thêm nhà phát hành</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'orders') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'orders') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shopping-cart"></i>
                                <p>Đơn hàng <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.orders.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách đơn hàng</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'customers') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'customers') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Khách hàng <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.customers.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.customers.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách khách hàng</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'banners') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'banners') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-image"></i>
                                <p>Banner <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.banners.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.banners.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách banner</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.banners.create') }}" class="nav-link {{ Route::currentRouteName() == 'admin.banners.create' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thêm banner</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'articles') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'articles') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-newspaper"></i>
                                <p>Bài viết <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.articles.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.articles.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách bài viết</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.articles.create') }}" class="nav-link {{ Route::currentRouteName() == 'admin.articles.create' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thêm bài viết</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'reviews') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'reviews') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-star"></i>
                                <p>Đánh giá <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.reviews.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách đánh giá</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'contacts') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'contacts') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-envelope"></i>
                                <p>Liên hệ <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.contacts.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.contacts.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách liên hệ</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item {{ Str::contains(Route::currentRouteName(), 'coupons') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ Str::contains(Route::currentRouteName(), 'coupons') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-ticket-alt"></i>
                                <p>Mã giảm giá <i class="right fas fa-angle-left"></i></p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ Route::currentRouteName() == 'admin.coupons.index' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách mã giảm giá</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.coupons.create') }}" class="nav-link {{ Route::currentRouteName() == 'admin.coupons.create' ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thêm mã giảm giá</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.chatbot.index') }}" class="nav-link {{ Str::contains(Route::currentRouteName(), 'admin.chatbot') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-comments"></i>
                                <p>Quản lý Chatbot</p>
                            </a>
                        </li>

                        <li class="nav-header">THỐNG KÊ</li>

                        <li class="nav-item">
                            <a href="{{ route('admin.statistics.revenue') }}" class="nav-link {{ Route::currentRouteName() == 'admin.statistics.revenue' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>Doanh thu</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.statistics.orders') }}" class="nav-link {{ Route::currentRouteName() == 'admin.statistics.orders' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Đơn hàng</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.statistics.users') }}" class="nav-link {{ Route::currentRouteName() == 'admin.statistics.users' ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>Người dùng</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('page_title', 'Bảng điều khiển')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Trang chủ</a></li>
                                <li class="breadcrumb-item active">@yield('breadcrumb', 'Bảng điều khiển')</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Lỗi!</strong>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <strong>GameStation Admin</strong>
            <div class="float-right d-none d-sm-inline-block">
                <b>Phiên bản</b> 1.0
            </div>
        </footer>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.min.js"></script>
    @yield('extra_js')
</body>
</html>
