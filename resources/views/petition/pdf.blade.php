<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $tr->title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.6;
        }

        h1 {
            font-size: 18px;
            margin-bottom: 8px;
        }

        h2 {
            font-size: 14px;
            margin-top: 24px;
        }

        .muted {
            color: #777;
            font-size: 11px;
        }

        .signature {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

    <h1>{{ $tr->title }}</h1>
    <div class="muted">
        URL: {{ url("/{$locale}/petition/{$tr->slug}/{$petition->id}") }}<br>
        Total signatures: {{ $petition->signature_count }}
    </div>

    <h2>Description</h2>
    {!! $tr->description !!}

    <h2>Latest Signatures</h2>

    @forelse($signatures as $sig)
        <div class="signature">
            <strong>{{ $sig->name ?? 'Anonymous' }}</strong>
            <span class="muted">
                ({{ optional($sig->created_at)->format('Y-m-d') }})
            </span><br>
            {{ $sig->comment ?? 'I support this petition' }}
        </div>
    @empty
        <p>No signatures yet.</p>
    @endforelse

</body>

</html>
