<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Admin')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        html,
        body {
            height: 100%;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #fff;
            color: #111;
        }

        /* top right utility bar */
        .fc-topbar {
            padding: 10px 14px;
            font-size: 14px;
            text-align: right;
            border-bottom: 1px solid #cfcfcf;
            background: #fff;
        }

        .fc-topbar a {
            color: #1a57c8;
            text-decoration: none;
        }

        .fc-topbar a:hover {
            text-decoration: underline;
        }

        .fc-topbar .sep {
            color: #777;
            padding: 0 6px;
        }

        /* frame */
        .fc-frame {
            display: flex;
            gap: 12px;
            padding: 10px;
            background: #fff;
        }

        /* left menu */
        .fc-menu {
            width: 210px;
            background: #d7d7d7;
            border: 1px solid #9e9e9e;
            border-radius: 4px;
            padding: 6px;
            box-sizing: border-box;
        }

        .fc-menu .group-title {
            font-weight: bold;
            padding: 6px 8px;
            background: #c7c7c7;
            border: 1px solid #9e9e9e;
            border-radius: 3px;
            margin: 6px 0 6px;
        }

        .fc-menu a {
            display: block;
            padding: 6px 8px;
            text-decoration: none;
            color: #0b53c1;
            border-radius: 3px;
            font-size: 14px;
        }

        .fc-menu a:hover {
            background: #c9c9c9;
        }

        .fc-menu a.active {
            background: #bdbdbd;
            color: #000;
            font-weight: bold;
        }

        /* content panel */
        .fc-panel {
            flex: 1;
            background: #fff;
            border: 1px solid #9e9e9e;
            border-radius: 4px;
            padding: 10px 12px;
            min-height: calc(100vh - 70px);
            box-sizing: border-box;
        }

        .fc-panel h1 {
            margin: 0 0 8px;
            font-size: 18px;
        }

        /* tabs + boxes */
        .fc-tab {
            display: inline-block;
            background: #e6e6e6;
            border: 1px solid #9e9e9e;
            border-bottom: none;
            padding: 3px 10px;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
            font-weight: bold;
            font-size: 14px;
            margin: 10px 0 0;
        }

        .fc-box {
            border: 1px solid #9e9e9e;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .fc-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 12px;
            align-items: center;
            padding: 6px 0;
        }

        .fc-row label {
            font-weight: bold;
            font-size: 14px;
        }

        .fc-input,
        .fc-select,
        .fc-textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #bdbdbd;
            border-radius: 4px;
            padding: 6px 8px;
            font-size: 14px;
            background: #fff;
        }

        .fc-textarea {
            min-height: 140px;
            resize: vertical;
            font-family: Consolas, "Courier New", monospace;
            font-size: 13px;
        }

        .fc-btn {
            border: 1px solid #7d7d7d;
            background: #e6e6e6;
            padding: 4px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .fc-btn:hover {
            background: #dcdcdc;
        }

        .fc-success {
            border: 1px solid #77c777;
            background: #e9ffe9;
            color: #135f13;
            padding: 6px 8px;
            border-radius: 4px;
            margin: 6px 0 10px;
        }
    </style>

    @stack('head')
</head>

<body>
    <div class="fc-topbar">
        <a href="https://www.freecause.com" target="_blank">https://www.freecause.com</a>
        <span class="sep">|</span> v 1.2.1
        <span class="sep">|</span> support
        <span class="sep">|</span> <strong>{{ session('admin_username', 'admin') }}</strong>
        <span class="sep">|</span>

        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="fc-btn">exit</button>
        </form>
    </div>

    <div class="fc-frame">
        @include('admin.partials.sidebar')

        <div class="fc-panel">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>

</html>
