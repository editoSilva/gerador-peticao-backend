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
    </style>
</head>
<body>
{!! nl2br(e($content)) !!}
{{-- <p style="text-align: center">
    Vitória da Conquista - BA, {{ \Carbon\Carbon::now() }}
</p> --}}

</br>
</br>
</br>
<p style="text-align: center">___________________________________________</p>
<p style="text-align: center">{{ $name }}</p>
</body>
</html>
