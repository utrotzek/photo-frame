<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Foto Rahmen</title>
    <meta name="description" content="Login Page">
    <meta name="viewport" content="height=device-height,
                      width=device-width, initial-scale=1.0,
                      minimum-scale=1.0, maximum-scale=1.0,
                      user-scalable=no, target-densitydpi=device-dpi">

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div id="app">
        <div>
            <app></app>
        </div>
    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>
