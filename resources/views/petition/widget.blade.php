<!doctype html>
<html lang="{{ $translation->locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; overflow: hidden; font-family: Arial, sans-serif; }
        .widget { width: 100%; height: 100vh; border: 1px solid #ccc; border-radius: 6px; background: #fff; overflow: hidden; padding: 8px; }
        .logo { display: block; height: 28%; background: url('{{ asset("legacy/images/widget-pbadge-{$type}-logo.png") }}') left top / contain no-repeat; }
        .title { display: block; color: #a00117; overflow: hidden; text-decoration: none; white-space: nowrap; text-overflow: ellipsis; }
        .progress { height: 24%; margin: 5px 0; background: #777; border-radius: 4px; overflow: hidden; }
        .progress span { display: block; width: {{ min(100, $petition->goal_signatures ? ($petition->signature_count / $petition->goal_signatures) * 100 : 0) }}%; height: 100%; background: #3a9b47; }
        .counts { text-align: center; font-size: 12px; }
        .sign { display: block; margin: 4px auto 0; padding: 4px 12px; width: max-content; background: #a00117; color: white; border-radius: 4px; font-weight: bold; text-decoration: none; text-transform: uppercase; }
        @if ($type === '125x125') .title { display: none; } .widget { padding: 4px; } .logo { height: 22%; } @endif
    </style>
</head>
<body>
    <div class="widget">
        <a class="logo" href="{{ url('/') }}" target="_blank" rel="noopener"></a>
        <a class="title" href="{{ lroute('petition.show', ['locale' => $translation->locale, 'slug' => $translation->slug, 'id' => $petition->id]) }}" target="_blank" rel="noopener">{{ $translation->title }}</a>
        <div class="counts">{{ __('pet.widget_signatures') }} {{ number_format($petition->signature_count) }} / {{ __('pet.widget_goal') }} {{ number_format($petition->goal_signatures) }}</div>
        <div class="progress"><span></span></div>
        <a class="sign" href="{{ lroute('petition.show', ['locale' => $translation->locale, 'slug' => $translation->slug, 'id' => $petition->id]) }}" target="_blank" rel="noopener">{{ __('pet.widget_sign') }}</a>
    </div>
</body>
</html>
