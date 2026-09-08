<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('home') }}" class="brand-link">
        <img src="{{ asset('images/logo.jpg') }}" alt="Yayasan Unggul Amanah Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Yayasan Unggul Amanah</span>
    </a>
    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <!-- <div class="image">
              <img src="../../dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div> -->
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->roles[0]->name }}</a>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('home') }}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                @can('role-list')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('roles.index') }}">
                            <p>Manage Role</p>
                        </a>
                    </li>
                @endcan
                @can('pegawai-list')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pegawai.index') }}">
                            <i class="nav-icon fas fa-user"></i>
                            <p>Manage Relawan</p>
                        </a>
                    </li>
                @endcan
                @can('pekerjaan-list')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('pekerjaan.index') }}">
                            <p>Manage Pekerjaan</p>
                        </a>
                    </li>
                @endcan
                @can('program-list')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('program.index') }}">
                            <i class="align-middle" data-feather="sliders"></i>
                            <p>Manage Program</p>
                        </a>
                    </li>
                @endcan
                @can('donatur-list')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('donatur.index') }}">
                            <i class="align-middle" data-feather="sliders"></i>
                            <p>Manage Donatur</p>
                        </a>
                    </li>
                @endcan
                @can('transaksi-list')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('transaksi.index') }}">
                            <i class="align-middle" data-feather="sliders"></i>
                            <p>Transaksi</p>
                        </a>
                    </li>
                @endcan
                @can('setoran-list')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('setoran.index') }}">
                            <i class="align-middle" data-feather="sliders"></i>
                            <p>Setoran</p>
                        </a>
                    </li>
                @endcan
                @can('reha-list')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reha.index') }}">
                            <i class="align-middle" data-feather="sliders"></i>
                            <p>Report Harian</p>
                        </a>
                    </li>
                @endcan
                @can('korel-list')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('korel.index') }}">
                            <i class="align-middle" data-feather="sliders"></i>
                            <p>Koordinator Relawan</p>
                        </a>
                    </li>
                @endcan

            </ul>
        </nav>
    </div>
</aside>
