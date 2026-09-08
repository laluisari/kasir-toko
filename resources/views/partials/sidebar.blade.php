<aside class="sidebar">
    @php $soLock = \App\Models\StockOpname::active()->exists(); @endphp
    <button type="button" class="sidebar-close-btn">
        <iconify-icon icon="radix-icons:cross-2"></iconify-icon>
    </button>
    <div>
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <img src="{{ asset('assets-admin/images/rentafy-logo/logo-full.svg') }}" alt="site logo" class="light-logo" style="max-width: 60%;">
            <img src="{{ asset('assets-admin/images/rentafy-logo/logo-light.svg') }}" alt="site logo" class="dark-logo" style="max-width: 60%;">
            <img src="{{ asset('assets-admin/images/rentafy-logo/logo-icon.svg') }}" alt="site logo" class="logo-icon">
        </a>
    </div>
    <div class="sidebar-menu-area">
        <ul class="sidebar-menu" id="sidebar-menu">
            <li>
                <a href="{{ route('dashboard') }}">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="menu-icon"></iconify-icon>
                    <span>Dashboard</span>
                </a>
            </li>

            @if(auth()->user()->role === 'admin')
            <li class="dropdown">
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:box-outline" class="menu-icon"></iconify-icon>
                    <span>Inventory</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('products.index') }}">
                            <i class="ri-circle-fill circle-icon text-success-main w-auto"></i> Produk
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('categories.index') }}">
                            <i class="ri-circle-fill circle-icon text-warning-main w-auto"></i> Kategori
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('stock-opname.index') }}">
                            <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> Stock Opname
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            <li class="dropdown" @if ($soLock) style="opacity: 0.45; pointer-events: none;" title="Penjualan terkunci — Stock Opname sedang berjalan." @endif>
                <a href="javascript:void(0)">
                    <iconify-icon icon="solar:cart-3-outline" class="menu-icon"></iconify-icon>
                    <span>Penjualan</span>
                </a>
                <ul class="sidebar-submenu">
                    <li>
                        <a href="{{ route('sales.index') }}">
                            <i class="ri-circle-fill circle-icon text-primary-600 w-auto"></i> 
                            @if(auth()->user()->role === 'kasir')
                                Kasir
                            @else
                                Kasir / POS
                            @endif
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('sales.history') }}">
                            <i class="ri-circle-fill circle-icon text-info-main w-auto"></i> History Penjualan
                        </a>
                    </li>
                </ul>
            </li>

            @if(auth()->user()->role === 'admin')
            <li>
                <a href="{{ route('users.index') }}">
                    <iconify-icon icon="flowbite:users-group-outline" class="menu-icon"></iconify-icon>
                    <span>Admin & Staff</span>
                </a>
            </li>

            <li>
                <a href="{{ route('buyers.index') }}">
                    <iconify-icon icon="solar:user-bold" class="menu-icon"></iconify-icon>
                    <span>Buyers</span>
                </a>
            </li>

            <li>
                <a href="{{ route('expense.index') }}">
                    <iconify-icon icon="solar:wallet-bold" class="menu-icon"></iconify-icon>
                    <span>Expense</span>
                </a>
            </li>
            @endif

        </ul>
    </div>
</aside>
