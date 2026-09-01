<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

@include('partials.head')

<body>

    @include('partials.sidebar')

    <main class="dashboard-main">
        @include('partials.navbar')

        <div class="dashboard-main-body">

            @include('partials.breadcrumb')

            @yield('content')
        </div>

        @include('partials.footer')
    </main>

    @include('partials.scripts')
    @yield('scripts')
</body>

</html>
