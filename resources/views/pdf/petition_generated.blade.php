<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            text-align: justify;
            margin: 20px;
            line-height: 1.5;
            color: #000;
        }
        p {
            margin-bottom: 12px;
        }

        .no-break {
            page-break-inside: avoid;
        }

        .signature-block {
            margin-top: 80px;
            text-align: center;
            page-break-inside: avoid;
        }

        .anexo {
            page-break-before: always;
            text-align: center;
        }

        h3 {
            page-break-after: avoid;
        }

    </style>
</head>
<body>
{!! nl2br(e($content)) !!}

<div class="signature-block">
    <p>{{ $cidade }} - {{ $estado }}, {{ ucfirst(\Carbon\Carbon::now()->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y')) }}</p>
    <br><br>
    <p>___________________________________________</p>
    <p>{{ $name }}</p>
</div>

@if (!empty($attachments))

    @foreach ($attachments as $img)
        <div class="anexo">
            <img src="{{ $img }}" style="max-width: 100%; max-height: 100%;" />
        </div>
    @endforeach
@endif

</body>
</html>
