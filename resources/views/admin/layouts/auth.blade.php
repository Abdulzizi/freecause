<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Login')</title>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>

    <style>
        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f2f2f2;
        }

        .admin-auth-wrap {
            min-height: 100%;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 40px 15px;
        }

        .admin-auth-stage {
            width: 100%;
            max-width: 1050px;
            background: #ffffff;
            border: 1px solid #cfcfcf;
            border-radius: 4px;
            box-shadow: 0 0 0 6px rgba(120, 80, 80, 0.15);
            padding: 40px 20px 60px;
        }

        .admin-logo {
            text-align: center;
            margin: 10px 0 25px;
            font-size: 44px;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .admin-logo .red {
            color: #b30000;
        }

        .admin-logo .black {
            color: #111;
        }

        .login-box {
            width: 320px;
            margin: 0 auto;
            border-radius: 4px;
            border: 1px solid #3f66c7;
            background: linear-gradient(#86a9ff, #1f3f96);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
            padding: 16px;
        }

        .login-title {
            text-align: center;
            color: #e9f0ff;
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 6px;
            text-shadow: 0 1px 0 rgba(0, 0, 0, .25);
            margin: 4px 0 12px;
        }

        .field-label {
            color: #e9f0ff;
            font-size: 12px;
            margin: 10px 0 4px;
        }

        .field-input {
            width: 100%;
            box-sizing: border-box;
            padding: 8px 10px;
            border-radius: 2px;
            border: 1px solid #9bb2e8;
            outline: none;
            background: #fff;
        }

        .row-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 12px;
        }

        .remember {
            color: #e9f0ff;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            user-select: none;
        }

        .btn-login {
            border: 1px solid #a7b6da;
            background: #f2f2f2;
            padding: 6px 10px;
            border-radius: 2px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
        }

        .alert {
            width: 320px;
            margin: 0 auto 12px;
            border: 1px solid #d7b3b3;
            background: #fff2f2;
            color: #7a1d1d;
            padding: 10px 12px;
            border-radius: 3px;
            font-size: 13px;
        }

        .success {
            border: 1px solid #b8d7b3;
            background: #f2fff2;
            color: #1d7a2a;
        }
    </style>

    @stack('head')
</head>

<body>
    <div class="admin-auth-wrap">
        <div class="admin-auth-stage">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>

</html>
