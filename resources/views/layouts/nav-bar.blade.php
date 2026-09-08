<nav id="sidebar" class="sidebar js-sidebar">
    <div class="sidebar-content js-simplebar">
        <a class="sidebar-brand" href="{{ route('home') }}">
            <span class="align-middle">{{ Auth::user()->roles[0]->name }}</span>
        </a>
        <ul class="sidebar-nav">
            <li class="sidebar-header">Pages</li>
            <li class="sidebar-item">
                <a class="sidebar-link" href="{{ route('home') }}">
                    <i class="align-middle" data-feather="sliders"></i>
                    <span class="align-middle">Dashboard</span>
                </a>
            </li>
            @can('role-list')
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('roles.index') }}">
                        <i class="align-middle" data-feather="sliders"></i>
                        <span class="align-middle">Manage Role</span>
                    </a>
                </li>
            @endcan
            @can('pegawai-list')
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('pegawai.index') }}">
                        <i class="align-middle" data-feather="sliders"></i>
                        <span class="align-middle">Manage Penghimpun</span>
                    </a>
                </li>
            @endcan
            @can('pekerjaan-list')
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('pekerjaan.index') }}">
                        <i class="align-middle" data-feather="sliders"></i>
                        <span class="align-middle">Manage Pekerjaan</span>
                    </a>
                </li>
            @endcan
            @can('program-list')
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('program.index') }}">
                        <i class="align-middle" data-feather="sliders"></i>
                        <span class="align-middle">Manage Program</span>
                    </a>
                </li>
            @endcan
            @can('donatur-list')
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('donatur.index') }}">
                        <i class="align-middle" data-feather="sliders"></i>
                        <span class="align-middle">Manage Nasabah</span>
                    </a>
                </li>
            @endcan
            @can('import-list')
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('import.index') }}">
                        <i class="align-middle" data-feather="upload"></i>
                        <span class="align-middle">Import Data</span>
                    </a>
                </li>
            @endcan
            @can('tunai-list')
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('tunai.index') }}">
                        <i class="align-middle" data-feather="dollar-sign"></i>
                        <span class="align-middle">List Tunai</span>
                    </a>
                </li>
            @endcan
            @can('transaksi-list')
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('transaksi.index') }}">
                        <i class="align-middle" data-feather="sliders"></i>
                        <span class="align-middle">Transaksi</span>
                    </a>
                </li>
            @endcan
            @can('setoran-list')
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('setoran.index') }}">
                        <i class="align-middle" data-feather="sliders"></i>
                        <span class="align-middle">Setoran</span>
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
                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('korel.index') }}">
                        <i class="align-middle" data-feather="sliders"></i>
                        <span class="align-middle">Koordinator Penghimpun</span>
                    </a>
                </li>
            @endcan
        </ul>
    </div>
</nav>
