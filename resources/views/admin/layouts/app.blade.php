<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Admin Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f4f4;
        }

        .admin-header {
            background: #1f3f96;
            color: #fff;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-header h1 {
            margin: 0;
            font-size: 18px;
        }

        .admin-header form {
            margin: 0;
        }

        .admin-header button {
            background: #fff;
            border: 1px solid #ccc;
            padding: 6px 10px;
            cursor: pointer;
            font-size: 12px;
        }

        .admin-content {
            padding: 20px;
        }
    </style>

    @stack('head')
</head>

<body>

    <div class="admin-header">
        <h1>FreeCause Admin</h1>

        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit">logout</button>
        </form>
    </div>

    <div class="admin-content">
        @yield('content')
    </div>

    @stack('scripts')
</body>

</html>
